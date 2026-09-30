<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Order Management Console</h1>
            <p class="text-xs text-gray-500">Admin control over order prices, payment amounts, due calculation, and status</p>
        </div>

        <div class="flex gap-2">
            <a href="<?= url('/admin/orders') ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold border <?= empty($statusFilter) ? 'bg-brand-600 text-white border-brand-600' : 'bg-white border-gray-200 text-gray-700' ?>">All</a>
            <a href="<?= url('/admin/orders?status=pending') ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold border <?= $statusFilter === 'pending' ? 'bg-amber-600 text-white border-amber-600' : 'bg-white border-gray-200 text-gray-700' ?>">Pending</a>
            <a href="<?= url('/admin/orders?status=processing') ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold border <?= $statusFilter === 'processing' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white border-gray-200 text-gray-700' ?>">Processing</a>
            <a href="<?= url('/admin/orders?status=delivered') ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold border <?= $statusFilter === 'delivered' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white border-gray-200 text-gray-700' ?>">Delivered</a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-800 text-xs font-medium rounded-r">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                <tr>
                    <th class="p-4">Order Number</th>
                    <th class="p-4">Customer Details</th>
                    <th class="p-4">Total Amount</th>
                    <th class="p-4">Paid / Due</th>
                    <th class="p-4">Payment Method & Status</th>
                    <th class="p-4">Fulfillment Status</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-gray-700">
                <?php foreach ($orders as $o): ?>
                    <tr class="hover:bg-gray-50/50">
                        <td class="p-4 font-bold font-mono text-gray-900"><?= e($o['order_number']) ?></td>
                        <td class="p-4">
                            <p class="font-bold text-gray-900"><?= e($o['customer_name']) ?></p>
                            <p class="text-[11px] text-gray-500"><?= e($o['customer_phone'] ?: $o['phone']) ?></p>
                        </td>
                        <td class="p-4 font-bold text-brand-600">৳<?= e(format_money($o['total_amount'])) ?></td>
                        <td class="p-4">
                            <p class="text-emerald-600 font-bold">Paid: ৳<?= e(format_money($o['paid_amount'])) ?></p>
                            <p class="text-rose-600 font-bold">Due: ৳<?= e(format_money($o['due_amount'])) ?></p>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $o['payment_status'] === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($o['payment_status'] === 'partially_paid' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                <?= e($o['payment_method']) ?> • <?= e(str_replace('_', ' ', $o['payment_status'])) ?>
                            </span>
                            <?php if (!empty($o['payment_proof'])): ?>
                                <a href="<?= url('/admin/orders/' . $o['id']) ?>" class="mt-1 inline-block bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold px-1.5 py-0.5 rounded text-[10px] border border-purple-200" title="Payment Proof Screenshot Uploaded">
                                    📷 Proof
                                </a>
                            <?php endif; ?>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-800">
                                <?= e($o['order_status']) ?>
                            </span>
                        </td>
                        <td class="p-4 text-gray-400"><?= e($o['created_at']) ?></td>
                        <td class="p-4">
                            <a href="<?= url('/admin/orders/' . $o['id']) ?>" class="bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold px-3 py-1.5 rounded-lg border border-brand-200 transition">
                                Manage Order
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
