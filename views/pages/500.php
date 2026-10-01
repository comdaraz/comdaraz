<div class="max-w-md mx-auto px-4 py-20 text-center space-y-4">
    <div class="text-6xl font-black text-brand-600">500</div>
    <h2 class="text-2xl font-bold text-gray-900">Something Went Wrong</h2>
    <p class="text-xs text-gray-500"><?= e($error ?? 'An unexpected error occurred. Please try again or return to the homepage.') ?></p>
    <div>
        <a href="<?= url('/') ?>" class="inline-block bg-brand-600 hover:bg-brand-700 text-white font-bold px-6 py-2.5 rounded-xl text-xs transition">
            Back to Home
        </a>
    </div>
</div>
