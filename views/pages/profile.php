<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    <!-- Flash Notifications -->
    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 text-rose-700 text-xs font-bold rounded-r shadow-sm">
            <?= e($error) ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 text-emerald-700 text-xs font-bold rounded-r shadow-sm">
            <?= e($success) ?>
        </div>
    <?php endif; ?>

    <!-- User Header Card -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-brand-500 to-amber-500 text-white rounded-2xl flex items-center justify-center font-black text-2xl shadow-md">
                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-extrabold text-gray-900"><?= e($user['name']) ?></h1>
                    <!-- Credit Indicator formatted with slash -->
                    <span class="bg-amber-100 text-amber-800 text-xs px-3 py-1 rounded-full font-bold border border-amber-200">
                        Credit: <?= (int)$user['credit_current'] ?> / <?= (int)$user['credit_max'] ?>
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    <?= e($user['email'] ?: $user['phone'] ?: 'No Contact') ?> • 
                    <span class="capitalize font-semibold text-brand-600"><?= e($user['role']) ?></span>
                </p>
                <div class="mt-2 flex items-center space-x-2 text-[11px]">
                    <?php if ($user['is_verified']): ?>
                        <span class="bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded font-bold">✓ Verified Account</span>
                    <?php else: ?>
                        <span class="bg-rose-100 text-rose-800 px-2.5 py-0.5 rounded font-bold">⚠️ Unverified (OTP Required)</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Wallet Summary Quick Pill -->
        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-right space-y-1">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Wallet Balance</span>
            <p class="text-2xl font-black text-brand-600">৳<?= format_money($wallet['balance'] ?? '0.00') ?></p>
            <a href="<?= url('/wallet') ?>" class="inline-block text-xs text-brand-600 font-bold hover:underline">Manage Wallet →</a>
        </div>
    </div>

    <!-- Task Completion Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Task Completion & Credits</h2>
                <p class="text-xs text-gray-500">Track and complete daily tasks assigned by Admin</p>
            </div>
            <div class="text-right">
                <span class="text-sm font-black text-brand-600">
                    <?= (int)$user['credit_current'] ?> / <?= (int)$user['credit_max'] ?> Credits
                </span>
            </div>
        </div>

        <!-- Progress Bar -->
        <?php 
            $max = (int)$user['credit_max'];
            $current = (int)$user['credit_current'];
            $pct = $max > 0 ? min(100, round(($current / $max) * 100)) : 0;
        ?>
        <div class="w-full bg-gray-100 h-4 rounded-full overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-brand-500 to-amber-500 h-full transition-all duration-500 font-bold text-[10px] text-white flex items-center justify-center" style="width: <?= $pct ?>%">
                <?= $pct ?>%
            </div>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4 bg-amber-50/50 p-4 rounded-xl border border-amber-100">
            <div class="text-xs text-gray-700">
                <p class="font-bold">Admin Granted Quota: <?= $current ?> / <?= $max ?></p>
                <p class="text-gray-500 text-[11px]">Admin controls your credit quota. Click below to complete your task.</p>
            </div>

            <form action="<?= url('/profile/complete-task') ?>" method="POST">
                <?= csrf_field() ?>
                <button type="submit" <?= ($max <= 0 || $current >= $max) ? 'disabled' : '' ?> class="bg-brand-600 hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow transition">
                    <?= ($current >= $max && $max > 0) ? '✓ All Tasks Done' : 'Complete Next Task' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- My Recent Orders Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900">My Orders & Purchase History</h2>
                <p class="text-xs text-gray-500">Track your order statuses, payment breakdowns, and due amounts</p>
            </div>
            <a href="<?= url('/my-orders') ?>" class="text-xs text-brand-600 font-bold hover:underline">View All Orders →</a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="text-center py-6 text-xs text-gray-500">
                You haven't placed any orders yet. <a href="<?= url('/marketplace') ?>" class="text-brand-600 font-bold hover:underline">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                        <tr>
                            <th class="p-3">Order Number</th>
                            <th class="p-3">Total Amount</th>
                            <th class="p-3">Paid / Due</th>
                            <th class="p-3">Payment</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <?php foreach ($orders as $o): ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-3 font-bold font-mono text-gray-900">#<?= e($o['order_number']) ?></td>
                                <td class="p-3 font-bold text-brand-600">৳<?= e(format_money($o['total_amount'])) ?></td>
                                <td class="p-3">
                                    <span class="text-emerald-600 font-bold block">Paid: ৳<?= e(format_money($o['paid_amount'])) ?></span>
                                    <span class="text-rose-600 font-bold block">Due: ৳<?= e(format_money($o['due_amount'])) ?></span>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $o['payment_status'] === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($o['payment_status'] === 'partially_paid' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                        <?= e($o['payment_method']) ?> • <?= e(str_replace('_', ' ', $o['payment_status'])) ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-800">
                                        <?= e($o['order_status']) ?>
                                    </span>
                                </td>
                                <td class="p-3 text-gray-400"><?= e($o['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- OTP Verification Section -->
    <?php if (!$user['is_verified'] || !empty($user['otp_code'])): ?>
        <div class="bg-white rounded-2xl border border-amber-200 shadow-md p-6 space-y-4">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-amber-100 text-amber-700 rounded-lg flex items-center justify-center font-bold">🔑</div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Account Verification (Admin Manual OTP)</h3>
                    <p class="text-xs text-gray-500">Enter the single-use OTP provided by Admin to verify your account</p>
                </div>
            </div>

            <form action="<?= url('/profile/verify-otp') ?>" method="POST" class="flex items-center gap-3">
                <?= csrf_field() ?>
                <input type="text" name="otp_code" required placeholder="Enter 6-digit OTP" class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500">
                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-4 py-2 rounded-xl text-xs">Verify OTP</button>
            </form>
        </div>
    <?php endif; ?>

</div>
