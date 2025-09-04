# ONDC Search Flow Implementation

This document explains how the ONDC search flow is implemented in this project, covering the complete journey from `/search` request to `/on_search` callback.

## 🎯 Overview

The ONDC (Open Network for Digital Commerce) search flow follows this pattern:

1. **Buyer sends search request** → `/search` endpoint
2. **Seller processes search** → Generates catalog data
3. **Seller sends callback** → `/on_search` endpoint to buyer
4. **Buyer receives catalog** → Processes the response

## 🔄 Complete Flow Diagram

```
┌─────────────┐    Search Request    ┌─────────────┐
│   Buyer     │ ──────────────────→ │   Seller    │
│   (BAP)     │                     │   (BPP)     │
└─────────────┘                     └─────────────┘
       │                                   │
       │                                   │ Process Search
       │                                   │ Generate Catalog
       │                                   │
       │                                   ▼
       │                            ┌─────────────┐
       │                            │  Catalog    │
       │                            │  Data       │
       │                            └─────────────┘
       │                                   │
       │                                   │
       │                                   ▼
       │                            ┌─────────────┐
       │                            │ Send Callback│
       │                            │ to Buyer    │
       │                            └─────────────┘
       │                                   │
       │                                   │
       │                                   ▼
       │                            ┌─────────────┐
       │                            │ HTTP POST   │
       │                            │ /on_search  │
       │                            └─────────────┘
       │                                   │
       │                                   │
       │                                   ▼
       │                            ┌─────────────┐
       │                            │ Buyer's     │
       │                            │ /on_search  │
       │                            │ Endpoint    │
       │                            └─────────────┘
       │                                   │
       │                                   │
       │                                   ▼
       │                            ┌─────────────┐
       │                            │ Process     │
       │                            │ Catalog     │
       │                            │ Response    │
       │                            └─────────────┘
       │                                   │
       │                                   │
       ▼                                   ▼
┌─────────────┐                     ┌─────────────┐
│   Buyer     │                     │   Seller    │
│ Receives    │                     │   Logs      │
│ Catalog     │                     │   Success   │
└─────────────┘                     └─────────────┘
```

## 📁 Files and Components

### 1. **Search Endpoint** (`src/search.php`)
- **Purpose**: Receives ONDC search requests from buyers
- **Functionality**: 
  - Validates incoming requests
  - Processes search parameters
  - Generates catalog data
  - **Triggers on_search callback**
  - Returns immediate response

### 2. **Callback Trigger** (Built into `search.php`)
- **Purpose**: Automatically sends catalog data to buyer
- **Functionality**:
  - Extracts buyer's BAP URI from request
  - Generates ONDC-compliant on_search response
  - Creates authorization header with Ed25519 signature
  - Sends HTTP POST to buyer's `/on_search` endpoint

### 3. **Test Callback Endpoint** (`test_on_search_endpoint.php`)
- **Purpose**: Simulates buyer's `/on_search` endpoint for testing
- **Functionality**:
  - Receives callbacks from seller
  - Validates ONDC message structure
  - Logs received data
  - Returns validation results

### 4. **Flow Test Script** (`test_ondc_flow.php`)
- **Purpose**: Demonstrates complete ONDC flow
- **Functionality**:
  - Sends search request
  - Verifies search response
  - Checks callback logs
  - Tests callback endpoint

## 🔧 Technical Implementation

### Search Request Processing

```php
public function handleSearchRequest($requestData) {
    // 1. Validate request structure
    $this->validateSearchRequest($requestData);
    
    // 2. Extract search parameters
    $searchParams = $this->extractSearchParameters($requestData);
    
    // 3. Process search based on type
    $searchResults = $this->processSearch($searchParams);
    
    // 4. Generate search response
    $searchResponse = $this->generateSearchResponse($requestData, $searchResults);
    
    // 5. 🔥 TRIGGER ON_SEARCH CALLBACK
    $this->triggerOnSearchCallback($requestData, $searchResults);
    
    return $searchResponse;
}
```

### Callback Triggering

```php
private function triggerOnSearchCallback($requestData, $searchResults) {
    // 1. Extract buyer's BAP URI
    $bapUri = $requestData['context']['bap_uri'];
    
    // 2. Build callback URL
    $onSearchUrl = $this->extractBaseUrl($bapUri) . '/on_search';
    
    // 3. Generate on_search response
    $onSearchResponse = $this->generateOnSearchResponse($requestData, $searchResults);
    
    // 4. Send callback to buyer
    $this->sendCallback($onSearchUrl, $onSearchResponse);
}
```

### Authorization Header Creation

```php
private function sendCallback($url, $data) {
    // 1. Create Ed25519 signature
    $authHeader = $this->createAuthorizationHeader(json_encode($data));
    
    // 2. Send HTTP POST with authorization
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: ' . $authHeader,
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
}
```

## 📊 Message Flow Examples

