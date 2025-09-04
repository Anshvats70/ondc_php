<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

use Dotenv\Dotenv;
use Ramsey\Uuid\Uuid;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeload();

class ONDCSearchCallback {
    
    private $logFile;
    private $baseUrl;
    
    public function __construct() {
        // Create logs directory if it doesn't exist
        $this->logFile = dirname(__DIR__) . '/logs/on_search.log';
        $logsDir = dirname($this->logFile);
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
        
        // Load base URL from environment
        $this->baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost';
    }
    
    /**
     * Handle incoming ONDC search request
     */
    public function handleSearchRequest($requestData) {
        $this->log("🔍 Received ONDC Search Request");
        $this->log("Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT));
        
        try {
            // Validate request structure
            $this->validateSearchRequest($requestData);
            
            // Extract search parameters
            $searchParams = $this->extractSearchParameters($requestData);
            
            // Process search based on type
            $searchResults = $this->processSearch($searchParams);
            
            // Generate response
            $response = $this->generateSearchResponse($requestData, $searchResults);
            
            $this->log("✅ Search processed successfully");
            $this->log("Response: " . json_encode($response, JSON_PRETTY_PRINT));
            
            return $response;
            
        } catch (Exception $e) {
            $this->log("❌ Error processing search: " . $e->getMessage());
            return $this->generateErrorResponse($e->getMessage());
        }
    }
    
    /**
     * Validate incoming search request
     */
    private function validateSearchRequest($requestData) {
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
        if (!isset($requestData['message']['intent'])) {
            throw new Exception("Missing intent in message");
        }
    }
    
    /**
     * Extract search parameters from request
     */
    private function extractSearchParameters($requestData) {
        $intent = $requestData['message']['intent'];
        
        $params = [
            'domain' => $requestData['context']['domain'] ?? 'ONDC:RET10',
            'search_type' => $intent['item']['descriptor']['name'] ?? 'general',
            'category' => $intent['item']['category_id'] ?? null,
            'fulfillment' => $intent['fulfillment'] ?? null,
            'location' => $intent['fulfillment']['locations'][0] ?? null,
            'city' => null,
            'state' => null,
            'country' => null
        ];
        
        // Extract location details
        if ($params['location']) {
            $params['city'] = $params['location']['city']['name'] ?? null;
            $params['state'] = $params['location']['state']['name'] ?? null;
            $params['country'] = $params['location']['country']['name'] ?? null;
        }
        
        $this->log("Extracted Search Parameters: " . json_encode($params, JSON_PRETTY_PRINT));
        
        return $params;
    }
    
    /**
     * Process search based on parameters
     */
    private function processSearch($searchParams) {
        $this->log("Processing search for: " . $searchParams['search_type']);
        
        // Mock catalog data - replace with your actual catalog
        $catalog = $this->getMockCatalog($searchParams);
        
        // Filter based on search parameters
        $filteredItems = $this->filterCatalogItems($catalog, $searchParams);
        
        return [
            'total_items' => count($filteredItems),
            'items' => $filteredItems,
            'search_params' => $searchParams
        ];
    }
    
    /**
     * Get mock catalog data
     */
    private function getMockCatalog($searchParams) {
        // This should be replaced with your actual catalog data
        $catalog = [
            [
                'id' => 'item_001',
                'name' => 'Organic Basmati Rice',
                'category' => 'Foodgrains',
                'price' => 120.00,
                'currency' => 'INR',
                'unit' => 'kg',
                'description' => 'Premium quality organic basmati rice',
                'brand' => 'Organic Valley',
                'images' => ['https://example.com/rice1.jpg'],
                'fulfillment' => [
                    'type' => 'Delivery',
                    'locations' => ['Delhi', 'Mumbai', 'Bangalore']
                ]
            ],
            [
                'id' => 'item_002',
                'name' => 'Fresh Apples',
                'category' => 'Fruits',
                'price' => 180.00,
                'currency' => 'INR',
                'unit' => 'kg',
                'description' => 'Fresh red apples from Kashmir',
                'brand' => 'Kashmir Fresh',
                'images' => ['https://example.com/apples1.jpg'],
                'fulfillment' => [
                    'type' => 'Delivery',
                    'locations' => ['Delhi', 'Punjab', 'Haryana']
                ]
            ],
            [
                'id' => 'item_003',
                'name' => 'Dairy Milk Chocolate',
                'category' => 'Snacks',
                'price' => 50.00,
                'currency' => 'INR',
                'unit' => 'pack',
                'description' => 'Classic dairy milk chocolate bar',
                'brand' => 'Cadbury',
                'images' => ['https://example.com/chocolate1.jpg'],
                'fulfillment' => [
                    'type' => 'Delivery',
                    'locations' => ['Delhi', 'Mumbai', 'Chennai']
                ]
            ]
        ];
        
        return $catalog;
    }
    
    /**
     * Filter catalog items based on search parameters
     */
    private function filterCatalogItems($catalog, $searchParams) {
        $filtered = $catalog;
        
        // Filter by category if specified
        if ($searchParams['category']) {
            $filtered = array_filter($filtered, function($item) use ($searchParams) {
                return stripos($item['category'], $searchParams['category']) !== false;
            });
        }
        
        // Filter by location if specified
        if ($searchParams['city']) {
            $filtered = array_filter($filtered, function($item) use ($searchParams) {
                return in_array($searchParams['city'], $item['fulfillment']['locations']);
            });
        }
        
        return array_values($filtered);
    }
    
    /**
     * Generate ONDC compliant search response
     */
    private function generateSearchResponse($requestData, $searchResults) {
        $messageId = Uuid::uuid4()->toString();
        $timestamp = date('c');
        
        $response = [
            'context' => [
                'domain' => $requestData['context']['domain'],
                'country' => $requestData['context']['country'] ?? 'IND',
                'city' => $requestData['context']['city'] ?? 'std:080',
                'action' => 'on_search',
                'core_version' => '1.2.0',
                'bap_id' => $_ENV['SUBSCRIBER_ID'] ?? 'neo-server.rozana.in',
                'bap_uri' => $_ENV['SUBSCRIBER_URL'] ?? $this->baseUrl . '/bapl',
                'transaction_id' => $requestData['context']['transaction_id'] ?? Uuid::uuid4()->toString(),
                'message_id' => $messageId,
                'timestamp' => $timestamp,
                'ttl' => 'PT30S'
            ],
            'message' => [
                'ack' => [
                    'status' => 'ACK'
                ],
                'catalog' => [
                    'bpp/descriptor' => [
                        'name' => 'Rozana Catalog',
                        'short_desc' => 'Fresh groceries and household items',
                        'long_desc' => 'Wide selection of fresh groceries, dairy, fruits, vegetables and household essentials'
                    ],
                    'bpp/providers' => [
                        [
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
                            ],
                            'items' => $this->formatCatalogItems($searchResults['items'])
                        ]
                    ]
                ]
            ]
        ];
        
        return $response;
    }
    
