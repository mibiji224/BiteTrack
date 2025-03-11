<?php
session_start();

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













if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $meal_name = $_POST['meal_name'];
    $calories = $_POST['calories'];
    $protein = $_POST['protein'];
    $carbs = $_POST['carbs'];

    $stmt = $conn->prepare("INSERT INTO meals (user_id, meal_name, calories, protein, carbs) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isddd", $user_id, $meal_name, $calories, $protein, $carbs);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Error adding meal: " . $conn->error]);
    }

    $stmt->close();
}

// Pagination Setup
$limit = 10;  // Meals per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;


$meals = [];
$sql = "SELECT meal_name, calories, protein, carbs, date_added FROM meals WHERE user_id = ? ORDER BY date_added DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $meals[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrition Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleModal() {
            document.getElementById("mealModal").classList.toggle("hidden");
        }

        async function searchFood() {
            const foodInput = document.getElementById("food_search").value;
            if (!foodInput) {
                alert("Please enter a food item!");
                return;
            }

            try {
                const response = await fetch(`https://api.calorieninjas.com/v1/nutrition?query=${foodInput}`, {
                    method: "GET",
                    headers: {
                        'X-Api-Key': 'FmEM2rbCs+c9j0rAbzaJRA==IVZqSzB9NOhvqjAs' // Replace with your actual API key
                    }
                });

                const data = await response.json();
                
                if (data.items.length === 0) {
                    alert("No data found for this food item!");
                    return;
                }

                const food = data.items[0];
                document.getElementById("meal_name").value = food.name;
                document.getElementById("calories").value = food.calories;
                document.getElementById("protein").value = food.protein_g;
                document.getElementById("carbs").value = food.carbohydrates_total_g;
            } catch (error) {
                console.error("Error fetching food data:", error);
                alert("Failed to retrieve food data!");
            }
        }

        function filterTable() {
            let input = document.getElementById("table_search").value.toLowerCase();
            let rows = document.querySelectorAll("#mealTable tbody tr");

            rows.forEach(row => {
                let mealName = row.cells[0].textContent.toLowerCase();
                row.style.display = mealName.includes(input) ? "" : "none";
            });
        }
    </script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">Nutrition Tracker</h2>

        <div class="flex justify-between items-center mb-4">
            <input type="text" id="table_search" class="p-2 border border-gray-300 rounded-lg w-full" placeholder="Search meals..." onkeyup="filterTable()">
            <button onclick="toggleModal()" class="bg-green-500 text-white px-4 py-2 rounded-lg ml-2">+ Add Meal</button>
        </div>

        <div class="relative overflow-x-auto shadow-md rounded-lg">
            <table id="mealTable" class="w-full text-sm text-left text-gray-900 border border-gray-200">
            <thead class="text-xs uppercase bg-gray-100 border-b">
                    <tr>
                        <th class="p-3 text-left">Meal Name</th>
                        <th class="p-3 text-left">Calories</th>
                        <th class="p-3 text-left">Protein (g)</th>
                        <th class="p-3 text-left">Carbs (g)</th>
                        <th class="p-3 text-left">Date Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($meals as $meal) : ?>
                        <tr class="border-t">
                            <td class="p-3"><?= htmlspecialchars($meal['meal_name']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($meal['calories']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($meal['protein']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($meal['carbs']) ?></td>
                            <td class="p-3"><?= htmlspecialchars($meal['date_added']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="mealModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Add Meal</h3>

            <div class="flex space-x-2">
                <input type="text" id="food_search" class="w-full p-2 border border-gray-300 rounded-lg" placeholder="Enter food (e.g., apple, pizza)">
                <button type="button" onclick="searchFood()" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Search</button>
            </div>

            <form id="mealForm" method="POST" class="mt-4 space-y-3">
                <label class="block text-gray-700">Meal Name:</label>
                <input type="text" id="meal_name" name="meal_name" class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly required>

                <label class="block text-gray-700">Calories:</label>
                <input type="number" id="calories" name="calories" class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly required>

                <label class="block text-gray-700">Protein (g):</label>
                <input type="number" id="protein" name="protein" class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly required>

                <label class="block text-gray-700">Carbohydrates (g):</label>
                <input type="number" id="carbs" name="carbs" class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100" readonly required>

                <button type="submit" class="w-full bg-green-500 text-white p-3 rounded-lg hover:bg-green-600 mt-4">Add Meal</button>
                <button type="button" onclick="toggleModal()" class="w-full bg-gray-500 text-white p-3 rounded-lg mt-2">Cancel</button>
            </form>
        </div>
    </div>
</body>
</html>
