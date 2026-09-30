<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Affiliate Commissions</h1>
        <p class="text-xs text-gray-500">Validate conversion records and approve affiliate wallet commission payouts</p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-800 text-xs font-medium rounded-r">
            <?= e($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 text-rose-800 text-xs font-medium rounded-r">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Affiliate</th>
                    <th class="p-4">Order #</th>
                    <th class="p-4">Commission Amount</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-gray-700">
                <?php foreach ($commissions as $c): ?>
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-mono font-bold">#<?= $c['id'] ?></td>
                        <td class="p-4 font-bold text-gray-900"><?= e($c['affiliate_name']) ?></td>
                        <td class="p-4 font-mono"><?= e($c['order_number']) ?></td>
                        <td class="p-4 font-bold text-brand-600">৳<?= e(format_money($c['commission_amount'])) ?></td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $c['status'] === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($c['status'] === 'cancelled' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') ?>">
                                <?= e($c['status']) ?>
                            </span>
                        </td>
                        <td class="p-4 text-gray-400"><?= e($c['created_at']) ?></td>
                        <td class="p-4">
                            <?php if ($c['status'] === 'pending'): ?>
                                <form action="<?= url('/admin/commissions/process') ?>" method="POST" class="inline flex gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="commission_id" value="<?= $c['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded text-[10px] font-bold">Credit Wallet</button>
                                    <button type="submit" name="action" value="reject" class="bg-rose-600 hover:bg-rose-700 text-white px-2.5 py-1 rounded text-[10px] font-bold">Reject</button>
                                </form>
                            <?php else: ?>
                                <span class="text-[10px] text-gray-400 font-bold">Processed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
