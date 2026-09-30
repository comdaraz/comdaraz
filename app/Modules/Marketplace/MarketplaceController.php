<?php

namespace App\Modules\Marketplace;

use App\Core\Database;
use App\Core\Cache;
use App\Core\View;

class MarketplaceController {
    public function index(): void {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 8;
        $offset = ($page - 1) * $limit;

        $categorySlug = $_GET['category'] ?? null;
        $search = trim($_GET['q'] ?? '');

        // Fetch categories with consistent Cache API callback fallback
        $categories = Cache::get('categories_active', function() {
            return Database::fetchAll("SELECT id, name, slug, icon FROM categories WHERE status = 'active' ORDER BY sort_order ASC");
        }, 1800);

        // Build product search query dynamically using specific column selection
        $where = ["status = 'active'"];
        $params = [];

        if (!empty($categorySlug)) {
            $cat = Database::fetchOne("SELECT id FROM categories WHERE slug = :slug LIMIT 1", ['slug' => $categorySlug]);
            if ($cat) {
                $where[] = "category_id = :cat_id";
                $params['cat_id'] = $cat['id'];
            }
        }

        if (!empty($search)) {
            $where[] = "(title LIKE :q_title OR short_description LIKE :q_desc)";
            $params['q_title'] = '%' . $search . '%';
            $params['q_desc'] = '%' . $search . '%';
        }

        $whereSql = implode(' AND ', $where);

        // Count total products
        $countSql = "SELECT COUNT(*) as total FROM products WHERE {$whereSql}";
        $totalResult = Database::fetchOne($countSql, $params);
        $totalProducts = (int)($totalResult['total'] ?? 0);
        $totalPages = (int)ceil($totalProducts / $limit);

        // Query product list (explicit columns, NO SELECT *)
        $productsSql = "SELECT id, category_id, title, slug, sku, price, discount_price, stock_quantity, commission_rate, short_description, main_image, status 
                        FROM products 
                        WHERE {$whereSql} 
                        ORDER BY id DESC 
                        LIMIT {$limit} OFFSET {$offset}";
        
        $products = Database::fetchAll($productsSql, $params);

        View::render('pages/marketplace', [
            'title' => 'Marketplace - Discover Products & Earn Commissions',
            'categories' => $categories,
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentCategory' => $categorySlug,
            'searchQuery' => $search
        ]);
    }
}
