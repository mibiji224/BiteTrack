
<?php
session_start();
require_once 'includes/header.php';
$host = "localhost";
$username = "root";
$password = "";
$database = "nutrition_tracker";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION["user_id"];

// Get current date and the start of the current week (Monday)
$currentDate = date('Y-m-d');
$startOfWeek = date('Y-m-d', strtotime('monday this week'));

// Default period is week
$period = 'week';  // Set period to 'week' to only consider weekly data

// Fetch weekly intake (from Monday to today)
$totalIntakeQuery = "SELECT SUM(calories) AS total_calories, SUM(protein) AS total_protein, SUM(carbs) AS total_carbs
                     FROM meals WHERE user_id = ? AND DATE(date_added) >= ?";
$stmt = $conn->prepare($totalIntakeQuery);
$stmt->bind_param("is", $user_id, $startOfWeek);
$stmt->execute();
$totalIntakeResult = $stmt->get_result()->fetch_assoc();
$totalCalories = $totalIntakeResult['total_calories'] ?: 0;
$totalProtein = $totalIntakeResult['total_protein'] ?: 0;
$totalCarbs = $totalIntakeResult['total_carbs'] ?: 0;
$stmt->close();

// Pagination Setup
$limit = 13; // Meals per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get Total Meals Count for this week
$totalMealsQuery = "SELECT COUNT(*) as total FROM meals WHERE user_id = ? AND DATE(date_added) >= ?";
$stmt = $conn->prepare($totalMealsQuery);
$stmt->bind_param("is", $user_id, $startOfWeek);
$stmt->execute();
$totalMealsResult = $stmt->get_result()->fetch_assoc();
$totalMeals = $totalMealsResult['total'];
$totalPages = ceil($totalMeals / $limit);
$stmt->close();

