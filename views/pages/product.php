<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden p-6 sm:p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Product Image -->
            <div class="space-y-4">
                <div class="h-80 sm:h-96 rounded-2xl overflow-hidden bg-gray-100 border border-gray-100 flex items-center justify-center">
                    <?php 
                        $prodImg = !empty($product['main_image']) ? $product['main_image'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80';
                        if (!empty($prodImg) && !str_starts_with($prodImg, 'http://') && !str_starts_with($prodImg, 'https://') && !str_starts_with($prodImg, '/')) {
                            $prodImg = url($prodImg);
                        }
                    ?>
                    <img src="<?= e($prodImg) ?>" alt="<?= e($product['title']) ?>" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80';">
                </div>
            </div>

            <!-- Product Info -->
            <div class="space-y-6 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center space-x-2 text-xs font-semibold text-brand-600 uppercase tracking-wider">
                        <span><?= e($product['category_name']) ?></span>
                        <span>•</span>
                        <span>SKU: <?= e($product['sku']) ?></span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight">
                        <?= e($product['title']) ?>
                    </h1>

                    <div class="flex items-center space-x-3">
                        <span class="text-2xl sm:text-3xl font-black text-brand-600">
                            ৳<?= e(format_money($product['discount_price'] ?: $product['price'])) ?>
                        </span>
                        <?php if (!empty($product['discount_price']) && money_comp($product['price'], $product['discount_price']) > 0): ?>
                            <span class="text-base text-gray-400 line-through font-semibold">
                                ৳<?= e(format_money($product['price'])) ?>
                            </span>
                            <?php 
                                $savings = money_sub($product['price'], $product['discount_price']);
                                $savePct = round(($savings / (float)$product['price']) * 100);
                            ?>
                            <span class="bg-rose-100 text-rose-700 text-xs px-2.5 py-0.5 rounded-full font-extrabold uppercase">
                                <?= $savePct ?>% OFF (Save ৳<?= e($savings) ?>)
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php 
                        $effectivePriceStr = format_money($product['discount_price'] ?: $product['price']);
                        $commRateStr = format_money($product['commission_rate']);
                        $estCommission = money_mul($effectivePriceStr, bcdiv($commRateStr, '100.00', 4));
                    ?>

                    <!-- Product ROI & Affiliate Share Card -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-4 text-xs space-y-3 shadow-sm" x-data="{ copied: false }">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-amber-900 uppercase tracking-wider text-[11px]">⚡ Product ROI & Affiliate Earnings</span>
                            <span class="bg-amber-500 text-white font-black px-2 py-0.5 rounded text-[10px]"><?= e($product['commission_rate']) ?>% ROI Share</span>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <div>
                                <p class="text-gray-500 text-[11px]">Estimated Commission per Sale:</p>
                                <p class="text-lg font-black text-brand-700">৳<?= e($estCommission) ?></p>
                            </div>
                            <button type="button" @click="navigator.clipboard.writeText('<?= url('/marketplace/product/' . $product['id']) ?>'); copied = true; setTimeout(() => copied = false, 2500)" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-4 py-2 rounded-xl shadow transition flex items-center space-x-1.5">
                                <span x-show="!copied">🔗 Share & Earn ROI</span>
                                <span x-show="copied" style="display: none;">✓ Link Copied!</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Overview</h3>
                        <p class="text-sm text-gray-600 leading-relaxed"><?= e($product['short_description']) ?></p>
                    </div>

                    <div class="space-y-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Stock Status</h3>
                        <span class="inline-block text-xs font-bold px-3 py-1 rounded-full <?= $product['stock_quantity'] > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                            <?= $product['stock_quantity'] > 0 ? 'In Stock (' . $product['stock_quantity'] . ' units available)' : 'Out of Stock' ?>
                        </span>
                    </div>
                </div>

                <!-- Add to Cart Form -->
                <form action="<?= url('/cart/add') ?>" method="POST" class="pt-6 border-t border-gray-100 space-y-4" x-data="{ selectedSize: '', selectedColor: '' }">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                    <?php if (!empty($product['size_options'])): ?>
                        <?php $sizes = array_map('trim', explode(',', $product['size_options'])); ?>
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Select Size (সাইজ পছন্দ করুন)</label>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($sizes as $idx => $s): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="selected_size" value="<?= e($s) ?>" class="sr-only" x-model="selectedSize" <?= $idx === 0 ? 'required' : '' ?>>
                                        <span class="px-3.5 py-1.5 rounded-xl border text-xs font-bold transition inline-block"
                                              :class="selectedSize === '<?= e($s) ?>' ? 'bg-brand-600 text-white border-brand-600 shadow' : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-brand-500'">
                                            <?= e($s) ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['color_options'])): ?>
                        <?php $colors = array_map('trim', explode(',', $product['color_options'])); ?>
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Select Color (কালার পছন্দ করুন)</label>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($colors as $idx => $c): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="selected_color" value="<?= e($c) ?>" class="sr-only" x-model="selectedColor" <?= $idx === 0 ? 'required' : '' ?>>
                                        <span class="px-3.5 py-1.5 rounded-xl border text-xs font-bold transition inline-block"
                                              :class="selectedColor === '<?= e($c) ?>' ? 'bg-brand-600 text-white border-brand-600 shadow' : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-brand-500'">
                                            🎨 <?= e($c) ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center space-x-4">
                        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Quantity</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>" class="w-20 px-3 py-2 rounded-xl border border-gray-200 text-center text-sm font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?> class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl shadow-lg transition text-sm disabled:opacity-50">
                            Add to Cart
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Full Description -->
        <?php if (!empty($product['full_description'])): ?>
            <div class="mt-12 pt-8 border-t border-gray-100 space-y-3">
                <h3 class="text-lg font-bold text-gray-900">Product Specifications & Description</h3>
                <div class="text-sm text-gray-600 leading-relaxed space-y-2">
                    <?= nl2br(e($product['full_description'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
