<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Withdrawal Requests</h1>
        <p class="text-xs text-gray-500">Approve or reject pending user wallet withdrawal payouts</p>
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
                    <th class="p-4">Method & Account Info</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-gray-700">
                <?php foreach ($withdrawals as $w): ?>
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-mono font-bold"><?= e($w['reference_no']) ?></td>
                        <td class="p-4 font-semibold"><?= e($w['user_name']) ?></td>
                        <td class="p-4 font-bold text-rose-600">৳<?= e(format_money($w['amount'])) ?></td>
                        <td class="p-4">
                            <p class="uppercase font-bold text-[10px] text-gray-500"><?= e($w['method']) ?></p>
                            <p class="font-mono text-gray-700 text-[10px]"><?= e($w['account_info']) ?></p>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $w['status'] === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($w['status'] === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') ?>">
                                <?= e($w['status']) ?>
                            </span>
                            <?php if (!empty($w['rejection_reason'])): ?>
                                <p class="text-[10px] text-rose-600 mt-1 max-w-xs italic font-medium">Reason: <?= e($w['rejection_reason']) ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-gray-400"><?= e($w['created_at']) ?></td>
                        <td class="p-4">
                            <?php if ($w['status'] === 'pending'): ?>
                                <div class="space-y-2" x-data="{ reason: '' }">
                                    <form action="<?= url('/admin/withdrawals/process') ?>" method="POST" class="flex flex-col gap-1.5">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="withdrawal_id" value="<?= $w['id'] ?>">

                                        <!-- Preset Rejection Templates Dropdown -->
                                        <select @change="reason = $event.target.value" class="px-2 py-1 border border-gray-200 rounded text-[10px]">
                                            <option value="">-- Preset Rejection Reason --</option>
                                            <?php if (!empty($rejectionTemplates)): ?>
                                                <?php foreach ($rejectionTemplates as $tpl): ?>
                                                    <option value="<?= e($tpl['message']) ?>"><?= e($tpl['title']) ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>

                                        <input type="text" name="rejection_reason" x-model="reason" placeholder="Reason message if rejecting..." class="px-2 py-1 border border-gray-200 rounded text-[10px]">

                                        <div class="flex gap-2 pt-1">
                                            <button type="submit" name="action" value="approve" class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded text-[10px] font-bold">Approve</button>
                                            <button type="submit" name="action" value="reject" class="bg-rose-600 hover:bg-rose-700 text-white px-2.5 py-1 rounded text-[10px] font-bold">Reject</button>
                                        </div>
                                    </form>
                                </div>
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