// Fetch Meals with Pagination for this week
$meals = [];
$sql = "SELECT meal_name, calories, protein, carbs, date_added FROM meals WHERE user_id = ? AND DATE(date_added) >= ? ORDER BY date_added DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isii", $user_id, $startOfWeek, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}
$stmt->close();
$conn->close();
?>



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script defer src="script.js"></script> <!-- Link to JavaScript -->
    <script src="https://cdn.tailwindcss.com"></script>
    <title>BiteTrack - Your Nutrient Tracker!</title>
    <script src="js/food_db_api.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Flowbite (for dropdowns, animations, etc.) -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="custom/css/custom.css">
    <link rel="stylesheet" href="css/button.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <script>
        // JavaScript to toggle dropdown
        document.addEventListener("DOMContentLoaded", function () {
            const toggleBtn = document.getElementById("dashboardToggle");
            const dropdownMenu = document.getElementById("dashboardDropdown");

            toggleBtn.addEventListener("click", function (event) {
                event.preventDefault();
                dropdownMenu.classList.toggle("max-h-0");
                dropdownMenu.classList.toggle("opacity-0");
                dropdownMenu.classList.toggle("max-h-[200px]");
                dropdownMenu.classList.toggle("opacity-100");
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- ApexCharts Script -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- ApexCharts Script -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var options = {
                series: [75.55],
                chart: {
                    height: 229,
                    type: "radialBar",
                },
                plotOptions: {
                    radialBar: {
                        hollow: {
                            size: "70%",
                        },

                        // Apply the gradient to the radial bar
                        track: {
                            background: '#e6e6e6', // Light background color for track
                        }
                    },
                },
                colors: ['#FCD404', '#FB6F74'],  // Define the gradient colors
                fill: {
                    type: 'gradient',  // Use gradient fill
                    gradient: {
                        shade: 'light',
                        type: 'linear',
                        shadeIntensity: 0.5,
                        gradientToColors: ['#FB6F74'],  // Gradient from #FCD404 to #FB6F74
                        inverseColors: false,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 100]
                    }
                },
                labels: ["Progress"],
            };

            var chart = new ApexCharts(document.querySelector("#chartTwo"), options);
            chart.render();
        });


        async function fetchCalories() {
            const query = document.getElementById("foodInput").value;
            if (!query) {
                alert("Please enter a food item.");
                return;
            }

            const apiKey = "FmEM2rbCs+c9j0rAbzaJRA==IVZqSzB9NOhvqjAs"; // Replace with your CalorieNinjas API key
            const url = `https://api.calorieninjas.com/v1/nutrition?query=${encodeURIComponent(query)}`;

            try {
                const response = await fetch(url, {
                    headers: { 'X-Api-Key': apiKey }
                });

                const data = await response.json();

                if (data.items && data.items.length > 0) {
                    let item = data.items[0]; // Taking the first result

                    document.getElementById("bmr1").innerHTML = `<strong>${item.calories}</strong> kcal`;
                    document.getElementById("bmr2").innerHTML = `<strong>${item.protein_g}</strong> g`;
                    document.getElementById("bmr3").innerHTML = `<strong>${item.carbohydrates_total_g}</strong> g`;
                } else {
                    document.getElementById("bmr1").innerHTML = "No data found";
                    document.getElementById("bmr2").innerHTML = "No data found";
                    document.getElementById("bmr3").innerHTML = "No data found";
                }
            } catch (error) {
                console.error("Error fetching data:", error);
                document.getElementById("bmr1").innerHTML = "Error fetching data";
                document.getElementById("bmr2").innerHTML = "Error fetching data";
                document.getElementById("bmr3").innerHTML = "Error fetching data";
            }
        }
    </script>




</head>

<body class="flex flex-col min-h-screen">

    <main class="flex-grow">
        <!-- MAIN CONTENT -->

        <!-- ===== Page Wrapper Start ===== -->
        <div class="flex h-screen overflow-hidden">
            <!-- ===== Sidebar Start ===== -->
            <aside :class="sidebarToggle ? 'translate-x-0 lg:w-[90px]' : '-translate-x-full'"
                class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 transition-all duration-300 lg:static lg:translate-x-0 dark:border-gray-800 dark:bg-black -translate-x-full shadow-lg"
                @click.outside="sidebarToggle = false">

                <!-- SIDEBAR LOGO -->
                <div class="flex items-center gap-4 p-2 mb-2 mt-2">
                    <a href="dashboard.php" class="flex items-center gap-1 logo-hover">
                        <img src="photos/plan.png" class="h-10 w-auto" alt="BiteTrack Logo">
                        <span class="text-lg font-bold text-gray-900">BiteTrack</span>
                    </a>
                </div>

                <hr class="border-gray-300 w-full mx-0">


                <div class="container mx-auto p-6 pt-2">
                    <h1 class="text-3xl font-semibold text-gray-800"><span
                            class="text-2xl font-semibold text-gray-800">Welcome to your</span> Dashboard</h1>
                    <p class="text-gray-600 mt-2">Manage your Goals, Progress, and other features.</p>
                </div>
                <hr class="border-gray-300 w-full mx-0">
                <!-- SIDEBAR HEADER -->

                <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar">
                    <!-- SIDEBAR Menu -->
                    <nav class="mt-4">
                        <ul class="flex flex-col gap-4">

                            <!-- Menu Item: Dashboard -->
                            <li>
                                <a href="#"
                                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                    <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z" />
                                        <path
                                            d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1.969 1.969 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z" />
                                        <path
                                            d="M8.961 16a.93.93 0 0 0 .189-.019l3.4-.679a.961.961 0 0 0 .49-.263l6.118-6.117a2.884 2.884 0 0 0-4.079-4.078l-6.117 6.117a.96.96 0 0 0-.263.491l-.679 3.4A.961.961 0 0 0 8.961 16Zm7.477-9.8a.958.958 0 0 1 .68-.281.961.961 0 0 1 .682 1.644l-.315.315-1.36-1.36.313-.318Zm-5.911 5.911 4.236-4.236 1.359 1.359-4.236 4.237-1.7.339.341-1.699Z" />
                                    </svg>
                                    <span class="flex-1 ms-3 whitespace-nowrap">Edit Goals</span>
                                </a>
                            </li>
                            <li>
                                <a href="meals.php"
                                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" />
                                    </svg>

                                    <span class="flex-1 ms-3 whitespace-nowrap">Meal Logs</span>
                                </a>
                            </li>
                            <li>
                                <a href="#"
                                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                    <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                        viewBox="0 0 18 18">
                                        <path
                                            d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z" />
                                    </svg>
                                    <span class="flex-1 ms-3 whitespace-nowrap">Progress Tracking</span>
                                    <span
                                        class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300">Pro</span>
                                </a>
                            </li>
                            <!-- Side Bar: COMMUNITY PAGE -->
                            <li>
                                <a href="#"
                                    class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                                    </svg>

                                    <span class="flex-1 ms-3 whitespace-nowrap">Community Page</span>
                                    <span
                                        class="inline-flex items-center justify-center w-3 h-3 p-3 ms-3 text-sm font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300">3</span>
                                </a>
                            </li>
                        </ul>
                </div>
                <section>
                    <div id="sb_userprofile" class="absolute bottom-4 right-4 flex items-center gap-4">

                        <!-- Logout Button -->
                        <a href="logout.php" id="log_out" class="auth-buttons flex items-center gap-2 px-5 py-2 rounded-full text-black font-semibold shadow-md 
               bg-gradient-to-r from-yellow-400 to-red-400 hover:scale-105 hover:opacity-90 
               transition duration-300 ease-in-out">
                            Logout
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </a>
                    </div>
                </section>


            </aside>

            <!-- ===== 
       
      
    



            <!-- ===== Content Area Start ===== -->
            <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
                <!-- Small Device Overlay Start -->
                <div :class="sidebarToggle ? 'block lg:hidden' : 'hidden'"
                    class="fixed z-9 h-screen w-full bg-gray-900/50 hidden"></div>
                <!-- Small Device Overlay End -->

                <!-- ===== Main Content Start ===== -->
                <main>
                    <!-- ===== Header Start ===== -->


                    <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                        <div class="grid grid-cols-30 gap-4 md:gap-6">
                            <div class="col-span-12 space-y-6 xl:col-span-7">
                                <!-- Metric Group One -->
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6">

                                    <!-- Metric Item Start -->
                                    <div
                                        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-md md:p-6">
                                        <div class="mt-2 flex items-end justify-between">
                                            <div>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Calorie
                                                    Intake</span>
                                                <h4
                                                    class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                                                    <?= htmlspecialchars($totalCalories) ?> kcal
                                                </h4>
                                            </div>

                                            <span
                                                class="flex items-center gap-1 rounded-full bg-error-50 py-0.5 pl-2 pr-2 text-sm font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                                <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M5.31462 10.3761C5.45194 10.5293 5.65136 10.6257 5.87329 10.6257C5.8736 10.6257 5.8739 10.6257 5.87421 10.6257C6.0663 10.6259 6.25845 10.5527 6.40505 10.4062L9.40514 7.4082C9.69814 7.11541 9.69831 6.64054 9.40552 6.34754C9.11273 6.05454 8.63785 6.05438 8.34486 6.34717L6.62329 8.06753L6.62329 1.875C6.62329 1.46079 6.28751 1.125 5.87329 1.125C5.45908 1.125 5.12329 1.46079 5.12329 1.875L5.12329 8.06422L3.40516 6.34719C3.11218 6.05439 2.6373 6.05454 2.3445 6.34752C2.0517 6.64051 2.05185 7.11538 2.34484 7.40818L5.31462 10.3761Z"
                                                        fill=""></path>
                                                </svg>

                                                11.01%
                                                <br>
                                                lower than last week.
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Metric Item End -->

                                    <!-- Metric Item Start -->
                                    <div
                                        class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] shadow-md md:p-6">
                                        <div class="mt-2 flex items-end justify-between">
                                            <div>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Weight</span>
                                                <h4
                                                    class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">
                                                    62kg
                                                </h4>
                                            </div>

                                            <span
                                                class="flex items-center gap-1 rounded-full bg-error-50 py-0.5 pl-2 pr-2.5 text-sm font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">
                                                <svg class="fill-current" width="12" height="12" viewBox="0 0 12 12"
                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M5.31462 10.3761C5.45194 10.5293 5.65136 10.6257 5.87329 10.6257C5.8736 10.6257 5.8739 10.6257 5.87421 10.6257C6.0663 10.6259 6.25845 10.5527 6.40505 10.4062L9.40514 7.4082C9.69814 7.11541 9.69831 6.64054 9.40552 6.34754C9.11273 6.05454 8.63785 6.05438 8.34486 6.34717L6.62329 8.06753L6.62329 1.875C6.62329 1.46079 6.28751 1.125 5.87329 1.125C5.45908 1.125 5.12329 1.46079 5.12329 1.875L5.12329 8.06422L3.40516 6.34719C3.11218 6.05439 2.6373 6.05454 2.3445 6.34752C2.0517 6.64051 2.05185 7.11538 2.34484 7.40818L5.31462 10.3761Z"
                                                        fill=""></path>
                                                </svg>

                                                9.05%
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Metric Item End -->
                                </div>
                                <!-- Metric Group One -->

                                <!-- ====== Chart One Start -->
                                <div
                                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03] shadow-md max-h-64 sm:max-h-80 overflow-y-auto">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-2">
                                            Daily Log Checker
                                        </h3>
                                    </div>
                                    <div
                                        class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.05] shadow-sm transition-all duration-300 hover:shadow-md">
                                        <div
                                            class="flex items-center justify-between p-4 rounded-lg hover:bg-gray-100 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-800 transition-all duration-300 max-w-screen-lg mx-auto">
                                            <!-- Text Content (60% width) -->
                                            <div class="flex-1 mr-4" style="flex-basis: 60%;">
                                                <h5
                                                    class="mb-2 text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                                                    Drank water?
                                                </h5>
                                                <p class="text-sm font-normal text-gray-700 dark:text-gray-400 mb-4">
                                                    Have you drank enough glasses of water today?
                                                </p>
                                            </div>

                                            <!-- Digit Input (on the right side) -->
                                            <input type="number" id="water-input"
                                                class="w-24 h-12 text-center border border-gray-300 rounded-full bg-white dark:bg-gray-900 dark:text-white text-gray-900 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                placeholder="0" min="0" step="1">
                                        </div>
                                    </div>

                                    <!-- GOAL 1 -->
                                    <div
                                        class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.05] shadow-sm transition-all duration-300 hover:shadow-md">
                                        <div
                                            class="flex items-center justify-between p-4 rounded-lg hover:bg-gray-100 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-800 transition-all duration-300 max-w-screen-lg mx-auto">
                                            <!-- Text Content (60% width) -->
                                            <div class="flex-1 mr-4" style="flex-basis: 60%;">
                                                <h5
                                                    class="mb-2 text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                                                    Food Eaten
                                                </h5>
                                                <p class="text-sm font-normal text-gray-700 dark:text-gray-400 mb-4">
                                                    what did you eat today?
                                                </p>
                                            </div>

                                            <!-- Button (on the right side) -->
                                            <div>
                                                <input type="text" id="foodInput" placeholder="Enter food name" />
                                                <button onclick="fetchCalories()">Search</button>
                                                <!-- Submit Button -->
                                                <button id="submitGoals"
                                                    class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg">Save
                                                    Goals</button>

                                                <div id="results"></div>
                                            </div>
                                        </div>

                                    </div>
                                    <!-- GOAL 1 -->
                                    <br><br>
                                    <!-- GOAL 1 (Calories) -->
                                    <div class="w-full rounded-xl border border-gray-200 bg-white shadow-sm">
                                        <h5 class="mb-2 text-lg font-semibold text-gray-900">Calories</h5>
                                        <p class="text-sm text-gray-700">Calories needed to sustain basic functions.</p>
                                        <div class="pb-1">
                                            <div id="bmr1" class="text-lg text-gray-900 font-bold">0 kcal</div>
                                        </div>
                                    </div>

                                    <!-- GOAL 2 (Protein) -->
                                    <div class="w-full rounded-xl border border-gray-200 bg-white shadow-sm">
                                        <h5 class="mb-2 text-lg font-semibold text-gray-900">Protein Intake</h5>
                                        <p class="text-sm text-gray-700">Protein requirement for muscle maintenance.</p>
                                        <div class="pb-1">
                                            <div id="bmr2" class="text-lg text-gray-900 font-bold">0 g</div>
                                        </div>
                                    </div>

                                    <!-- GOAL 3 (Carbohydrates) -->
                                    <div class="w-full rounded-xl border border-gray-200 bg-white shadow-sm">
                                        <h5 class="mb-2 text-lg font-semibold text-gray-900">Carbohydrates</h5>
                                        <p class="text-sm text-gray-700">Carbs are the primary source of energy.</p>
                                        <div class="pb-1">
                                            <div id="bmr3" class="text-lg text-gray-900 font-bold">0 g</div>
                                        </div>
                                    </div>


                                </div>

                                <!-- ====== Chart One End -->
                            </div>
                            <div
                                class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03] shadow-md max-h-[29rem] w-full">
                                <!-- Title Section -->
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-2">
                                        Goals
                                    </h3>
                                </div>

                                <!-- Scrollable Content Section -->
                                <div class="flex flex-col h-full w-full">
                                    <!-- Scrollable Content Section -->
                                    <div class="flex-1 min-h-0 overflow-hidden">
                                        <div class="h-full max-h-full overflow-y-auto space-y-2 pr-2 flex flex-col">
                                            <!-- Reduced space-y-4 to space-y-2 -->

                                            <!-- GOAL 1 -->
                                            <div
                                                class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.05] shadow-sm transition-all duration-300 hover:shadow-md">
                                                <a href="#"
                                                    class="block p-4 rounded-lg hover:bg-gray-100 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-800 transition-all duration-300">
                                                    <!-- Reduced padding to p-3 -->
                                                    <h5
                                                        class="mb-2 text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                                                        BMR (Basal Metabolic Rate)
                                                    </h5>
                                                    <p
                                                        class="text-sm font-normal text-gray-700 dark:text-gray-400 mb-4">
                                                        Number of calories your body needs to accomplish its most basic
                                                        (basal) life-sustaining functions.
                                                    </p>
                                                    <!-- Progress bar container -->
                                                    <div class="pb-1">
                                                        <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
                                                            <div class="text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full"
                                                                style="background: linear-gradient(to right, #FCD404, #FB6F74); width: 20%">
                                                                20%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>


                                            <!-- GOAL 1 -->
                                            <div
                                                class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.05] shadow-sm transition-all duration-300 hover:shadow-md">
                                                <a href="#"
                                                    class="block p-4 rounded-lg hover:bg-gray-100 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-800 transition-all duration-300">
                                                    <!-- Reduced padding to p-3 -->
                                                    <h5
                                                        class="mb-2 text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                                                        BMR (Basal Metabolic Rate)
                                                    </h5>
                                                    <p
                                                        class="text-sm font-normal text-gray-700 dark:text-gray-400 mb-4">
                                                        Number of calories your body needs to accomplish its most basic
                                                        (basal) life-sustaining functions.
                                                    </p>
                                                    <!-- Progress bar container -->
                                                    <div class="pb-1">
                                                        <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
                                                            <div class="text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full"
                                                                style="background: linear-gradient(to right, #FCD404, #FB6F74); width: 43%">
                                                                43%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <!-- GOAL 1 -->
                                            <div
                                                class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.05] shadow-sm transition-all duration-300 hover:shadow-md">
                                                <a href="#"
                                                    class="block p-4 rounded-lg hover:bg-gray-100 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-800 transition-all duration-300">
                                                    <!-- Reduced padding to p-3 -->
                                                    <h5
                                                        class="mb-2 text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                                                        BMR (Basal Metabolic Rate)
                                                    </h5>
                                                    <p
                                                        class="text-sm font-normal text-gray-700 dark:text-gray-400 mb-4">
                                                        Number of calories your body needs to accomplish its most basic
                                                        (basal) life-sustaining functions.
                                                    </p>
                                                    <!-- Progress bar container -->
                                                    <div class="pb-1">
                                                        <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
                                                            <div class="text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full"
                                                                style="background: linear-gradient(to right, #FCD404, #FB6F74); width: 56%">
                                                                56%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <!-- GOAL 1 -->
                                            <div
                                                class="w-full rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.05] shadow-sm transition-all duration-300 hover:shadow-md">
                                                <a href="#"
                                                    class="block p-4 rounded-lg hover:bg-gray-100 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-800 transition-all duration-300">
                                                    <!-- Reduced padding to p-3 -->
                                                    <h5
                                                        class="mb-2 text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                                                        BMR (Basal Metabolic Rate)
                                                    </h5>
                                                    <p
                                                        class="text-sm font-normal text-gray-700 dark:text-gray-400 mb-4">
                                                        Number of calories your body needs to accomplish its most basic
                                                        (basal) life-sustaining functions.
                                                    </p>
                                                    <!-- Progress bar container -->
                                                    <div class="pb-1">
                                                        <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
                                                            <div class="text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full"
                                                                style="background: linear-gradient(to right, #FCD404, #FB6F74); width: 20%">
                                                                20%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="p-4">

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>



                            <div class="col-span-12 md:col-span-7 xl:col-span-3">
                                <!-- Chart Container -->
                                <div
                                    class="rounded-2xl border border-gray-200 bg-gray-100 dark:border-gray-800 dark:bg-white/[0.03] h-full flex flex-col shadow-md">
                                    <div
                                        class="shadow-default rounded-2xl bg-white px-4 md:px-5 pb-6 pt-5 dark:bg-gray-900 flex flex-col h-full">

                                        <!-- Title Section -->
                                        <div class="flex flex-col sm:flex-row justify-between items-start ">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                                    Monthly Target
                                                </h3>
                                                <p class="mt-1 text-sm md:text-base text-gray-500 dark:text-gray-400">
                                                    Target you’ve set for each month
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Chart Section -->
                                        <div class="relative flex-1 max-h-[250px] mt-4">
                                            <div id="chartTwo" class="h-full min-h-[229px]"></div>
                                        </div>

                                        <!-- Bottom Text -->
                                        <p
                                            class="mx-auto mt-4 w-full max-w-[90%] md:max-w-[380px] text-center text-sm md:text-base text-gray-500">
                                            You are almost close to your goal!
                                        </p>

                                    </div>
                                </div>
                            </div>



                            <!-- ====== Community Page Start ====== -->
                            <div class="col-span-12">
                                <div
                                    class="overflow-auto max-h-90 rounded-2xl border border-gray-200 bg-white p-5 shadow-lg dark:border-gray-800 dark:bg-gray-800/[0.05]">
                                    <!-- Header -->
                                    <div
                                        class="bg-gradient-to-r from-blue-500 to-indigo-600 text-black text-lg font-semibold py-3 px-4 rounded-t-xl">
                                        News Feed ✨
                                    </div>

                                    <!-- Scrollable Section -->
                                    <div
                                        class="h-80 overflow-y-auto p-4 space-y-4 scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200">
                                        <!-- Post Loop -->
                                        <div class="space-y-6">
                                            <!-- Single Post -->
                                            <div class="space-y-4">
                                                <div class="flex items-start space-x-3">
                                                    <img src="photos/user.png" alt="User Icon"
                                                        class="w-12 h-12 rounded-full border-2 border-gray-300">
                                                    <p class="text-gray-800 flex-1 text-sm md:text-base">
                                                        Fitness isn’t about being better than someone else; it’s about
                                                        being better than you used to be. Keep going.
                                                        <span
                                                            class="font-semibold text-blue-500">#SelfImprovement</span>
                                                    </p>
                                                </div>

                                                <!-- Actions: Like, Repost, Reply -->
                                                <div class="flex items-center justify-between mt-2">
                                                    <!-- Like & Repost Buttons -->
                                                    <div class="flex space-x-4">
                                                        <button
                                                            class="flex items-center space-x-2 text-gray-600 hover:text-red-500 transition duration-300">
                                                            <i class="fa-solid fa-heart"></i>
                                                            <span class="text-sm">Like</span>
                                                        </button>

                                                        <button
                                                            class="flex items-center space-x-2 text-gray-600 hover:text-green-500 transition duration-300">
                                                            <i class="fa-solid fa-retweet"></i>
                                                            <span class="text-sm">Repost</span>
                                                        </button>
                                                    </div>

                                                    <!-- Reply Input Field -->
                                                    <div
                                                        class="flex items-center border border-gray-300 rounded-full px-3 py-1 w-full max-w-md">
                                                        <input type="text" placeholder="Write a reply..."
                                                            class="w-full outline-none bg-transparent text-gray-700 text-sm placeholder-gray-500">
                                                        <button
                                                            class="text-blue-500 hover:text-blue-700 transition duration-300">
                                                            <i class="fa-solid fa-paper-plane"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="border-gray-300 my-4">

                                            <!-- More Posts (Looped) -->
                                            <div class="space-y-4">
                                                <div class="flex items-start space-x-3">
                                                    <img src="photos/user.png" alt="User Icon"
                                                        class="w-12 h-12 rounded-full border-2 border-gray-300">
                                                    <p class="text-gray-800 flex-1 text-sm md:text-base">
                                                        "Let food be thy medicine and medicine be thy food." –
                                                        Hippocrates. A well-balanced diet makes a difference.
                                                        <span class="font-semibold text-blue-500">#HealthyEating</span>
                                                    </p>
                                                </div>

                                                <!-- Actions -->
                                                <div class="flex items-center justify-between mt-2">
                                                    <div class="flex space-x-4">
                                                        <button
                                                            class="flex items-center space-x-2 text-gray-600 hover:text-red-500 transition duration-300">
                                                            <i class="fa-solid fa-heart"></i>
                                                            <span class="text-sm">Like</span>
                                                        </button>
                                                        <button
                                                            class="flex items-center space-x-2 text-gray-600 hover:text-green-500 transition duration-300">
                                                            <i class="fa-solid fa-retweet"></i>
                                                            <span class="text-sm">Repost</span>
                                                        </button>
                                                    </div>

                                                    <div
                                                        class="flex items-center border border-gray-300 rounded-full px-3 py-1 w-full max-w-md">
                                                        <input type="text" placeholder="Write a reply..."
                                                            class="w-full outline-none bg-transparent text-gray-700 text-sm placeholder-gray-500">
                                                        <button
                                                            class="text-blue-500 hover:text-blue-700 transition duration-300">
                                                            <i class="fa-solid fa-paper-plane"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="border-gray-300 my-4">

                                            <!-- Repeat for Each Post -->
                                            <div class="space-y-4">
                                                <div class="flex items-start space-x-3">
                                                    <img src="photos/user.png" alt="User Icon"
                                                        class="w-12 h-12 rounded-full border-2 border-gray-300">
                                                    <p class="text-gray-800 flex-1 text-sm md:text-base">
                                                        Cardio doesn’t have to be boring. Try different activities like
                                                        hiking, swimming, or sports to stay active.
                                                        <span class="font-semibold text-blue-500">#StayActive</span>
                                                    </p>
                                                </div>

                                                <!-- Actions -->
                                                <div class="flex items-center justify-between mt-2">
                                                    <div class="flex space-x-4">
                                                        <button
                                                            class="flex items-center space-x-2 text-gray-600 hover:text-red-500 transition duration-300">
                                                            <i class="fa-solid fa-heart"></i>
                                                            <span class="text-sm">Like</span>
                                                        </button>
                                                        <button
                                                            class="flex items-center space-x-2 text-gray-600 hover:text-green-500 transition duration-300">
                                                            <i class="fa-solid fa-retweet"></i>
                                                            <span class="text-sm">Repost</span>
                                                        </button>
                                                    </div>

                                                    <div
                                                        class="flex items-center border border-gray-300 rounded-full px-3 py-1 w-full max-w-md">
                                                        <input type="text" placeholder="Write a reply..."
                                                            class="w-full outline-none bg-transparent text-gray-700 text-sm placeholder-gray-500">
                                                        <button
                                                            class="text-blue-500 hover:text-blue-700 transition duration-300">
                                                            <i class="fa-solid fa-paper-plane"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="border-gray-300 my-4">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ====== Community Page End ====== -->

                </main>
                <!-- ===== Main Content End ===== -->
            </div>
            <!-- ===== Content Area End ===== -->
        </div>
        <!-- ===== Page Wrapper End ===== -->
    </main>
