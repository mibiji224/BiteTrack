<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "nutrition_tracker";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming user_id is known (replace with session-based authentication)
$user_id = 1;

// Fetch meal logs for the current user
$sql = "SELECT meal_name, calories, protein, carbs, date_added FROM meals WHERE user_id = ? ORDER BY date_added DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $meal_name = $_POST['meal_name'];
    $calories = $_POST['calories'];
    $protein = $_POST['protein'];
    $carbs = $_POST['carbs'];
    $date_added = $_POST['date_added'];

    $insert_stmt = $conn->prepare("INSERT INTO meals (user_id, meal_name, calories, protein, carbs, date_added) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("isddds", $user_id, $meal_name, $calories, $protein, $carbs, $date_added);

    if ($insert_stmt->execute()) {
        echo "<script>alert('Meal added successfully!'); window.location.reload();</script>";
    } else {
        echo "<script>alert('Error adding meal: " . $conn->error . "');</script>";
    }

    $insert_stmt->close();
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
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            text-align: center;
            border-radius: 10px;
        }
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
    <script>
        // Open Modal
        function openModal() {
            document.getElementById("mealModal").style.display = "block";
        }

        // Close Modal
        function closeModal() {
            document.getElementById("mealModal").style.display = "none";
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
                        'X-Api-Key': 'FmEM2rbCs+c9j0rAbzaJRA==IVZqSzB9NOhvqjAs'
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

        function setCurrentDate() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById("date_added").value = today;
        }



        window.onload = setCurrentDate;
    </script>
</head>
<body>
    <h2>Nutrition Tracker</h2>

    <!-- Button to Open Modal -->
    <button onclick="openModal()">+ Add Meal</button>

    <!-- Modal -->
    <div id="mealModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add Meal</h2>

            <label for="food_search">Search Food:</label>
            <input type="text" id="food_search" placeholder="Enter food (e.g., apple, pizza)">
            <button type="button" onclick="searchFood()">Search</button>
            <br><br>

            <form id="mealForm" onsubmit="addMeal(event)">
                <label for="meal_name">Meal Name:</label>
                <input type="text" id="meal_name" name="meal_name" readonly required><br><br>

                <label for="calories">Calories:</label>
                <input type="number" id="calories" name="calories" readonly required><br><br>

                <label for="protein">Protein (g):</label>
                <input type="number" id="protein" name="protein" readonly required><br><br>

                <label for="carbs">Carbohydrates (g):</label>
                <input type="number" id="carbs" name="carbs" readonly required><br><br>

                <label for="date_added">Date:</label>
                <input type="date" id="date_added" name="date_added" readonly required><br><br>

                <input type="hidden" name="user_id" value="1"> <!-- Replace with dynamic user ID -->
                <button type="submit">Add Meal</button>
            </form>
        </div>
    </div>

    <h2>Meal Log</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Date</th>
                <th>Meal Name</th>
                <th>Calories</th>
                <th>Protein (g)</th>
                <th>Carbs (g)</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["date_added"]) ?></td>
                    <td><?= htmlspecialchars($row["meal_name"]) ?></td>
                    <td><?= htmlspecialchars($row["calories"]) ?></td>
                    <td><?= htmlspecialchars($row["protein"]) ?></td>
                    <td><?= htmlspecialchars($row["carbs"]) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
