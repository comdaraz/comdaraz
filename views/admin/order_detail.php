<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Admin Control Console — Order #<?= e($order['order_number']) ?></h1>
            <p class="text-xs text-gray-500">Full administrative control over pricing, paid/due amounts, customer info & fulfillment</p>
        </div>
        <a href="<?= url('/admin/orders') ?>" class="text-xs font-bold text-gray-500 hover:text-gray-900">← Back to Orders</a>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Items List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                <h3 class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-3">Purchased Items & Variations</h3>
                <div class="divide-y divide-gray-100 text-xs">
                    <?php foreach ($items as $item): ?>
                        <div class="py-3 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-gray-900"><?= e($item['product_title_snapshot']) ?></p>
                                <p class="text-gray-500 mt-0.5">৳<?= e(format_money($item['unit_price_snapshot'])) ?> × <?= $item['quantity'] ?></p>

                                <?php if (!empty($item['selected_size']) || !empty($item['selected_color'])): ?>
                                    <div class="flex items-center gap-2 mt-1">
                                        <?php if (!empty($item['selected_size'])): ?>
                                            <span class="bg-gray-100 text-gray-700 text-[10px] font-extrabold px-2 py-0.5 rounded border border-gray-200">
                                                Size: <?= e($item['selected_size']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($item['selected_color'])): ?>
                                            <span class="bg-amber-50 text-amber-800 text-[10px] font-extrabold px-2 py-0.5 rounded border border-amber-200">
                                                Color: <?= e($item['selected_color']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="font-bold text-brand-600 text-sm">
                                ৳<?= e(format_money($item['subtotal'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-t border-gray-100 pt-3 flex justify-between items-center font-bold text-sm">
                    <span>Original Calculated Total</span>
                    <span class="text-brand-600">৳<?= e(format_money($order['total_amount'])) ?></span>
                </div>
            </div>

            <!-- Customer Registration vs Order Info Comparison -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-2">Customer Account Metadata</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="font-bold text-gray-400 block uppercase text-[10px]">Registered Account:</span>
                        <p class="font-bold text-gray-900"><?= e($order['reg_name']) ?></p>
                        <p class="text-gray-500"><?= e($order['email']) ?></p>
                        <p class="text-gray-500"><?= e($order['reg_phone']) ?></p>
                    </div>
                    <div>
                        <span class="font-bold text-gray-400 block uppercase text-[10px]">Order Recipient Details:</span>
                        <p class="font-bold text-gray-900"><?= e($order['customer_name']) ?></p>
                        <p class="text-gray-500"><?= e($order['customer_phone']) ?></p>
                        <p class="text-gray-500 line-clamp-2"><?= e($order['shipping_address']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Payment Verification Screenshot Card (পেমেন্ট প্রুফ স্ক্রিনশট) -->
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-2 flex items-center justify-between">
                    <span>📷 Payment Verification Screenshot (পেমেন্ট প্রমাণপত্র)</span>
                    <?php if (!empty($order['payment_proof'])): ?>
                        <span class="bg-emerald-100 text-emerald-800 font-extrabold text-[10px] px-2 py-0.5 rounded">Screenshot Attached</span>
                    <?php else: ?>
                        <span class="bg-gray-100 text-gray-600 font-bold text-[10px] px-2 py-0.5 rounded">No Screenshot</span>
                    <?php endif; ?>
                </h3>

                <?php if (!empty($order['payment_proof'])): ?>
                    <div class="space-y-3">
                        <p class="text-gray-600 font-medium">কাস্টমার প্রদত্ত পেমেন্ট স্ক্রিনশট (এডমিন টাকা পাওয়ার সত্যতা যাচাই করুন):</p>
                        <div class="border border-gray-200 rounded-xl overflow-hidden bg-gray-50 p-2 max-w-md">
                            <a href="<?= e(url($order['payment_proof'])) ?>" target="_blank" title="Click to view full image">
                                <img src="<?= e(url($order['payment_proof'])) ?>" alt="Payment Proof Screenshot" class="w-full max-h-72 object-contain rounded-lg hover:opacity-95 transition shadow-sm border border-gray-100">
                            </a>
                        </div>
                        <div class="pt-1">
                            <a href="<?= e(url($order['payment_proof'])) ?>" target="_blank" class="inline-flex items-center gap-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 px-3 py-1.5 rounded-lg font-bold text-xs transition shadow-sm">
                                <span>🔍 Open Full Size Screenshot in New Tab</span> ↗
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-gray-50 rounded-xl text-gray-500 text-center italic text-xs">
                        No payment screenshot uploaded for this order (Cash on Delivery or direct payment).
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Comprehensive Admin Financial & Status Control Box -->
        <?php 
            $totFloat = (float)str_replace(',', '', format_money($order['total_amount']));
            $pdFloat = (float)str_replace(',', '', format_money($order['paid_amount']));
        ?>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xl space-y-4"
             x-data="{ 
                 admTotal: <?= $totFloat ?>, 
                 admPaid: <?= $pdFloat ?>,
                 get admDue() {
                     return Math.max(0, this.admTotal - this.admPaid);
                 }
             }">
            <h3 class="font-bold text-gray-900 text-base border-b border-gray-100 pb-3 flex items-center gap-2">
                <span>⚙️</span> Admin Controls
            </h3>

            <form action="<?= url('/admin/orders/update-financials') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                <div>
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Customer Name</label>
                    <input type="text" name="customer_name" value="<?= e($order['customer_name']) ?>" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Customer Phone Number</label>
                    <input type="text" name="customer_phone" value="<?= e($order['customer_phone']) ?>" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Delivery Address</label>
                    <textarea name="shipping_address" required rows="2" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500"><?= e($order['shipping_address']) ?></textarea>
                </div>

                <!-- Price & Financial Controls (Admin Only) -->
                <div class="bg-brand-50/50 p-4 rounded-xl border border-brand-100 space-y-3">
                    <span class="block font-black text-brand-900 uppercase text-[10px] tracking-wider">💰 Order Financial Management</span>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Total Order Price (BDT)</label>
                        <input type="number" step="0.01" name="total_amount" x-model.number="admTotal" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <span class="text-[10px] text-gray-400">Admin দাম বাড়াতে/কমাতে পারবেন</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Customer Paid Amount (BDT)</label>
                        <input type="number" step="0.01" name="paid_amount" x-model.number="admPaid" required class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold text-emerald-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <span class="text-[10px] text-gray-400">কাস্টমার যে পরিমাণ টাকা জমা দিয়েছে</span>
                    </div>

                    <div class="p-2.5 bg-white rounded-lg border border-brand-200 flex justify-between items-center text-xs font-bold">
                        <span class="text-rose-700">Calculated Due Amount:</span>
                        <span class="text-rose-700 text-sm">৳<span x-text="admDue.toFixed(2)"></span></span>
                    </div>
                </div>

                <!-- Payment Configuration -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full px-2.5 py-2 rounded-xl border border-gray-200 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="COD" <?= $order['payment_method'] === 'COD' ? 'selected' : '' ?>>COD (Cash on Delivery)</option>
                            <option value="Wallet" <?= $order['payment_method'] === 'Wallet' ? 'selected' : '' ?>>Wallet Balance</option>
                            <option value="Binance Pay (USDT)" <?= (in_array($order['payment_method'], ['Binance Pay (USDT)', 'Binance', 'Binary', 'Binary Pay'], true)) ? 'selected' : '' ?>>Binance / Binary Pay (USDT)</option>
                            <option value="bKash" <?= $order['payment_method'] === 'bKash' ? 'selected' : '' ?>>bKash</option>
                            <option value="Nagad" <?= $order['payment_method'] === 'Nagad' ? 'selected' : '' ?>>Nagad</option>
                            <option value="Rocket" <?= $order['payment_method'] === 'Rocket' ? 'selected' : '' ?>>Rocket</option>
                            <option value="Bank" <?= $order['payment_method'] === 'Bank' ? 'selected' : '' ?>>Bank Wire</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Payment Option</label>
                        <select name="payment_type" class="w-full px-2.5 py-2 rounded-xl border border-gray-200 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="full" <?= $order['payment_type'] === 'full' ? 'selected' : '' ?>>Full Payment</option>
                            <option value="partial" <?= $order['payment_type'] === 'partial' ? 'selected' : '' ?>>Partial (Advance)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Payment Status</label>
                        <select name="payment_status" class="w-full px-2.5 py-2 rounded-xl border border-gray-200 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                            <option value="partially_paid" <?= $order['payment_status'] === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                            <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1">Order Status</label>
                        <select name="order_status" class="w-full px-2.5 py-2 rounded-xl border border-gray-200 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl shadow-lg transition text-xs uppercase tracking-wider">
                    Save Order Controls & Update
                </button>
            </form>
        </div>
    </div>
</div>
