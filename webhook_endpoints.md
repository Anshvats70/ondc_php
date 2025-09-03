# 🚀 ONDC Webhook Endpoints Configuration

## 📍 On-Search Callback Endpoint

### **Endpoint URL:**
```
POST http://localhost/ondc/src/on_search_callback.php
```

## 🛒 On-Select Callback Endpoint

### **Endpoint URL:**
```
POST http://localhost/ondc/src/on_select_callback.php
```

## 🚀 On-Init Callback Endpoint

### **Endpoint URL:**
```
POST http://localhost/ondc/src/on_init_callback.php
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

### **Sample On-Select Request:**
```json
{
  "context": {
    "domain": "ONDC:RET10",
    "country": "IND",
    "city": "std:080",
    "action": "select",
    "bap_id": "test-bap.ondc.org",
    "bap_uri": "https://test-bap.ondc.org",
    "transaction_id": "txn_select_123",
    "message_id": "msg_select_123",
    "timestamp": "2025-09-03T15:30:00+02:00",
    "ttl": "PT30S"
  },
  "message": {
    "order": {
      "items": [
        {
          "id": "item_001",
          "quantity": 2,
          "descriptor": {
            "name": "Organic Basmati Rice"
          },
          "price": {
            "currency": "INR",
            "value": "120"
          },
          "category_id": "Foodgrains",
          "fulfillment_id": "fulfillment_001",
          "location_id": "location_001"
        },
        {
          "id": "item_002",
          "quantity": 1,
          "descriptor": {
            "name": "Fresh Apples"
          },
          "price": {
            "currency": "INR",
            "value": "180"
          },
          "category_id": "Fruits",
          "fulfillment_id": "fulfillment_001",
          "location_id": "location_001"
        }
      ],
      "fulfillment": {
        "type": "Delivery",
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

### **Sample On-Select Response:**
```json
{
  "context": {
    "domain": "ONDC:RET10",
    "country": "IND",
    "city": "std:080",
    "action": "on_select",
    "core_version": "1.2.0",
    "bap_id": "neo-server.rozana.in",
    "bap_uri": "https://neo-server.rozana.in/bapl",
    "transaction_id": "txn_select_123",
    "message_id": "msg_select_123",
    "timestamp": "2025-09-03T15:30:00+02:00",
    "ttl": "PT30S"
  },
  "message": {
    "ack": {
      "status": "ACK"
    },
    "order": {
      "provider": {
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
        ]
      },
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
          "location_id": "location_001",
          "quantity": {
            "count": 2,
            "measure": {
              "unit": "kg",
              "value": 2
            }
          },
          "seller_details": {
            "name": "Organic Valley Farms",
            "rating": 4.5,
            "reviews": 1250
          },
          "return_policy": {
            "returnable": true,
            "return_window": "7 days",
            "refund_policy": "Full refund for damaged items"
          }
        }
      ],
      "fulfillments": [
        {
          "id": "fulfillment_001",
          "type": "Delivery",
          "provider_name": "Rozana Delivery",
          "rating": 4.5,
          "state": {
            "descriptor": {
              "name": "Ready for delivery"
            }
          },
          "tracking": false,
          "customer": {
            "person": {
              "name": "Customer Name"
            },
            "contact": {
              "phone": "+91-XXXXXXXXXX",
              "email": "customer@example.com"
            }
          },
          "stops": [
            {
              "type": "end",
              "location": {
                "address": {
                  "locality": "Customer Address",
                  "city": "Delhi",
                  "state": "Delhi",
                  "country": "India"
                }
              }
            }
          ]
        }
      ],
      "payments": [
        {
          "id": "payment_001",
          "type": "ON-ORDER",
          "collected_by": "BAP",
          "params": {
            "amount": "240",
            "currency": "INR"
          }
        }
      ],
      "billing": {
        "name": "Customer Name",
        "address": {
          "locality": "Customer Address",
          "city": "Delhi",
          "state": "Delhi",
          "country": "India"
        },
        "email": "customer@example.com",
        "phone": "+91-XXXXXXXXXX"
      },
      "quote": {
        "price": {
          "currency": "INR",
          "value": "333.20"
        },
        "breakup": [
          {
            "title": "Subtotal",
            "price": {
              "currency": "INR",
              "value": "240"
            }
          },
          {
            "title": "Delivery Charge",
            "price": {
              "currency": "INR",
              "value": "50"
            }
          },
          {
            "title": "GST (18%)",
            "price": {
              "currency": "INR",
              "value": "43.20"
            }
          },
          {
            "title": "Total",
            "price": {
              "currency": "INR",
              "value": "333.20"
            }
          }
        ]
      }
    }
  }
}
```

## 🚀 On-Init Callback Details

The on_init callback handles order initialization requests and provides comprehensive order details including:
- **Order Creation**: Generates unique order IDs and sets initial state
- **Item Processing**: Validates and processes selected items with detailed information
- **Pricing Calculation**: Calculates subtotal, delivery charges, and GST (18%)
- **Fulfillment Setup**: Configures delivery details with estimated delivery times
- **Payment Configuration**: Sets up payment parameters and status
- **Customer Information**: Manages billing and customer details
- **Comprehensive Response**: Returns complete order structure for ONDC compliance

### **Key Features:**
- **City-based Delivery Charges**: Delhi (+₹10), Mumbai/Bangalore/Chennai (+₹20), Pune/Hyderabad (+₹15)
- **Dynamic Pricing**: Calculates GST and delivery charges automatically
- **Item Validation**: Ensures all required fields are present
- **Error Handling**: Returns proper error responses for invalid requests
- **Logging**: Comprehensive logging for audit trails

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
# Test on_search callback
D:\xampp\php\php.exe test_on_search_callback.php

# Test on_select callback
D:\xampp\php\php.exe test_on_select_callback.php

# Test on_init callback
D:\xampp\php\php.exe test_on_init_callback.php
```

### **Test HTTP Endpoint:**
```bash
# Test on_search callback
curl -X POST http://localhost/ondc/src/on_search_callback.php \
  -H 'Content-Type: application/json' \
  -d '{"context":{"domain":"ONDC:RET10","country":"IND","city":"std:080","action":"search","bap_id":"test-bap.ondc.org","bap_uri":"https://test-bap.ondc.org","transaction_id":"txn_123","message_id":"msg_123","timestamp":"2025-09-03T15:16:59+02:00","ttl":"PT30S"},"message":{"intent":{"item":{"descriptor":{"name":"Rice"},"category_id":"Foodgrains"},"fulfillment":{"locations":[{"city":{"name":"Delhi"},"state":{"name":"Delhi"},"country":{"name":"India"}}]}}}}'

# Test on_select callback
curl -X POST http://localhost/ondc/src/on_select_callback.php \
  -H 'Content-Type: application/json' \
  -d '{"context":{"domain":"ONDC:RET10","country":"IND","city":"std:080","action":"select","bap_id":"test-bap.ondc.org","bap_uri":"https://test-bap.ondc.org","transaction_id":"txn_select_123","message_id":"msg_select_123","timestamp":"2025-09-03T15:30:00+02:00","ttl":"PT30S"},"message":{"order":{"items":[{"id":"item_001","quantity":2,"descriptor":{"name":"Organic Basmati Rice"},"price":{"currency":"INR","value":"120"},"category_id":"Foodgrains","fulfillment_id":"fulfillment_001","location_id":"location_001"}],"fulfillment":{"type":"Delivery","locations":[{"city":{"name":"Delhi"},"state":{"name":"Delhi"},"country":{"name":"India"}}]}}}}'

# Test on_init callback
curl -X POST http://localhost/ondc/src/on_init_callback.php \
  -H 'Content-Type: application/json' \
  -d '{"context":{"domain":"ONDC:RET10","country":"IND","city":"std:080","action":"init","bap_id":"test-bap.ondc.org","bap_uri":"https://test-bap.ondc.org","transaction_id":"txn_init_123","message_id":"msg_init_123","timestamp":"2025-09-03T15:45:00+02:00","ttl":"PT30S"},"message":{"order":{"items":[{"id":"item_001","quantity":2,"descriptor":{"name":"Organic Basmati Rice"},"price":{"currency":"INR","value":"120"},"category_id":"Foodgrains","fulfillment_id":"fulfillment_001","location_id":"location_001"}],"fulfillment":{"type":"Delivery","locations":[{"city":{"name":"Delhi"},"state":{"name":"Delhi"},"country":{"name":"India"}}]},"payment":{"type":"ON-ORDER","collected_by":"BAP"},"billing":{"name":"John Doe","address":{"locality":"Connaught Place","city":"Delhi","state":"Delhi","country":"India"},"email":"john.doe@example.com","phone":"+91-9876543210"}}}}'
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
# on_search callback logs
logs/on_search_callback.log

# on_select callback logs  
logs/on_select_callback.log

# on_init callback logs
logs/on_init_callback.log
```

## 🔐 Security Features

- Input validation
- JSON parsing error handling
- Proper HTTP status codes
- Request logging for audit trails
- Environment variable configuration

## 🎯 Next Steps

1. **Test the endpoint locally** using the provided test scripts
2. **Use web interfaces** for easy testing:
   - `test_webhook.html` - Test on_search callback
   - `test_select_webhook.html` - Test on_select callback
   - `test_init_webhook.html` - Test on_init callback
3. **Deploy to production** with proper SSL certificates
4. **Register the webhook URL** with ONDC registry
5. **Monitor logs** for incoming requests
6. **Customize catalog data** to match your inventory
7. **Add authentication** if required by ONDC
8. **Implement rate limiting** for production use
