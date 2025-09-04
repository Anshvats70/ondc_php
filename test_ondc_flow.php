<?php
/**
 * Test script to demonstrate the complete ONDC flow:
 * 1. Send search request to /search
 * 2. Verify search response
 * 3. Check if on_search callback was triggered
 * 4. Verify callback logs
 */

echo "🚀 Testing Complete ONDC Search Flow\n";
echo "====================================\n\n";

// Test data for ONDC search
$searchData = [
    "context" => [
        "domain" => "ONDC:RET10",
        "action" => "search",
        "country" => "IND",
        "city" => "std:080",
        "core_version" => "1.2.0",
        "bap_id" => "test-buyer.local",
        "bap_uri" => "http://localhost/ondc",
        "transaction_id" => "T" . time(),
        "message_id" => "M" . time(),
        "timestamp" => date('c'),
        "ttl" => "PT30S"
    ],
    "message" => [
        "intent" => [
            "category" => [
                "id" => "Foodgrains"
            ],
            "fulfillment" => [
                "type" => "Delivery"
            ],
            "payment" => [
                "@ondc/org/buyer_app_finder_fee_type" => "percent",
                "@ondc/org/buyer_app_finder_fee_amount" => "3"
            ],
            "tags" => [
                [
                    "code" => "bap_terms",
                    "list" => [
                        [
                            "code" => "static_terms",
                            "value" => ""
                        ],
                        [
                            "code" => "static_terms_new",
                            "value" => "https://github.com/ONDC-Official/NP-Static-Terms/buyerNP_BNP/1.0/tc.pdf"
                        ],
                        [
                            "code" => "effective_date",
                            "value" => "2023-10-01T00:00:00.000Z"
                        ]
                    ]
                ]
            ]
        ]
    ]
];

echo "📋 Step 1: Sending ONDC Search Request\n";
echo "--------------------------------------\n";
echo "URL: http://localhost/ondc/src/search.php\n";
echo "Data: " . json_encode($searchData, JSON_PRETTY_PRINT) . "\n\n";

// Send search request
$searchResponse = sendSearchRequest($searchData);

if ($searchResponse['success']) {
    echo "✅ Search Request Successful!\n";
    echo "HTTP Code: " . $searchResponse['http_code'] . "\n";
    echo "Response: " . json_encode($searchResponse['data'], JSON_PRETTY_PRINT) . "\n\n";
    
    echo "📋 Step 2: Checking Search Logs\n";
    echo "--------------------------------\n";
    checkSearchLogs();
    
    echo "📋 Step 3: Checking Callback Logs\n";
    echo "---------------------------------\n";
    checkCallbackLogs();
    
    echo "📋 Step 4: Testing Callback Endpoint\n";
    echo "------------------------------------\n";
    testCallbackEndpoint($searchResponse['data']);
    
} else {
    echo "❌ Search Request Failed!\n";
    echo "Error: " . $searchResponse['error'] . "\n";
}

echo "\n🎯 ONDC Flow Test Completed!\n";

/**
 * Send search request to the endpoint
 */
function sendSearchRequest($data) {
    $url = 'http://localhost/ondc/src/search.php';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'cURL Error: ' . $error
        ];
    }
    
    $responseData = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Invalid JSON response: ' . $response
        ];
    }
    
    return [
        'success' => true,
        'http_code' => $httpCode,
        'data' => $responseData
    ];
}

/**
 * Check search logs for callback information
 */
function checkSearchLogs() {
    $logFile = 'logs/search.log';
    
    if (!file_exists($logFile)) {
        echo "⚠️ Search log file not found: $logFile\n";
        return;
    }
    
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    
    // Find callback-related logs
    $callbackLogs = [];
    foreach ($lines as $line) {
        if (strpos($line, 'on_search') !== false || 
            strpos($line, 'callback') !== false ||
            strpos($line, 'Triggering') !== false) {
            $callbackLogs[] = $line;
        }
    }
    
    if (empty($callbackLogs)) {
        echo "⚠️ No callback logs found in search.log\n";
    } else {
        echo "📝 Callback-related logs found:\n";
        foreach (array_slice($callbackLogs, -5) as $log) { // Show last 5
            echo "   " . trim($log) . "\n";
        }
    }
    echo "\n";
}

/**
 * Check callback logs
 */
function checkCallbackLogs() {
    $logFile = 'logs/on_search_callback.log';
    
    if (!file_exists($logFile)) {
        echo "⚠️ Callback log file not found: $logFile\n";
        echo "   This means no callbacks were received yet\n\n";
        return;
    }
    
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    
    // Find recent callback logs
    $recentLogs = array_slice($lines, -20); // Last 20 lines
    
    echo "📝 Recent callback logs:\n";
    foreach ($recentLogs as $log) {
        if (trim($log) && strpos($log, 'Received on_search callback') !== false) {
            echo "   " . trim($log) . "\n";
        }
    }
    echo "\n";
}

/**
 * Test the callback endpoint directly
 */
function testCallbackEndpoint($searchResponse) {
    $callbackData = [
        'context' => [
            'domain' => 'ONDC:RET10',
            'action' => 'on_search',
            'country' => 'IND',
            'city' => 'std:080',
            'core_version' => '1.2.0',
            'bap_id' => 'test-buyer.local',
            'bap_uri' => 'http://localhost/ondc',
            'bpp_id' => 'neo-server.rozana.in',
            'bpp_uri' => 'https://neo-server.rozana.in/ondc',
            'transaction_id' => 'T' . time(),
            'message_id' => 'msg_' . uniqid(),
            'timestamp' => date('c'),
            'ttl' => 'PT30S'
        ],
        'message' => [
            'ack' => [
                'status' => 'ACK'
            ],
            'catalog' => $searchResponse['message']['catalog'] ?? []
        ]
    ];
    
    $url = 'http://localhost/ondc/test_on_search_endpoint.php';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($callbackData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Callback endpoint test failed: " . $error . "\n";
    } else {
        echo "✅ Callback endpoint test successful!\n";
        echo "HTTP Code: " . $httpCode . "\n";
        
        $responseData = json_decode($response, true);
        if ($responseData && isset($responseData['status'])) {
            echo "Status: " . $responseData['status'] . "\n";
            if (isset($responseData['message'])) {
                echo "Message: " . $responseData['message'] . "\n";
            }
        }
    }
    echo "\n";
}
?>
