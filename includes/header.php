<!-- includes/header.php -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BiteTrack - Your Nutrient Tracker!</title>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>


  <script>
    // Mobile menu toggle
    document.getElementById('menu-btn').addEventListener('click', function () {
      document.getElementById('mobile-menu').classList.toggle('open');
    });
  </script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Flowbite (for dropdowns, animations, etc.) -->
  <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="custom/css/custom.css">
  <style>
    /* Glowing Shadow for Header */
    .glowing-header {
      box-shadow: 0 0 15px rgba(70, 95, 255, 0.3);
    }

    /* Gray Outline at Bottom of Header */
    .header-outline {
      border-bottom: 2px solid #e5e7eb;
      /* Tailwind's gray-200 color */
    }

    /* Menu Links */
    .nav-link {
      font-weight: 500;
      font-size: 1rem;
      color: #4b5563; /* Tailwind gray-700 */
      transition: color 0.3s, transform 0.3s;
    }

    .nav-link:hover {
      color: #3b82f6; /* Tailwind blue-500 */
      transform: translateY(-2px);
    }

    /* Mobile Menu */
    .mobile-menu-link {
      transition: background-color 0.3s;
    }

    .mobile-menu-link:hover {
      background-color: #f3f4f6;
    }

    .mobile-menu {
      display: none;
    }

    .mobile-menu.open {
      display: block;
      animation: slideDown 0.3s ease-out forwards;
    }

    @keyframes slideDown {
      from {
        transform: translateY(-20px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
  </style>


</head>

<body class="bg-gray-50">
  <header class="bg-white shadow-md glowing-header header-outline">
    <nav class="max-w-7xl mx-auto flex items-center justify-between px-4 py-3 lg:px-10">
      <!-- Logo on the Left -->
      <a href="index.php" class="text-2xl font-semibold text-gray-700 hover:text-blue-500 transition duration-300">BiteTrack</a>

      <!-- Desktop Menu -->
      <div class="hidden lg:flex space-x-8">
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="meals.php" class="nav-link">Meals Log</a>
        <a href="logout.php" class="flex items-center gap-2 text-red-600 nav-link">
          Logout <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
      </div>

      <!-- Mobile Menu Button -->
      <button id="menu-btn" class="lg:hidden text-gray-700 hover:text-gray-900 focus:outline-none">
        <i class="fa-solid fa-bars text-2xl"></i>
      </button>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu absolute top-16 right-5 bg-white shadow-lg rounded-lg w-48 py-3">
      <a href="dashboard.php" class="block px-5 py-2 text-gray-700 mobile-menu-link">Dashboard</a>
      <a href="meals.php" class="block px-5 py-2 text-gray-700 mobile-menu-link">Meals Log</a>
      <a href="goals.php" class="block px-5 py-2 text-gray-700 mobile-menu-link">Goals</a>
      <a href="logout.php" class="flex items-center gap-2 px-5 py-2 text-red-600 mobile-menu-link">
        Logout <i class="fa-solid fa-arrow-right-from-bracket"></i>
      </a>
    </div>
  </header>

  <script>
    // Mobile menu toggle
    document.getElementById('menu-btn').addEventListener('click', function () {
      document.getElementById('mobile-menu').classList.toggle('open');
    });
  </script>

  
</body>

</html>
