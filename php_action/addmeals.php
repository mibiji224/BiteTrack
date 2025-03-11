<?php
session_start();
header("Content-Type: application/json");

$host = "localhost";
$username = "root";
$password = "";
$database = "nutrition_tracker";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION["user_id"];
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "Invalid input"]);
    exit();
}

$meal_name = $data["meal_name"];
$calories = $data["calories"];
$protein = $data["protein"];
$carbs = $data["carbs"];

// Insert meal goal into database
$stmt = $conn->prepare("INSERT INTO user_goals (user_id, meal_name, calories, protein, carbs) 
                        VALUES (?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE meal_name = VALUES(meal_name), calories = VALUES(calories), protein = VALUES(protein), carbs = VALUES(carbs)");
$stmt->bind_param("isddd", $user_id, $meal_name, $calories, $protein, $carbs);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Database error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
