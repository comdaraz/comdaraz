<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Flash Messages -->
    <?php if (!empty($success)): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-800 text-sm font-medium rounded-r shadow-sm">
            <?= e($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 text-rose-800 text-sm font-medium rounded-r shadow-sm">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- Hero Banner -->
    <div class="relative bg-gradient-to-r from-brand-600 via-brand-500 to-amber-500 text-white rounded-2xl p-8 sm:p-12 shadow-xl overflow-hidden">
        <div class="relative z-10 max-w-2xl space-y-4">
            <span class="inline-block bg-white/20 backdrop-blur-md text-amber-100 text-xs px-3 py-1 rounded-full font-bold uppercase tracking-wider">Nano Modular E-Commerce</span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                Shop Premium Products & Earn Instant Commissions
            </h1>
            <p class="text-amber-100 text-sm sm:text-base font-medium">
                Welcome to Bangladesh's fastest high-performance affiliate marketplace. Share product links and earn real commission directly into your secure wallet.
            </p>
            <div class="pt-2 flex flex-wrap gap-4">
                <a href="<?= url('/marketplace') ?>" class="bg-white text-brand-600 font-bold px-6 py-3 rounded-xl shadow-lg hover:bg-amber-50 transition transform hover:-translate-y-0.5">
                    Browse Marketplace
                </a>
                <?php if (!\App\Core\Session::get('user_id')): ?>
                    <a href="<?= url('/register') ?>" class="bg-brand-700/80 hover:bg-brand-800 text-white font-bold px-6 py-3 rounded-xl shadow-lg border border-white/20 backdrop-blur-md transition transform hover:-translate-y-0.5">
                        Become an Affiliate
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Feature Highlights -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-2">
            <div class="w-10 h-10 bg-brand-100 text-brand-600 rounded-xl flex items-center justify-center font-bold">⚡</div>
            <h3 class="font-bold text-gray-900">Zero Framework Overhead</h3>
            <p class="text-gray-500 text-xs">Built on ultra-lightweight PHP 8.2+ PDO architecture designed specifically for Shared Hosting speed.</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-2">
            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center font-bold">💼</div>
            <h3 class="font-bold text-gray-900">Transparent Affiliate System</h3>
            <p class="text-gray-500 text-xs">Real-time link tracking, click logs, conversion validation, and direct wallet payouts.</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-2">
            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-bold">🔒</div>
            <h3 class="font-bold text-gray-900">Atomic Wallet Ledger</h3>
            <p class="text-gray-500 text-xs">Every balance mutation is protected by database transactions and immutable ledger records.</p>
        </div>
    </div>
</div>
