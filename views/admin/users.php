<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">User Management</h1>
            <p class="text-xs text-gray-500">View registered accounts, status & role privileges</p>
        </div>

        <form action="<?= url('/admin/users') ?>" method="GET" class="flex gap-2 w-full md:w-80">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name, email, phone..." class="w-full px-4 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
            <button type="submit" class="bg-brand-600 text-white font-bold px-4 py-2 rounded-xl text-xs">Search</button>
        </form>
    </div>

    <?php if (!empty($success)): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-800 text-xs font-medium rounded-r">
            <?= e($success) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 text-rose-800 text-xs font-medium rounded-r">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                    <tr>
                        <th class="p-4">User ID</th>
                        <th class="p-4">Name</th>
                        <th class="p-4">Email / Phone</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Credits</th>
                        <th class="p-4">Status & OTP</th>
                        <th class="p-4">Wallet</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-700">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="p-4 font-mono">#<?= $u['id'] ?></td>
                            <td class="p-4 font-bold text-gray-900"><?= e($u['name']) ?></td>
                            <td class="p-4">
                                <p><?= e($u['email'] ?: 'No Email') ?></p>
                                <p class="text-[10px] text-gray-400"><?= e($u['phone'] ?: 'No Phone') ?></p>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= e($u['role']) ?>
                                </span>
                            </td>
                            <!-- Credit Column formatted with slash -->
                            <td class="p-4">
                                <form action="<?= url('/admin/users/update-credits') ?>" method="POST" class="flex items-center space-x-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <input type="number" name="credit_current" value="<?= (int)$u['credit_current'] ?>" class="w-12 px-1.5 py-0.5 rounded border border-gray-200 text-center text-[10px] font-bold" title="Current Credits">
                                    <span class="text-gray-400 font-bold">/</span>
                                    <input type="number" name="credit_max" value="<?= (int)$u['credit_max'] ?>" class="w-12 px-1.5 py-0.5 rounded border border-gray-200 text-center text-[10px] font-bold" title="Max Credits">
                                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-1.5 py-0.5 rounded text-[9px] font-bold">Save</button>
                                </form>
                            </td>
                            <td class="p-4 space-y-1">
                                <div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                        <?= e($u['status']) ?>
                                    </span>
                                </div>
                                <?php if (!empty($u['otp_code'])): ?>
                                    <span class="inline-block bg-amber-100 text-amber-900 px-1.5 py-0.5 rounded text-[10px] font-mono font-bold">OTP: <?= e($u['otp_code']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 space-y-1">
                                <div class="font-extrabold text-brand-600 text-sm">৳<?= e(format_money($u['balance'] ?? '0.00')) ?></div>
                                <!-- Full Balance Control (Add & Deduct) -->
                                <form action="<?= url('/admin/users/manage-balance') ?>" method="POST" class="flex flex-col space-y-1 mt-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <div class="flex items-center space-x-1">
                                        <input type="number" step="0.01" min="0.01" name="amount" required placeholder="Amount ৳" class="w-16 px-1.5 py-0.5 rounded border border-gray-200 text-[10px] font-bold focus:outline-none focus:ring-1 focus:ring-brand-500" title="Balance Amount">
                                        <button type="submit" name="action" value="add" class="bg-emerald-600 hover:bg-emerald-700 text-white px-1.5 py-0.5 rounded text-[9px] font-bold transition shadow-sm" title="Deposit / Add Balance">+ Add</button>
                                        <button type="submit" name="action" value="deduct" class="bg-rose-600 hover:bg-rose-700 text-white px-1.5 py-0.5 rounded text-[9px] font-bold transition shadow-sm" title="Deduct / Subtract Balance">- Deduct</button>
                                    </div>
                                </form>
                            </td>
                            <td class="p-4 space-y-2">
                                <!-- User Edit Info Form (Name, Email, Phone, Role, Status) -->
                                <details class="group">
                                    <summary class="cursor-pointer text-[10px] font-bold text-brand-700 hover:text-brand-900 bg-brand-50 hover:bg-brand-100 px-2 py-0.5 rounded border border-brand-200 inline-block">
                                        ✏️ Edit Details
                                    </summary>
                                    <form action="<?= url('/admin/users/update-info') ?>" method="POST" class="mt-2 p-2 bg-gray-50 rounded-lg border border-gray-200 space-y-1.5 text-[10px] w-48">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <div>
                                            <label class="font-bold text-gray-700 block">Name</label>
                                            <input type="text" name="name" value="<?= e($u['name']) ?>" required class="w-full px-1.5 py-0.5 rounded border border-gray-200">
                                        </div>
                                        <div>
                                            <label class="font-bold text-gray-700 block">Email</label>
                                            <input type="email" name="email" value="<?= e($u['email'] ?? '') ?>" class="w-full px-1.5 py-0.5 rounded border border-gray-200">
                                        </div>
                                        <div>
                                            <label class="font-bold text-gray-700 block">Phone</label>
                                            <input type="text" name="phone" value="<?= e($u['phone'] ?? '') ?>" class="w-full px-1.5 py-0.5 rounded border border-gray-200">
                                        </div>
                                        <div class="flex gap-1">
                                            <div class="w-1/2">
                                                <label class="font-bold text-gray-700 block">Role</label>
                                                <select name="role" class="w-full px-1 py-0.5 rounded border border-gray-200">
                                                    <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                                    <option value="affiliate" <?= $u['role'] === 'affiliate' ? 'selected' : '' ?>>Affiliate</option>
                                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                </select>
                                            </div>
                                            <div class="w-1/2">
                                                <label class="font-bold text-gray-700 block">Status</label>
                                                <select name="status" class="w-full px-1 py-0.5 rounded border border-gray-200">
                                                    <option value="active" <?= $u['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                    <option value="inactive" <?= $u['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                    <option value="suspended" <?= $u['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                                </select>
                                            </div>
                                        </div>
                                        <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-1 rounded text-[9px] mt-1">Save Profile</button>
                                    </form>
                                </details>

                                <!-- Role & Status Quick Toggle Form -->
                                <form action="<?= url('/admin/users/update-status') ?>" method="POST" class="flex items-center space-x-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <select name="status" class="px-1.5 py-0.5 rounded border border-gray-200 text-[10px]">
                                        <option value="active" <?= $u['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $u['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        <option value="suspended" <?= $u['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                    </select>
                                    <select name="role" class="px-1.5 py-0.5 rounded border border-gray-200 text-[10px]">
                                        <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                        <option value="affiliate" <?= $u['role'] === 'affiliate' ? 'selected' : '' ?>>Affiliate</option>
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-2 py-0.5 rounded text-[10px] font-bold">Role</button>
                                </form>

                                <!-- Password Change, OTP, & Delete User Forms -->
                                <div class="flex items-center space-x-1">
                                    <form action="<?= url('/admin/users/update-password') ?>" method="POST" class="flex items-center space-x-1">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="password" name="new_password" required minlength="6" placeholder="New pass" class="w-14 px-1 py-0.5 rounded border border-gray-200 text-[10px]">
                                        <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-1.5 py-0.5 rounded text-[9px] font-bold">Pass</button>
                                    </form>

                                    <form action="<?= url('/admin/users/generate-otp') ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-1.5 py-0.5 rounded text-[9px] font-bold" title="Generate OTP">OTP</button>
                                    </form>

                                    <!-- Delete User Form -->
                                    <form action="<?= url('/admin/users/delete') ?>" method="POST" class="inline" onsubmit="return confirm('PERMANENT DELETION WARNING:\nAre you sure you want to delete user #<?= $u['id'] ?> (<?= e($u['name']) ?>)? This cannot be undone!');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-1.5 py-0.5 rounded text-[9px] font-bold" title="Delete User">Del</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center space-x-2 p-4 border-t border-gray-100">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= url('/admin/users?page=' . $i . ($search ? '&q=' . e($search) : '')) ?>" class="px-3 py-1 rounded text-xs font-bold border <?= $i === $currentPage ? 'bg-brand-600 text-white' : 'bg-white text-gray-700' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
