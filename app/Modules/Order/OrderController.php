<?php

namespace App\Modules\Order;

use App\Core\Database;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\View;

class OrderController {
    public function checkout(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            Session::setFlash('error', 'Please log in to complete checkout.');
            redirect(url('/login'));
        }

        CSRF::verifyOrDie();

        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $paymentMethod = trim($_POST['payment_method'] ?? 'COD');
        $paymentType = trim($_POST['payment_type'] ?? 'full'); // 'full' or 'partial'
        $customPaidAmount = isset($_POST['paid_amount']) ? format_money($_POST['paid_amount']) : null;

        // Process Payment Proof Screenshot Upload (if provided)
        $paymentProof = null;
        if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $fileTmpPath = $_FILES['payment_proof']['tmp_name'];
            $fileName = $_FILES['payment_proof']['name'];
            $fileSize = $_FILES['payment_proof']['size'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($ext, $allowedExts, true) && $fileSize <= 10 * 1024 * 1024) {
                $uploadDir = __DIR__ . '/../../../public/uploads/payment_proofs/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                $newFileName = 'proof_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
                $destPath = $uploadDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $paymentProof = '/uploads/payment_proofs/' . $newFileName;
                }
            }
        }

        // Auto-fetch registration details if customer details empty
        $user = Database::fetchOne("SELECT name, phone FROM users WHERE id = :uid LIMIT 1", ['uid' => $userId]);
        if (empty($customerName) && $user) {
            $customerName = $user['name'];
        }
        if (empty($customerPhone) && $user) {
            $customerPhone = $user['phone'];
        }

        if (empty($shippingAddress)) {
            Session::setFlash('error', 'Please provide a valid shipping address.');
            redirect(url('/cart'));
        }

        $sessionId = session_id();
        $cart = Database::fetchOne("SELECT id FROM carts WHERE (user_id IS NOT NULL AND user_id = :uid) OR session_id = :sid ORDER BY id DESC LIMIT 1", [
            'uid' => $userId ?: 0,
            'sid' => $sessionId
        ]);

        if (!$cart) {
            Session::setFlash('error', 'Your shopping cart is empty.');
            redirect(url('/cart'));
        }

        $cartId = (int)$cart['id'];

        $cartItems = Database::fetchAll("SELECT product_id, quantity, selected_size, selected_color FROM cart_items WHERE cart_id = :cid", ['cid' => $cartId]);

        if (empty($cartItems)) {
            Session::setFlash('error', 'Your cart has no valid items.');
            redirect(url('/cart'));
        }

        Database::beginTransaction();
        try {
            $totalAmount = "0.00";
            $totalCommission = "0.00";
            $preparedItems = [];

            foreach ($cartItems as $cItem) {
                $pid = (int)$cItem['product_id'];
                $qty = (int)$cItem['quantity'];

                // 1. Concurrency Guard: SELECT FOR UPDATE pessimistic lock
                $product = Database::fetchOne(
                    "SELECT id, title, price, discount_price, stock_quantity, commission_rate, status 
                     FROM products 
                     WHERE id = :id FOR UPDATE",
                    ['id' => $pid]
                );

                if (!$product || $product['status'] !== 'active') {
                    throw new \Exception("Product is no longer available.");
                }

                if ($qty > (int)$product['stock_quantity']) {
                    throw new \Exception("Item '" . $product['title'] . "' has insufficient stock (Requested: {$qty}, Available: {$product['stock_quantity']}).");
                }

                // 2. Decimal-safe Money & Per-Item Commission Calculations (BCMath)
                $unitPrice = !empty($product['discount_price']) ? format_money($product['discount_price']) : format_money($product['price']);
                $subtotal = money_mul($unitPrice, (string)$qty);
                $totalAmount = money_add($totalAmount, $subtotal);

                // Per-Item Commission: item_subtotal * (commission_rate / 100)
                $commRate = format_money($product['commission_rate']);
                $commRateRatio = bcdiv($commRate, "100.00", 6);
                $itemCommission = bcmul($subtotal, $commRateRatio, 2);
                $totalCommission = money_add($totalCommission, $itemCommission);

                $preparedItems[] = [
                    'product_id' => $pid,
                    'title' => $product['title'],
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                    'selected_size' => $cItem['selected_size'] ?? null,
                    'selected_color' => $cItem['selected_color'] ?? null,
                    'item_commission' => $itemCommission
                ];
            }

            // Financial Validation: Calculate Paid and Due amounts
            if ($paymentType === 'full' && $customPaidAmount === null) {
                $paidAmount = ($paymentMethod === 'COD') ? "0.00" : $totalAmount;
            } else {
                $paidAmount = $customPaidAmount ?? "0.00";
            }

            if (money_comp($paidAmount, $totalAmount) > 0) {
                $paidAmount = $totalAmount;
            }

            $dueAmount = money_sub($totalAmount, $paidAmount);

            // Determine Payment Status
            if (money_comp($paidAmount, $totalAmount) >= 0) {
                $paymentStatus = 'paid';
            } elseif (money_comp($paidAmount, "0.00") > 0) {
                $paymentStatus = 'partially_paid';
            } else {
                $paymentStatus = 'unpaid';
            }

            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

            // Insert Order record with customer info & financial status
            Database::query(
                "INSERT INTO orders (order_number, user_id, customer_name, customer_phone, total_amount, paid_amount, due_amount, payment_method, payment_type, payment_status, order_status, shipping_address, payment_proof) 
                 VALUES (:order_no, :uid, :cname, :cphone, :total, :paid, :due, :pmethod, :ptype, :pstatus, 'pending', :addr, :proof)",
                [
                    'order_no' => $orderNumber,
                    'uid' => $userId,
                    'cname' => $customerName,
                    'cphone' => $customerPhone,
                    'total' => $totalAmount,
                    'paid' => $paidAmount,
                    'due' => $dueAmount,
                    'pmethod' => $paymentMethod,
                    'ptype' => $paymentType,
                    'pstatus' => $paymentStatus,
                    'addr' => $shippingAddress,
                    'proof' => $paymentProof
                ]
            );
            $orderId = (int)Database::lastInsertId();

            // Insert Order Items with selected variants
            foreach ($preparedItems as $pItem) {
                Database::query(
                    "INSERT INTO order_items (order_id, product_id, product_title_snapshot, unit_price_snapshot, quantity, subtotal, selected_size, selected_color)
                     VALUES (:oid, :pid, :title, :price, :qty, :subtotal, :size, :color)",
                    [
                        'oid' => $orderId,
                        'pid' => $pItem['product_id'],
                        'title' => $pItem['title'],
                        'price' => $pItem['unit_price'],
                        'qty' => $pItem['quantity'],
                        'subtotal' => $pItem['subtotal'],
                        'size' => $pItem['selected_size'],
                        'color' => $pItem['selected_color']
                    ]
                );

                // Concurrency Guard: Atomic UPDATE with condition check
                $stmt = Database::query(
                    "UPDATE products SET stock_quantity = stock_quantity - :qty WHERE id = :pid AND stock_quantity >= :qty_check AND status = 'active'",
                    ['qty' => $pItem['quantity'], 'qty_check' => $pItem['quantity'], 'pid' => $pItem['product_id']]
                );

                if ($stmt->rowCount() === 0) {
                    throw new \Exception("Stock conflict: Item '" . $pItem['title'] . "' was purchased concurrently by another user.");
                }
            }

            // Affiliate Conversion Tracking (if referred)
            $referredLinkId = Session::get('referred_affiliate_link_id');
            if ($referredLinkId) {
                $link = Database::fetchOne("SELECT id, affiliate_profile_id FROM affiliate_links WHERE id = :id LIMIT 1", ['id' => $referredLinkId]);
                if ($link) {
                    Database::query(
                        "INSERT INTO conversions (affiliate_link_id, order_id, order_total, status) 
                         VALUES (:alid, :oid, :total, 'pending')",
                        [
                            'alid' => $link['id'],
                            'oid' => $orderId,
                            'total' => $totalAmount
                        ]
                    );
                    $conversionId = (int)Database::lastInsertId();

                    // Insert Affiliate Commission record using sum of per-item commissions
                    Database::query(
                        "INSERT INTO affiliate_commissions (conversion_id, affiliate_profile_id, commission_amount, status) 
                         VALUES (:cid, :apid, :amt, 'pending')",
                        [
                            'cid' => $conversionId,
                            'apid' => $link['affiliate_profile_id'],
                            'amt' => $totalCommission
                        ]
                    );
                }
            }

            // Clear Cart items
            Database::query("DELETE FROM cart_items WHERE cart_id = :cid", ['cid' => $cartId]);

            // Audit log
            audit_log("order_placed", "Order #{$orderNumber} placed for total BDT {$totalAmount}", $userId);

            Database::commit();

            Session::setFlash('success', "Order placed successfully! Order #" . $orderNumber);
            redirect(url('/my-orders'));
        } catch (\Exception $e) {
            Database::rollBack();
            Session::setFlash('error', $e->getMessage());
            redirect(url('/cart'));
        }
    }

    public function myOrders(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            Session::setFlash('error', 'Please log in to view your orders.');
            redirect(url('/login'));
        }

        $orders = Database::fetchAll(
            "SELECT o.id, o.order_number, o.total_amount, 
                    o.payment_status, o.order_status, 
                    o.shipping_address, o.created_at 
             FROM orders o 
             WHERE o.user_id = :uid 
             ORDER BY o.id DESC",
            ['uid' => $userId]
        );

        foreach ($orders as &$o) {
            $o['customer_name'] = Session::get('user_name');
            $o['customer_phone'] = '';
            $o['payment_method'] = 'Wallet';
            $o['payment_type'] = 'full';
            if ($o['payment_status'] === 'paid') {
                $o['paid_amount'] = $o['total_amount'];
                $o['due_amount'] = '0.00';
            } else {
                $o['paid_amount'] = '0.00';
                $o['due_amount'] = $o['total_amount'];
            }
            $o['items'] = Database::fetchAll(
                "SELECT product_title_snapshot, unit_price_snapshot, quantity, subtotal 
                 FROM order_items WHERE order_id = :oid",
                ['oid' => $o['id']]
            );
        }
        unset($o);

        View::render('pages/my_orders', [
            'title' => 'My Orders & Order History - Daraz Affiliate Platform',
            'orders' => $orders,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }
}
