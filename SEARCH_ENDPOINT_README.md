# ONDC Search Endpoint Implementation

This document describes the implementation of the `/search` endpoint for ONDC (Open Network for Digital Commerce) following the specification provided.

## Overview

The search endpoint is used to communicate the intent for a full or incremental catalog refresh. It supports:
- Full catalog refresh
- Distributed search by city by category
- Different search types based on domain (Grocery, F&B, Personal Care, Home Care)
- Static terms for transaction level contracts

## Files Created

1. **`src/search.php`** - Main search endpoint implementation
2. **`test_search_endpoint.php`** - PHP test script
3. **`test_search_webpage.html`** - Web-based test interface
4. **`SEARCH_ENDPOINT_README.md`** - This documentation

## Environment Variables Required

Create a `.env` file in the root directory with the following variables:

```env
SIGNING_PRIVATE_KEY="your signing private key"
SIGNING_PUB_KEY="your signing public key"
ENC_PUB_KEY="your encryption/crypto public key"
ENC_PRIV_KEY="your encryption/crypto private key"
COUNTERPARTY_PUB_KEY="the other party's signing public key"
SUBSCRIBER_ID="your subscriber id"
UNIQUE_KEY_ID="your ukid"
```

## API Endpoint

**URL:** `POST /src/search.php`

**Content-Type:** `application/json`

## Request Format

The search request follows the ONDC specification:

```json
{
  "context": {
    "domain": "ONDC:RET10",
    "action": "search",
    "country": "IND",
    "city": "std:080",
    "core_version": "1.2.0",
    "bap_id": "buyerNP.com",
    "bap_uri": "https://buyerNP.com/ondc",
    "transaction_id": "T1",
    "message_id": "M1",
    "timestamp": "2023-06-03T08:00:00.000Z",
    "ttl": "PT30S"
  },
  "message": {
    "intent": {
      "category": {
        "id": "Foodgrains"
      },
      "fulfillment": {
        "type": "Delivery"
      },
      "payment": {
        "@ondc/org/buyer_app_finder_fee_type": "percent",
        "@ondc/org/buyer_app_finder_fee_amount": "3"
      },
      "tags": [
        {
          "code": "bap_terms",
          "list": [
            {
              "code": "static_terms",
              "value": ""
            },
            {
              "code": "static_terms_new",
              "value": "https://github.com/ONDC-Official/NP-Static-Terms/buyerNP_BNP/1.0/tc.pdf"
            },
            {
              "code": "effective_date",
              "value": "2023-10-01T00:00:00.000Z"
            }
          ]
        }
      ]
    }
  }
}
```

## Supported Domains

| Domain | Description | Search Type |
|--------|-------------|-------------|
| `ONDC:RET10` | Grocery | Distributed by category |
| `ONDC:RET11` | F&B (Food & Beverages) | By city only |
| `ONDC:RET12` | Personal Care | Distributed by category |
| `ONDC:RET13` | Home Care | Distributed by category |

## Response Format

The endpoint returns a catalog response with the following structure:

```json
{
  "context": {
    "domain": "ONDC:RET10",
    "country": "IND",
    "city": "std:080",
    "action": "search",
    "core_version": "1.2.0",
    "bap_id": "buyerNP.com",
    "bap_uri": "https://buyerNP.com/ondc",
    "transaction_id": "T1",
    "message_id": "generated-uuid",
    "timestamp": "2023-06-03T08:00:00.000Z",
    "ttl": "PT30S"
  },
  "message": {
    "catalog": {
      "bpp/fulfillments": [
        {
          "id": "f1",
          "type": "Delivery",
          "provider_name": "Seller NP",
          "rating": "4.5",
          "state": {
            "descriptor": {
              "name": "Active"
            }
          }
        }
      ],
      "bpp/descriptor": {
        "name": "Seller Network Participant",
        "code": "SELLER_NP_001"
      },
      "bpp/providers": [
        {
          "id": "seller_001",
          "descriptor": {
            "name": "Premium Seller",
            "code": "SELLER_PREMIUM_001"
          },
          "items": [
            {
              "id": "item_001",
              "descriptor": {
                "name": "Premium Basmati Rice",
                "code": "RICE_BASMATI_001"
              },
              "price": {
                "currency": "INR",
                "value": "120.00"
              },
              "category_id": "Foodgrains",
              "fulfillment_id": "f1"
            }
          ],
          "fulfillments": [
            {
              "id": "f1",
              "type": "Delivery"
            }
          ]
        }
      ]
    }
  }
}
```