</body>

<script defer="" src="bundle.js"></script>
<script defer=""
    src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
    integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
    data-cf-beacon="{&quot;rayId&quot;:&quot;91b7c147fdd902a9&quot;,&quot;version&quot;:&quot;2025.1.0&quot;,&quot;r&quot;:1,&quot;token&quot;:&quot;67f7a278e3374824ae6dd92295d38f77&quot;,&quot;serverTiming&quot;:{&quot;name&quot;:{&quot;cfExtPri&quot;:true,&quot;cfL4&quot;:true,&quot;cfSpeedBrain&quot;:true,&quot;cfCacheStatus&quot;:true}}}"
    crossorigin="anonymous"></script>


<svg id="SvgjsSvg1001" width="2" height="0" xmlns="http://www.w3.org/2000/svg" version="1.1"
    xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.dev"
    style="overflow: hidden; top: -100%; left: -100%; position: absolute; opacity: 0;">
    <defs id="SvgjsDefs1002"></defs>
    <polyline id="SvgjsPolyline1003" points="0,0"></polyline>
    <path id="SvgjsPath1004" d="M0 0 ">

    </path>
</svg>

<div class="jvm-tooltip"></div>

<script>
    document.getElementById("submitGoals").addEventListener("click", async function () {
        const meal_name = document.getElementById("food_search").value.trim(); // Get the food input
        const calories = document.getElementById("bmr1").innerText.replace(" kcal", "").trim();
        const protein = document.getElementById("bmr2").innerText.replace(" g", "").trim();
        const carbs = document.getElementById("bmr3").innerText.replace(" g", "").trim();

        if (!meal_name) {
            alert("Please enter a meal name before submitting!");
            return;
        }

        const response = await fetch("addmeals.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                meal_name: meal_name,
                calories: calories,
                protein: protein,
                carbs: carbs
            })
        });

        const result = await response.json();
        if (result.success) {
            alert("Meal goals saved successfully!");
        } else {
            alert("Failed to save meal goals: " + result.error);
        }
    });
</script>


</body>

</html>