<div class="space-y-6">
    <!-- Flash Messages -->
    <?php if (!empty($success)): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-800 text-xs font-medium rounded-r shadow-sm">
            <?= e($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 text-rose-800 text-xs font-medium rounded-r shadow-sm">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- Dashboard Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Users</span>
            <div class="text-3xl font-black text-gray-900"><?= number_format($totalUsers) ?></div>
            <p class="text-[10px] text-emerald-600 font-bold"><?= number_format($activeUsers) ?> active</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Orders</span>
            <div class="text-3xl font-black text-gray-900"><?= number_format($totalOrders) ?></div>
            <p class="text-[10px] text-amber-600 font-bold"><?= number_format($pendingOrders) ?> pending</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Products</span>
            <div class="text-3xl font-black text-gray-900"><?= number_format($totalProducts) ?></div>
            <p class="text-[10px] text-gray-500">Catalog item count</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Commissions Paid</span>
            <div class="text-3xl font-black text-brand-600">৳<?= e(format_money($totalCommission)) ?></div>
            <p class="text-[10px] text-gray-500">Affiliate payouts</p>
        </div>
    </div>

    <!-- Action Alerts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-amber-50 border border-amber-200 p-6 rounded-2xl flex items-center justify-between">
            <div class="space-y-1">
                <h4 class="font-bold text-amber-900 text-sm">Pending Financial Requests</h4>
                <p class="text-xs text-amber-700"><?= $pendingRecharges ?> Recharges • <?= $pendingWithdrawals ?> Withdrawals awaiting review</p>
            </div>
            <div class="flex gap-2">
                <a href="<?= url('/admin/recharges') ?>" class="bg-amber-600 text-white font-bold px-3 py-1.5 rounded-lg text-xs hover:bg-amber-700">Recharges</a>
                <a href="<?= url('/admin/withdrawals') ?>" class="bg-amber-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs hover:bg-amber-800">Withdrawals</a>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 p-6 rounded-2xl flex items-center justify-between">
            <div class="space-y-1">
                <h4 class="font-bold text-blue-900 text-sm">Product Management</h4>
                <p class="text-xs text-blue-700">Add or edit catalog items and stock levels</p>
            </div>
            <a href="<?= url('/admin/products/create') ?>" class="bg-blue-600 text-white font-bold px-4 py-1.5 rounded-lg text-xs hover:bg-blue-700">+ Add Product</a>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden space-y-4">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h3 class="font-bold text-gray-900 text-lg">Recent Orders</h3>
            <a href="<?= url('/admin/orders') ?>" class="text-xs font-bold text-brand-600 hover:underline">View All Orders →</a>
        </div>

        <?php if (empty($recentOrders)): ?>
            <div class="p-8 text-center text-xs text-gray-500">No orders recorded yet.</div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                        <tr>
                            <th class="p-4">Order #</th>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Total Amount</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 font-bold text-gray-900"><?= e($ord['order_number']) ?></td>
                                <td class="p-4 font-semibold"><?= e($ord['customer_name']) ?></td>
                                <td class="p-4 font-bold text-brand-600">৳<?= e(format_money($ord['total_amount'])) ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                                        <?= e($ord['order_status']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-gray-400"><?= e($ord['created_at']) ?></td>
                                <td class="p-4">
                                    <a href="<?= url('/admin/orders/' . $ord['id']) ?>" class="text-brand-600 font-bold hover:underline">Manage</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
