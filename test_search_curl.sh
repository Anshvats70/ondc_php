#!/bin/bash

# ONDC Search Endpoint Test Script
# This script provides various cURL commands to test the search endpoint

echo "🔍 ONDC Search Endpoint Test Script"
echo "==================================="
echo ""

# Base URL - adjust this to match your setup
BASE_URL="http://localhost/ondc/src/search.php"

# Test 1: Foodgrains Search (ONDC:RET10)
echo "Test 1: Foodgrains Search (ONDC:RET10)"
echo "--------------------------------------"
curl -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
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
        }
      }
    }
  }' | jq '.' 2>/dev/null || cat

echo ""
echo ""

# Test 2: Fruits and Vegetables Search (ONDC:RET10)
echo "Test 2: Fruits and Vegetables Search (ONDC:RET10)"
echo "------------------------------------------------"
curl -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "context": {
      "domain": "ONDC:RET10",
      "action": "search",
      "country": "IND",
      "city": "std:080",
      "core_version": "1.2.0",
      "bap_id": "buyerNP.com",
      "bap_uri": "https://buyerNP.com/ondc",
      "transaction_id": "T2",
      "message_id": "M2",
      "timestamp": "2023-06-03T08:00:00.000Z",
      "ttl": "PT30S"
    },
    "message": {
      "intent": {
        "category": {
          "id": "Fruits and Vegetables"
        },
        "fulfillment": {
          "type": "Delivery"
        },
        "payment": {
          "@ondc/org/buyer_app_finder_fee_type": "percent",
          "@ondc/org/buyer_app_finder_fee_amount": "3"
        }
      }
    }
  }' | jq '.' 2>/dev/null || cat

echo ""
echo ""

# Test 3: Beverages Search (ONDC:RET11 - F&B)
echo "Test 3: Beverages Search (ONDC:RET11 - F&B)"
echo "--------------------------------------------"
curl -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "context": {
      "domain": "ONDC:RET11",
      "action": "search",
      "country": "IND",
      "city": "std:080",
      "core_version": "1.2.0",
      "bap_id": "buyerNP.com",
      "bap_uri": "https://buyerNP.com/ondc",
      "transaction_id": "T3",
      "message_id": "M3",
      "timestamp": "2023-06-03T08:00:00.000Z",
      "ttl": "PT30S"
    },
    "message": {
      "intent": {
        "category": {
          "id": "Beverages"
        },
        "fulfillment": {
          "type": "Delivery"
        },
        "payment": {
          "@ondc/org/buyer_app_finder_fee_type": "percent",
          "@ondc/org/buyer_app_finder_fee_amount": "3"
        }
      }
    }
  }' | jq '.' 2>/dev/null || cat

echo ""
echo ""

# Test 4: Personal Care Search (ONDC:RET12)
echo "Test 4: Personal Care Search (ONDC:RET12)"
echo "-----------------------------------------"
curl -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "context": {
      "domain": "ONDC:RET12",
      "action": "search",
      "country": "IND",
      "city": "std:080",
      "core_version": "1.2.0",
      "bap_id": "buyerNP.com",
      "bap_uri": "https://buyerNP.com/ondc",
      "transaction_id": "T4",
      "message_id": "M4",
      "timestamp": "2023-06-03T08:00:00.000Z",
      "ttl": "PT30S"
    },
    "message": {
      "intent": {
        "category": {
          "id": "Personal Care"
        },
        "fulfillment": {
          "type": "Delivery"
        },
        "payment": {
          "@ondc/org/buyer_app_finder_fee_type": "percent",
          "@ondc/org/buyer_app_finder_fee_amount": "3"
        }
      }
    }
  }' | jq '.' 2>/dev/null || cat

echo ""
echo ""

# Test 5: Home Care Search (ONDC:RET13)
echo "Test 5: Home Care Search (ONDC:RET13)"
echo "-------------------------------------"
curl -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "context": {
      "domain": "ONDC:RET13",
      "action": "search",
      "country": "IND",
      "city": "std:080",
      "core_version": "1.2.0",
      "bap_id": "buyerNP.com",
      "bap_uri": "https://buyerNP.com/ondc",
      "transaction_id": "T5",
      "message_id": "M5",
      "timestamp": "2023-06-03T08:00:00.000Z",
      "ttl": "PT30S"
    },
    "message": {
      "intent": {
        "category": {
          "id": "Home Care"
        },
        "fulfillment": {
          "type": "Delivery"
        },
        "payment": {
          "@ondc/org/buyer_app_finder_fee_type": "percent",
          "@ondc/org/buyer_app_finder_fee_amount": "3"
        }
      }
    }
  }' | jq '.' 2>/dev/null || cat

echo ""
echo ""

# Test 6: Error Test - Missing Required Fields
echo "Test 6: Error Test - Missing Required Fields"
echo "--------------------------------------------"
curl -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "context": {
      "domain": "ONDC:RET10"
    }
  }' | jq '.' 2>/dev/null || cat

echo ""
echo ""

# Test 7: Error Test - Invalid JSON
echo "Test 7: Error Test - Invalid JSON"
echo "---------------------------------"
curl -X POST "$BASE_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "invalid": "json",
    "missing": "required fields"
  }' | jq '.' 2>/dev/null || cat

echo ""
echo ""

echo "✅ All tests completed!"
echo ""
echo "📋 Test Summary:"
echo "================="
echo "• Test 1-5: Valid search requests for different domains and categories"
echo "• Test 6: Missing required fields (should return validation error)"
echo "• Test 7: Invalid JSON structure (should return validation error)"
echo ""
echo "🔗 Endpoint URL: $BASE_URL"
echo "📝 Method: POST"
echo "📄 Content-Type: application/json"
echo ""
echo "💡 Tip: If you don't have 'jq' installed, the responses will be displayed as-is."
echo "   Install jq for better JSON formatting: sudo apt-get install jq (Ubuntu/Debian)"
echo "   or brew install jq (macOS)"
