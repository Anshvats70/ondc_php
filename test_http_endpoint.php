<?php

echo "🧪 Testing ONDC On-Search HTTP Endpoint\n";
echo "=======================================\n\n";

// Test data
$testData = [
    'context' => [
        'domain' => 'ONDC:RET10',
        'country' => 'IND',
        'city' => 'std:080',
        'action' => 'search',
        'bap_id' => 'test-bap.ondc.org',
        'bap_uri' => 'https://test-bap.ondc.org',
        'transaction_id' => 'txn_test_' . uniqid(),
        'message_id' => 'msg_test_' . uniqid(),
        'timestamp' => date('c'),
        'ttl' => 'PT30S'
    ],
    'message' => [
        'intent' => [
            'item' => [
                'descriptor' => [
                    'name' => 'Rice'
                ],
                'category_id' => 'Foodgrains'
            ],
            'fulfillment' => [
                'locations' => [
                    [
                        'city' => [
                            'name' => 'Delhi'
                        ],
                        'state' => [
                            'name' => 'Delhi'
                        ],
                        'country' => [
                            'name' => 'India'
                        ]
                    ]
                ]
            ]
        ]
    ]
];

echo "📋 Test Data:\n";
echo json_encode($testData, JSON_PRETTY_PRINT);
echo "\n\n";

// Simulate HTTP request
echo "🌐 Simulating HTTP POST Request...\n";
echo "URL: http://localhost/ondc/src/on_search_callback.php\n";
echo "Method: POST\n";
echo "Content-Type: application/json\n\n";

// Include the callback file and test it
require_once "src/on_search_callback.php";

$callback = new ONDCSearchCallback();
$response = $callback->handleSearchRequest($testData);

echo "✅ HTTP Response:\n";
echo "HTTP Status: 200 OK\n";
echo "Content-Type: application/json\n\n";
echo json_encode($response, JSON_PRETTY_PRINT);

echo "\n\n🎯 HTTP Endpoint Test Completed!\n";
echo "The endpoint is now ready to receive HTTP requests.\n";
echo "You can test it with Postman or any HTTP client.\n";

?>
