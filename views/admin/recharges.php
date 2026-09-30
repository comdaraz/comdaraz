<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Recharge Requests</h1>
        <p class="text-xs text-gray-500">Approve or reject pending user wallet deposit requests via atomic ledger</p>
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
                    <th class="p-4">Ref #</th>
                    <th class="p-4">User</th>
                    <th class="p-4">Amount</th>
                    <th class="p-4">Method</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-gray-700">
                <?php foreach ($recharges as $r): ?>
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-mono font-bold"><?= e($r['reference_no']) ?></td>
                        <td class="p-4 font-semibold"><?= e($r['user_name']) ?></td>
                        <td class="p-4 font-bold text-emerald-600">৳<?= e(format_money($r['amount'])) ?></td>
                        <td class="p-4 uppercase text-[10px] font-bold text-gray-500">
                            <p><?= e($r['method']) ?></p>
                            <?php if (!empty($r['payment_proof'])): ?>
                                <a href="<?= e(url($r['payment_proof'])) ?>" target="_blank" class="mt-1 inline-flex items-center gap-1 text-[10px] font-extrabold text-brand-600 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 px-2 py-0.5 rounded border border-brand-200 transition" title="Click to view full size payment screenshot proof">
                                    <span>📷 View Screenshot</span> ↗
                                </a>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $r['status'] === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($r['status'] === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') ?>">
                                <?= e($r['status']) ?>
                            </span>
                        </td>
                        <td class="p-4 text-gray-400"><?= e($r['created_at']) ?></td>
                        <td class="p-4">
                            <?php if ($r['status'] === 'pending'): ?>
                                <form action="<?= url('/admin/recharges/process') ?>" method="POST" class="inline flex gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="recharge_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded text-[10px] font-bold">Approve</button>
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
