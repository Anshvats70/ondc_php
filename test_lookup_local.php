<?php

require_once "vendor/autoload.php";

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeload();

echo "🚀 ONDC Lookup API - Local Test\n";
echo "================================\n\n";

// Test 1: Generate fresh payload
echo "📋 Test 1: Generate Fresh Payload\n";
echo "--------------------------------\n";
$payload = [
    "subscriber_id" => "neo-server.rozana.in",
    "domain" => "ONDC:RET10",
    "ukId" => "3bd6f47a-d2ea-4210-a4ad-2a99dd66585b",
    "country" => "IND",
    "city" => "std:080",
    "type" => "BAP"
];

echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Use hardcoded authorization header
echo "🔐 Test 2: Using Hardcoded Authorization Header\n";
echo "-----------------------------------------------\n";

$jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

// Hardcoded authorization header from user
$authHeader = 'Signature keyId="neo-server.rozana.in|3bd6f47a-d2ea-4210-a4ad-2a99dd66585b|ed25519",algorithm="ed25519",created="1756904093",expires="1756907693",headers="(created) (expires) digest",signature="SmyCjoP+N9Umb+s5G4B2QnFZvz7N/IEeuXGVVF7VSG/gpoaace7/lIJOrOx/rIwjhstWO2PsAKkaJlab+bQUDQ=="';

echo "✅ Using Hardcoded Authorization Header:\n";
echo "$authHeader\n\n";

// Test 3: Generate local curl command
echo "📝 Test 3: Local Curl Command\n";
echo "------------------------------\n";
echo "curl --location 'https://preprod.registry.ondc.org/v2.0/lookup' \\\n";
echo "  --header 'Content-Type: application/json' \\\n";
echo "  --header 'Accept: application/json' \\\n";
echo "  --header 'Authorization: $authHeader' \\\n";
echo "  --data '$jsonPayload'\n\n";

// Test 4: Test local HTTP request (optional)
echo "🌐 Test 4: Test Local HTTP Request\n";
echo "----------------------------------\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://preprod.registry.ondc.org/v2.0/lookup',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $jsonPayload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: ' . $authHeader,
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

echo "Sending request to ONDC Registry...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "✅ HTTP Response Code: $httpCode\n";
    echo "📄 Response Body:\n";
    echo $response . "\n";
}

echo "\n🎯 Local Test Complete!\n";
?>
