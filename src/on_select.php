<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

use Dotenv\Dotenv;
use Ramsey\Uuid\Uuid;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeload();

class ONDCSelectCallback {
    
    private $logFile;
    
    public function __construct() {
        // Create logs directory if it doesn't exist
        $this->logFile = dirname(__DIR__) . '/logs/on_select_callback.log';
        $logsDir = dirname($this->logFile);
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
    }
    
    /**
     * Handle incoming ONDC select request
     */
    public function handleSelectRequest($requestData) {
        $this->log("🛒 Received ONDC Select Request");
        $this->log("Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT));
        
        try {
            // Validate request structure
            $this->validateSelectRequest($requestData);
            
            // Extract select parameters
            $selectParams = $this->extractSelectParameters($requestData);
            
            // Process selection and get item details
            $itemDetails = $this->processSelection($selectParams);
            
            // Generate response
            $response = $this->generateSelectResponse($requestData, $itemDetails);
            
            $this->log("✅ Selection processed successfully");
            $this->log("Response: " . json_encode($response, JSON_PRETTY_PRINT));
            
            return $response;
            
        } catch (Exception $e) {
            $this->log("❌ Error processing selection: " . $e->getMessage());
            return $this->generateErrorResponse($e->getMessage());
        }
    }
    
    /**
     * Validate incoming select request
     */
    private function validateSelectRequest($requestData) {
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
    }
    
    /**
     * Extract select parameters from request
     */
    private function extractSelectParameters($requestData) {
        $order = $requestData['message']['order'];
        
        $params = [
            'domain' => $requestData['context']['domain'] ?? 'ONDC:RET10',
            'items' => [],
            'fulfillment' => $order['fulfillment'] ?? null,
            'payment' => $order['payment'] ?? null,
            'billing' => $order['billing'] ?? null,
            'quote' => $order['quote'] ?? null
        ];
        
        // Extract item details
        foreach ($order['items'] as $item) {
            $itemInfo = [
                'id' => $item['id'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'descriptor' => $item['descriptor'] ?? null,
                'price' => $item['price'] ?? null,
                'fulfillment_id' => $item['fulfillment_id'] ?? null,
                'tags' => $item['tags'] ?? []
            ];
            
            $params['items'][] = $itemInfo;
        }
        
        return $params;
    }
    
    /**
     * Process selection and get item details
     */
    private function processSelection($selectParams) {
        $this->log("Processing selection for domain: " . $selectParams['domain']);
        $this->log("Items count: " . count($selectParams['items']));
        
        $itemDetails = [];
        
        foreach ($selectParams['items'] as $item) {
            $this->log("Processing item: " . ($item['id'] ?? 'unknown'));
            
            // Get item details from catalog/database
            $itemDetail = $this->getItemDetails($item);
            
            if ($itemDetail) {
                $itemDetails[] = $itemDetail;
            } else {
                $this->log("⚠️ Item not found: " . ($item['id'] ?? 'unknown'));
            }
        }
        
        return $itemDetails;
    }
    
    /**
     * Get item details from catalog/database
     */
    private function getItemDetails($item) {
        // This is a placeholder - replace with actual catalog lookup
        // You would typically query your database or catalog service here
        
        $itemId = $item['id'] ?? 'unknown';
        
        // Mock item details for demonstration
        $mockItems = [
            'item_001' => [
                'id' => 'item_001',
                'name' => 'Organic Rice',
                'description' => 'Premium quality organic rice',
                'price' => [
                    'currency' => 'INR',
                    'value' => '120.00'
                ],
                'quantity' => [
                    'available' => 100,
                    'unit' => 'kg'
                ],
                'fulfillment' => [
                    'id' => 'fulfillment_001',
                    'type' => 'Delivery',
                    'provider_name' => 'Local Delivery',
                    'tracking' => false
                ]
            ],
            'item_002' => [
                'id' => 'item_002',
                'name' => 'Fresh Tomatoes',
                'description' => 'Fresh red tomatoes',
                'price' => [
                    'currency' => 'INR',
                    'value' => '80.00'
                ],
                'quantity' => [
                    'available' => 50,
                    'unit' => 'kg'
                ],
                'fulfillment' => [
                    'id' => 'fulfillment_002',
                    'type' => 'Delivery',
                    'provider_name' => 'Local Delivery',
                    'tracking' => false
                ]
            ]
        ];
        
        return $mockItems[$itemId] ?? null;
    }
    
    /**
     * Generate select response
     */
    private function generateSelectResponse($requestData, $itemDetails) {
        $messageId = Uuid::uuid4()->toString();
        $timestamp = date('c');
        
        $response = [
            'context' => [
                'domain' => $requestData['context']['domain'] ?? 'ONDC:RET10',
                'country' => $requestData['context']['country'] ?? 'IND',
                'city' => $requestData['context']['city'] ?? 'std:080',
                'action' => 'on_select',
                'core_version' => $requestData['context']['core_version'] ?? '1.2.0',
                'bap_id' => $requestData['context']['bap_id'] ?? 'buyer-app.ondc.org',
                'bap_uri' => $requestData['context']['bap_uri'] ?? 'https://buyer-app.ondc.org',
                'bpp_id' => $requestData['context']['bpp_id'] ?? 'seller-app.ondc.org',
                'bpp_uri' => $requestData['context']['bpp_uri'] ?? 'https://seller-app.ondc.org',
                'transaction_id' => $requestData['context']['transaction_id'] ?? Uuid::uuid4()->toString(),
                'message_id' => $messageId,
                'timestamp' => $timestamp,
                'ttl' => 'PT30S'
            ],
            'message' => [
                'order' => [
                    'provider' => [
                        'id' => 'seller-001',
                        'name' => 'Fresh Grocery Store',
                        'descriptor' => [
                            'name' => 'Fresh Grocery Store',
                            'short_desc' => 'Your trusted source for fresh groceries'
                        ]
                    ],
                    'items' => [],
                    'fulfillment' => [
                        'id' => 'fulfillment_001',
                        'type' => 'Delivery',
                        'provider_name' => 'Local Delivery',
                        'tracking' => false,
                        'start' => [
                            'location' => [
                                'gps' => '12.9716,77.5946',
                                'address' => [
                                    'locality' => 'Koramangala',
                                    'city' => 'Bangalore',
                                    'state' => 'Karnataka',
                                    'country' => 'India'
                                ]
                            ]
                        ],
                        'end' => [
                            'location' => [
                                'gps' => '12.9716,77.5946',
                                'address' => [
                                    'locality' => 'Customer Location',
                                    'city' => 'Bangalore',
                                    'state' => 'Karnataka',
                                    'country' => 'India'
                                ]
                            ]
                        ]
                    ],
                    'payment' => [
                        'id' => 'payment_001',
                        'type' => 'ON-ORDER',
                        'collected_by' => 'BPP',
                        'params' => [
                            'amount' => '0.00',
                            'currency' => 'INR'
                        ]
                    ],
                    'billing' => [
                        'name' => 'Customer Name',
                        'address' => [
                            'locality' => 'Customer Location',
                            'city' => 'Bangalore',
                            'state' => 'Karnataka',
                            'country' => 'India'
                        ],
                        'email' => 'customer@example.com',
                        'phone' => '+91-9876543210'
                    ],
                    'quote' => [
                        'price' => [
                            'currency' => 'INR',
                            'value' => '0.00'
                        ],
                        'breakup' => []
                    ]
                ]
            ]
        ];
        
        // Add items to response
        $totalValue = 0;
        foreach ($itemDetails as $item) {
            $itemResponse = [
                'id' => $item['id'],
                'descriptor' => [
                    'name' => $item['name'],
                    'description' => $item['description']
                ],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'fulfillment_id' => $item['fulfillment']['id']
            ];
            
            $response['message']['order']['items'][] = $itemResponse;
            
            // Calculate total
            $totalValue += floatval($item['price']['value']) * floatval($item['quantity']['available']);
        }
        
        // Update quote with calculated total
        $response['message']['order']['quote']['price']['value'] = number_format($totalValue, 2);
        
        // Add price breakup
        $response['message']['order']['quote']['breakup'] = [
            [
                'title' => 'Item Total',
                'price' => [
                    'currency' => 'INR',
                    'value' => number_format($totalValue, 2)
                ]
            ]
        ];
        
        return $response;
    }
    
    /**
     * Generate error response
     */
    private function generateErrorResponse($errorMessage) {
        $messageId = Uuid::uuid4()->toString();
        $timestamp = date('c');
        
        return [
            'context' => [
                'domain' => 'ONDC:RET10',
                'country' => 'IND',
                'city' => 'std:080',
                'action' => 'on_select',
                'core_version' => '1.2.0',
                'bap_id' => 'buyer-app.ondc.org',
                'bap_uri' => 'https://buyer-app.ondc.org',
                'bpp_id' => 'seller-app.ondc.org',
                'bpp_uri' => 'https://seller-app.ondc.org',
                'transaction_id' => Uuid::uuid4()->toString(),
                'message_id' => $messageId,
                'timestamp' => $timestamp,
                'ttl' => 'PT30S'
            ],
            'error' => [
                'code' => '400',
                'message' => $errorMessage,
                'path' => 'on_select',
                'type' => 'ONDC_ERROR'
            ]
        ];
    }
    
    /**
     * Log message to file
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message" . PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also output to console for debugging
        echo $logEntry;
    }
}

// Handle incoming request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);
    
    if ($requestData === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    
    $selectCallback = new ONDCSelectCallback();
    $response = $selectCallback->handleSelectRequest($requestData);
    
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} else {
    // Handle GET request for testing
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST for select requests.']);
}
