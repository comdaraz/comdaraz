<?php

namespace App\Modules\Wallet;

use App\Core\Database;
use App\Core\Session;
use App\Core\View;

class WalletController {
    public function show(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            redirect(url('/login'));
        }

        // Verify user exists in database (handles stale session cookie after re-seed)
        $userExists = Database::fetchOne("SELECT id FROM users WHERE id = :uid LIMIT 1", ['uid' => $userId]);
        if (!$userExists) {
            Session::destroy();
            redirect(url('/login'));
        }

        // Fresh balance check (NEVER CACHED)
        $wallet = Database::fetchOne("SELECT id, balance, pending_balance, currency, status FROM wallets WHERE user_id = :uid LIMIT 1", [
            'uid' => $userId
        ]);

        if (!$wallet) {
            Database::query("INSERT INTO wallets (user_id, balance, pending_balance) VALUES (:uid, 0.00, 0.00)", ['uid' => $userId]);
            $wallet = Database::fetchOne("SELECT id, balance, pending_balance, currency, status FROM wallets WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);
        }

        $transactions = Database::fetchAll(
            "SELECT reference_id, type, amount, balance_before, balance_after, description, status, created_at 
             FROM wallet_transactions 
             WHERE wallet_id = :wid 
             ORDER BY id DESC LIMIT 20",
            ['wid' => $wallet['id']]
        );

        $withdrawals = Database::fetchAll(
            "SELECT reference_no, amount, method, status, rejection_reason, created_at 
             FROM withdrawals 
             WHERE user_id = :uid 
             ORDER BY id DESC LIMIT 10",
            ['uid' => $userId]
        );

        View::render('pages/wallet', [
            'title' => 'My Financial Wallet - Daraz Affiliate Platform',
            'wallet' => $wallet,
            'transactions' => $transactions,
            'withdrawals' => $withdrawals,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Centralized Atomic Ledger Balance Transaction
     * Enforces strict BCMath decimal math, transaction type sign rules, idempotency, and non-negative balance.
     */
    public static function processLedgerTransaction(int $walletId, string $referenceId, string $type, string $amountStr, string $description): bool {
        $amountClean = format_money($amountStr);

        $allowedTypes = ['recharge', 'withdrawal', 'commission_payout', 'purchase_payment', 'refund'];
        if (!in_array($type, $allowedTypes, true)) {
            error_log("Ledger error: Invalid transaction type '{$type}'.");
            return false;
        }

        // Amount Sign Rules
        if (in_array($type, ['recharge', 'commission_payout', 'refund'], true)) {
            if (money_comp($amountClean, "0.00") <= 0) {
                error_log("Ledger error: Transaction type '{$type}' requires a positive credit amount.");
                return false;
            }
        } elseif (in_array($type, ['withdrawal', 'purchase_payment'], true)) {
            if (money_comp($amountClean, "0.00") >= 0) {
                error_log("Ledger error: Transaction type '{$type}' requires a negative debit amount.");
                return false;
            }
        }

        // Idempotency check: Prevent duplicate processing via unique reference_id
        $existing = Database::fetchOne("SELECT id FROM wallet_transactions WHERE reference_id = :ref LIMIT 1", ['ref' => $referenceId]);
        if ($existing) {
            return false; // Already processed
        }

        Database::beginTransaction();
        try {
            // Pessimistic locking FOR UPDATE
            $wallet = Database::fetchOne("SELECT id, balance, status FROM wallets WHERE id = :wid FOR UPDATE", ['wid' => $walletId]);
            if (!$wallet || $wallet['status'] !== 'active') {
                throw new \Exception("Wallet not found or frozen.");
            }

            $balanceBefore = format_money($wallet['balance']);
            $balanceAfter = money_add($balanceBefore, $amountClean);

            if (money_comp($balanceAfter, "0.00") < 0) {
                throw new \Exception("Insufficient wallet balance for transaction. Balance cannot fall below zero.");
            }

            // Update wallet balance atomically
            Database::query("UPDATE wallets SET balance = :bal WHERE id = :wid", [
                'bal' => $balanceAfter,
                'wid' => $walletId
            ]);

            // Create Immutable Ledger Record
            Database::query(
                "INSERT INTO wallet_transactions (wallet_id, reference_id, type, amount, balance_before, balance_after, description, status) 
                 VALUES (:wid, :ref, :type, :amt, :before, :after, :desc, 'completed')",
                [
                    'wid' => $walletId,
                    'ref' => $referenceId,
                    'type' => $type,
                    'amt' => $amountClean,
                    'before' => $balanceBefore,
                    'after' => $balanceAfter,
                    'desc' => $description
                ]
            );

            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollBack();
            error_log("Ledger error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve Pending Recharge Request (Guarded against duplicate approval)
     */
    public static function approveRecharge(int $rechargeId): bool {
        Database::beginTransaction();
        try {
            $recharge = Database::fetchOne("SELECT id, user_id, reference_no, amount, status FROM recharges WHERE id = :id FOR UPDATE", ['id' => $rechargeId]);
            if (!$recharge || $recharge['status'] !== 'pending') {
                throw new \Exception("Recharge request unavailable or already processed.");
            }

            $wallet = Database::fetchOne("SELECT id FROM wallets WHERE user_id = :uid FOR UPDATE", ['uid' => $recharge['user_id']]);
            if (!$wallet) {
                throw new \Exception("User wallet not found.");
            }

            $amountStr = format_money($recharge['amount']);
            $refId = 'REC-' . $recharge['reference_no'];

            $success = self::processLedgerTransaction(
                (int)$wallet['id'],
                $refId,
                'recharge',
                $amountStr,
                "Approved Wallet Recharge #" . $recharge['reference_no']
            );

            if ($success) {
                Database::query("UPDATE recharges SET status = 'completed' WHERE id = :id", ['id' => $rechargeId]);
                audit_log("recharge_approved", "Recharge #{$recharge['reference_no']} approved for user #{$recharge['user_id']}");
                Database::commit();
                return true;
            } else {
                throw new \Exception("Ledger transaction failed.");
            }
        } catch (\Exception $e) {
            Database::rollBack();
            error_log("Recharge approval error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve Pending Withdrawal Request (Guarded against duplicate approval)
     */
    public static function approveWithdrawal(int $withdrawalId): bool {
        Database::beginTransaction();
        try {
            $withdrawal = Database::fetchOne("SELECT id, user_id, reference_no, amount, status FROM withdrawals WHERE id = :id FOR UPDATE", ['id' => $withdrawalId]);
            if (!$withdrawal || $withdrawal['status'] !== 'pending') {
                throw new \Exception("Withdrawal request unavailable or already processed.");
            }

            $wallet = Database::fetchOne("SELECT id FROM wallets WHERE user_id = :uid FOR UPDATE", ['uid' => $withdrawal['user_id']]);
            if (!$wallet) {
                throw new \Exception("User wallet not found.");
            }

            $amountStr = format_money($withdrawal['amount']);
            $negativeAmount = money_sub("0.00", $amountStr);
            $refId = 'WTH-' . $withdrawal['reference_no'];

            $success = self::processLedgerTransaction(
                (int)$wallet['id'],
                $refId,
                'withdrawal',
                $negativeAmount,
                "Approved Wallet Withdrawal #" . $withdrawal['reference_no']
            );

            if ($success) {
                Database::query("UPDATE withdrawals SET status = 'approved' WHERE id = :id", ['id' => $withdrawalId]);
                audit_log("withdrawal_approved", "Withdrawal #{$withdrawal['reference_no']} approved for user #{$withdrawal['user_id']}");
                Database::commit();
                return true;
            } else {
                throw new \Exception("Ledger transaction failed.");
            }
        } catch (\Exception $e) {
            Database::rollBack();
            error_log("Withdrawal approval error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve & Credit Pending Commission (Guarded against duplicate approval)
     */
    public static function approveCommission(int $commissionId): bool {
        Database::beginTransaction();
        try {
            $comm = Database::fetchOne("SELECT id, conversion_id, affiliate_profile_id, commission_amount, status FROM affiliate_commissions WHERE id = :id FOR UPDATE", ['id' => $commissionId]);
            if (!$comm || $comm['status'] !== 'pending') {
                throw new \Exception("Commission unavailable or already processed.");
            }

            $affProfile = Database::fetchOne("SELECT user_id FROM affiliate_profiles WHERE id = :apid LIMIT 1", ['apid' => $comm['affiliate_profile_id']]);
            if (!$affProfile) {
                throw new \Exception("Affiliate profile not found.");
            }

            $wallet = Database::fetchOne("SELECT id FROM wallets WHERE user_id = :uid FOR UPDATE", ['uid' => $affProfile['user_id']]);
            if (!$wallet) {
                throw new \Exception("Affiliate wallet not found.");
            }

            $amountStr = format_money($comm['commission_amount']);
            $refId = 'COM-' . $comm['id'] . '-' . $comm['conversion_id'];

            $success = self::processLedgerTransaction(
                (int)$wallet['id'],
                $refId,
                'commission_payout',
                $amountStr,
                "Affiliate Commission Payout for Conversion #" . $comm['conversion_id']
            );

            if ($success) {
                Database::query("UPDATE affiliate_commissions SET status = 'paid' WHERE id = :id", ['id' => $commissionId]);
                Database::query("UPDATE conversions SET status = 'approved' WHERE id = :id", ['id' => $comm['conversion_id']]);
                audit_log("commission_approved", "Commission #{$commissionId} approved for user #{$affProfile['user_id']}");
                Database::commit();
                return true;
            } else {
                throw new \Exception("Ledger transaction failed.");
            }
        } catch (\Exception $e) {
            Database::rollBack();
            error_log("Commission credit error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * User Submit Deposit / Recharge Request
     */
    public function handleRecharge(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            redirect(url('/login'));
        }

        \App\Core\CSRF::verifyOrDie();

        $amountInput = trim($_POST['amount'] ?? '0.00');
        $amount = format_money($amountInput);
        $method = trim($_POST['payment_method'] ?? 'bKash');
        $senderNumber = trim($_POST['sender_number'] ?? '');
        $trxRef = trim($_POST['transaction_ref'] ?? '');

        if (money_comp($amount, "10.00") < 0) {
            Session::setFlash('error', 'Minimum deposit amount is BDT 10.00.');
            redirect(url('/wallet'));
        }

        if (empty($senderNumber)) {
            Session::setFlash('error', 'Please provide the Sender Number / Account / Binance Pay ID money was sent from.');
            redirect(url('/wallet'));
        }

        // Process Payment Proof Screenshot Upload
        $paymentProof = null;
        if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $fileTmpPath = $_FILES['payment_proof']['tmp_name'];
            $fileName = $_FILES['payment_proof']['name'];
            $fileSize = $_FILES['payment_proof']['size'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($ext, $allowedExts, true) && $fileSize <= 10 * 1024 * 1024) {
                $uploadDir = __DIR__ . '/../../../public/uploads/recharge_proofs/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                $newFileName = 'recharge_proof_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
                $destPath = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $paymentProof = '/uploads/recharge_proofs/' . $newFileName;
                }
            }
        }

        $refNo = 'RECH-' . time() . '-' . rand(100, 999);
        $methodDetails = $method;
        if (!empty($senderNumber)) {
            $methodDetails .= " | Sender: " . $senderNumber;
        }
        if (!empty($trxRef)) {
            $methodDetails .= " | TrxID: " . $trxRef;
        }
        $methodDetails = mb_substr($methodDetails, 0, 250);

        Database::query(
            "INSERT INTO recharges (user_id, reference_no, amount, method, payment_proof, status) VALUES (:uid, :ref, :amt, :method, :proof, 'pending')",
            [
                'uid' => $userId,
                'ref' => $refNo,
                'amt' => $amount,
                'method' => $methodDetails,
                'proof' => $paymentProof
            ]
        );

        audit_log("user_recharge_request", "User #{$userId} submitted deposit request of BDT {$amount} via {$methodDetails}", $userId);
        Session::setFlash('success', "Deposit request of ৳{$amount} submitted successfully! Pending admin approval.");
        redirect(url('/wallet'));
    }

    /**
     * User Submit Withdrawal Request
     */
    public function handleWithdrawal(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            redirect(url('/login'));
        }

        \App\Core\CSRF::verifyOrDie();

        $amountInput = trim($_POST['amount'] ?? '0.00');
        $amount = format_money($amountInput);
        $method = trim($_POST['payment_method'] ?? 'bKash');
        $accountInfo = trim($_POST['account_details'] ?? '');

        if (money_comp($amount, "50.00") < 0) {
            Session::setFlash('error', 'Minimum withdrawal amount is BDT 50.00.');
            redirect(url('/wallet'));
        }

        if (empty($accountInfo)) {
            Session::setFlash('error', 'Please provide account number / details.');
            redirect(url('/wallet'));
        }

        $wallet = Database::fetchOne("SELECT balance FROM wallets WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);
        $currentBalance = format_money($wallet['balance'] ?? '0.00');

        if (money_comp($amount, $currentBalance) > 0) {
            Session::setFlash('error', 'Requested withdrawal amount exceeds your available wallet balance.');
            redirect(url('/wallet'));
        }

        $refNo = 'WTH-' . time() . '-' . rand(100, 999);

        Database::query(
            "INSERT INTO withdrawals (user_id, reference_no, amount, method, account_info, status) VALUES (:uid, :ref, :amt, :method, :info, 'pending')",
            [
                'uid' => $userId,
                'ref' => $refNo,
                'amt' => $amount,
                'method' => $method,
                'info' => $accountInfo
            ]
        );

        audit_log("user_withdrawal_request", "User #{$userId} submitted withdrawal request of BDT {$amount}", $userId);
        Session::setFlash('success', "Withdrawal request of ৳{$amount} submitted successfully! Pending admin approval.");
        redirect(url('/wallet'));
    }
}
