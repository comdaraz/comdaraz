<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Daraz Affiliate Platform') ?></title>
    <!-- Tailwind CSS CDN for instant styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN for lightweight interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316', // Daraz-style vibrant orange
                            600: '#ea580c',
                            700: '#c2410c',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased flex flex-col min-h-screen">

    <!-- Top Bar -->
    <header class="bg-brand-600 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="<?= url('/') ?>" class="flex items-center space-x-2 font-extrabold text-xl tracking-tight">
                    <span class="bg-white text-brand-600 px-2.5 py-1 rounded-lg shadow-sm">Daraz</span>
                    <span class="text-amber-200">Affiliate</span>
                </a>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-6 text-sm font-medium">
                    <a href="<?= url('/') ?>" class="hover:text-amber-200 transition-colors">Home</a>
                    <a href="<?= url('/marketplace') ?>" class="hover:text-amber-200 transition-colors">Marketplace</a>
                    <?php if (\App\Core\Session::get('user_id')): ?>
                        <a href="<?= url('/cart') ?>" class="hover:text-amber-200 transition-colors flex items-center space-x-1">
                            <span>Cart</span>
                        </a>
                        <a href="<?= url('/my-orders') ?>" class="hover:text-amber-200 transition-colors">My Orders</a>
                        <a href="<?= url('/wallet') ?>" class="hover:text-amber-200 transition-colors">Wallet</a>
                        <a href="<?= url('/profile') ?>" class="hover:text-amber-200 transition-colors">Profile</a>
                        <a href="<?= url('/affiliate') ?>" class="hover:text-amber-200 transition-colors">Affiliate Panel</a>
                    <?php endif; ?>
                </nav>

                <!-- Auth / User Actions -->
                <div class="flex items-center space-x-4 text-sm font-medium">
                    <?php if (\App\Core\Session::get('user_id')): ?>
                        <?php
                            $uCred = \App\Core\Database::fetchOne("SELECT credit_current, credit_max FROM users WHERE id = :uid LIMIT 1", ['uid' => \App\Core\Session::get('user_id')]);
                            $creditBadge = $uCred ? " <span class='bg-amber-500/20 text-amber-200 text-xs px-2 py-0.5 rounded-full font-bold ml-1'>" . (int)$uCred['credit_current'] . " / " . (int)$uCred['credit_max'] . "</span>" : "";
                        ?>
                        <a href="<?= url('/profile') ?>" class="hidden sm:inline-flex items-center text-amber-100 hover:text-white transition">
                            <span>Hi, <?= e(\App\Core\Session::get('user_name')) ?></span>
                            <?= $creditBadge ?>
                        </a>
                        <form action="<?= url('/logout') ?>" method="POST" class="inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="bg-brand-700 hover:bg-brand-800 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition">
                                Logout
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="hover:text-amber-200">Login</a>
                        <a href="<?= url('/register') ?>" class="bg-white text-brand-600 hover:bg-amber-50 px-4 py-1.5 rounded-lg text-xs font-bold shadow-sm transition">
                            Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-grow pb-16 md:pb-0">
        <?= $content ?>
    </main>

    <!-- Mobile Bottom Navigation Bar (Responsive 4-Button Menu) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-lg px-2 py-1.5">
        <div class="grid grid-cols-4 gap-1 text-center">
            <a href="<?= url('/') ?>" class="flex flex-col items-center py-1 text-xs text-gray-600 hover:text-brand-600 font-medium">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Home</span>
            </a>
            <a href="<?= url('/marketplace') ?>" class="flex flex-col items-center py-1 text-xs text-gray-600 hover:text-brand-600 font-medium">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z" />
                </svg>
                <span>Marketplace</span>
            </a>
            <a href="<?= url('/cart') ?>" class="flex flex-col items-center py-1 text-xs text-gray-600 hover:text-brand-600 font-medium">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Cart</span>
            </a>
            <a href="<?= url(\App\Core\Session::get('user_id') ? '/profile' : '/login') ?>" class="flex flex-col items-center py-1 text-xs text-gray-600 hover:text-brand-600 font-medium">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Account</span>
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8 border-t border-gray-800 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs space-y-2">
            <p class="font-semibold text-gray-300">Daraz-Style Nano Modular Affiliate Platform &copy; <?= date('Y') ?></p>
            <p>Built with PHP 8.2+ PDO, Custom Nano Router, Tailwind CSS & Alpine.js. Optimized for Shared Hosting.</p>
        </div>
    </footer>

</body>
</html>
