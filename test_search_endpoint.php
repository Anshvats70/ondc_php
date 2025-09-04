<?php

/**
 * Test script for ONDC Search Endpoint
 * This script demonstrates how to test the search functionality
 */

// Test data based on the ONDC specification
$testSearchData = [
    "context" => [
        "domain" => "ONDC:RET10",
        "action" => "search",
        "country" => "IND",
        "city" => "std:080",
        "core_version" => "1.2.0",
        "bap_id" => "neo-server.rozana.in",
        "bap_uri" => "http://localhost/ondc/src",
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

echo "🧪 Testing ONDC Search Endpoint\n";
echo "================================\n\n";

// Test 1: Test with Foodgrains category
echo "Test 1: Foodgrains Category Search\n";
echo "----------------------------------\n";
$response1 = testSearchEndpoint($testSearchData);
echo "Response: " . json_encode($response1, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Test with different category
echo "Test 2: Fruits and Vegetables Category Search\n";
echo "---------------------------------------------\n";
$testSearchData['message']['intent']['category']['id'] = 'Fruits and Vegetables';
$response2 = testSearchEndpoint($testSearchData);
echo "Response: " . json_encode($response2, JSON_PRETTY_PRINT) . "\n\n";

// Test 3: Test with different domain (F&B)
echo "Test 3: F&B Domain Search (ONDC:RET11)\n";
echo "--------------------------------------\n";
$testSearchData['context']['domain'] = 'ONDC:RET11';
$testSearchData['message']['intent']['category']['id'] = 'Beverages';
$response3 = testSearchEndpoint($testSearchData);
echo "Response: " . json_encode($response3, JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Test with Personal Care domain
echo "Test 4: Personal Care Domain Search (ONDC:RET12)\n";
echo "------------------------------------------------\n";
$testSearchData['context']['domain'] = 'ONDC:RET12';
$testSearchData['message']['intent']['category']['id'] = 'Personal Care';
$response4 = testSearchEndpoint($testSearchData);
echo "Response: " . json_encode($response4, JSON_PRETTY_PRINT) . "\n\n";

echo "✅ All tests completed!\n";

/**
 * Test the search endpoint
 */
function testSearchEndpoint($data) {
    $url = 'http://localhost/ondc/src/search.php';
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        return [
            'error' => 'cURL Error: ' . curl_error($ch),
            'http_code' => $httpCode
        ];
    }
    
    curl_close($ch);
    
    // Parse response
    $responseData = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'error' => 'Invalid JSON response: ' . $response,
            'http_code' => $httpCode
        ];
    }
    
    return [
        'data' => $responseData,
        'http_code' => $httpCode
    ];
}

/**
 * Alternative: Test using file_get_contents (if cURL is not available)
 */
function testSearchEndpointAlternative($data) {
    $url = 'http://localhost/ondc/src/search.php';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            'content' => json_encode($data)
        ]
    ]);
    
    try {
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            return ['error' => 'Failed to get response from endpoint'];
        }
        
        $responseData = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON response: ' . $response];
        }
        
        return ['data' => $responseData];
        
    } catch (Exception $e) {
        return ['error' => 'Exception: ' . $e->getMessage()];
    }
}

echo "\n📋 Usage Instructions:\n";
echo "=====================\n";
echo "1. Make sure your XAMPP server is running\n";
echo "2. Ensure the search.php file is accessible at: http://localhost/ondc/src/search.php\n";
echo "3. Set up your .env file with the required keys:\n";
echo "   - SUBSCRIBER_ID\n";
echo "   - UNIQUE_KEY_ID\n";
echo "   - SIGNING_PRIVATE_KEY\n";
echo "   - SIGNING_PUB_KEY\n";
echo "4. Run this test script: php test_search_endpoint.php\n";
echo "5. Check the logs directory for detailed logs\n";
echo "\n🔗 Endpoint URL: http://localhost/ondc/src/search.php\n";
echo "📝 Method: POST\n";
echo "📄 Content-Type: application/json\n";
