<?php

namespace App\Modules\Product;

use App\Core\Database;
use App\Core\Cache;
use App\Core\View;

class ProductController {
    public function show(string $id): void {
        $productId = (int)$id;

        $cacheKey = "product_detail_" . $productId;
        $product = Cache::get($cacheKey);

        if (!$product) {
            $sql = "SELECT p.id, p.category_id, p.title, p.slug, p.sku, p.price, p.discount_price, p.stock_quantity, 
                           p.commission_rate, p.short_description, p.full_description, p.main_image, p.status,
                           c.name as category_name, c.slug as category_slug
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.id = :id AND p.status = 'active' LIMIT 1";
            
            $product = Database::fetchOne($sql, ['id' => $productId]);

            if ($product) {
                Cache::set($cacheKey, $product, 1800);
            }
        }

        if (!$product) {
            http_response_code(404);
            View::render('pages/404', ['title' => 'Product Not Found']);
            return;
        }

        // Fetch additional product images
        $images = Database::fetchAll("SELECT image_path FROM product_images WHERE product_id = :id ORDER BY sort_order ASC", ['id' => $productId]);

        View::render('pages/product', [
            'title' => e($product['title']) . ' - Daraz Affiliate Platform',
            'product' => $product,
            'images' => $images
        ]);
    }
}