    /**
     * Format catalog items for ONDC response
     */
    private function formatCatalogItems($items) {
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
                'location_id' => 'location_001'
            ];
        }
        
        return $formattedItems;
    }
    
    /**
     * Generate error response
     */
    private function generateErrorResponse($errorMessage) {
        return [
            'context' => [
                'action' => 'on_search',
                'bap_id' => $_ENV['SUBSCRIBER_ID'] ?? 'neo-server.rozana.in',
                'bap_uri' => $_ENV['SUBSCRIBER_URL'] ?? $this->baseUrl . '/bapl',
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
    echo "🚀 ONDC Search Callback API\n";
    echo "============================\n\n";
    
    $callback = new ONDCSearchCallback();
    
    // Test with sample request
    $sampleRequest = [
        'context' => [
            'domain' => 'ONDC:RET10',
            'country' => 'IND',
            'city' => 'std:080',
            'action' => 'search',
            'bap_id' => 'test-bap.ondc.org',
            'bap_uri' => $this->baseUrl . '/test-bap',
            'transaction_id' => Uuid::uuid4()->toString(),
            'message_id' => Uuid::uuid4()->toString(),
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
    
    echo "📋 Testing with sample search request...\n\n";
    $response = $callback->handleSearchRequest($sampleRequest);
    
    echo "\n🎯 Test completed! Check logs/on_search.log for details.\n";
    
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
        
        $callback = new ONDCSearchCallback();
        $response = $callback->handleSearchRequest($requestData);
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
}
?>
