<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="{ depositModal: false, withdrawModal: false }">
    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Financial Wallet</h1>
            <p class="text-xs text-gray-500">Atomic ledger balance, deposit & withdrawal management</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="depositModal = true" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg hover:shadow-brand-500/20 transition flex items-center gap-2">
                <span>➕</span> Deposit / Recharge (ডিপোজিট)
            </button>
            <button @click="withdrawModal = true" class="bg-gray-900 hover:bg-black text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow transition flex items-center gap-2">
                <span>💸</span> Withdraw Funds (উইথড্র)
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php if (!empty($success)): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl p-4 font-semibold flex items-center gap-2">
            <span>✅</span>
            <span><?= e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl p-4 font-semibold flex items-center gap-2">
            <span>⚠️</span>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Balance Card Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-brand-600 to-brand-700 text-white p-6 rounded-2xl shadow-xl space-y-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-100">Available Balance</span>
            <div class="text-3xl font-black">
                ৳<?= e(format_money($wallet['balance'])) ?>
            </div>
            <span class="inline-block text-[10px] bg-white/20 px-2 py-0.5 rounded font-bold uppercase tracking-wider text-white">
                <?= e($wallet['currency']) ?> • Atomic Ledger Secured
            </span>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Pending Commissions</span>
            <div class="text-3xl font-black text-amber-600">
                ৳<?= e(format_money($wallet['pending_balance'])) ?>
            </div>
            <p class="text-[10px] text-gray-500">Under conversion validation</p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Wallet Status</span>
            <div class="text-2xl font-black text-emerald-600 uppercase">
                <?= e($wallet['status']) ?>
            </div>
            <p class="text-[10px] text-gray-500">Protected against duplicate transactions</p>
        </div>
    </div>

    <!-- Ledger Transaction Log Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden space-y-4">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900 text-lg">Transaction History</h3>
            <span class="text-xs text-gray-400">Showing last 20 records</span>
        </div>

        <?php if (empty($transactions)): ?>
            <div class="p-8 text-center text-xs text-gray-500">
                No ledger transactions recorded yet.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                        <tr>
                            <th class="p-4">Reference</th>
                            <th class="p-4">Type</th>
                            <th class="p-4">Amount</th>
                            <th class="p-4">Balance After</th>
                            <th class="p-4">Description</th>
                            <th class="p-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <?php foreach ($transactions as $tx): ?>
                            <?php $isCredit = money_comp((string)$tx['amount'], '0.00') >= 0; ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 font-mono text-gray-500"><?= e($tx['reference_id']) ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] <?= $isCredit ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                                        <?= e($tx['type']) ?>
                                    </span>
                                </td>
                                <td class="p-4 font-bold <?= $isCredit ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= $isCredit ? '+' : '' ?>৳<?= e(format_money($tx['amount'])) ?>
                                </td>
                                <td class="p-4 font-semibold text-gray-900">৳<?= e(format_money($tx['balance_after'])) ?></td>
                                <td class="p-4"><?= e($tx['description']) ?></td>
                                <td class="p-4 text-gray-400"><?= e($tx['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Withdrawal Requests & Rejection Reasons -->
    <?php if (!empty($withdrawals)): ?>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6 space-y-4">
            <h2 class="text-lg font-bold text-gray-900">Withdrawal Payout Status</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase border-b border-gray-100">
                        <tr>
                            <th class="p-4">Ref #</th>
                            <th class="p-4">Method</th>
                            <th class="p-4">Amount</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <?php foreach ($withdrawals as $w): ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-4 font-mono font-bold"><?= e($w['reference_no']) ?></td>
                                <td class="p-4 uppercase font-bold text-[10px] text-gray-500"><?= e($w['method']) ?></td>
                                <td class="p-4 font-bold text-rose-600">৳<?= e(format_money($w['amount'])) ?></td>
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase <?= $w['status'] === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($w['status'] === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') ?>">
                                        <?= e($w['status']) ?>
                                    </span>
                                    <?php if ($w['status'] === 'rejected' && !empty($w['rejection_reason'])): ?>
                                        <div class="mt-1.5 p-2 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg text-[11px]">
                                            <span class="font-bold">Rejection Reason:</span> <?= e($w['rejection_reason']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-gray-400"><?= e($w['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Deposit / Recharge Modal -->
    <div x-show="depositModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="depositModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-6 transform transition-all">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Deposit / Wallet Recharge</h3>
                    <p class="text-xs text-gray-500">Add funds to your financial wallet</p>
                </div>
                <button @click="depositModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>

            <form action="<?= url('/wallet/recharge') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?= csrf_field() ?>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 uppercase">Payment Method</label>
                    <select name="payment_method" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="bKash">bKash Personal / Merchant</option>
                        <option value="Nagad">Nagad Personal</option>
                        <option value="Rocket">Rocket</option>
                        <option value="Binance Pay (USDT)">Binance Pay (USDT / Pay ID)</option>
                        <option value="Bank Transfer">Bank Wire Transfer</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 uppercase">Sender Mobile / Account / Binance Pay ID <span class="text-rose-500">*</span></label>
                    <input type="text" name="sender_number" placeholder="e.g. 017XXXXXXXX or Binance Pay ID: 12345678" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[10px] text-gray-400">যে নাম্বার/আইডি থেকে টাকা পাঠিয়েছেন তা উল্লেখ করুন</p>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 uppercase">Deposit Amount (BDT)</label>
                    <input type="number" step="0.01" min="10" name="amount" placeholder="e.g. 1000.00" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 uppercase">Transaction TrxID / TxHash (Optional)</label>
                    <input type="text" name="transaction_ref" placeholder="e.g. TrxID / TxHash: 9X872ABC" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Payment Proof Screenshot Upload (পেমেন্ট প্রুফ স্ক্রিনশট) -->
                <div class="space-y-1 p-3 bg-brand-50/50 rounded-xl border border-brand-100">
                    <label class="text-xs font-bold text-brand-900 uppercase flex items-center justify-between">
                        <span>📷 Payment Proof Screenshot</span>
                        <span class="text-[10px] text-brand-700 font-semibold">পেমেন্ট স্ক্রিনশট</span>
                    </label>
                    <input type="file" name="payment_proof" accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" class="w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-600 file:text-white hover:file:bg-brand-700 cursor-pointer">
                    <p class="text-[10px] text-gray-500">টাকা পাঠানোর পর প্রুফ হিসেবে স্ক্রিনশট আপলোড করুন (JPG, PNG, WEBP)</p>
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" @click="depositModal = false" class="w-1/2 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow">
                        Submit Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div x-show="withdrawModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="withdrawModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-6 transform transition-all">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h3 class="font-bold text-gray-900 text-base">Withdraw Funds</h3>
                    <p class="text-xs text-gray-500">Request payout from your available balance</p>
                </div>
                <button @click="withdrawModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
            </div>

            <form action="<?= url('/wallet/withdraw') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 uppercase">Payout Method</label>
                    <select name="payment_method" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="bKash">bKash Personal</option>
                        <option value="Nagad">Nagad Personal</option>
                        <option value="Rocket">Rocket</option>
                        <option value="Binance Pay (USDT)">Binance Pay (USDT / Pay ID)</option>
                        <option value="Bank Transfer">Bank Account</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 uppercase">Withdrawal Amount (BDT)</label>
                    <input type="number" step="0.01" min="50" name="amount" placeholder="e.g. 500.00" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-[10px] text-gray-400">Available: ৳<?= e(format_money($wallet['balance'])) ?></p>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-gray-700 uppercase">Account Number / Info</label>
                    <input type="text" name="account_details" placeholder="e.g. 01700000000" required class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="pt-4 flex gap-3">
                    <button type="button" @click="withdrawModal = false" class="w-1/2 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold shadow">
                        Submit Withdrawal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
