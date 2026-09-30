<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Product Catalog Management</h1>
            <p class="text-xs text-gray-500">Manage products, stock quantity, pricing & commission rates</p>
        </div>

        <div class="flex gap-2 w-full md:w-auto">
            <form action="<?= url('/admin/products') ?>" method="GET" class="flex gap-2">
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search SKU or Title..." class="px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                <button type="submit" class="bg-brand-600 text-white font-bold px-4 py-2 rounded-xl text-xs">Search</button>
            </form>
            <a href="<?= url('/admin/products/create') ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl text-xs flex items-center whitespace-nowrap">
                + Add Product
            </a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-800 text-xs font-medium rounded-r">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                    <tr>
                        <th class="p-4">SKU</th>
                        <th class="p-4">Title</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Commission</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-700">
                    <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 font-mono font-bold"><?= e($p['sku']) ?></td>
                            <td class="p-4 font-bold text-gray-900"><?= e($p['title']) ?></td>
                            <td class="p-4 text-gray-500"><?= e($p['category_name']) ?></td>
                            <td class="p-4 font-bold text-brand-600">
                                ৳<?= e(format_money($p['discount_price'] ?: $p['price'])) ?>
                            </td>
                            <td class="p-4 font-bold <?= $p['stock_quantity'] > 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                <?= $p['stock_quantity'] ?> units
                            </td>
                            <td class="p-4 font-bold text-purple-600"><?= e($p['commission_rate']) ?>%</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $p['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                    <?= e($p['status']) ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <a href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>" class="text-brand-600 font-bold hover:underline">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
