<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900"><?= $product ? 'Edit Product #' . $product['id'] : 'Create New Product' ?></h1>
        <a href="<?= url('/admin/products') ?>" class="text-xs font-bold text-gray-500 hover:text-gray-900">← Back to Products</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 text-rose-800 text-xs font-medium rounded-r">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form action="<?= url('/admin/products/save') ?>" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
        <?= csrf_field() ?>
        <?php if ($product): ?>
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <?php endif; ?>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Product Title</label>
            <input type="text" name="title" required value="<?= e($product['title'] ?? '') ?>" placeholder="e.g. Wireless Noise-Canceling Earbuds Pro" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">SKU Code</label>
                <input type="text" name="sku" required value="<?= e($product['sku'] ?? '') ?>" placeholder="EAR-PRO-01" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Category</label>
                <select name="category_id" required class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Regular Price (৳)</label>
                <input type="text" name="price" required value="<?= e($product['price'] ?? '0.00') ?>" placeholder="3500.00" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Discount Price (৳)</label>
                <input type="text" name="discount_price" value="<?= e($product['discount_price'] ?? '') ?>" placeholder="2990.00" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Stock Quantity</label>
                <input type="number" name="stock_quantity" required min="0" value="<?= e($product['stock_quantity'] ?? '50') ?>" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Affiliate Commission Rate (%)</label>
                <input type="text" name="commission_rate" required value="<?= e($product['commission_rate'] ?? '5.00') ?>" placeholder="5.00" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="active" <?= ($product && $product['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($product && $product['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    <option value="out_of_stock" <?= ($product && $product['status'] === 'out_of_stock') ? 'selected' : '' ?>>Out of Stock</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Available Sizes (Comma Separated)</label>
                <input type="text" name="size_options" value="<?= e($product['size_options'] ?? '') ?>" placeholder="e.g. S, M, L, XL, XXL" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-[10px] text-gray-400">কমা (,) দিয়ে সাইজগুলো লিখুন (যেমন: S, M, L, XL)</span>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Available Colors (Comma Separated)</label>
                <input type="text" name="color_options" value="<?= e($product['color_options'] ?? '') ?>" placeholder="e.g. Black, White, Red, Blue, Gold" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-[10px] text-gray-400">কমা (,) দিয়ে কালারগুলো লিখুন (যেমন: Red, Black, Blue)</span>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Product Image (Upload File or Enter Image URL)</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <span class="block text-[11px] text-gray-500 font-semibold mb-1">Upload File (JPG, PNG, WEBP):</span>
                    <input type="file" name="image_file" accept="image/*" class="w-full px-3 py-1.5 rounded-xl border border-gray-200 text-xs bg-gray-50 text-gray-700">
                </div>
                <div>
                    <span class="block text-[11px] text-gray-500 font-semibold mb-1">OR Enter Image URL:</span>
                    <input type="text" name="main_image" value="<?= e($product['main_image'] ?? '') ?>" placeholder="https://..." class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
            <?php if (!empty($product['main_image'])): ?>
                <div class="mt-2 flex items-center space-x-2">
                    <span class="text-[10px] text-gray-400 font-bold">Current Preview:</span>
                    <img src="<?= e($product['main_image']) ?>" class="w-10 h-10 object-cover rounded-lg border border-gray-200">
                </div>
            <?php endif; ?>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Short Overview</label>
            <textarea name="short_description" rows="2" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500"><?= e($product['short_description'] ?? '') ?></textarea>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Full Specifications & Description</label>
            <textarea name="full_description" rows="4" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500"><?= e($product['full_description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-xl shadow-md transition text-xs uppercase tracking-wider">
            <?= $product ? 'Update Product' : 'Create Product' ?>
        </button>
    </form>
</div>
