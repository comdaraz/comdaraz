<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Affiliate Partner Panel</h1>
            <p class="text-xs text-gray-500">Track your referral links, clicks, and earned commissions</p>
        </div>
        <div class="bg-brand-50 border border-brand-200 px-4 py-2 rounded-xl text-xs font-bold text-brand-700">
            Affiliate Code: <span class="font-mono"><?= e($profile['affiliate_code']) ?></span>
        </div>
    </div>

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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Link Clicks</span>
            <div class="text-3xl font-black text-gray-900"><?= number_format($clicksCount) ?></div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Validated Conversions</span>
            <div class="text-3xl font-black text-emerald-600"><?= number_format($conversionsCount) ?></div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Earned Commissions</span>
            <div class="text-3xl font-black text-brand-600">৳<?= e(format_money($totalCommission)) ?></div>
        </div>
    </div>

    <!-- Active Links Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden space-y-4">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-gray-900 text-lg">My Active Affiliate Links</h3>
            <a href="<?= url('/marketplace') ?>" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition">
                + Generate New Link from Marketplace
            </a>
        </div>

        <?php if (empty($myLinks)): ?>
            <div class="p-8 text-center text-xs text-gray-500 space-y-2">
                <p>You haven't generated any affiliate links yet.</p>
                <p>Go to the <a href="<?= url('/marketplace') ?>" class="text-brand-600 font-bold hover:underline">Marketplace</a> and click the 🔗 button on any product!</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                        <tr>
                            <th class="p-4">Product</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Commission</th>
                            <th class="p-4">Affiliate URL</th>
                            <th class="p-4">Date Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <?php foreach ($myLinks as $link): ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 font-bold text-gray-900"><?= e($link['title']) ?></td>
                                <td class="p-4 font-semibold">৳<?= e(format_money($link['price'])) ?></td>
                                <td class="p-4 text-emerald-600 font-bold"><?= e($link['commission_rate']) ?>%</td>
                                <td class="p-4 font-mono text-brand-600 select-all">
                                    <?= e(url('/ref/' . $link['slug'])) ?>
                                </td>
                                <td class="p-4 text-gray-400"><?= e($link['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
