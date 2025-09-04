<?php

require_once "vendor/autoload.php";

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->safeload();

class ONDCLookupAPI {
    
    private $baseUrl;
    private $subscriberId;
    private $ukId;
    private $country;
    private $city;
    private $domain;
    private $type;
    
    public function __construct() {
        $this->baseUrl = $_ENV['ONDC_LOOKUP_URL'] ?? 'https://preprod.registry.ondc.org';
        $this->subscriberId = $_ENV['SUBSCRIBER_ID'] ?? 'neo-server.rozana.in';
        $this->ukId = $_ENV['UNIQUE_KEY_ID'] ?? '3bd6f47a-d2ea-4210-a4ad-2a99dd66585b';
        $this->country = $_ENV['COUNTRY'] ?? 'IND';
        $this->city = $_ENV['CITY'] ?? 'std:080';
        $this->domain = $_ENV['DOMAIN'] ?? 'ONDC:RET10';
        $this->type = $_ENV['TYPE'] ?? 'BAP';
    }
    
    /**
     * Generate ONDC lookup request payload (exact as specified)
     */
    public function generateExactLookupPayload() {
        $payload = [
            "subscriber_id" => "neo-server.rozana.in",
            "domain" => "ONDC:RET10",
            "ukId" => "3bd6f47a-d2ea-4210-a4ad-2a99dd66585b",
            "country" => "IND",
            "city" => "std:080",
            "type" => "BAP"
        ];
        
        return $payload;
    }
    
    /**
     * Generate ONDC lookup request payload with custom data
     */
    public function generateLookupPayload($customData = []) {
        $payload = [
            "subscriber_id" => $customData['subscriber_id'] ?? $this->subscriberId,
            "domain" => $customData['domain'] ?? $this->domain,
            "ukId" => $customData['ukId'] ?? $this->ukId,
            "country" => $customData['country'] ?? $this->country,
            "city" => $customData['city'] ?? $this->city,
            "type" => $customData['type'] ?? $this->type
        ];
        
        return $payload;
    }
    
    /**
     * Generate authorization header for ONDC requests
     */
    public function generateAuthHeader($requestBody) {
        $now = new DateTime();
        $created = $now->getTimestamp();
        $expires = $now->add(new DateInterval("PT1H"))->getTimestamp();
        
        // Create signing string
        $digest = base64_encode(sodium_crypto_generichash($requestBody, "", 64));
        $signingString = "(created): $created\n(expires): $expires\ndigest: BLAKE-512=$digest";
        
        // Sign the string
        $signature = base64_encode(sodium_crypto_sign_detached(
            $signingString, 
            base64_decode($_ENV['SIGNING_PRIVATE_KEY'])
        ));
        
        $subscriberId = $_ENV['SUBSCRIBER_ID'];
        $uniqueKeyId = $_ENV['UNIQUE_KEY_ID'];
        
        $header = "Signature keyId=\"$subscriberId|$uniqueKeyId|ed25519\"," .
                 "algorithm=\"ed25519\"," .
                 "created=\"$created\"," .
                 "expires=\"$expires\"," .
                 "headers=\"(created) (expires) digest\"," .
                 "signature=\"$signature\"";
        
        return $header;
    }
    
    /**
     * Generate exact lookup authorization header (matching Python code)
     */
    public function generateExactLookupAuth() {
        echo "🔐 Generating Exact Lookup Authorization Header\n";
        echo str_repeat("=", 60) . "\n\n";
        
        // Exact payload from user's specification
        $exactPayload = $this->generateExactLookupPayload();
        
        // Convert to JSON string (exactly as it will be sent)
        $jsonPayload = json_encode($exactPayload, JSON_UNESCAPED_SLASHES);
        
        echo "📋 Exact Payload:\n";
        echo json_encode($exactPayload, JSON_PRETTY_PRINT) . "\n\n";
        
        echo "📝 JSON String (for hashing):\n";
        echo "   $jsonPayload\n\n";
        
        // Generate fresh authorization header for this exact payload
        echo "🔑 Generating fresh authorization header...\n";
        $authHeader = $this->generateAuthHeader($jsonPayload);
        
        echo "✅ Fresh Authorization Header:\n";
        echo "   $authHeader\n\n";
        
        // Extract timestamps for verification
        preg_match('/created="(\d+)"/', $authHeader, $createdMatch);
        preg_match('/expires="(\d+)"/', $authHeader, $expiresMatch);
        
        if ($createdMatch && $expiresMatch) {
            $created = (int)$createdMatch[1];
            $expires = (int)$expiresMatch[1];
            
            $createdDt = date('Y-m-d H:i:s', $created);
            $expiresDt = date('Y-m-d H:i:s', $expires);
            
            echo "⏰ Timestamp Analysis:\n";
            echo "   Created: $createdDt (timestamp: $created)\n";
            echo "   Expires: $expiresDt (timestamp: $expires)\n";
            echo "   Valid for: " . ($expires - $created) . " seconds\n\n";
        }
        
        // Generate exact curl command
        echo "📝 Exact Working Curl Command:\n";
        echo "curl --location 'https://preprod.registry.ondc.org/v2.0/lookup' \\\n";
        echo "  --header 'Content-Type: application/json' \\\n";
        echo "  --header 'Accept: application/json' \\\n";
        echo "  --header 'Authorization: $authHeader' \\\n";
        echo "  --data '$jsonPayload'\n\n";
        
        // Save to file
        $output = "Exact Authorization Header:\n";
        $output .= "$authHeader\n\n";
        $output .= "Exact Payload:\n";
        $output .= json_encode($exactPayload, JSON_PRETTY_PRINT) . "\n\n";
        $output .= "Exact Curl Command:\n";
        $output .= "curl --location 'https://preprod.registry.ondc.org/v2.0/lookup' \\\n";
        $output .= "  --header 'Content-Type: application/json' \\\n";
        $output .= "  --header 'Accept: application/json' \\\n";
        $output .= "  --header 'Authorization: $authHeader' \\\n";
        $output .= "  --data '$jsonPayload'\n";
        
        file_put_contents('exact_lookup_auth.txt', $output);
        
        echo "💾 Exact auth header saved to 'exact_lookup_auth.txt'\n\n";
        
        return ['auth_header' => $authHeader, 'json_payload' => $jsonPayload];
    }
    
