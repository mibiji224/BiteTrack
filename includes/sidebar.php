<?php
// Get current page for active state styling
$current_page = basename($_SERVER['PHP_SELF']);

// Logic for Community Badge: 
// Assumes session is started in dashboard.php
$show_community_badge = !isset($_SESSION['last_sns_visit']);

// Menu Items Configuration
$menu_items = [
    [
        'name' => 'Dashboard',
        'link' => 'dashboard.php',
        'icon' => '<i class="fas fa-home text-lg"></i>'
    ],
    [
        'name' => 'Profile & Goals',
        'link' => 'goals.php',
        'icon' => '<i class="fas fa-bullseye text-lg"></i>'
    ],
    [
        'name' => 'Meal Logs',
        'link' => 'meals.php',
        'icon' => '<i class="fas fa-utensils text-lg"></i>'
    ],
    [
        'name' => 'Community',
        'link' => 'sns.php',
        'icon' => '<i class="fas fa-users text-lg"></i>',
        'badge' => $show_community_badge
    ]
];
?>

<div 
    x-show="sidebarOpen" 
    @click="sidebarOpen = false"
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
></div>

<aside 
    :class="sidebarOpen ? 'translate-x-0 lg:w-64' : '-translate-x-full lg:w-0 lg:translate-x-0 lg:overflow-hidden'"
    class="fixed inset-y-0 left-0 z-50 bg-white border-r border-gray-200 
           transform transition-all duration-300 ease-in-out 
           lg:static lg:inset-auto flex flex-col shrink-0 shadow-xl lg:shadow-none whitespace-nowrap"
>
    
    <div class="h-20 flex items-center justify-between px-6 border-b border-gray-50 shrink-0">
        <a href="dashboard.php" class="flex items-center gap-3 group overflow-hidden">
            <div class="p-2 bg-indigo-50 rounded-xl group-hover:bg-indigo-100 transition-colors duration-300 shrink-0">
                <img src="photos/plan.png" class="h-8 w-auto" alt="Logo">
            </div>
            <span class="text-xl font-bold text-gray-800 tracking-tight group-hover:text-indigo-600 transition-colors">
                BiteTrack
            </span>
        </a>
        
        <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto overflow-x-hidden">
        <?php foreach ($menu_items as $item): 
            $isActive = ($current_page == $item['link']);
            
            // Dynamic Classes
            $baseClass = "flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 relative group overflow-hidden";
            $activeClass = "bg-indigo-50 text-indigo-600 shadow-sm border border-indigo-100";
            $inactiveClass = "text-gray-600 hover:bg-gray-50 hover:text-gray-900 border border-transparent";
            
            $finalClass = $isActive ? $activeClass : $inactiveClass;
            $iconColor = $isActive ? "text-indigo-600" : "text-gray-400 group-hover:text-gray-600";
        ?>
            <a href="<?= $item['link'] ?>" class="<?= $baseClass ?> <?= $finalClass ?>" @click="if(window.innerWidth < 1024) sidebarOpen = false">
                
                <span class="<?= $iconColor ?> transition-colors duration-200 w-6 text-center flex justify-center shrink-0">
                    <?= $item['icon'] ?>
                </span>

                <span class="whitespace-nowrap"><?= $item['name'] ?></span>
                
                <?php if ($isActive): ?>
                    <span class="absolute right-3 w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                <?php endif; ?>

                <?php if (isset($item['badge']) && $item['badge']): ?>
                    <span class="ml-auto flex h-2.5 w-2.5 relative shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="p-4 border-t border-gray-100 shrink-0 overflow-hidden">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 transition-all duration-200 group border border-red-100">
            <div class="p-1.5 bg-red-100 rounded-lg group-hover:bg-red-200 transition-colors shrink-0">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <span class="whitespace-nowrap">Sign Out</span>
        </a>
    </div>
</aside>