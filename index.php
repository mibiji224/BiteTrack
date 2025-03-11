<?php
require_once 'php_action/db_connect.php';

session_start();

// Redirect if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: http://localhost:3000/dashboard.php');
    exit();
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = md5($_POST['password']); // Keep using md5 for now

    if (empty($username) || empty($password)) {
        if ($username == "") $errors[] = "Username is required";
        if ($_POST['password'] == "") $errors[] = "Password is required"; // Check the raw input
    } else {
        // Query the database for the user
        $sql = "SELECT user_id FROM users WHERE username = ? AND password = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['user_id']; // Store user_id in session

            // Debugging (optional)
            echo "Session user_id set: " . $_SESSION['user_id'];

            header('Location: http://localhost:3000/dashboard.php');
            exit();
        } else {
            $errors[] = "Incorrect username/password combination";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BiteTrack</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="custom/css/custom.css">
    <link rel="stylesheet" href="css/button.css">
    <style>
        .auth-buttons {
            display: inline-flex;
            border: 1px solid #ccc;
            border-radius: 25px;
            overflow: hidden;
            background: white;
            padding: 4px;
            align-items: center;
            position: relative;
        }

        .auth-buttons a,
        .auth-buttons button {
            border: none;
            outline: none;
            width: 100px;
            /* Fixed width to prevent movement */
            height: 40px;
            /* Fixed height */
            font-size: 16px;
            cursor: pointer;
            background: transparent;
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        /* Default Sign Up button */
        .auth-buttons .signup-btn {
            background: linear-gradient(to right, #FCD404, #FB6F74);
            color: white;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        /* Sign Up glows when hovered */
        .auth-buttons .signup-btn:hover {
            box-shadow: 0px 0px 12px rgba(252, 212, 4, 0.7);
        }

        /* When Log In is hovered, it gets the gradient & glow, while Sign Up becomes transparent */
        .auth-buttons button:hover {
            background: linear-gradient(to right, #FCD404, #FB6F74);
            color: white;
            border-radius: 20px;
            box-shadow: 0px 0px 12px rgba(252, 212, 4, 0.7);
        }

        .auth-buttons button:hover+.signup-btn {
            background: transparent;
            color: #333;
            border: 1px solid #FCD404;
            box-shadow: none;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col bg-white w-full">


    <main id="welcome-section"
        class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
        <aside class="pl-32 m-0">
            <div class="text-center text-md-start flex-grow-1 mb-0 mb-md-0">
                <h1 class="display-4 fw-bold text-dark">Welcome to BiteTrack!</h1>
                <p class="text-muted mt-3"><span style="font-weight: bold; font-size: 1.25rem;">Your ultimate companion
                        for
                        effortless and reliable food tracking!</span><br>
                    <span style="display: inline-block; max-width: 650px;">
                        We are dedicated to helping you monitor your meals, stay on top of your nutrition, and make
                        informed
                        choices about your diet. Whether you're aiming for a healthier lifestyle, managing dietary
                        restrictions, or simply keeping track of your favorite meals, BiteTrack is here to support you
                        every
                        step of the way.
                    </span></span>
                </p>
                <div class="auth-buttons mt-4">
                    <button onclick="showSignIn()">Log In</button>
                    <a href="register.php" class="signup-btn">Sign Up</a>
                </div>
            </div>
        </aside>
        <aside>
            <div class="text-center flex-grow-1 ms-md-0 mt-0 mt-md-0">
                <img src="photos/mealbg.jpg" alt="Nutrition Tracker Image" class="img-fluid rounded shadow">
            </div>
        </aside>
    </main>
    <!-- ABOUT US PAGE -->
    <hr id="linediv" class="border-gray-600 dark:border-gray-700 my-4">

    <div>
        <main id="about-section" class="max-w-7xl mx-auto px-6 py-12 lg:px-12">
            <!-- About Us Section -->
            <section class="text-center">
                <h1 class="text-4xl font-bold text-gray-900">
                    About <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-[#FCD404] to-[#FB6F74]">Us</span>
                </h1>
                <p class="mt-4 text-lg text-gray-600">
                    At <strong>BiteTrack</strong>, our mission is to make healthy living simple and accessible.
                    Track meals, monitor nutrition, and set health goals with ease.
                </p>
            </section>

            <!-- Features Section -->
            <section class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg bg-white shadow-md p-5 text-center">
                    <img src="photos/food2.jpg" alt="Monitor Progress"
                        class="w-full max-h-[300px] object-cover rounded-md mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Track Your Meals</h2>
                    <p class="mt-2 text-gray-600 text-sm">Log your daily meals and keep track of your calorie intake.
                    </p>
                </div>

                <div class="rounded-lg bg-white shadow-md p-5 text-center">
                    <img src="photos/meal.gif" alt="Monitor Progress"
                        class="w-full max-h-[300px] object-cover rounded-md mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Set Your Goals</h2>
                    <p class="mt-2 text-gray-600 text-sm">Set your fitness and nutrition goals and monitor your
                        progress.</p>
                </div>

                <div class="rounded-lg bg-white shadow-md p-5 text-center">
                    <img src="photos/food3.jpg" alt="Monitor Progress"
                        class="w-full max-h-[300px] object-cover rounded-md mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Monitor Your Progress</h2>
                    <p class="mt-2 text-gray-600 text-sm">Get detailed reports and insights on your health and fitness
                        journey.</p>
                </div>
            </section>
        </main>
        <hr id="linediv2" class="border-gray-600 dark:border-gray-700 my-4">

        <!-- Contact Us Section -->
        <section id="contact-section" class="mt-12 bg-gray-100 rounded-lg p-6 lg:p-8 max-w-5xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
                <!-- Contact Info -->
                <div class="bg-white shadow-sm rounded-lg p-4 md:p-6 flex flex-col justify-center h-full">
                    <h2 class="text-3xl font-bold text-gray-900 text-center mb-4">Contact <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-[#FCD404] to-[#FB6F74]">Us</span>
                    </h2>
                    <p class="text-center text-gray-600 text-sm mb-6">Have questions? Reach out to us, and we'll be
                        happy to help!</p>
                    <div class="mt-auto"> <!-- Pushes content to the bottom -->
                        <p class="text-gray-700 text-sm md:text-base"><strong>Email:</strong> support@bitetrack.com</p>
                        <p class="text-gray-700 text-sm md:text-base mt-2"><strong>Phone:</strong> +123 456 7890</p>
                        <p class="text-gray-700 text-sm md:text-base mt-2"><strong>Address:</strong> 123 Healthy St,
                            Wellness City</p>
                    </div>
                </div>

                <!-- Contact Form -->
                <form class="bg-white shadow-sm rounded-lg p-4 md:p-6 flex flex-col justify-between h-full">
                    <div>
                        <div class="mb-3">
                            <label class="block text-gray-700 font-semibold text-sm mb-1">Your Name</label>
                            <input type="text" class="w-full p-2 border rounded-lg text-sm"
                                placeholder="Enter your name">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 font-semibold text-sm mb-1">Your Email</label>
                            <input type="email" class="w-full p-2 border rounded-lg text-sm"
                                placeholder="Enter your email">
                        </div>

                        <div class="mb-3">
                            <label class="block text-gray-700 font-semibold text-sm mb-1">Message</label>
                            <textarea class="w-full p-2 border rounded-lg text-sm" rows="3"
                                placeholder="Type your message"></textarea>
                        </div>
                    </div>
                    <button id="sendmess"
                        class="bg-danger text-white font-bold py-2 px-4 rounded-lg text-sm w-full md:w-auto mt-2">Send
                        Message</button>
                </form>
            </div>
        </section>

    </div>
    <!-- SIGN IN PAGE -->
    <div id="signin-section" class="container d-none flex justify-center items-center min-h-screen">
        <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-6">
            <div class="text-center">
                <img class="h-12 mx-auto" src="photos/plan.png" alt="Your Company">
                <h2 class="mt-4 text-2xl font-bold text-gray-900">Sign in to your account</h2>
            </div>

            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="mt-6">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-900">Username</label>
                    <input type="text" name="username" id="username"
                        class="w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        required>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-900">Password</label>
                    <input type="password" name="password" id="password"
                        class="w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        required>
                </div>

                <div class="mt-6 flex justify-center">
                    <button type="submit" id="main_si"
                        class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 px-6 rounded-md shadow-md transition">
                        Sign in
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Don't have an account?
                <a href="register.php" class="text-indigo-600 hover:text-indigo-500 font-medium">Register</a>
            </p>
        </div>
    </div>


    <!-- FOOTER -->
    <script>
        function showSignIn() {
            document.getElementById('welcome-section').classList.add('d-none');  // Hide the welcome section
            document.getElementById('about-section').classList.add('d-none'); //contact-section
            document.getElementById('contact-section').classList.add('d-none'); //contact-section
            document.getElementById('linediv').classList.add('d-none');
            document.getElementById('linediv2').classList.add('d-none');    // Hide the about section
            document.getElementById('signin-section').classList.remove('d-none'); // ✅ Show the sign-in section

            // ✅ Hide the footer as well
            var footer = document.getElementById('footer');
            if (footer) {
                footer.classList.add('d-none');
            }
        }
    </script>

</body>

<?php include 'includes/footer.php'; ?>

</html>