    /**
     * Perform ONDC lookup request
     */
    public function performLookup($customData = []) {
        try {
            $payload = $this->generateLookupPayload($customData);
            $requestBody = json_encode($payload);
            
            // Generate authorization header
            $authHeader = $this->generateAuthHeader($requestBody);
            
            // Prepare cURL request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->baseUrl . '/v2.0/lookup',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $requestBody,
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
            
            return [
                'success' => $httpCode >= 200 && $httpCode < 300,
                'http_code' => $httpCode,
                'response' => json_decode($response, true),
                'raw_response' => $response,
                'request_payload' => $payload,
                'auth_header' => $authHeader
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'request_payload' => $payload ?? null
            ];
        }
    }
    
    /**
     * Validate lookup response
     */
    public function validateLookupResponse($response) {
        if (!$response['success']) {
            return false;
        }
        
        $data = $response['response'];
        
        // Basic validation
        if (!isset($data['subscriber_id']) || !isset($data['ukId'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get lookup statistics
     */
    public function getLookupStats() {
        return [
            'base_url' => $this->baseUrl,
            'subscriber_id' => $this->subscriberId,
            'uk_id' => $this->ukId,
            'country' => $this->country,
            'city' => $this->city,
            'domain' => $this->domain,
            'type' => $this->type
        ];
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $lookup = new ONDCLookupAPI();
    
    if (isset($argv[1]) && $argv[1] === '--exact') {
        // Generate exact lookup auth header (matching Python code)
        $lookup->generateExactLookupAuth();
        
    } elseif (isset($argv[1]) && $argv[1] === '--test') {
        echo "Testing ONDC Lookup API...\n";
        echo "========================\n\n";
        
        // Test with default payload
        echo "Default Payload:\n";
        $defaultPayload = $lookup->generateLookupPayload();
        echo json_encode($defaultPayload, JSON_PRETTY_PRINT) . "\n\n";
        
        // Test lookup request
        echo "Performing lookup request...\n";
        $result = $lookup->performLookup();
        
        if ($result['success']) {
            echo "✅ Lookup successful!\n";
            echo "HTTP Code: " . $result['http_code'] . "\n";
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ Lookup failed!\n";
            if (isset($result['error'])) {
                echo "Error: " . $result['error'] . "\n";
            }
            if (isset($result['http_code'])) {
                echo "HTTP Code: " . $result['http_code'] . "\n";
            }
        }
        
        echo "\nAuth Header:\n" . $result['auth_header'] . "\n";
        
    } elseif (isset($argv[1]) && $argv[1] === '--payload') {
        // Show exact payload
        $payload = $lookup->generateExactLookupPayload();
        echo json_encode($payload, JSON_PRETTY_PRINT) . "\n";
        
    } elseif (isset($argv[1]) && $argv[1] === '--stats') {
        // Show configuration
        $stats = $lookup->getLookupStats();
        echo json_encode($stats, JSON_PRETTY_PRINT) . "\n";
        
    } else {
        echo "ONDC Lookup API\n";
        echo "Usage:\n";
        echo "  php lookup.php --exact   # Generate exact lookup auth header\n";
        echo "  php lookup.php --test    # Test the lookup API\n";
        echo "  php lookup.php --payload # Show exact payload\n";
        echo "  php lookup.php --stats   # Show configuration\n";
        echo "\n";
    }
}
?>
