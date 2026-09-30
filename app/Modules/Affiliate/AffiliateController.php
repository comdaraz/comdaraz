<?php

namespace App\Modules\Affiliate;

use App\Core\Database;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\View;

class AffiliateController {
    public function dashboard(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            redirect(url('/login'));
        }

        $profile = Database::fetchOne("SELECT id, affiliate_code, status, created_at FROM affiliate_profiles WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);

        if (!$profile) {
            // Auto-create affiliate profile if not existing
            $code = 'AFF-' . strtoupper(substr(md5($userId . time()), 0, 8));
            Database::query("INSERT INTO affiliate_profiles (user_id, affiliate_code, status) VALUES (:uid, :code, 'approved')", [
                'uid' => $userId,
                'code' => $code
            ]);
            $profile = Database::fetchOne("SELECT id, affiliate_code, status, created_at FROM affiliate_profiles WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);
        }

        $affiliateProfileId = (int)$profile['id'];

        // Stats summary
        $clicksCount = (int)(Database::fetchOne("SELECT COUNT(*) as total FROM affiliate_clicks ac JOIN affiliate_links al ON ac.affiliate_link_id = al.id WHERE al.affiliate_profile_id = :apid", ['apid' => $affiliateProfileId])['total'] ?? 0);
        
        $conversionsCount = (int)(Database::fetchOne("SELECT COUNT(*) as total FROM conversions c JOIN affiliate_links al ON c.affiliate_link_id = al.id WHERE al.affiliate_profile_id = :apid", ['apid' => $affiliateProfileId])['total'] ?? 0);

        $commResult = Database::fetchOne("SELECT SUM(commission_amount) as total FROM affiliate_commissions WHERE affiliate_profile_id = :apid AND status = 'paid'", ['apid' => $affiliateProfileId]);
        $totalCommission = format_money($commResult['total'] ?? '0.00');

        $myLinks = Database::fetchAll(
            "SELECT al.id, al.slug, p.title, p.price, p.commission_rate, al.created_at 
             FROM affiliate_links al 
             JOIN products p ON al.product_id = p.id 
             WHERE al.affiliate_profile_id = :apid 
             ORDER BY al.id DESC",
            ['apid' => $affiliateProfileId]
        );

        View::render('pages/affiliate_dashboard', [
            'title' => 'Affiliate Dashboard - Daraz Affiliate Platform',
            'profile' => $profile,
            'clicksCount' => $clicksCount,
            'conversionsCount' => $conversionsCount,
            'totalCommission' => $totalCommission,
            'myLinks' => $myLinks,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function createLink(): void {
        Session::start();
        $userId = Session::get('user_id');
        if (!$userId) {
            redirect(url('/login'));
        }

        CSRF::verifyOrDie();

        $productId = (int)($_POST['product_id'] ?? 0);
        $product = Database::fetchOne("SELECT id FROM products WHERE id = :id AND status = 'active' LIMIT 1", ['id' => $productId]);

        if (!$product) {
            Session::setFlash('error', 'Invalid product selected.');
            redirect(url('/affiliate'));
        }

        $profile = Database::fetchOne("SELECT id FROM affiliate_profiles WHERE user_id = :uid LIMIT 1", ['uid' => $userId]);
        if (!$profile) {
            Session::setFlash('error', 'Affiliate profile not found.');
            redirect(url('/affiliate'));
        }

        $slug = 'ref-' . strtolower(substr(md5($userId . $productId . microtime()), 0, 10));

        Database::query("INSERT INTO affiliate_links (affiliate_profile_id, product_id, slug) VALUES (:apid, :pid, :slug)", [
            'apid' => $profile['id'],
            'pid' => $productId,
            'slug' => $slug
        ]);

        Session::setFlash('success', 'Affiliate link created successfully!');
        redirect(url('/affiliate'));
    }

    public function trackClick(string $slug): void {
        $link = Database::fetchOne("SELECT al.id, al.product_id FROM affiliate_links al WHERE al.slug = :slug LIMIT 1", ['slug' => $slug]);

        if ($link) {
            // Record click
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $referer = $_SERVER['HTTP_REFERER'] ?? '';

            Database::query("INSERT INTO affiliate_clicks (affiliate_link_id, ip_address, user_agent, referer) VALUES (:alid, :ip, :ua, :ref)", [
                'alid' => $link['id'],
                'ip' => $ip,
                'ua' => $ua,
                'ref' => $referer
            ]);

            Session::set('referred_affiliate_link_id', $link['id']);
            redirect(url('/marketplace/product/' . $link['product_id']));
        } else {
            redirect(url('/marketplace'));
        }
    }
}
