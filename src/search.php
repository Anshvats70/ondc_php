<?php

require_once dirname(__DIR__) . "/vendor/autoload.php";

use Dotenv\Dotenv;
use Ramsey\Uuid\Uuid;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeload();

class ONDCSearch {
    
    private $logFile;
    private $subscriberId;
    private $uniqueKeyId;
    private $signingPrivKey;
    private $signingPubKey;
    private $baseUrl;
    
    public function __construct() {
        // Create logs directory if it doesn't exist
        $this->logFile = dirname(__DIR__) . '/logs/search.log';
        $logsDir = dirname($this->logFile);
        if (!is_dir($logsDir)) {
            mkdir($logsDir, 0755, true);
        }
        
        // Load environment variables with fallbacks
        $this->subscriberId = $_ENV['SUBSCRIBER_ID'] ?? 'neo-server.rozana.in';
        $this->uniqueKeyId = $_ENV['UNIQUE_KEY_ID'] ?? '3bd6f47a-d2ea-4210-a4ad-2a99dd66585b';
        $this->signingPrivKey = $_ENV['SIGNING_PRIVATE_KEY'] ?? '';
        $this->signingPubKey = $_ENV['SIGNING_PUB_KEY'] ?? '';
        $this->baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost';
        
        // Log initialization
        $this->log("🔧 ONDCSearch initialized");
        $this->log("Subscriber ID: " . $this->subscriberId);
        $this->log("Unique Key ID: " . $this->uniqueKeyId);
        $this->log("Signing Private Key: " . (!empty($this->signingPrivKey) ? 'Set' : 'Not set'));
        $this->log("Signing Public Key: " . (!empty($this->signingPubKey) ? 'Set' : 'Not set'));
        $this->log("Base URL: " . $this->baseUrl);
    }
    
    /**
     * Handle incoming search request
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
            
            // Generate search response
            $searchResponse = $this->generateSearchResponse($requestData, $searchResults);
            
            // Trigger on_search callback to buyer
            $this->triggerOnSearchCallback($requestData, $searchResults);
            
            $this->log("✅ Search processed successfully");
            $this->log("Search Response: " . json_encode($searchResponse, JSON_PRETTY_PRINT));
            
            return $searchResponse;
            
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
        $context = $requestData['context'];
        $requiredContextFields = ['domain', 'action', 'country', 'city', 'core_version', 'bap_id', 'bap_uri', 'transaction_id', 'message_id', 'timestamp', 'ttl'];
        
        foreach ($requiredContextFields as $field) {
            if (!isset($context[$field])) {
                throw new Exception("Missing required context field: $field");
            }
        }
        
        // Validate message
        if (!isset($requestData['message']['intent'])) {
            throw new Exception("Missing intent in message");
        }
        
        $intent = $requestData['message']['intent'];
        if (!isset($intent['category']) || !isset($intent['fulfillment'])) {
            throw new Exception("Missing required intent fields: category, fulfillment");
        }
    }
    
    /**
     * Extract search parameters from request
     */
    private function extractSearchParameters($requestData) {
        $context = $requestData['context'];
        $intent = $requestData['message']['intent'];
        
        $params = [
            'domain' => $context['domain'] ?? 'ONDC:RET10',
            'action' => $context['action'] ?? 'search',
            'country' => $context['country'] ?? 'IND',
            'city' => $context['city'] ?? 'std:080',
            'core_version' => $context['core_version'] ?? '1.2.0',
            'bap_id' => $context['bap_id'] ?? '',
            'bap_uri' => $context['bap_uri'] ?? '',
            'transaction_id' => $context['transaction_id'] ?? '',
            'message_id' => $context['message_id'] ?? '',
            'timestamp' => $context['timestamp'] ?? '',
            'ttl' => $context['ttl'] ?? 'PT30S',
            'category' => $intent['category'] ?? null,
            'fulfillment' => $intent['fulfillment'] ?? null,
            'payment' => $intent['payment'] ?? null,
            'tags' => $intent['tags'] ?? []
        ];
        
        return $params;
    }
    