## Features

### 1. Authentication & Security
- Uses Ed25519 digital signatures
- BLAKE-512 message hashing
- Environment-based key management
- Request validation and sanitization

### 2. Search Types
- **Distributed by Category**: For domains like Grocery, Personal Care, Home Care
- **By City Only**: For domains like F&B where category hierarchy is inconsistent

### 3. Catalog Generation
- Mock catalog data based on search parameters
- Category-specific item generation
- Fulfillment and provider information

### 4. Logging
- Comprehensive request/response logging
- Error tracking and debugging
- Log files stored in `/logs/search.log`

## Testing

### Method 1: Web Interface
1. Open `test_search_webpage.html` in a web browser
2. Use the tabs to test different scenarios:
   - **Manual Input**: Fill form fields manually
   - **Preset Examples**: Use predefined test cases
   - **Custom JSON**: Paste custom JSON payloads

### Method 2: PHP Script
```bash
php test_search_endpoint.php
```

### Method 3: cURL
```bash
curl -X POST http://localhost/ondc/src/search.php \
  -H "Content-Type: application/json" \
  -d @search_request.json
```

## Error Handling

The endpoint provides comprehensive error handling:

- **400 Bad Request**: Invalid JSON or missing required fields
- **405 Method Not Allowed**: Non-POST requests
- **500 Internal Server Error**: Server-side processing errors

Error responses include:
```json
{
  "error": {
    "code": "SEARCH_ERROR",
    "message": "Error description",
    "timestamp": "2023-06-03T08:00:00.000Z"
  }
}
```

## Validation

The endpoint validates:
- Required context fields (domain, action, country, city, etc.)
- Required message fields (intent, category, fulfillment)
- JSON structure and format
- Environment variable configuration

## Security Considerations

1. **Private Keys**: Never expose private keys in code or logs
2. **Environment Variables**: Use `.env` files for sensitive data
3. **Input Validation**: All inputs are validated and sanitized
4. **HTTPS**: Use HTTPS in production for secure communication
5. **Rate Limiting**: Consider implementing rate limiting for production use

## Dependencies

- PHP 7.4+
- Composer dependencies:
  - `vlucas/phpdotenv` - Environment variable management
  - `ramsey/uuid` - UUID generation
  - `phpseclib/phpseclib` - Cryptographic functions

## Installation

1. Ensure PHP and Composer are installed
2. Run `composer install` to install dependencies
3. Copy `env.example` to `.env` and configure your keys
4. Ensure the web server can access the `src/` directory
5. Test the endpoint using the provided test files

## Usage Examples

### Basic Search Request
```php
$searchData = [
    'context' => [
        'domain' => 'ONDC:RET10',
        'action' => 'search',
        'country' => 'IND',
        'city' => 'std:080',
        // ... other required fields
    ],
    'message' => [
        'intent' => [
            'category' => ['id' => 'Foodgrains'],
            'fulfillment' => ['type' => 'Delivery']
        ]
    ]
];

$response = file_get_contents('http://localhost/ondc/src/search.php', false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($searchData)
    ]
]));
```

## Troubleshooting

### Common Issues

1. **Environment Variables Not Set**
   - Ensure `.env` file exists and contains all required variables
   - Check file permissions and ownership

2. **Signature Verification Failed**
   - Verify private/public key pairs are correctly configured
   - Check key format and encoding

3. **Logs Directory Not Writable**
   - Ensure `/logs` directory exists and is writable by web server
   - Check directory permissions (755 recommended)

4. **Composer Dependencies Missing**
   - Run `composer install` to install required packages
   - Check `composer.json` for correct dependencies

### Debug Mode

Enable debug logging by checking the `/logs/search.log` file for detailed information about requests and responses.

## Contributing

When contributing to this implementation:

1. Follow the existing code style
2. Add comprehensive error handling
3. Include proper logging for debugging
4. Update this documentation for any new features
5. Test thoroughly with different scenarios

## License

This implementation follows the ONDC specification and is provided as-is for educational and development purposes.
