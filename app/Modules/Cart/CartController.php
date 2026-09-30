<?php

namespace App\Modules\Cart;

use App\Core\Database;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\View;

class CartController {
    private function getCartId(): int {
        Session::start();
        $sessionId = session_id();
        $userId = Session::get('user_id');

        $cart = Database::fetchOne("SELECT id, user_id FROM carts WHERE session_id = :sid OR (user_id IS NOT NULL AND user_id = :uid) LIMIT 1", [
            'sid' => $sessionId,
            'uid' => $userId ?: 0
        ]);

        if ($cart) {
            if ($userId && !$cart['user_id']) {
                Database::query("UPDATE carts SET user_id = :uid WHERE id = :id", ['uid' => $userId, 'id' => $cart['id']]);
            }
            return (int)$cart['id'];
        }

        Database::query("INSERT INTO carts (user_id, session_id) VALUES (:uid, :sid)", [
            'uid' => $userId ?: null,
            'sid' => $sessionId
        ]);

        return (int)Database::lastInsertId();
    }

    public function index(): void {
        $cartId = $this->getCartId();
        Session::start();
        $userId = Session::get('user_id');
        $user = null;
        if ($userId) {
            $user = Database::fetchOne("SELECT id, name, phone, email FROM users WHERE id = :id LIMIT 1", ['id' => $userId]);
        }

        $sql = "SELECT ci.id as cart_item_id, ci.quantity, ci.selected_size, ci.selected_color, p.id as product_id, p.title, p.price, p.discount_price, p.main_image, p.stock_quantity 
                FROM cart_items ci 
                JOIN products p ON ci.product_id = p.id 
                WHERE ci.cart_id = :cart_id AND p.status = 'active'";
        
        $items = Database::fetchAll($sql, ['cart_id' => $cartId]);

        $subtotal = "0.00";
        foreach ($items as &$item) {
            $effectivePrice = !empty($item['discount_price']) ? format_money($item['discount_price']) : format_money($item['price']);
            $item['effective_price'] = $effectivePrice;
            $item['item_subtotal'] = money_mul($effectivePrice, (string)$item['quantity']);
            $subtotal = money_add($subtotal, $item['item_subtotal']);
        }

        View::render('pages/cart', [
            'title' => 'Your Shopping Cart - Daraz Affiliate Platform',
            'items' => $items,
            'subtotal' => $subtotal,
            'user' => $user,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function add(): void {
        CSRF::verifyOrDie();
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $selectedSize = trim($_POST['selected_size'] ?? '');
        $selectedColor = trim($_POST['selected_color'] ?? '');

        $product = Database::fetchOne("SELECT id, stock_quantity, status FROM products WHERE id = :id LIMIT 1", ['id' => $productId]);

        if (!$product || $product['status'] !== 'active') {
            Session::setFlash('error', 'Product is unavailable.');
            redirect(url('/cart'));
        }

        if ($quantity > (int)$product['stock_quantity']) {
            Session::setFlash('error', 'Requested quantity exceeds available stock.');
            redirect(url('/cart'));
        }

        $cartId = $this->getCartId();

        $existing = Database::fetchOne("SELECT id, quantity FROM cart_items WHERE cart_id = :cid AND product_id = :pid AND (selected_size = :size OR (selected_size IS NULL AND :size2 = '')) AND (selected_color = :color OR (selected_color IS NULL AND :color2 = '')) LIMIT 1", [
            'cid' => $cartId,
            'pid' => $productId,
            'size' => $selectedSize ?: null,
            'size2' => $selectedSize,
            'color' => $selectedColor ?: null,
            'color2' => $selectedColor
        ]);

        if ($existing) {
            $newQty = (int)$existing['quantity'] + $quantity;
            if ($newQty > (int)$product['stock_quantity']) {
                $newQty = (int)$product['stock_quantity'];
            }
            Database::query("UPDATE cart_items SET quantity = :qty WHERE id = :id", [
                'qty' => $newQty,
                'id' => $existing['id']
            ]);
        } else {
            Database::query("INSERT INTO cart_items (cart_id, product_id, quantity, selected_size, selected_color) VALUES (:cid, :pid, :qty, :size, :color)", [
                'cid' => $cartId,
                'pid' => $productId,
                'qty' => $quantity,
                'size' => $selectedSize ?: null,
                'color' => $selectedColor ?: null
            ]);
        }

        Session::setFlash('success', 'Product added to cart!');
        redirect(url('/cart'));
    }

    public function update(): void {
        CSRF::verifyOrDie();

        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        $cartId = $this->getCartId();

        $item = Database::fetchOne("SELECT ci.id, ci.product_id, p.stock_quantity FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.id = :id AND ci.cart_id = :cid LIMIT 1", [
            'id' => $cartItemId,
            'cid' => $cartId
        ]);

        if ($item) {
            if ($quantity > (int)$item['stock_quantity']) {
                Session::setFlash('error', 'Quantity exceeds available stock.');
            } else {
                Database::query("UPDATE cart_items SET quantity = :qty WHERE id = :id", [
                    'qty' => $quantity,
                    'id' => $cartItemId
                ]);
                Session::setFlash('success', 'Cart updated successfully.');
            }
        }

        redirect(url('/cart'));
    }

    public function remove(): void {
        CSRF::verifyOrDie();

        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $cartId = $this->getCartId();

        Database::query("DELETE FROM cart_items WHERE id = :id AND cart_id = :cid", [
            'id' => $cartItemId,
            'cid' => $cartId
        ]);

        Session::setFlash('success', 'Item removed from cart.');
        redirect(url('/cart'));
    }
}
