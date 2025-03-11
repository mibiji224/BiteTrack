<?php
$api_key = "xURY1NGNvFUgDmNVX1no+w==uRCSd79qlz5t4Kxs"; // Replace with your actual API key
$food = isset($_GET["food"]) ? $_GET["food"] : ""; // Get user input from URL query

if (empty($food)) {
    echo json_encode(["error" => "No food item provided"]);
    exit;
}

$url = "https://api.calorieninjas.com/v1/nutrition?query=" . urlencode($food);

$options = [
    "http" => [
        "header" => "X-Api-Key: $api_key",
        "method" => "GET"
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$data = json_decode($response, true);

header('Content-Type: application/json');
echo json_encode($data);
?>
