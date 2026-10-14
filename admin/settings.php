<?php include 'header.php'; ?>
<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <p class="text-xs font-semibold text-emerald-700 mb-1">Administration</p>
        <h1 class="text-3xl font-extrabold text-gray-900">System settings</h1>
        <p class="text-sm text-gray-500 mt-1">Configure your rental rules, notifications, and business profile.</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-3xl">
        <h3 class="font-bold text-gray-900 text-lg">Business profile</h3>
        <p class="text-xs text-gray-500 mb-6">Details shown in reservations and customer receipts.</p>
        <form class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Business name</label>
                <input type="text" value="BikeMo Rentals" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:bg-white focus:outline-none">
            </div>
            <button type="submit" class="bg-[#121F17] hover:bg-black text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition">Save changes</button>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?></main></body></html>