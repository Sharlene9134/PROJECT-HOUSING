<?php
echo "Testing cURL to Node.js...<br><br>";

// Test if cURL is enabled
if (!function_exists('curl_init')) {
    die("ERROR: cURL is NOT enabled in PHP!");
}
echo "✅ cURL is enabled<br>";

// Test connection to Node.js
$ch = curl_init('http://localhost:3000/new-property');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'id' => 999,
    'title' => 'Test Property',
    'price' => 1000000,
    'description' => 'Test description',
    'location' => 'Test Location',
    'seller_name' => 'Test Seller'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: " . $httpCode . "<br>";
echo "Response: " . $response . "<br>";

if ($error) {
    echo "cURL Error: " . $error . "<br>";
}

if ($httpCode == 200) {
    echo "<br>✅ SUCCESS! Node.js received the test property!";
    echo "<br>Check your buyer dashboard - you should see a test property!";
} else {
    echo "<br>❌ FAILED! Node.js didn't receive the test property.";
}
?>