<?php

/**
 * Simple test script to debug the search endpoint
 */

echo "🧪 Simple ONDC Search Test\n";
echo "==========================\n\n";

// Test data
$testData = [
    "context" => [
        "domain" => "ONDC:RET10",
        "action" => "search",
        "country" => "IND",
        "city" => "std:080",
        "core_version" => "1.2.0",
        "bap_id" => "neo-server.rozana.in",
        "bap_uri" => "https://neo-server.rozana.in/ondc",
        "transaction_id" => "T1",
        "message_id" => "M1",
        "timestamp" => "2023-06-03T08:00:00.000Z",
        "ttl" => "PT30S"
    ],
    "message" => [
        "intent" => [
            "category" => [
                "id" => "Foodgrains"
            ],
            "fulfillment" => [
                "type" => "Delivery"
            ]
        ]
    ]
];

echo "📤 Sending test request...\n";
echo "URL: http://localhost/ondc/src/search.php\n";
echo "Data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

// Test using file_get_contents
$url = 'http://localhost/ondc/src/search.php';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        'content' => json_encode($testData)
    ]
]);

try {
    echo "🔄 Making request...\n";
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Failed to get response\n";
        echo "Error info: " . print_r(error_get_last(), true) . "\n";
    } else {
        echo "✅ Response received:\n";
        echo "Raw response: " . $response . "\n\n";
        
        // Try to parse JSON
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "⚠️ Response is not valid JSON: " . json_last_error_msg() . "\n";
            echo "This might indicate a PHP error in the endpoint\n";
        } else {
            echo "📋 Parsed response:\n";
            echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n🔍 Debug Information:\n";
echo "====================\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "File exists (search.php): " . (file_exists('src/search.php') ? 'Yes' : 'No') . "\n";
echo "File exists (vendor/autoload.php): " . (file_exists('vendor/autoload.php') ? 'Yes' : 'No') . "\n";

// Check if logs directory exists
$logsDir = 'logs';
if (is_dir($logsDir)) {
    echo "Logs directory: " . $logsDir . " (exists)\n";
    echo "Logs directory writable: " . (is_writable($logsDir) ? 'Yes' : 'No') . "\n";
} else {
    echo "Logs directory: " . $logsDir . " (does not exist)\n";
}

// Check for search.log
$searchLog = $logsDir . '/search.log';
if (file_exists($searchLog)) {
    echo "Search log exists: Yes\n";
    echo "Search log size: " . filesize($searchLog) . " bytes\n";
    echo "Last 5 lines of search log:\n";
    $lines = file($searchLog);
    $lastLines = array_slice($lines, -5);
    foreach ($lastLines as $line) {
        echo "  " . trim($line) . "\n";
    }
} else {
    echo "Search log exists: No\n";
}
