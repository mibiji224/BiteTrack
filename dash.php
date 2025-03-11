<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Calorie Tracker</title>
    <script>
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
                    headers: {
                        'X-Api-Key': apiKey
                    }
                });

                const data = await response.json();

                if (data.items && data.items.length > 0) {
                    let resultHTML = "<h3>Results:</h3>";
                    data.items.forEach(item => {
                        resultHTML += `
                            <div class="food-item">
                                <p><strong>Food:</strong> ${item.name}</p>
                                <p><strong>Calories:</strong> ${item.calories} kcal</p>
                                <p><strong>Carbs:</strong> ${item.carbohydrates_total_g} g</p>
                                <p><strong>Protein:</strong> ${item.protein_g} g</p>
                                <p><strong>Fats:</strong> ${item.fat_total_g} g</p>
                                <hr>
                            </div>
                        `;
                    });
                    document.getElementById("results").innerHTML = resultHTML;
                } else {
                    document.getElementById("results").innerHTML = "<p>No data found.</p>";
                }
            } catch (error) {
                console.error("Error fetching data:", error);
                document.getElementById("results").innerHTML = "<p>Failed to fetch data. Try again.</p>";
            }
        }
    </script>
</head>
<body>
    <h2>Food Calorie Tracker</h2>
    <input type="text" id="foodInput" placeholder="Enter food name...">
    <button onclick="fetchCalories()">Search</button>

    <div id="results"></div>
</body>
</html>
