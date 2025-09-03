<?php

require_once "vendor/autoload.php";

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeload();

echo "🚀 ONDC On-Search Callback Test\n";
echo "================================\n\n";

// Test 1: Test the callback class directly
echo "📋 Test 1: Testing ONDCSearchCallback Class\n";
echo "--------------------------------------------\n";

require_once "src/on_search_callback.php";

$callback = new ONDCSearchCallback();

// Sample search request for rice
$riceSearchRequest = [
    'context' => [
        'domain' => 'ONDC:RET10',
        'country' => 'IND',
        'city' => 'std:080',
        'action' => 'search',
        'bap_id' => 'test-bap.ondc.org',
        'bap_uri' => 'https://test-bap.ondc.org',
        'transaction_id' => 'txn_' . uniqid(),
        'message_id' => 'msg_' . uniqid(),
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

echo "🔍 Testing Rice Search Request...\n";
$riceResponse = $callback->handleSearchRequest($riceSearchRequest);

echo "\n✅ Rice Search Response:\n";
echo json_encode($riceResponse, JSON_PRETTY_PRINT);

// Test 2: Test with different search parameters
echo "\n\n📋 Test 2: Testing Apple Search Request\n";
echo "----------------------------------------\n";

$appleSearchRequest = $riceSearchRequest;
$appleSearchRequest['message']['intent']['item']['descriptor']['name'] = 'Apples';
$appleSearchRequest['message']['intent']['item']['category_id'] = 'Fruits';
$appleSearchRequest['context']['transaction_id'] = 'txn_' . uniqid();
$appleSearchRequest['context']['message_id'] = 'msg_' . uniqid();

echo "🔍 Testing Apple Search Request...\n";
$appleResponse = $callback->handleSearchRequest($appleSearchRequest);

echo "\n✅ Apple Search Response:\n";
echo json_encode($appleResponse, JSON_PRETTY_PRINT);

// Test 3: Test error handling
echo "\n\n📋 Test 3: Testing Error Handling\n";
echo "-----------------------------------\n";

$invalidRequest = [
    'context' => [
        'domain' => 'ONDC:RET10'
        // Missing required fields
    ]
];

echo "🔍 Testing Invalid Request (should show error)...\n";
$errorResponse = $callback->handleSearchRequest($invalidRequest);

echo "\n❌ Error Response:\n";
echo json_encode($errorResponse, JSON_PRETTY_PRINT);

// Test 4: Test with chocolate search
echo "\n\n📋 Test 4: Testing Chocolate Search Request\n";
echo "---------------------------------------------\n";

$chocolateSearchRequest = $riceSearchRequest;
$chocolateSearchRequest['message']['intent']['item']['descriptor']['name'] = 'Chocolate';
$chocolateSearchRequest['message']['intent']['item']['category_id'] = 'Snacks';
$chocolateSearchRequest['context']['transaction_id'] = 'txn_' . uniqid();
$chocolateSearchRequest['context']['message_id'] = 'msg_' . uniqid();

echo "🔍 Testing Chocolate Search Request...\n";
$chocolateResponse = $callback->handleSearchRequest($chocolateSearchRequest);

echo "\n✅ Chocolate Search Response:\n";
echo json_encode($chocolateResponse, JSON_PRETTY_PRINT);

echo "\n\n🎯 All Tests Completed!\n";
echo "📁 Check logs/on_search_callback.log for detailed logs\n";
echo "🌐 The callback is ready to handle HTTP POST requests\n";
echo "📋 Sample curl command to test HTTP endpoint:\n\n";

// Generate sample curl command
$samplePayload = json_encode($riceSearchRequest, JSON_UNESCAPED_SLASHES);
echo "curl -X POST http://localhost/ondc/src/on_search_callback.php \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '$samplePayload'\n";

?>
