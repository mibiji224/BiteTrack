<?php
require_once 'php_action/db_connect.php';

session_start();

$errors = array();

if ($_POST) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($firstName) || empty($lastName) ||empty($email) || empty($username) || empty($password) || empty($confirmPassword)) {
        if ($firstName == "") {
            $errors[] = "First Name is required";
        }
        if ($lastName == "") {
            $errors[] = "Last Name is required";
        }
        if ($username == "") {
            $errors[] = "Username is required";
        }
        if ($email == "") {
            $errors[] = "Email is required";
        }
        if ($password == "") {
            $errors[] = "Password is required";
        }
        if ($confirmPassword == "") {
            $errors[] = "Confirm Password is required";
        }
    } elseif ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $hashedPassword = md5($password);
            $insertSql = "INSERT INTO users ( username, password, email, first_name, last_name) VALUES (?, ?, ?, ?, ?)";
            $stmt = $connect->prepare($insertSql);
            $stmt->bind_param("sssss",  $username, $hashedPassword, $email, $firstName, $lastName);

            if ($stmt->execute()) {
                $_SESSION['userId'] = $stmt->insert_id;
                header('location: http://localhost:3000/dashboard.php');
                exit();
            } else {
                $errors[] = "Failed to register. Please try again.";
            }
        } else {
            $errors[] = "Username already exists";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BiteTrack - Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/button.css">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
<div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-lg mx-auto">
    <div class="text-center mb-6">
        <h3 class="text-2xl font-semibold text-gray-900">Create an Account</h3>
    </div>

    <!-- Error Messages -->
    <div class="messages">
        <?php if ($errors) {
            foreach ($errors as $value) {
                echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-900 p-3 mb-3 rounded-md" role="alert">
                    <i class="fa fa-exclamation-circle"></i> ' . $value . '
                </div>';
            }
        } ?>
    </div>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-900">First Name</label>
                <input type="text" class="w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="first_name" name="first_name" placeholder="Enter first name" required>
            </div>
            <div>
                <label for="last_name" class="block text-sm font-medium text-gray-900">Last Name</label>
                <input type="text" class="w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="last_name" name="last_name" placeholder="Enter last name" required>
            </div>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-900">Email</label>
            <input type="email" class="w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="email" name="email" placeholder="example@email.com" required>
        </div>

        <div class="mb-4">
            <label for="username" class="block text-sm font-medium text-gray-900">Username</label>
            <input type="text" class="w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="username" name="username" placeholder="Enter username" required>
        </div>

        <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-900">Password</label>
                <input type="password" class="w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="password" name="password" placeholder="Enter password" required>
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-900">Confirm Password</label>
                <input type="password" class="w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
            </div>
        </div>

        <div class="mt-6 flex justify-center">
            <button type="submit" id="reg_btn" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2 px-6 rounded-md shadow-md transition">
                Register
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">Already have an account? 
            <a href="index.php" class="text-indigo-600 hover:text-indigo-500 font-medium">Sign in</a>
        </p>
    </div>
</div>


</body>

</html>