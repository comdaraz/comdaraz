<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-6">
        <h1 class="text-xl font-bold text-gray-900">Category Management</h1>

        <?php if (!empty($success)): ?>
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-800 text-xs font-medium rounded-r">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Category Name</th>
                        <th class="p-4">Slug</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Created Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-700">
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 font-mono">#<?= $cat['id'] ?></td>
                            <td class="p-4 font-bold text-gray-900"><?= e($cat['name']) ?></td>
                            <td class="p-4 font-mono text-gray-500"><?= e($cat['slug']) ?></td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $cat['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                    <?= e($cat['status']) ?>
                                </span>
                            </td>
                            <td class="p-4 text-gray-400"><?= e($cat['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Category Sidebar Form -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-gray-900">Add Category</h2>
        <form action="<?= url('/admin/categories/save') ?>" method="POST" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Category Name</label>
                <input type="text" name="name" required placeholder="e.g. Home Appliances" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-2.5 rounded-xl shadow-md transition text-xs uppercase tracking-wider">
                Create Category
            </button>
        </form>
    </div>
</div>