### 1. **Search Request** (Buyer → Seller)
```json
{
  "context": {
    "domain": "ONDC:RET10",
    "action": "search",
    "bap_id": "buyer.com",
    "bap_uri": "https://buyer.com/ondc",
    "transaction_id": "T123",
    "message_id": "M123"
  },
  "message": {
    "intent": {
      "category": {"id": "Foodgrains"},
      "fulfillment": {"type": "Delivery"}
    }
  }
}
```

### 2. **Search Response** (Seller → Buyer, Immediate)
```json
{
  "context": {
    "action": "search",
    "message_id": "seller-msg-123"
  },
  "message": {
    "catalog": {
      "bpp/providers": [...],
      "bpp/fulfillments": [...]
    }
  }
}
```

### 3. **on_search Callback** (Seller → Buyer, Asynchronous)
```json
{
  "context": {
    "action": "on_search",
    "bap_id": "buyer.com",
    "bap_uri": "https://buyer.com/ondc",
    "bpp_id": "seller.com",
    "bpp_uri": "https://seller.com/ondc",
    "message_id": "callback-msg-123"
  },
  "message": {
    "ack": {"status": "ACK"},
    "catalog": {
      "bpp/providers": [...],
      "bpp/fulfillments": [...]
    }
  }
}
```

## 🧪 Testing the Flow

### Method 1: Complete Flow Test
```bash
php test_ondc_flow.php
```

### Method 2: Individual Component Testing
```bash
# Test search endpoint
curl -X POST http://localhost/ondc/src/search.php \
  -H "Content-Type: application/json" \
  -d @search_request.json

# Test callback endpoint
curl -X POST http://localhost/ondc/test_on_search_endpoint.php \
  -H "Content-Type: application/json" \
  -d @callback_data.json
```

### Method 3: Web Interface
- Open `test_search_webpage.html` in browser
- Send search requests and observe callback behavior

## 📝 Logging and Monitoring

### Search Logs (`logs/search.log`)
- Search request details
- Callback triggering information
- Authorization header creation
- Callback sending results

### Callback Logs (`logs/on_search_callback.log`)
- Received callback data
- Validation results
- Processing status

## 🔐 Security Features

### 1. **Ed25519 Digital Signatures**
- Private key from environment variables
- BLAKE-512 message hashing
- Timestamp-based expiration

### 2. **Authorization Headers**
```
Signature keyId="subscriber|ukid|ed25519",
algorithm="ed25519",
created="timestamp",
expires="timestamp",
headers="(created) (expires) digest",
signature="base64_signature"
```

### 3. **Environment Variable Management**
- Sensitive keys stored in `.env`
- No hardcoded credentials
- Secure key rotation support

## 🚀 Production Deployment

### 1. **Environment Configuration**
```env
SUBSCRIBER_ID=your-subscriber-id
UNIQUE_KEY_ID=your-unique-key-id
SIGNING_PRIVATE_KEY=your-ed25519-private-key
BPP_URI=https://your-domain.com/ondc
```

### 2. **HTTPS Requirements**
- Use HTTPS in production
- Valid SSL certificates
- Secure key transmission

### 3. **Rate Limiting**
- Implement request throttling
- Prevent abuse and DoS attacks
- Monitor callback success rates

## 🐛 Troubleshooting

### Common Issues

1. **Callback Not Sent**
   - Check BAP URI in search request
   - Verify network connectivity
   - Check authorization header generation

2. **Callback Failed**
   - Verify buyer's `/on_search` endpoint
   - Check authorization header format
   - Monitor HTTP response codes

3. **Signature Verification Failed**
   - Verify private/public key pairs
   - Check timestamp synchronization
   - Validate message format

### Debug Commands

```bash
# Check search logs
tail -f logs/search.log

# Check callback logs
tail -f logs/on_search_callback.log

# Test endpoint connectivity
curl -v http://localhost/ondc/src/search.php

# Verify environment variables
php -r "echo getenv('SUBSCRIBER_ID');"
```

## 📚 ONDC Specification Compliance

This implementation follows ONDC specifications:

- ✅ **Message Structure**: Compliant with ONDC message format
- ✅ **Authentication**: Ed25519 digital signatures
- ✅ **Callback Flow**: Proper on_search implementation
- ✅ **Error Handling**: Comprehensive error responses
- ✅ **Logging**: Detailed request/response tracking
- ✅ **Validation**: Input validation and sanitization

## 🔮 Future Enhancements

1. **Webhook Management**
   - Retry mechanisms for failed callbacks
   - Callback status tracking
   - Webhook health monitoring

2. **Advanced Security**
   - Rate limiting implementation
   - IP whitelisting
   - Advanced threat detection

3. **Performance Optimization**
   - Async callback processing
   - Response caching
   - Load balancing support

4. **Monitoring & Analytics**
   - Callback success metrics
   - Response time tracking
   - Error rate monitoring

---

**Note**: This implementation provides a solid foundation for ONDC compliance. For production use, ensure proper security measures, monitoring, and error handling are in place.
