<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BikeMo Admin</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bikeDark: '#121F17',
                        bikeHover: '#1B2E22',
                        bikeGreen: '#B4F23E',
                    }
                }
            }
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-[#121F17] text-white flex flex-col justify-between hidden md:flex border-r border-gray-800">
        <div>
            <!-- Brand Logo -->
            <div class="flex items-center gap-3 px-6 py-6">
                <div class="bg-[#B4F23E] text-[#121F17] p-2 rounded-xl font-bold text-lg flex items-center justify-center">
                    <i class="fa-solid fa-bicycle"></i>
                </div>
                <span class="text-xl font-bold tracking-wide">BikeMo</span>
            </div>

            <!-- Workspace Navigation -->
            <div class="px-4 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-3">Workspace</p>
                <nav class="space-y-1">
                    <a href="overview.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo ($current_page == 'overview.php') ? 'bg-[#1B2E22] text-white font-semibold' : 'text-gray-300 hover:bg-[#1B2E22]/50 hover:text-white'; ?>">
                        <i class="fa-solid fa-table-cells-large w-5"></i> Overview
                    </a>
                    <a href="bikes.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo ($current_page == 'bikes.php') ? 'bg-[#1B2E22] text-white font-semibold' : 'text-gray-300 hover:bg-[#1B2E22]/50 hover:text-white'; ?>">
                        <i class="fa-solid fa-bicycle w-5"></i> Bikes
                    </a>
                    <a href="rentals.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition justify-between <?php echo ($current_page == 'rentals.php') ? 'bg-[#1B2E22] text-white font-semibold' : 'text-gray-300 hover:bg-[#1B2E22]/50 hover:text-white'; ?>">
                        <span class="flex items-center gap-3"><i class="fa-solid fa-file-lines w-5"></i> Rentals</span>
                        <span class="bg-[#B4F23E] text-[#121F17] text-xs font-bold px-2 py-0.5 rounded-full">8</span>
                    </a>
                    <a href="customers.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo ($current_page == 'customers.php') ? 'bg-[#1B2E22] text-white font-semibold' : 'text-gray-300 hover:bg-[#1B2E22]/50 hover:text-white'; ?>">
                        <i class="fa-solid fa-users w-5"></i> Customers
                    </a>
                    <a href="reports.php" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?php echo ($current_page == 'reports.php') ? 'bg-[#1B2E22] text-white font-semibold' : 'text-gray-300 hover:bg-[#1B2E22]/50 hover:text-white'; ?>">
                        <i class="fa-solid fa-chart-line w-5"></i> Reports
                    </a>
                </nav>
            </div>
        </div>

        <!-- Sidebar Footer / User Profile -->
        <div class="p-4 border-t border-gray-800 space-y-3">
            <div class="bg-[#1B2E22]/60 p-3 rounded-xl border border-gray-800">
                <div class="flex items-center justify-between text-xs text-gray-300 mb-1">
                    <span class="font-semibold text-white">System settings</span>
                </div>
                <p class="text-[11px] text-gray-400 mb-2">Manage preferences</p>
                <a href="settings.php" class="block text-center bg-[#121F17] hover:bg-black text-xs font-medium text-white py-1.5 rounded-lg border border-gray-700 transition">Open settings</a>
            </div>

            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 text-white w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm">AR</div>
                    <div>
                        <h4 class="text-xs font-bold text-white">Andrea R.</h4>
                        <p class="text-[10px] text-gray-400">Administrator</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-xs text-gray-500"></i>
            </div>
        </div>
    </aside>

    <!-- Main Content Area Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
            <div class="relative w-96">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-search text-xs"></i>
                </span>
                <input type="text" placeholder="Search bikes, customers, rentals..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm focus:outline-none focus:bg-white focus:border-gray-400 transition">
            </div>
            <div class="flex items-center gap-4">
                <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition">
                    <i class="fa-regular fa-bell"></i>
                </button>
            </div>
        </header>

        <!-- Dynamic Page Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50 p-8">