<?php

namespace App\Modules\Profile;

use App\Core\Database;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\View;

class ProfileController {
    public function index(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            redirect(url('/login'));
        }

        $user = Database::fetchOne("SELECT id, name, email, phone, role, status, credit_current, credit_max, otp_code, is_verified, created_at FROM users WHERE id = :id LIMIT 1", ['id' => $userId]);
        $wallet = Database::fetchOne("SELECT balance, pending_balance FROM wallets WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);
        $orders = Database::fetchAll("SELECT id, order_number, total_amount, paid_amount, due_amount, payment_method, payment_status, order_status, created_at FROM orders WHERE user_id = :uid ORDER BY id DESC LIMIT 5", ['uid' => $userId]);

        View::render('pages/profile', [
            'title' => 'My Account & Task Dashboard',
            'user' => $user,
            'wallet' => $wallet,
            'orders' => $orders,
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success')
        ]);
    }

    public function completeTask(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            redirect(url('/login'));
        }

        CSRF::verifyOrDie();

        $user = Database::fetchOne("SELECT credit_current, credit_max FROM users WHERE id = :id LIMIT 1", ['id' => $userId]);
        if (!$user) {
            redirect(url('/profile'));
        }

        $current = (int)$user['credit_current'];
        $max = (int)$user['credit_max'];

        if ($max <= 0) {
            Session::setFlash('error', 'No task credits assigned by Admin yet. Please request Admin to grant credit quota.');
            redirect(url('/profile'));
        }

        if ($current >= $max) {
            Session::setFlash('error', "All assigned task credits ({$current} / {$max}) already completed for today!");
            redirect(url('/profile'));
        }

        $newCredit = $current + 1;
        Database::query("UPDATE users SET credit_current = :nc WHERE id = :uid", [
            'nc' => $newCredit,
            'uid' => $userId
        ]);

        audit_log("user_task_completed", "User #{$userId} completed a task. Credits updated to {$newCredit} / {$max}", $userId);
        Session::setFlash('success', "Task completed successfully! Credits: {$newCredit} / {$max}");
        redirect(url('/profile'));
    }

    public function showOTPVerify(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            redirect(url('/login'));
        }

        $user = Database::fetchOne("SELECT id, name, email, phone, role, status, is_verified FROM users WHERE id = :id LIMIT 1", ['id' => $userId]);
        if (!$user) {
            redirect(url('/login'));
        }

        if ($user['is_verified']) {
            redirect(url('/profile'));
        }

        View::render('pages/otp_verify', [
            'title' => 'Activate Account (Admin OTP)',
            'user' => $user,
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success')
        ]);
    }

    public function verifyOTP(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            redirect(url('/login'));
        }

        CSRF::verifyOrDie();

        $inputOTP = trim($_POST['otp_code'] ?? '');
        $user = Database::fetchOne("SELECT otp_code, is_verified FROM users WHERE id = :id LIMIT 1", ['id' => $userId]);

        if (empty($inputOTP)) {
            Session::setFlash('error', 'Please enter the verification OTP.');
            redirect(url('/verify-otp'));
        }

        if (empty($user['otp_code'])) {
            Session::setFlash('error', 'No OTP code generated for your account. Please ask Admin to send OTP.');
            redirect(url('/verify-otp'));
        }

        if (hash_equals((string)$user['otp_code'], $inputOTP)) {
            Database::query("UPDATE users SET is_verified = 1, otp_code = NULL WHERE id = :uid", ['uid' => $userId]);
            audit_log("user_otp_verified", "User #{$userId} successfully verified OTP code.", $userId);
            Session::setFlash('success', 'Account successfully activated with Admin OTP!');
            redirect(url('/profile'));
        } else {
            Session::setFlash('error', 'Invalid OTP code. Please check with Admin for your unique OTP.');
            redirect(url('/verify-otp'));
        }
    }
}
