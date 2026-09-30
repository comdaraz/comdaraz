<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">My Orders & Order History</h1>
            <p class="text-xs text-gray-500">View all your placed orders, track payment status, due amounts, and delivery updates</p>
        </div>
        <a href="<?= url('/marketplace') ?>" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-4 py-2 rounded-xl transition">
            Browse Products
        </a>
    </div>

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

    <?php if (empty($orders)): ?>
        <div class="bg-white p-12 text-center rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <div class="text-4xl">📦</div>
            <h3 class="font-bold text-gray-800 text-lg">No orders placed yet</h3>
            <p class="text-xs text-gray-500">You haven't placed any orders. Start shopping to view your order history here.</p>
            <div>
                <a href="<?= url('/marketplace') ?>" class="inline-block bg-brand-600 hover:bg-brand-700 text-white font-bold px-6 py-2.5 rounded-xl text-xs transition">
                    Start Shopping
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($orders as $o): ?>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden space-y-4 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-gray-100 gap-2">
                        <div>
                            <span class="text-xs font-mono font-bold text-gray-500 uppercase">Order Ref:</span>
                            <span class="text-base font-extrabold text-gray-900 font-mono">#<?= e($o['order_number']) ?></span>
                            <span class="text-xs text-gray-400 block sm:inline sm:ml-2">Placed on <?= e($o['created_at']) ?></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase <?= $o['payment_status'] === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($o['payment_status'] === 'partially_paid' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                Payment: <?= e(str_replace('_', ' ', $o['payment_status'])) ?>
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-gray-100 text-gray-800">
                                Status: <?= e($o['order_status']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Purchased Items -->
                    <div class="divide-y divide-gray-50 text-xs">
                        <?php foreach ($o['items'] as $item): ?>
                            <div class="py-2.5 flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-gray-900 text-xs sm:text-sm"><?= e($item['product_title_snapshot']) ?></p>
                                    <p class="text-gray-500">৳<?= e(format_money($item['unit_price_snapshot'])) ?> × <?= $item['quantity'] ?></p>
                                    <?php if (!empty($item['selected_size']) || !empty($item['selected_color'])): ?>
                                        <div class="flex items-center gap-2 mt-1">
                                            <?php if (!empty($item['selected_size'])): ?>
                                                <span class="bg-gray-100 text-gray-700 text-[10px] font-bold px-2 py-0.5 rounded border border-gray-200">
                                                    Size: <?= e($item['selected_size']) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['selected_color'])): ?>
                                                <span class="bg-amber-50 text-amber-800 text-[10px] font-bold px-2 py-0.5 rounded border border-amber-200">
                                                    Color: <?= e($item['selected_color']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="font-bold text-gray-900 text-sm">
                                    ৳<?= e(format_money($item['subtotal'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Payment & Financial Summary -->
                    <div class="bg-gray-50/80 rounded-xl p-4 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs border border-gray-100">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Total Amount</span>
                            <span class="text-sm font-black text-brand-600">৳<?= e(format_money($o['total_amount'])) ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Paid Amount</span>
                            <span class="text-sm font-black text-emerald-600">৳<?= e(format_money($o['paid_amount'])) ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Due Amount</span>
                            <span class="text-sm font-black text-rose-600">৳<?= e(format_money($o['due_amount'])) ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase block">Payment Method</span>
                            <span class="text-xs font-bold text-gray-700 uppercase"><?= e($o['payment_method']) ?> (<?= e($o['payment_type']) ?>)</span>
                        </div>
                    </div>

                    <!-- Recipient Info -->
                    <div class="text-xs text-gray-500 pt-1 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="font-bold text-gray-700">Delivery Address:</span> <?= e($o['shipping_address']) ?> | <span class="font-bold text-gray-700">Contact:</span> <?= e($o['customer_phone']) ?>
                        </div>
                        <?php if (!empty($o['payment_proof'])): ?>
                            <div>
                                <a href="<?= e(url($o['payment_proof'])) ?>" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-600 hover:text-brand-800 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded">
                                    📷 View Uploaded Payment Proof ↗
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
