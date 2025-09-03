<?php

require_once "vendor/autoload.php";

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeload();

echo "🛒 ONDC On-Select Callback Test\n";
echo "================================\n\n";

// Test 1: Test the callback class directly
echo "📋 Test 1: Testing ONDCSelectCallback Class\n";
echo "--------------------------------------------\n";

require_once "src/on_select_callback.php";

$callback = new ONDCSelectCallback();

// Sample select request for rice and apples
$riceAppleSelectRequest = [
    'context' => [
        'domain' => 'ONDC:RET10',
        'country' => 'IND',
        'city' => 'std:080',
        'action' => 'select',
        'bap_id' => 'test-bap.ondc.org',
        'bap_uri' => 'https://test-bap.ondc.org',
        'transaction_id' => 'txn_' . uniqid(),
        'message_id' => 'msg_' . uniqid(),
        'timestamp' => date('c'),
        'ttl' => 'PT30S'
    ],
    'message' => [
        'order' => [
            'items' => [
                [
                    'id' => 'item_001',
                    'quantity' => 2,
                    'descriptor' => [
                        'name' => 'Organic Basmati Rice'
                    ],
                    'price' => [
                        'currency' => 'INR',
                        'value' => '120'
                    ],
                    'category_id' => 'Foodgrains',
                    'fulfillment_id' => 'fulfillment_001',
                    'location_id' => 'location_001'
                ],
                [
                    'id' => 'item_002',
                    'quantity' => 1,
                    'descriptor' => [
                        'name' => 'Fresh Apples'
                    ],
                    'price' => [
                        'currency' => 'INR',
                        'value' => '180'
                    ],
                    'category_id' => 'Fruits',
                    'fulfillment_id' => 'fulfillment_001',
                    'location_id' => 'location_001'
                ]
            ],
            'fulfillment' => [
                'type' => 'Delivery',
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

echo "🛒 Testing Rice + Apple Select Request...\n";
$riceAppleResponse = $callback->handleSelectRequest($riceAppleSelectRequest);

echo "\n✅ Rice + Apple Select Response:\n";
echo json_encode($riceAppleResponse, JSON_PRETTY_PRINT);

// Test 2: Test with single chocolate item
echo "\n\n📋 Test 2: Testing Single Chocolate Select Request\n";
echo "----------------------------------------------------\n";

$chocolateSelectRequest = $riceAppleSelectRequest;
$chocolateSelectRequest['message']['order']['items'] = [
    [
        'id' => 'item_003',
        'quantity' => 3,
        'descriptor' => [
            'name' => 'Dairy Milk Chocolate'
        ],
        'price' => [
            'currency' => 'INR',
            'value' => '50'
        ],
        'category_id' => 'Snacks',
        'fulfillment_id' => 'fulfillment_001',
        'location_id' => 'location_001'
    ]
];
$chocolateSelectRequest['context']['transaction_id'] = 'txn_' . uniqid();
$chocolateSelectRequest['context']['message_id'] = 'msg_' . uniqid();

echo "🛒 Testing Chocolate Select Request...\n";
$chocolateResponse = $callback->handleSelectRequest($chocolateSelectRequest);

echo "\n✅ Chocolate Select Response:\n";
echo json_encode($chocolateResponse, JSON_PRETTY_PRINT);

// Test 3: Test error handling
echo "\n\n📋 Test 3: Testing Error Handling\n";
echo "-----------------------------------\n";

$invalidRequest = [
    'context' => [
        'domain' => 'ONDC:RET10'
        // Missing required fields
    ]
];

echo "🛒 Testing Invalid Request (should show error)...\n";
$errorResponse = $callback->handleSelectRequest($invalidRequest);

echo "\n❌ Error Response:\n";
echo json_encode($errorResponse, JSON_PRETTY_PRINT);

// Test 4: Test with mixed items
echo "\n\n📋 Test 4: Testing Mixed Items Select Request\n";
echo "-----------------------------------------------\n";

$mixedSelectRequest = $riceAppleSelectRequest;
$mixedSelectRequest['message']['order']['items'] = [
    [
        'id' => 'item_001',
        'quantity' => 1,
        'descriptor' => [
            'name' => 'Organic Basmati Rice'
        ],
        'price' => [
            'currency' => 'INR',
            'value' => '120'
        ],
        'category_id' => 'Foodgrains',
        'fulfillment_id' => 'fulfillment_001',
        'location_id' => 'location_001'
    ],
    [
        'id' => 'item_002',
        'quantity' => 2,
        'descriptor' => [
            'name' => 'Fresh Apples'
        ],
        'price' => [
            'currency' => 'INR',
            'value' => '180'
        ],
        'category_id' => 'Fruits',
        'fulfillment_id' => 'fulfillment_001',
        'location_id' => 'location_001'
    ],
    [
        'id' => 'item_003',
        'quantity' => 1,
        'descriptor' => [
            'name' => 'Dairy Milk Chocolate'
        ],
        'price' => [
            'currency' => 'INR',
            'value' => '50'
        ],
        'category_id' => 'Snacks',
        'fulfillment_id' => 'fulfillment_001',
        'location_id' => 'location_001'
    ]
];
$mixedSelectRequest['context']['transaction_id'] = 'txn_' . uniqid();
$mixedSelectRequest['context']['message_id'] = 'msg_' . uniqid();

echo "🛒 Testing Mixed Items Select Request...\n";
$mixedResponse = $callback->handleSelectRequest($mixedSelectRequest);

echo "\n✅ Mixed Items Select Response:\n";
echo json_encode($mixedResponse, JSON_PRETTY_PRINT);

echo "\n\n🎯 All Tests Completed!\n";
echo "📁 Check logs/on_select_callback.log for detailed logs\n";
echo "🌐 The callback is ready to handle HTTP POST requests\n";
echo "📋 Sample curl command to test HTTP endpoint:\n\n";

// Generate sample curl command
$samplePayload = json_encode($riceAppleSelectRequest, JSON_UNESCAPED_SLASHES);
echo "curl -X POST http://localhost/ondc/src/on_select_callback.php \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -d '$samplePayload'\n";

?>
