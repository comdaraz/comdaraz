<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">System Audit Logs</h1>
        <p class="text-xs text-gray-500">Immutable security event records and admin activity log (Append-Only)</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                <tr>
                    <th class="p-4">Log ID</th>
                    <th class="p-4">Action</th>
                    <th class="p-4">User / Admin</th>
                    <th class="p-4">Details</th>
                    <th class="p-4">IP Address</th>
                    <th class="p-4">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-gray-700">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-mono">#<?= $log['id'] ?></td>
                        <td class="p-4 font-mono font-bold text-brand-600"><?= e($log['action']) ?></td>
                        <td class="p-4 font-bold text-gray-900"><?= e($log['user_name'] ?: 'System / Guest') ?></td>
                        <td class="p-4 text-gray-600"><?= e($log['details']) ?></td>
                        <td class="p-4 font-mono text-gray-400"><?= e($log['ip_address']) ?></td>
                        <td class="p-4 text-gray-400"><?= e($log['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
