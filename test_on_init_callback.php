<?php

require_once "vendor/autoload.php";
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeload();

echo "🚀 ONDC On-Init Callback Test\n";
echo "==============================\n\n";

require_once "src/on_init_callback.php";
$callback = new ONDCInitCallback();

// Test 1: Rice + Apple Init Request
$riceAppleInitRequest = [
    'context' => [
        'domain' => 'ONDC:RET10',
        'country' => 'IND',
        'city' => 'std:080',
        'action' => 'init',
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
            ],
            'payment' => [
                'type' => 'ON-ORDER',
                'collected_by' => 'BAP'
            ],
            'billing' => [
                'name' => 'John Doe',
                'address' => [
                    'locality' => 'Connaught Place',
                    'city' => 'Delhi',
                    'state' => 'Delhi',
                    'country' => 'India'
                ],
                'email' => 'john.doe@example.com',
                'phone' => '+91-9876543210'
            ]
        ]
    ]
];

echo "🛒 Testing Rice + Apple Init Request...\n";
$riceAppleResponse = $callback->handleInitRequest($riceAppleInitRequest);
echo "\n✅ Rice + Apple Init Response:\n";
echo json_encode($riceAppleResponse, JSON_PRETTY_PRINT);

// Test 2: Chocolate Init Request
$chocolateInitRequest = [
    'context' => [
        'domain' => 'ONDC:RET10',
        'country' => 'IND',
        'city' => 'std:080',
        'action' => 'init',
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
            ],
            'fulfillment' => [
                'type' => 'Delivery',
                'locations' => [
                    [
                        'city' => [
                            'name' => 'Mumbai'
                        ],
                        'state' => [
                            'name' => 'Maharashtra'
                        ],
                        'country' => [
                            'name' => 'India'
                        ]
                    ]
                ]
            ],
            'payment' => [
                'type' => 'ON-ORDER',
                'collected_by' => 'BAP'
            ],
            'billing' => [
                'name' => 'Jane Smith',
                'address' => [
                    'locality' => 'Bandra West',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'country' => 'India'
                ],
                'email' => 'jane.smith@example.com',
                'phone' => '+91-9876543211'
            ]
        ]
    ]
];

echo "\n\n🍫 Testing Chocolate Init Request...\n";
$chocolateResponse = $callback->handleInitRequest($chocolateInitRequest);
echo "\n✅ Chocolate Init Response:\n";
echo json_encode($chocolateResponse, JSON_PRETTY_PRINT);

// Test 3: Mixed Items Init Request
$mixedInitRequest = [
    'context' => [
        'domain' => 'ONDC:RET10',
        'country' => 'IND',
        'city' => 'std:080',
        'action' => 'init',
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
                    'quantity' => 2,
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
            ],
            'fulfillment' => [
                'type' => 'Delivery',
                'locations' => [
                    [
                        'city' => [
                            'name' => 'Bangalore'
                        ],
                        'state' => [
                            'name' => 'Karnataka'
                        ],
                        'country' => [
                            'name' => 'India'
                        ]
                    ]
                ]
            ],
            'payment' => [
                'type' => 'ON-ORDER',
                'collected_by' => 'BAP'
            ],
            'billing' => [
                'name' => 'Bob Wilson',
                'address' => [
                    'locality' => 'Indiranagar',
                    'city' => 'Bangalore',
                    'state' => 'Karnataka',
                    'country' => 'India'
                ],
                'email' => 'bob.wilson@example.com',
                'phone' => '+91-9876543212'
            ]
        ]
    ]
];

echo "\n\n🛍️ Testing Mixed Items Init Request...\n";
$mixedResponse = $callback->handleInitRequest($mixedInitRequest);
echo "\n✅ Mixed Items Init Response:\n";
echo json_encode($mixedResponse, JSON_PRETTY_PRINT);

// Test 4: Invalid Request (Missing Required Fields)
$invalidRequest = [
    'context' => [
        'domain' => 'ONDC:RET10'
        // Missing required fields
    ]
];

echo "\n\n❌ Testing Invalid Request (Missing Required Fields)...\n";
$invalidResponse = $callback->handleInitRequest($invalidRequest);
echo "\n✅ Invalid Request Response:\n";
echo json_encode($invalidResponse, JSON_PRETTY_PRINT);

echo "\n\n🎯 All Tests Completed!\n";
echo "📁 Check logs/on_init_callback.log for detailed logs\n";
echo "🌐 The callback is ready to handle HTTP POST requests\n";
echo "📋 Sample curl command to test HTTP endpoint:\n\n";

$sampleCurl = "curl --location 'http://localhost/ondc/src/on_init_callback.php' \\\n";
$sampleCurl .= " --header 'Content-Type: application/json' \\\n";
$sampleCurl .= " --data '" . json_encode($riceAppleInitRequest, JSON_UNESCAPED_SLASHES) . "'";

echo $sampleCurl . "\n";
?>
