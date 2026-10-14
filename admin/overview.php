<?php include 'header.php'; ?>

<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-end">
        <div>
            <p class="text-xs font-semibold text-emerald-700 mb-1">Monday, October 16</p>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Good morning, Andrea</h1>
            <p class="text-sm text-gray-500 mt-1">Here's what's happening with BikeMo today.</p>
        </div>
        <a href="reports.php" class="text-sm font-semibold text-emerald-700 hover:underline flex items-center gap-1">View operations report <i class="fa-solid fa-chevron-right text-xs"></i></a>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-purple-50 text-purple-600 p-2.5 rounded-xl"><i class="fa-solid fa-bicycle"></i></div>
                <span class="text-xs font-semibold text-gray-400">+4 this month</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900">48</h3>
            <p class="text-xs font-medium text-gray-500 mt-1">Total bikes</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-emerald-50 text-emerald-600 p-2.5 rounded-xl"><i class="fa-solid fa-check"></i></div>
                <span class="text-xs font-semibold text-gray-500">65% of the fleet</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900">31</h3>
            <p class="text-xs font-medium text-gray-500 mt-1">Available now</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-amber-50 text-amber-600 p-2.5 rounded-xl"><i class="fa-solid fa-clock"></i></div>
                <span class="text-xs font-semibold text-gray-500">3 due today</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900">12</h3>
            <p class="text-xs font-medium text-gray-500 mt-1">Active rentals</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-rose-50 text-rose-600 p-2.5 rounded-xl"><i class="fa-solid fa-wrench"></i></div>
                <span class="text-xs font-semibold text-gray-500">2 ready tomorrow</span>
            </div>
            <h3 class="text-3xl font-black text-gray-900">5</h3>
            <p class="text-xs font-medium text-gray-500 mt-1">Maintenance</p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php';  ?>
</main>
</body>
</html>