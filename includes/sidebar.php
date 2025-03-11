<aside class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 transition-all duration-300 lg:static lg:translate-x-0 dark:border-gray-800 dark:bg-black -translate-x-full shadow-lg">
    <div class="flex items-center gap-4 p-2 mb-2 mt-2">
        <a href="dashboard.php" class="flex items-center gap-1 logo-hover">
            <img src="photos/plan.png" class="h-10 w-auto" alt="BiteTrack Logo">
            <span class="text-lg font-bold text-gray-900">BiteTrack</span>
        </a>
    </div>
    <hr class="border-gray-300 w-full mx-0">
    <!-- Other sidebar content -->
    <section>
        <div id="sb_userprofile" class="absolute bottom-4 right-4 flex items-center gap-4">
            <a href="logout.php" id="log_out" class="auth-buttons flex items-center gap-2 px-5 py-2 rounded-full text-black font-semibold shadow-md bg-gradient-to-r from-yellow-400 to-red-400 hover:scale-105 hover:opacity-90 transition duration-300 ease-in-out">
                Logout
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </section>
</aside>