    /**
     * Process search based on parameters
     */
    private function processSearch($searchParams) {
        $this->log("Processing search for domain: " . $searchParams['domain']);
        $this->log("Category: " . json_encode($searchParams['category']));
        $this->log("City: " . $searchParams['city']);
        
        // Determine search type based on domain
        $searchType = $this->determineSearchType($searchParams['domain']);
        
        // Generate mock catalog data based on search parameters
        $catalogData = $this->generateCatalogData($searchParams, $searchType);
        
        return [
            'search_type' => $searchType,
            'catalog_data' => $catalogData,
            'total_items' => count($catalogData),
            'search_params' => $searchParams
        ];
    }
    
    /**
     * Determine search type based on domain
     */
    private function determineSearchType($domain) {
        switch ($domain) {
            case 'ONDC:RET10': // Grocery
                return 'distributed_by_category';
            case 'ONDC:RET11': // F&B
                return 'by_city_only';
            case 'ONDC:RET12': // Personal Care
                return 'distributed_by_category';
            case 'ONDC:RET13': // Home Care
                return 'distributed_by_category';
            default:
                return 'general';
        }
    }
    
    /**
     * Generate mock catalog data
     */
    private function generateCatalogData($searchParams, $searchType) {
        $categoryId = $searchParams['category']['id'] ?? 'general';
        $city = $searchParams['city'];
        
        // Mock catalog items based on category and city
        $catalogItems = [];
        
        switch ($categoryId) {
            case 'Foodgrains':
                $catalogItems = [
                    [
                        'id' => 'item_001',
                        'descriptor' => [
                            'name' => 'Premium Basmati Rice',
                            'code' => 'RICE_BASMATI_001'
                        ],
                        'price' => [
                            'currency' => 'INR',
                            'value' => '120.00'
                        ],
                        'category_id' => 'Foodgrains',
                        'fulfillment_id' => 'f1'
                    ],
                    [
                        'id' => 'item_002',
                        'descriptor' => [
                            'name' => 'Whole Wheat Flour',
                            'code' => 'WHEAT_FLOUR_001'
                        ],
                        'price' => [
                            'currency' => 'INR',
                            'value' => '45.00'
                        ],
                        'category_id' => 'Foodgrains',
                        'fulfillment_id' => 'f1'
                    ]
                ];
                break;
                
            case 'Fruits and Vegetables':
                $catalogItems = [
                    [
                        'id' => 'item_003',
                        'descriptor' => [
                            'name' => 'Fresh Apples',
                            'code' => 'APPLE_FRESH_001'
                        ],
                        'price' => [
                            'currency' => 'INR',
                            'value' => '180.00'
                        ],
                        'category_id' => 'Fruits and Vegetables',
                        'fulfillment_id' => 'f1'
                    ]
                ];
                break;
                
            default:
                $catalogItems = [
                    [
                        'id' => 'item_default',
                        'descriptor' => [
                            'name' => 'General Item',
                            'code' => 'GENERAL_001'
                        ],
                        'price' => [
                            'currency' => 'INR',
                            'value' => '100.00'
                        ],
                        'category_id' => $categoryId,
                        'fulfillment_id' => 'f1'
                    ]
                ];
        }
        
        return $catalogItems;
    }
    
