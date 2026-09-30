<div class="max-w-md mx-auto px-4 py-8 space-y-6">

    <!-- Top Logo -->
    <div class="flex justify-center">
        <a href="<?= url('/') ?>" class="flex items-center space-x-1.5 font-black text-2xl tracking-tight text-gray-900">
            <span class="bg-brand-600 text-white px-3 py-1 rounded-xl shadow-sm font-extrabold">Daraz</span>
            <span class="text-xs text-brand-600 font-bold uppercase tracking-wider self-end mb-1">Affiliate</span>
        </a>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-amber-200 shadow-xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 bg-amber-100 text-amber-800 rounded-2xl flex items-center justify-center font-bold text-2xl mx-auto border border-amber-200">
                🔑
            </div>
            <h1 class="text-xl font-extrabold text-gray-900">Account Activation (Admin OTP)</h1>
            <p class="text-xs text-gray-500">Enter the unique 6-digit OTP code provided by Admin for your Account ID <span class="font-mono font-bold text-gray-800">#<?= e($user['id']) ?></span></p>
        </div>

        <!-- Flash Messages -->
        <?php if (!empty($error)): ?>
            <div class="bg-rose-50 border-l-4 border-rose-500 p-3 text-rose-700 text-xs font-bold rounded-r">
                <?= e($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-3 text-emerald-700 text-xs font-bold rounded-r">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <!-- Account Info Summary -->
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-xs space-y-1">
            <p class="font-bold text-gray-800">Account Name: <?= e($user['name']) ?></p>
            <p class="text-gray-500">Mobile / Email: <?= e($user['phone'] ?: $user['email']) ?></p>
            <p class="text-[11px] text-amber-800 font-semibold pt-1">
                ⚠️ Account status: Pending Admin OTP activation. Contact Admin to get your activation code.
            </p>
        </div>

        <!-- OTP Verification Form with Double Click Locking -->
        <form action="<?= url('/verify-otp') ?>" method="POST" class="space-y-4" x-data="{ submitting: false }" @submit="if(submitting){ $event.preventDefault(); return false; }; submitting = true">
            <?= csrf_field() ?>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">6-Digit Activation OTP</label>
                <input type="text" name="otp_code" required maxlength="10" placeholder="e.g. 839201" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-base font-mono font-bold text-center tracking-widest focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>

            <button type="submit" :disabled="submitting" class="w-full bg-amber-400 hover:bg-amber-500 disabled:opacity-50 text-gray-900 font-bold py-3 rounded-xl shadow transition text-sm flex items-center justify-center">
                <span x-show="!submitting">Activate Account</span>
                <span x-show="submitting" style="display: none;">Verifying OTP...</span>
            </button>
        </form>
    </div>
</div>
