<!DOCTYPE html>
<html lang="en" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin Console - Daraz Affiliate Platform') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
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
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40 md:hidden" x-cloak></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed md:static inset-y-0 left-0 w-64 bg-gray-900 text-gray-300 z-50 transform md:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col justify-between shadow-2xl">
        <div class="space-y-6 py-6">
            <!-- Brand Logo -->
            <div class="px-6 flex items-center justify-between">
                <a href="<?= url('/admin') ?>" class="flex items-center space-x-2 font-extrabold text-lg text-white">
                    <span class="bg-brand-600 px-2 py-0.5 rounded text-white text-xs uppercase tracking-wider">Admin</span>
                    <span class="text-amber-400">Console</span>
                </a>
                <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-white">✕</button>
            </div>

            <!-- Navigation Links -->
            <nav class="px-4 space-y-1 text-xs font-semibold">
                <a href="<?= url('/admin') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>📊</span> <span>Dashboard</span>
                </a>
                <a href="<?= url('/admin/users') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>👥</span> <span>Users</span>
                </a>
                <a href="<?= url('/admin/products') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>📦</span> <span>Products</span>
                </a>
                <a href="<?= url('/admin/categories') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>🏷️</span> <span>Categories</span>
                </a>
                <a href="<?= url('/admin/orders') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>🛒</span> <span>Orders</span>
                </a>
                <a href="<?= url('/admin/recharges') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>💳</span> <span>Recharges</span>
                </a>
                <a href="<?= url('/admin/withdrawals') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>🏦</span> <span>Withdrawals</span>
                </a>
                <a href="<?= url('/admin/commissions') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>💰</span> <span>Commissions</span>
                </a>
                <a href="<?= url('/admin/wallet-transactions') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>📑</span> <span>Wallet Ledger</span>
                </a>
                <a href="<?= url('/admin/audit-logs') ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-gray-800 hover:text-white transition">
                    <span>🛡️</span> <span>Audit Logs</span>
                </a>
            </nav>
        </div>

        <!-- User Info & Exit -->
        <div class="p-4 border-t border-gray-800 text-xs space-y-3">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center">A</div>
                <div class="truncate">
                    <p class="font-bold text-white truncate"><?= e(\App\Core\Session::get('user_name')) ?></p>
                    <p class="text-gray-500 uppercase text-[10px]">Administrator</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="<?= url('/') ?>" class="w-full text-center bg-gray-800 hover:bg-gray-700 text-gray-300 py-1.5 rounded-lg text-[10px] font-bold transition">
                    Main Store
                </a>
                <form action="<?= url('/logout') ?>" method="POST" class="w-full">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white py-1.5 rounded-lg text-[10px] font-bold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Bar -->
        <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between shadow-sm md:px-8">
            <button @click="sidebarOpen = true" class="md:hidden text-gray-600 text-xl focus:outline-none">
                ☰
            </button>
            <h1 class="text-lg font-extrabold text-gray-900"><?= e($title ?? 'Admin Console') ?></h1>
            <div class="text-xs font-semibold text-gray-500">
                <?= date('Y-m-d H:i:s') ?> UTC
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?= $content ?>
        </main>
    </div>

</body>
</html>