    /**
     * Generate search response
     */
    private function generateSearchResponse($requestData, $searchResults) {
        $context = $requestData['context'];
        
        $response = [
            'context' => [
                'domain' => $context['domain'],
                'country' => $context['country'],
                'city' => $context['city'],
                'action' => 'search',
                'core_version' => $context['core_version'],
                'bap_id' => $context['bap_id'],
                'bap_uri' => $context['bap_uri'],
                'transaction_id' => $context['transaction_id'],
                'message_id' => Uuid::uuid4()->toString(),
                'timestamp' => date('c'),
                'ttl' => $context['ttl']
            ],
            'message' => [
                'catalog' => [
                    'bpp/fulfillments' => [
                        [
                            'id' => 'f1',
                            'type' => 'Delivery',
                            'provider_name' => 'Seller NP',
                            'rating' => '4.5',
                            'state' => [
                                'descriptor' => [
                                    'name' => 'Active'
                                ]
                            ]
                        ]
                    ],
                    'bpp/descriptor' => [
                        'name' => 'Seller Network Participant',
                        'code' => 'SELLER_NP_001'
                    ],
                    'bpp/providers' => [
                        [
                            'id' => 'seller_001',
                            'descriptor' => [
                                'name' => 'Premium Seller',
                                'code' => 'SELLER_PREMIUM_001'
                            ],
                            'items' => $searchResults['catalog_data'],
                            'fulfillments' => [
                                [
                                    'id' => 'f1',
                                    'type' => 'Delivery'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        return $response;
    }
    
    /**
     * Generate error response
     */
    private function generateErrorResponse($errorMessage) {
        return [
            'error' => [
                'code' => 'SEARCH_ERROR',
                'message' => $errorMessage,
                'timestamp' => date('c')
            ]
        ];
    }
    
    /**
     * Create authorization header for outgoing requests
     */
    public function createAuthorizationHeader($requestBody, $created = null, $expires = null) {
        $now = new DateTime();
        $oneHour = new DateInterval("PT1H");
        
        if ($created === null) {
            $created = $now->getTimestamp();
        }
        
        if ($expires === null) {
            $expires = $now->add($oneHour)->getTimestamp();
        }
        
        // If we don't have a private key, return a mock header for testing
        if (empty($this->signingPrivKey)) {
            $this->log("⚠️ No private key available, using mock signature for testing");
            $mockSignature = base64_encode(random_bytes(64)); // Generate random signature for testing
            $header = "Signature keyId=\"{$this->subscriberId}|{$this->uniqueKeyId}|ed25519\",algorithm=\"ed25519\",created=\"$created\",expires=\"$expires\",headers=\"(created) (expires) digest\",signature=\"$mockSignature\"";
            return $header;
        }
        
        $signingKey = $this->createSigningString($this->hashMessage($requestBody), $created, $expires);
        $signature = $this->signResponse($signingKey, $this->signingPrivKey);
        
        $header = "Signature keyId=\"{$this->subscriberId}|{$this->uniqueKeyId}|ed25519\",algorithm=\"ed25519\",created=\"$created\",expires=\"$expires\",headers=\"(created) (expires) digest\",signature=\"$signature\"";
        return $header;
    }
    
    /**
     * Hash message using BLAKE-512
     */
    private function hashMessage($message) {
        return base64_encode(sodium_crypto_generichash($message, "", 64));
    }
    
    /**
     * Create signing string
     */
    private function createSigningString($digestBase64, $created, $expires) {
        return "(created): $created\n(expires): $expires\ndigest: BLAKE-512=$digestBase64";
    }
    
    /**
     * Sign response using Ed25519
     */
    private function signResponse($signingKey, $privateKey) {
        return base64_encode(sodium_crypto_sign_detached($signingKey, base64_decode($privateKey)));
    }
    
    /**
     * Trigger on_search callback to buyer
     */
    private function triggerOnSearchCallback($requestData, $searchResults) {
        try {
            $context = $requestData['context'];
            $bapUri = $context['bap_uri'] ?? '';
            
            if (empty($bapUri)) {
                $this->log("⚠️ No BAP URI provided, skipping on_search callback");
                return;
            }
            
            // Extract the base URL from BAP URI
            $bapBaseUrl = $this->extractBaseUrl($bapUri);
            $onSearchUrl = $bapBaseUrl . 'on_search';
            
            $this->log("🚀 Triggering on_search callback to: " . $onSearchUrl);
            
            // Generate on_search response
            $onSearchResponse = $this->generateOnSearchResponse($requestData, $searchResults);
            
            // Send callback to buyer
            $this->sendCallback($onSearchUrl, $onSearchResponse);
            
        } catch (Exception $e) {
            $this->log("❌ Error triggering on_search callback: " . $e->getMessage());
        }
    }
    
    /**
     * Extract base URL from BAP URI
     */
    private function extractBaseUrl($bapUri) {
        // For local testing, if BAP URI contains /ondc/src, use that as base
        if (strpos($bapUri, '/ondc/src') !== false) {
            return $bapUri . '/';
        }
        
        // Remove /ondc or any path suffix
        if (strpos($bapUri, '/ondc') !== false) {
            $bapUri = str_replace('/ondc', '', $bapUri);
        }
        
        // Ensure it ends with /
        if (!str_ends_with($bapUri, '/')) {
            $bapUri .= '/';
        }
        
        return $bapUri;
    }
    
    /**
     * Generate on_search response
     */
    private function generateOnSearchResponse($requestData, $searchResults) {
        $context = $requestData['context'];
        
        $response = [
            'context' => [
                'domain' => $context['domain'],
                'country' => $context['country'],
                'city' => $context['city'],
                'action' => 'on_search',
                'core_version' => $context['core_version'],
                'bap_id' => $context['bap_id'],
                'bap_uri' => $context['bap_uri'],
                'bpp_id' => $this->subscriberId,
                'bpp_uri' => $_ENV['BPP_URI'] ?? $this->baseUrl . '/ondc',
                'transaction_id' => $context['transaction_id'],
                'message_id' => Uuid::uuid4()->toString(),
                'timestamp' => date('c'),
                'ttl' => $context['ttl']
            ],
            'message' => [
                'ack' => [
                    'status' => 'ACK'
                ],
                'catalog' => [
                    'bpp/fulfillments' => [
                        [
                            'id' => 'f1',
                            'type' => 'Delivery',
                            'provider_name' => 'Seller NP',
                            'rating' => '4.5',
                            'state' => [
                                'descriptor' => [
                                    'name' => 'Active'
                                ]
                            ]
                        ]
                    ],
                    'bpp/descriptor' => [
                        'name' => 'Seller Network Participant',
                        'code' => 'SELLER_NP_001'
                    ],
                    'bpp/providers' => [
                        [
                            'id' => 'seller_001',
                            'descriptor' => [
                                'name' => 'Premium Seller',
                                'code' => 'SELLER_PREMIUM_001'
                            ],
                            'items' => $searchResults['catalog_data'],
                            'fulfillments' => [
                                [
                                    'id' => 'f1',
                                    'type' => 'Delivery'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        
        return $response;
    }
    
    /**
     * Send callback to buyer
     */
    private function sendCallback($url, $data) {
        try {
            // Create authorization header for the callback
            $authHeader = $this->createAuthorizationHeader(json_encode($data));
            
            $this->log("📤 Sending callback to: " . $url);
            $this->log("📋 Callback data: " . json_encode($data, JSON_PRETTY_PRINT));
            $this->log("🔑 Auth header: " . $authHeader);
            
            // Use cURL to send the callback
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: ' . $authHeader,
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception("cURL Error: " . $error);
            }
            
            $this->log("✅ Callback sent successfully. HTTP Code: " . $httpCode);
            $this->log("📥 Response: " . $response);
            
        } catch (Exception $e) {
            $this->log("❌ Error sending callback: " . $e->getMessage());
        }
    }
    
    /**
     * Log messages to file
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

// Handle HTTP requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON input
        $input = file_get_contents('php://input');
        $requestData = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON input']);
            exit;
        }
        
        // Create search instance and process request
        $search = new ONDCSearch();
        $response = $search->handleSearchRequest($requestData);
        
        // Set response headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Return response
        echo json_encode($response, JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Handle preflight request
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
