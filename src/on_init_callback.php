<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

use Dotenv\Dotenv;
use Ramsey\Uuid\Uuid;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeload();

class ONDCInitCallback {
    
    private $logFile;
    
    public function __construct() {
        // Create logs directory if it doesn't exist
        $this->logFile = dirname(__DIR__) . '/logs/on_init_callback.log';
        $logsDir = dirname($this->logFile);
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
    }
    
    /**
     * Handle incoming ONDC init request
     */
    public function handleInitRequest($requestData) {
        $this->log("🚀 Received ONDC Init Request");
        $this->log("Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT));
        
        try {
            // Validate request structure
            $this->validateInitRequest($requestData);
            
            // Extract init parameters
            $initParams = $this->extractInitParameters($requestData);
            
            // Process initialization and get order details
            $orderDetails = $this->processInit($initParams);
            
            // Generate response
            $response = $this->generateInitResponse($requestData, $orderDetails);
            
            $this->log("✅ Init processed successfully");
            $this->log("Response: " . json_encode($response, JSON_PRETTY_PRINT));
            
            return $response;
            
        } catch (Exception $e) {
            $this->log("❌ Error processing init: " . $e->getMessage());
            return $this->generateErrorResponse($e->getMessage());
        }
    }
    
    /**
     * Validate incoming init request
     */
    private function validateInitRequest($requestData) {
        $requiredFields = ['context', 'message'];
        
        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // Validate context
        if (!isset($requestData['context']['domain'])) {
            throw new Exception("Missing domain in context");
        }
        
        // Validate message
        if (!isset($requestData['message']['order'])) {
            throw new Exception("Missing order in message");
        }
        
        if (!isset($requestData['message']['order']['items'])) {
            throw new Exception("Missing items in order");
        }
        
        if (!isset($requestData['message']['order']['fulfillment'])) {
            throw new Exception("Missing fulfillment in order");
        }
        
        if (!isset($requestData['message']['order']['payment'])) {
            throw new Exception("Missing payment in order");
        }
    }
    
    /**
     * Extract init parameters from request
     */
    private function extractInitParameters($requestData) {
        $order = $requestData['message']['order'];
        
        $params = [
            'domain' => $requestData['context']['domain'] ?? 'ONDC:RET10',
            'items' => [],
            'fulfillment' => $order['fulfillment'] ?? null,
            'payment' => $order['payment'] ?? null,
            'billing' => $order['billing'] ?? null,
            'quote' => $order['quote'] ?? null,
            'customer' => $order['customer'] ?? null
        ];
        
        // Extract item details
        foreach ($order['items'] as $item) {
            $itemInfo = [
                'id' => $item['id'] ?? null,
                'quantity' => $item['quantity'] ?? null,
                'descriptor' => $item['descriptor'] ?? null,
                'price' => $item['price'] ?? null,
                'category_id' => $item['category_id'] ?? null,
                'fulfillment_id' => $item['fulfillment_id'] ?? null,
                'location_id' => $item['location_id'] ?? null
            ];
            $params['items'][] = $itemInfo;
        }
        
        $this->log("Extracted Init Parameters: " . json_encode($params, JSON_PRETTY_PRINT));
        
        return $params;
    }
    
    /**
     * Process init and get detailed order information
     */
    private function processInit($initParams) {
        $this->log("Processing init for " . count($initParams['items']) . " items");
        
        $processedItems = [];
        $totalPrice = 0;
        
        foreach ($initParams['items'] as $item) {
            $itemDetails = $this->getItemDetails($item);
            $processedItems[] = $itemDetails;
            $totalPrice += $itemDetails['price'] * $item['quantity'];
        }
        
        // Calculate additional charges
        $deliveryCharge = $this->calculateDeliveryCharge($initParams['fulfillment']);
        $taxAmount = $totalPrice * 0.18; // 18% GST
        $totalAmount = $totalPrice + $deliveryCharge + $taxAmount;
        
        return [
            'items' => $processedItems,
            'total_price' => $totalPrice,
            'delivery_charge' => $deliveryCharge,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => 'INR',
            'init_params' => $initParams
        ];
    }
    
    /**
     * Get detailed information for a specific item
     */
    private function getItemDetails($item) {
        // Mock item database - replace with your actual database query
        $itemDatabase = [
            'item_001' => [
                'id' => 'item_001',
                'name' => 'Organic Basmati Rice',
                'category' => 'Foodgrains',
                'price' => 120.00,
                'currency' => 'INR',
                'unit' => 'kg',
                'description' => 'Premium quality organic basmati rice',
                'brand' => 'Organic Valley',
                'images' => ['https://example.com/rice1.jpg'],
                'seller_details' => [
                    'name' => 'Organic Valley Farms',
                    'rating' => 4.5,
                    'reviews' => 1250
                ],
                'fulfillment' => [
                    'type' => 'Delivery',
                    'locations' => ['Delhi', 'Mumbai', 'Bangalore'],
                    'delivery_time' => '2-3 days',
                    'delivery_charge' => 50.00
                ],
                'return_policy' => [
                    'returnable' => true,
                    'return_window' => '7 days',
                    'refund_policy' => 'Full refund for damaged items'
                ]
            ],
            'item_002' => [
                'id' => 'item_002',
                'name' => 'Fresh Apples',
                'category' => 'Fruits',
                'price' => 180.00,
                'currency' => 'INR',
                'unit' => 'kg',
                'description' => 'Fresh red apples from Kashmir',
                'brand' => 'Kashmir Fresh',
                'images' => ['https://example.com/apples1.jpg'],
                'seller_details' => [
                    'name' => 'Kashmir Fresh Fruits',
                    'rating' => 4.3,
                    'reviews' => 890
                ],
                'fulfillment' => [
                    'type' => 'Delivery',
                    'locations' => ['Delhi', 'Punjab', 'Haryana'],
                    'delivery_time' => '1-2 days',
                    'delivery_charge' => 40.00
                ],
                'return_policy' => [
                    'returnable' => true,
                    'return_window' => '3 days',
                    'refund_policy' => 'Replacement for damaged items'
                ]
            ],
            'item_003' => [
                'id' => 'item_003',
                'name' => 'Dairy Milk Chocolate',
                'category' => 'Snacks',
                'price' => 50.00,
                'currency' => 'INR',
                'unit' => 'pack',
                'description' => 'Classic dairy milk chocolate bar',
                'brand' => 'Cadbury',
                'images' => ['https://example.com/chocolate1.jpg'],
                'seller_details' => [
                    'name' => 'Cadbury India',
                    'rating' => 4.7,
                    'reviews' => 2100
                ],
                'fulfillment' => [
                    'type' => 'Delivery',
                    'locations' => ['Delhi', 'Mumbai', 'Chennai'],
                    'delivery_time' => '1-2 days',
                    'delivery_charge' => 30.00
                ],
                'return_policy' => [
                    'returnable' => true,
                    'return_window' => '7 days',
                    'refund_policy' => 'Full refund for damaged items'
                ]
            ]
        ];
        
        $itemId = $item['id'];
        if (isset($itemDatabase[$itemId])) {
            $details = $itemDatabase[$itemId];
            $details['selected_quantity'] = $item['quantity'];
            $details['total_price'] = $details['price'] * $item['quantity'];
            return $details;
        }
        
        // Return default item if not found
        return [
            'id' => $itemId,
            'name' => 'Unknown Item',
            'category' => 'General',
            'price' => 0.00,
            'currency' => 'INR',
            'unit' => 'piece',
            'description' => 'Item details not available',
            'brand' => 'Unknown',
            'images' => [],
            'selected_quantity' => $item['quantity'],
            'total_price' => 0.00
        ];
    }
    
    /**
     * Calculate delivery charge based on fulfillment details
     */
    private function calculateDeliveryCharge($fulfillment) {
        if (!$fulfillment) {
            return 50.00; // Default delivery charge
        }
        
        // Base delivery charge
        $baseCharge = 50.00;
        
        // Add location-based charges
        if (isset($fulfillment['locations']) && is_array($fulfillment['locations'])) {
            foreach ($fulfillment['locations'] as $location) {
                if (isset($location['city']['name'])) {
                    $city = strtolower($location['city']['name']);
                    
                    // Different delivery charges for different cities
                    switch ($city) {
                        case 'mumbai':
                        case 'bangalore':
                        case 'chennai':
                            return $baseCharge + 20.00; // Metro cities
                        case 'delhi':
                            return $baseCharge + 10.00; // Capital city
                        case 'pune':
                        case 'hyderabad':
                            return $baseCharge + 15.00; // Tier 1 cities
                        default:
                            return $baseCharge; // Other cities
                    }
                }
            }
        }
        
        return $baseCharge;
    }
    
    /**
     * Generate ONDC compliant init response
     */
    private function generateInitResponse($requestData, $orderDetails) {
        $messageId = Uuid::uuid4()->toString();
        $timestamp = date('c');
        $orderId = 'order_' . uniqid();
        
        $response = [
            'context' => [
                'domain' => $requestData['context']['domain'],
                'country' => $requestData['context']['country'] ?? 'IND',
                'city' => $requestData['context']['city'] ?? 'std:080',
                'action' => 'on_init',
                'core_version' => '1.2.0',
                'bap_id' => $_ENV['SUBSCRIBER_ID'] ?? 'neo-server.rozana.in',
                'bap_uri' => $_ENV['SUBSCRIBER_URL'] ?? 'https://neo-server.rozana.in/bapl',
                'transaction_id' => $requestData['context']['transaction_id'] ?? Uuid::uuid4()->toString(),
                'message_id' => $messageId,
                'timestamp' => $timestamp,
                'ttl' => 'PT30S'
            ],
            'message' => [
                'ack' => [
                    'status' => 'ACK'
                ],
                'order' => [
                    'id' => $orderId,
                    'state' => 'Created',
                    'provider' => [
                        'id' => 'provider_001',
                        'descriptor' => [
                            'name' => 'Rozana Store',
                            'short_desc' => 'Your neighborhood grocery store',
                            'long_desc' => 'Trusted grocery store serving the community with quality products'
                        ],
                        'locations' => [
                            [
                                'id' => 'location_001',
                                'descriptor' => [
                                    'name' => 'Rozana Main Store'
                                ],
                                'address' => [
                                    'locality' => 'Connaught Place',
                                    'city' => 'Delhi',
                                    'state' => 'Delhi',
                                    'country' => 'India'
                                ]
                            ]
                        ]
                    ],
                    'items' => $this->formatInitItems($orderDetails['items']),
                    'fulfillments' => $this->generateFulfillments($orderDetails),
                    'payments' => $this->generatePayments($orderDetails),
                    'billing' => $this->generateBilling($orderDetails),
                    'quote' => $this->generateQuote($orderDetails),
                    'customer' => $this->generateCustomer($orderDetails),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ]
            ]
        ];
        
        return $response;
    }
    
    /**
     * Format items for init response
     */
    private function formatInitItems($items) {
        $formattedItems = [];
        
        foreach ($items as $item) {
            $formattedItems[] = [
                'id' => $item['id'],
                'descriptor' => [
                    'name' => $item['name'],
                    'short_desc' => $item['description'],
                    'long_desc' => $item['description'],
                    'brand' => $item['brand'],
                    'images' => $item['images']
                ],
                'price' => [
                    'currency' => $item['currency'],
                    'value' => (string)$item['price']
                ],
                'category_id' => $item['category'],
                'fulfillment_id' => 'fulfillment_001',
                'location_id' => 'location_001',
                'quantity' => [
                    'count' => $item['selected_quantity'],
                    'measure' => [
                        'unit' => $item['unit'],
                        'value' => $item['selected_quantity']
                    ]
                ],
                'seller_details' => $item['seller_details'] ?? null,
                'return_policy' => $item['return_policy'] ?? null
            ];
        }
        
        return $formattedItems;
    }
    
    /**
     * Generate fulfillments for the order
     */
    private function generateFulfillments($orderDetails) {
        return [
            [
                'id' => 'fulfillment_001',
                'type' => 'Delivery',
                'provider_name' => 'Rozana Delivery',
                'rating' => 4.5,
                'state' => [
                    'descriptor' => [
                        'name' => 'Order confirmed'
                    ]
                ],
                'tracking' => true,
                'customer' => [
                    'person' => [
                        'name' => 'Customer Name'
                    ],
                    'contact' => [
                        'phone' => '+91-XXXXXXXXXX',
                        'email' => 'customer@example.com'
                    ]
                ],
                'stops' => [
                    [
                        'type' => 'end',
                        'location' => [
                            'address' => [
                                'locality' => 'Customer Address',
                                'city' => 'Delhi',
                                'state' => 'Delhi',
                                'country' => 'India'
                            ]
                        ]
                    ]
                ],
                'estimated_delivery' => [
                    'time' => date('c', strtotime('+2 days')),
                    'range' => [
                        'start' => date('c', strtotime('+1 day')),
                        'end' => date('c', strtotime('+3 days'))
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Generate payment options
     */
    private function generatePayments($orderDetails) {
        return [
            [
                'id' => 'payment_001',
                'type' => 'ON-ORDER',
                'collected_by' => 'BAP',
                'params' => [
                    'amount' => (string)$orderDetails['total_amount'],
                    'currency' => $orderDetails['currency']
                ],
                'status' => 'PENDING',
                'time' => [
                    'label' => 'Payment Due',
                    'timestamp' => date('c')
                ]
            ]
        ];
    }
    
    /**
     * Generate billing information
     */
    private function generateBilling($orderDetails) {
        return [
            'name' => 'Customer Name',
            'address' => [
                'locality' => 'Customer Address',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'country' => 'India'
            ],
            'email' => 'customer@example.com',
            'phone' => '+91-XXXXXXXXXX'
        ];
    }
    
    /**
     * Generate customer information
     */
    private function generateCustomer($orderDetails) {
        return [
            'id' => 'customer_001',
            'person' => [
                'name' => 'Customer Name'
            ],
            'contact' => [
                'phone' => '+91-XXXXXXXXXX',
                'email' => 'customer@example.com'
            ],
            'address' => [
                'locality' => 'Customer Address',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'country' => 'India'
            ]
        ];
    }
    
    /**
     * Generate quote with pricing details
     */
    private function generateQuote($orderDetails) {
        return [
            'price' => [
                'currency' => $orderDetails['currency'],
                'value' => (string)$orderDetails['total_amount']
            ],
            'breakup' => [
                [
                    'title' => 'Subtotal',
                    'price' => [
                        'currency' => $orderDetails['currency'],
                        'value' => (string)$orderDetails['total_price']
                    ]
                ],
                [
                    'title' => 'Delivery Charge',
                    'price' => [
                        'currency' => $orderDetails['currency'],
                        'value' => (string)$orderDetails['delivery_charge']
                    ]
                ],
                [
                    'title' => 'GST (18%)',
                    'price' => [
                        'currency' => $orderDetails['currency'],
                        'value' => (string)$orderDetails['tax_amount']
                    ]
                ],
                [
                    'title' => 'Total',
                    'price' => [
                        'currency' => $orderDetails['currency'],
                        'value' => (string)$orderDetails['total_amount']
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Generate error response
     */
    private function generateErrorResponse($errorMessage) {
        return [
            'context' => [
                'action' => 'on_init',
                'bap_id' => $_ENV['SUBSCRIBER_ID'] ?? 'neo-server.rozana.in',
                'bap_uri' => $_ENV['SUBSCRIBER_URL'] ?? 'https://neo-server.rozana.in/bapl',
                'message_id' => Uuid::uuid4()->toString(),
                'timestamp' => date('c'),
                'ttl' => 'PT30S'
            ],
            'message' => [
                'ack' => [
                    'status' => 'NACK'
                ],
                'error' => [
                    'code' => '500',
                    'message' => $errorMessage
                ]
            ]
        ];
    }
    
    /**
     * Log messages to file
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        echo $logMessage;
    }
}

// Handle CLI usage
if (php_sapi_name() === 'cli') {
    echo "🚀 ONDC Init Callback API\n";
    echo "==========================\n\n";
    
    $callback = new ONDCInitCallback();
    
    // Test with sample request
    $sampleRequest = [
        'context' => [
            'domain' => 'ONDC:RET10',
            'country' => 'IND',
            'city' => 'std:080',
            'action' => 'init',
            'bap_id' => 'test-bap.ondc.org',
            'bap_uri' => 'https://test-bap.ondc.org',
            'transaction_id' => Uuid::uuid4()->toString(),
            'message_id' => Uuid::uuid4()->toString(),
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
                    'name' => 'Customer Name',
                    'address' => [
                        'locality' => 'Customer Address',
                        'city' => 'Delhi',
                        'state' => 'Delhi',
                        'country' => 'India'
                    ],
                    'email' => 'customer@example.com',
                    'phone' => '+91-XXXXXXXXXX'
                ]
            ]
        ]
    ];
    
    echo "📋 Testing with sample init request...\n\n";
    $response = $callback->handleInitRequest($sampleRequest);
    
    echo "\n🎯 Test completed! Check logs/on_init_callback.log for details.\n";
    
} else {
    // Handle HTTP requests
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $requestData = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }
        
        $callback = new ONDCInitCallback();
        $response = $callback->handleInitRequest($requestData);
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}
?>
