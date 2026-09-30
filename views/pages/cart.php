<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Your Shopping Cart</h1>

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

    <?php if (empty($items)): ?>
        <div class="bg-white p-12 text-center rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <div class="text-4xl">🛒</div>
            <h3 class="font-bold text-gray-800 text-lg">Your cart is empty</h3>
            <p class="text-xs text-gray-500">Explore our marketplace to add your favorite items.</p>
            <div>
                <a href="<?= url('/marketplace') ?>" class="inline-block bg-brand-600 hover:bg-brand-700 text-white font-bold px-6 py-2.5 rounded-xl text-xs transition">
                    Shop Now
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items List -->
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($items as $item): ?>
                    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center flex-shrink-0">
                            <?php 
                                $cImg = !empty($item['main_image']) ? $item['main_image'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80';
                                if (!empty($cImg) && !str_starts_with($cImg, 'http://') && !str_starts_with($cImg, 'https://') && !str_starts_with($cImg, '/')) {
                                    $cImg = url($cImg);
                                }
                            ?>
                            <img src="<?= e($cImg) ?>" alt="<?= e($item['title']) ?>" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80';">
                        </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm">
                                    <a href="<?= url('/marketplace/product/' . $item['product_id']) ?>" class="hover:text-brand-600 transition">
                                        <?= e($item['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">Unit Price: ৳<?= e(format_money($item['effective_price'])) ?></p>

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
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto border-t sm:border-t-0 pt-3 sm:pt-0">
                            <!-- Update Quantity Form -->
                            <form action="<?= url('/cart/update') ?>" method="POST" class="flex items-center space-x-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_quantity'] ?>" class="w-16 px-2 py-1 border border-gray-200 rounded-lg text-center text-xs font-bold focus:outline-none focus:ring-1 focus:ring-brand-500">
                                <button type="submit" class="text-xs font-semibold text-gray-500 hover:text-brand-600">Update</button>
                            </form>

                            <div class="text-right">
                                <div class="font-bold text-brand-600 text-sm">
                                    ৳<?= e(format_money($item['item_subtotal'])) ?>
                                </div>
                            </div>

                            <!-- Remove Item Form -->
                            <form action="<?= url('/cart/remove') ?>" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold">✕</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary & Detailed Checkout -->
            <?php 
                $subtotalFloat = (float)str_replace(',', '', format_money($subtotal)); 
            ?>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xl space-y-6 h-fit"
                 x-data="{ 
                     totalAmount: <?= $subtotalFloat ?>, 
                     paymentMethod: 'COD', 
                     paymentType: 'full', 
                     customPaid: 0,
                     get paidAmount() {
                         if (this.paymentType === 'full') {
                             return this.paymentMethod === 'COD' ? 0 : this.totalAmount;
                         }
                         return Math.min(this.totalAmount, Math.max(0, Number(this.customPaid || 0)));
                     },
                     get dueAmount() {
                         return Math.max(0, this.totalAmount - this.paidAmount);
                     }
                 }">
                <h3 class="font-bold text-gray-900 text-lg">Order Summary & Payment</h3>

                <div class="space-y-3 text-xs border-b border-gray-100 pb-4">
                    <div class="flex justify-between text-gray-600">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-gray-900">৳<?= e(format_money($subtotal)) ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Estimated Shipping</span>
                        <span class="font-bold text-emerald-600">FREE</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold text-gray-900 pt-2 border-t border-gray-50">
                        <span>Total Order Amount</span>
                        <span class="text-brand-600">৳<?= e(format_money($subtotal)) ?></span>
                    </div>
                </div>

                <!-- Checkout Form -->
                <form action="<?= url('/checkout') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Customer Name</label>
                        <input type="text" name="customer_name" required value="<?= e($user['name'] ?? '') ?>" placeholder="Enter your full name" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Delivery Phone Number</label>
                        <input type="text" name="customer_phone" required value="<?= e($user['phone'] ?? '') ?>" placeholder="e.g. 01700000000" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Delivery Address</label>
                        <textarea name="shipping_address" required rows="2" placeholder="House/Road, Thana, District" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                    </div>

                    <!-- Payment Method Option -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Payment Method</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                            <label class="cursor-pointer border rounded-xl p-2.5 flex items-center space-x-2 transition"
                                   :class="paymentMethod === 'COD' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="payment_method" value="COD" x-model="paymentMethod" class="text-brand-600">
                                <span>💵 Cash on Delivery</span>
                            </label>
                            <label class="cursor-pointer border rounded-xl p-2.5 flex items-center space-x-2 transition"
                                   :class="paymentMethod === 'Wallet' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="payment_method" value="Wallet" x-model="paymentMethod" class="text-brand-600">
                                <span>💳 Wallet Balance</span>
                            </label>
                            <label class="cursor-pointer border rounded-xl p-2.5 flex items-center space-x-2 transition"
                                   :class="paymentMethod === 'Binance Pay (USDT)' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="payment_method" value="Binance Pay (USDT)" x-model="paymentMethod" class="text-brand-600">
                                <span>🪙 Binance / Binary Pay</span>
                            </label>
                            <label class="cursor-pointer border rounded-xl p-2.5 flex items-center space-x-2 transition"
                                   :class="paymentMethod === 'bKash' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="payment_method" value="bKash" x-model="paymentMethod" class="text-brand-600">
                                <span>📱 bKash</span>
                            </label>
                            <label class="cursor-pointer border rounded-xl p-2.5 flex items-center space-x-2 transition"
                                   :class="paymentMethod === 'Nagad' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="payment_method" value="Nagad" x-model="paymentMethod" class="text-brand-600">
                                <span>📱 Nagad</span>
                            </label>
                        </div>
                    </div>

                    <!-- Payment Type Option: Full vs Partial -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Payment Option</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="cursor-pointer border rounded-xl p-2.5 flex items-center space-x-2 transition"
                                   :class="paymentType === 'full' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="payment_type" value="full" x-model="paymentType" class="text-brand-600">
                                <span>Full Payment</span>
                            </label>
                            <label class="cursor-pointer border rounded-xl p-2.5 flex items-center space-x-2 transition"
                                   :class="paymentType === 'partial' ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="payment_type" value="partial" x-model="paymentType" class="text-brand-600">
                                <span>Partial (Advance)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Partial Payment Advance Input -->
                    <div x-show="paymentType === 'partial'" class="space-y-1 bg-amber-50/60 p-3 rounded-xl border border-amber-200 text-xs">
                        <label class="block font-bold text-amber-900 uppercase">Advance Paid Amount (৳)</label>
                        <input type="number" step="0.01" min="0" :max="totalAmount" name="paid_amount" x-model="customPaid" placeholder="e.g. 500.00" class="w-full px-3 py-2 rounded-xl border border-amber-300 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <p class="text-[10px] text-amber-700 font-semibold">অগ্রিম পরিশোধিত টাকার পরিমাণ উল্লেখ করুন</p>
                    </div>

                    <!-- Payment Proof Screenshot Upload (পেমেন্ট প্রমাণপত্র / স্ক্রিনশট আপলোড) -->
                    <div class="space-y-1.5 p-3 rounded-xl border border-brand-200 bg-brand-50/30">
                        <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider flex items-center justify-between">
                            <span class="flex items-center gap-1.5">📷 Payment Proof Screenshot <span class="text-brand-700">(পেমেন্ট স্ক্রিনশট)</span></span>
                            <span class="text-[10px] text-brand-600 font-semibold">Optional / এডমিন ভ্যালিডেশন</span>
                        </label>
                        <input type="file" name="payment_proof" accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" class="w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand-600 file:text-white hover:file:bg-brand-700 cursor-pointer">
                        <p class="text-[10px] text-gray-500">পেমেন্ট করার পর স্ক্রিনশট আপলোড করুন যেন এডমিন কোন নাম্বার/একাউন্ট থেকে টাকা এসেছে দেখতে পারে (JPG, PNG, WEBP)</p>
                    </div>

                    <!-- Financial Breakdown Box -->
                    <div class="bg-gray-50 p-3 rounded-xl space-y-1.5 border border-gray-100 text-xs">
                        <div class="flex justify-between text-gray-600">
                            <span>Paid Amount:</span>
                            <span class="font-bold text-emerald-600">৳<span x-text="paidAmount.toFixed(2)"></span></span>
                        </div>
                        <div class="flex justify-between text-gray-600 pt-1 border-t border-gray-200">
                            <span>Due Amount:</span>
                            <span class="font-bold text-rose-600">৳<span x-text="dueAmount.toFixed(2)"></span></span>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl shadow-lg transition text-xs uppercase tracking-wider">
                        Place Order Now
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
