<div class="max-w-sm mx-auto px-4 py-8 space-y-6">

    <!-- Top Brand Logo -->
    <div class="flex justify-center">
        <a href="<?= url('/') ?>" class="flex items-center space-x-1.5 font-black text-2xl tracking-tight text-gray-900">
            <span class="bg-brand-600 text-white px-3 py-1 rounded-xl shadow-sm font-extrabold">Daraz</span>
            <span class="text-xs text-brand-600 font-bold uppercase tracking-wider self-end mb-1">Affiliate</span>
        </a>
    </div>

    <!-- Main Login Card -->
    <div class="bg-white p-6 sm:p-7 rounded-2xl border border-gray-200 shadow-xl space-y-5">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Customer Login</h1>
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

        <form action="<?= url('/login') ?>" method="POST" class="space-y-4" x-data="{ identifier: 'admin@test.com', password: '123456', show: false, submitting: false }" @submit="if(submitting){ $event.preventDefault(); return false; }; submitting = true">
            <?= csrf_field() ?>

            <!-- Quick Demo Helper Pill -->
            <div class="bg-amber-50 border border-amber-200 p-2.5 rounded-xl space-y-1.5 text-xs">
                <p class="font-bold text-amber-900 text-center uppercase tracking-wider text-[10px]">⚡ Quick Demo Autofill (1-Click)</p>
                <div class="grid grid-cols-3 gap-1">
                    <button type="button" @click="identifier = 'admin@test.com'; password = '123456'" class="bg-white hover:bg-amber-100 text-amber-800 font-bold py-1 rounded border border-amber-200 shadow-2xs text-[10px]">
                        👑 Admin
                    </button>
                    <button type="button" @click="identifier = 'customer@test.com'; password = '123456'" class="bg-white hover:bg-amber-100 text-gray-700 font-bold py-1 rounded border border-amber-200 shadow-2xs text-[10px]">
                        🛒 Customer
                    </button>
                    <button type="button" @click="identifier = 'affiliate@test.com'; password = '123456'" class="bg-white hover:bg-amber-100 text-blue-800 font-bold py-1 rounded border border-amber-200 shadow-2xs text-[10px]">
                        💼 Affiliate
                    </button>
                </div>
            </div>

            <!-- Field 1: Mobile number / Identifier -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-gray-800">Mobile number</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <input type="text" name="identifier" x-model="identifier" required placeholder="Enter mobile number or email" class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                </div>
            </div>

            <!-- Field 2: Password -->
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-gray-800">Password</label>
                    <a href="#" @click.prevent="alert('Please contact Admin to reset your password.')" class="text-xs font-medium text-blue-600 hover:underline">Forgot password?</a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input :type="show ? 'text' : 'password'" name="password" x-model="password" required placeholder="Enter your password" class="w-full pl-9 pr-10 py-2.5 rounded-xl border border-gray-300 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.959 8.959 0 013.682-.793c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Login Primary Yellow Button -->
            <button type="submit" :disabled="submitting" class="w-full bg-amber-400 hover:bg-amber-500 disabled:opacity-50 text-gray-900 font-bold py-3 rounded-xl shadow transition text-sm flex items-center justify-center">
                <span x-show="!submitting">Login</span>
                <span x-show="submitting" style="display: none;">Logging in...</span>
            </button>
        </form>

        <!-- Conditions & Privacy Notice Text -->
        <p class="text-[11px] text-gray-500 leading-normal">
            By continuing, you agree to Amazon's <a href="#" class="text-blue-600 hover:underline">Conditions of Use</a> and <a href="#" class="text-blue-600 hover:underline">Privacy Notice</a>.
        </p>

        <!-- Divider -->
        <div class="relative flex py-2 items-center">
            <div class="flex-grow border-t border-gray-200"></div>
            <span class="flex-shrink mx-3 text-gray-400 text-[11px] font-semibold">New to Amazon?</span>
            <div class="flex-grow border-t border-gray-200"></div>
        </div>

        <!-- Create Account Secondary Button -->
        <a href="<?= url('/register') ?>" class="block w-full text-center bg-gray-50 hover:bg-gray-100 text-gray-800 font-semibold py-2.5 rounded-xl border border-gray-300 text-xs shadow-2xs transition">
            Create a new account
        </a>
    </div>

    <!-- Footer Terms & Copyright -->
    <div class="text-center space-y-2 text-[11px] text-gray-500">
        <div class="space-x-4">
            <a href="#" class="text-blue-600 hover:underline">Conditions of Use</a>
            <a href="#" class="text-blue-600 hover:underline">Privacy Notice</a>
            <a href="#" class="text-blue-600 hover:underline">Help</a>
        </div>
        <p>© 2026, Amazon.com, Inc. or its affiliates</p>
    </div>
</div>
