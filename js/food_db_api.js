async function searchFood() {
    const foodQuery = document.getElementById('foodSearch').value;
    
    if (!foodQuery) {
        alert('Please enter a food name!');
        return;
    }

    try {
        const response = await fetch(`/search?query=${foodQuery}`);
        const data = await response.json();

        // Display the results
        let resultsHTML = '<ul>';
        data.foods.food.forEach(food => {
            resultsHTML += `<li>
                                <strong>${food.food_name}</strong> - 
                                Serving Size: ${food.serving_size_g}g - 
                                Calories: ${food.foods.servings.serving[0].calories}
                              </li>`;
        });
        resultsHTML += '</ul>';

        document.getElementById('results').innerHTML = resultsHTML;
    } catch (error) {
        console.error('Error fetching food data:', error);
        alert('Error fetching food data!');
    }
}