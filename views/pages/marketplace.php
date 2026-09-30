<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Product Marketplace</h1>
            <p class="text-xs text-gray-500">Discover top deals and generate affiliate links to earn commission</p>
        </div>

        <!-- Search Bar -->
        <form action="<?= url('/marketplace') ?>" method="GET" class="flex gap-2 w-full md:w-80">
            <?php if (!empty($currentCategory)): ?>
                <input type="hidden" name="category" value="<?= e($currentCategory) ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Search products..." class="w-full px-4 py-2 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition">
                Search
            </button>
        </form>
    </div>

    <!-- Category Filters -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <a href="<?= url('/marketplace') ?>" class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition <?= empty($currentCategory) ? 'bg-brand-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100' ?>">
            All Categories
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= url('/marketplace?category=' . e($cat['slug'])) ?>" class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition <?= $currentCategory === $cat['slug'] ? 'bg-brand-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100' ?>">
                <?= e($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Product Grid -->
    <?php if (empty($products)): ?>
        <div class="bg-white p-12 text-center rounded-2xl border border-gray-100 shadow-sm space-y-2">
            <div class="text-3xl">🔍</div>
            <h3 class="font-bold text-gray-800">No products found</h3>
            <p class="text-xs text-gray-500">Try adjusting your search or category filter.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($products as $p): ?>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition">
                    <div class="relative h-48 bg-gray-100 flex items-center justify-center">
                        <?php 
                            $mImg = !empty($p['main_image']) ? $p['main_image'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80';
                            if (!empty($mImg) && !str_starts_with($mImg, 'http://') && !str_starts_with($mImg, 'https://') && !str_starts_with($mImg, '/')) {
                                $mImg = url($mImg);
                            }
                        ?>
                        <img src="<?= e($mImg) ?>" alt="<?= e($p['title']) ?>" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&q=80';">
                        <?php if (!empty($p['discount_price'])): ?>
                            <span class="absolute top-3 left-3 bg-rose-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                Sale
                            </span>
                        <?php endif; ?>
                        <span class="absolute top-3 right-3 bg-gradient-to-r from-brand-600 to-amber-500 text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow">
                            ROI: <?= e($p['commission_rate']) ?>%
                        </span>
                    </div>

                    <div class="p-4 flex-grow flex flex-col justify-between space-y-3">
                        <div class="space-y-1">
                            <h3 class="font-bold text-gray-900 text-sm line-clamp-2 leading-snug">
                                <a href="<?= url('/marketplace/product/' . $p['id']) ?>" class="hover:text-brand-600 transition">
                                    <?= e($p['title']) ?>
                                </a>
                            </h3>
                            <p class="text-xs text-gray-500 line-clamp-2"><?= e($p['short_description']) ?></p>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-gray-50">
                            <?php
                                $effPrice = format_money($p['discount_price'] ?: $p['price']);
                                $commAmt = money_mul($effPrice, bcdiv(format_money($p['commission_rate']), '100.00', 4));
                            ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-baseline space-x-2">
                                    <span class="text-base font-extrabold text-brand-600">
                                        ৳<?= e($effPrice) ?>
                                    </span>
                                    <?php if (!empty($p['discount_price']) && money_comp($p['price'], $p['discount_price']) > 0): ?>
                                        <span class="text-xs text-gray-400 line-through font-semibold">
                                            ৳<?= e(format_money($p['price'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-[10px] text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded font-bold border border-amber-100">
                                    Earn ৳<?= e($commAmt) ?>
                                </span>
                            </div>

                            <div class="flex gap-2">
                                <a href="<?= url('/marketplace/product/' . $p['id']) ?>" class="w-full text-center bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold py-2 rounded-xl text-xs transition">
                                    View Details
                                </a>
                                <?php if (\App\Core\Session::get('user_id') && \App\Core\Session::get('user_role') === 'affiliate'): ?>
                                    <form action="<?= url('/affiliate/create-link') ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <button type="submit" title="Generate Affiliate Link" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-2 rounded-xl text-xs transition">
                                            🔗
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination Links -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center space-x-2 pt-6">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= url('/marketplace?page=' . $i . ($currentCategory ? '&category=' . e($currentCategory) : '') . ($searchQuery ? '&q=' . e($searchQuery) : '')) ?>" class="px-3.5 py-1.5 rounded-lg text-xs font-bold border transition <?= $i === $currentPage ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
