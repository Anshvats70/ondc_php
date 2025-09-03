# 🚀 ONDC Webhook Endpoints Configuration

## 📍 On-Search Callback Endpoint

### **Endpoint URL:**
```
POST http://localhost/ondc/src/on_search_callback.php
```

### **Content-Type:**
```
application/json
```

### **Sample Request:**
```json
{
  "context": {
    "domain": "ONDC:RET10",
    "country": "IND",
    "city": "std:080",
    "action": "search",
    "bap_id": "test-bap.ondc.org",
    "bap_uri": "https://test-bap.ondc.org",
    "transaction_id": "txn_123456789",
    "message_id": "msg_123456789",
    "timestamp": "2025-09-03T15:16:59+02:00",
    "ttl": "PT30S"
  },
  "message": {
    "intent": {
      "item": {
        "descriptor": {
          "name": "Rice"
        },
        "category_id": "Foodgrains"
      },
      "fulfillment": {
        "locations": [
          {
            "city": {
              "name": "Delhi"
            },
            "state": {
              "name": "Delhi"
            },
            "country": {
              "name": "India"
            }
          }
        ]
      }
    }
  }
}
```

### **Sample Response:**
```json
{
  "context": {
    "domain": "ONDC:RET10",
    "country": "IND",
    "city": "std:080",
    "action": "on_search",
    "core_version": "1.2.0",
    "bap_id": "neo-server.rozana.in",
    "bap_uri": "https://neo-server.rozana.in/bapl",
    "transaction_id": "txn_123456789",
    "message_id": "msg_response_123456789",
    "timestamp": "2025-09-03T15:16:59+02:00",
    "ttl": "PT30S"
  },
  "message": {
    "ack": {
      "status": "ACK"
    },
    "catalog": {
      "bpp/descriptor": {
        "name": "Rozana Catalog",
        "short_desc": "Fresh groceries and household items",
        "long_desc": "Wide selection of fresh groceries, dairy, fruits, vegetables and household essentials"
      },
      "bpp/providers": [
        {
          "id": "provider_001",
          "descriptor": {
            "name": "Rozana Store",
            "short_desc": "Your neighborhood grocery store",
            "long_desc": "Trusted grocery store serving the community with quality products"
          },
          "locations": [
            {
              "id": "location_001",
              "descriptor": {
                "name": "Rozana Main Store"
              },
              "address": {
                "locality": "Connaught Place",
                "city": "Delhi",
                "state": "Delhi",
                "country": "India"
              }
            }
          ],
          "items": [
            {
              "id": "item_001",
              "descriptor": {
                "name": "Organic Basmati Rice",
                "short_desc": "Premium quality organic basmati rice",
                "long_desc": "Premium quality organic basmati rice",
                "brand": "Organic Valley",
                "images": ["https://example.com/rice1.jpg"]
              },
              "price": {
                "currency": "INR",
                "value": "120"
              },
              "category_id": "Foodgrains",
              "fulfillment_id": "fulfillment_001",
              "location_id": "location_001"
            }
          ]
        }
      ]
    }
  }
}
```

## 🔧 Configuration Steps

### **1. Local Development:**
- Ensure XAMPP is running
- Place files in `D:\xampp\htdocs\ondc\`
- Access via: `http://localhost/ondc/src/on_search_callback.php`

### **2. Production Deployment:**
- Upload to your web server
- Update the endpoint URL in ONDC registry
- Ensure HTTPS is enabled
- Configure proper domain and SSL certificates

### **3. Environment Variables:**
Make sure your `.env` file contains:
```env
SUBSCRIBER_ID=neo-server.rozana.in
SUBSCRIBER_URL=https://neo-server.rozana.in/bapl
```

## 🧪 Testing

### **Test Locally:**
```bash
D:\xampp\php\php.exe test_on_search_callback.php
```

### **Test HTTP Endpoint:**
```bash
curl -X POST http://localhost/ondc/src/on_search_callback.php \
  -H 'Content-Type: application/json' \
  -d '{"context":{"domain":"ONDC:RET10","country":"IND","city":"std:080","action":"search","bap_id":"test-bap.ondc.org","bap_uri":"https://test-bap.ondc.org","transaction_id":"txn_123","message_id":"msg_123","timestamp":"2025-09-03T15:16:59+02:00","ttl":"PT30S"},"message":{"intent":{"item":{"descriptor":{"name":"Rice"},"category_id":"Foodgrains"},"fulfillment":{"locations":[{"city":{"name":"Delhi"},"state":{"name":"Delhi"},"country":{"name":"India"}}]}}}}'
```

## 📊 Supported Search Types

| Category | Items | Price Range |
|----------|-------|-------------|
| **Foodgrains** | Rice, Wheat, Pulses | ₹50 - ₹200/kg |
| **Fruits** | Apples, Bananas, Oranges | ₹80 - ₹300/kg |
| **Vegetables** | Tomatoes, Onions, Potatoes | ₹40 - ₹150/kg |
| **Dairy** | Milk, Curd, Butter | ₹60 - ₹200/liter |
| **Snacks** | Chocolates, Biscuits, Chips | ₹20 - ₹100/pack |

## 🚨 Error Handling

The API returns proper error responses for:
- Invalid JSON
- Missing required fields
- Malformed requests
- Server errors

Error Response Format:
```json
{
  "context": {
    "action": "on_search",
    "bap_id": "neo-server.rozana.in",
    "bap_uri": "https://neo-server.rozana.in/bapl",
    "message_id": "msg_error_123",
    "timestamp": "2025-09-03T15:16:59+02:00",
    "ttl": "PT30S"
  },
  "message": {
    "ack": {
      "status": "NACK"
    },
    "error": {
      "code": "500",
      "message": "Error description"
    }
  }
}
```

## 📝 Logs

All requests and responses are logged to:
```
logs/on_search_callback.log
```

## 🔐 Security Features

- Input validation
- JSON parsing error handling
- Proper HTTP status codes
- Request logging for audit trails
- Environment variable configuration

## 🎯 Next Steps

1. **Test the endpoint locally** using the provided test scripts
2. **Deploy to production** with proper SSL certificates
3. **Register the webhook URL** with ONDC registry
4. **Monitor logs** for incoming requests
5. **Customize catalog data** to match your inventory
6. **Add authentication** if required by ONDC
7. **Implement rate limiting** for production use
