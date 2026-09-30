<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Immutable Wallet Ledger</h1>
        <p class="text-xs text-gray-500">System-wide audit trail of all wallet balance mutations (Read-Only)</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                <tr>
                    <th class="p-4">Reference ID</th>
                    <th class="p-4">User</th>
                    <th class="p-4">Type</th>
                    <th class="p-4">Amount</th>
                    <th class="p-4">Balance Before</th>
                    <th class="p-4">Balance After</th>
                    <th class="p-4">Description</th>
                    <th class="p-4">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-gray-700">
                <?php foreach ($transactions as $tx): ?>
                    <?php $isCredit = money_comp((string)$tx['amount'], '0.00') >= 0; ?>
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-mono text-gray-500 font-bold"><?= e($tx['reference_id']) ?></td>
                        <td class="p-4 font-bold text-gray-900"><?= e($tx['user_name']) ?></td>
                        <td class="p-4 uppercase text-[10px] font-bold">
                            <span class="px-2 py-0.5 rounded <?= $isCredit ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                <?= e($tx['type']) ?>
                            </span>
                        </td>
                        <td class="p-4 font-bold <?= $isCredit ? 'text-emerald-600' : 'text-rose-600' ?>">
                            <?= $isCredit ? '+' : '' ?>৳<?= e(format_money($tx['amount'])) ?>
                        </td>
                        <td class="p-4 text-gray-500">৳<?= e(format_money($tx['balance_before'])) ?></td>
                        <td class="p-4 font-bold text-gray-900">৳<?= e(format_money($tx['balance_after'])) ?></td>
                        <td class="p-4"><?= e($tx['description']) ?></td>
                        <td class="p-4 text-gray-400"><?= e($tx['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
