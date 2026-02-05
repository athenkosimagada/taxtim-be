# Crypto Tax API Documentation

---

## Overview

This API provides functionality to:

- Import and manage cryptocurrency transaction data
- Calculate FIFO-based capital gains and tax year reports
- Retrieve transaction history and summaries

The API is designed to help calculate capital gains for crypto disposals and trades in South African Rand (ZAR).

---

## Base URL

```
http://localhost:8000/api
```

---

# Endpoints

---

## 1. **Import Transactions**

```
POST /transactions/import
```

### Description

Import an array of transactions into the system.

### Request Body (JSON)

```json
[
  {
    "wallet": "default",
    "type": "BUY",
    "assetFrom": null,
    "assetTo": "BTC",
    "quantity": 0.1,
    "unitPriceZar": 80000,
    "feeZar": 0,
    "assetFromMarketPriceZar": null,
    "executedAt": "2024-11-01 00:00:00"
  },
  ...
]
```

### Response

- **201 Created**

```json
{
  "success": true,
  "message": "Transactions created successfully"
}
```

- **400 Bad Request** (invalid JSON)

```json
{
  "success": false,
  "message": "Invalid JSON input"
}
```

---

## 2. **Get All Transactions**

```
GET /transactions
```

### Description

Retrieve all transactions ordered by execution date ascending.

### Response

```json
[
  {
    "wallet": "default",
    "type": "BUY",
    "assetFrom": null,
    "assetTo": "BTC",
    "quantity": 0.1,
    "unitPriceZar": 80000,
    "feeZar": 0,
    "assetFromMarketPriceZar": null,
    "executedAt": {
      "date": "2024-11-01 00:00:00.000000",
      "timezone_type": 3,
      "timezone": "UTC"
    }
  },
  ...
]
```

---

## 3. **Calculate FIFO Capital Gains**

```
GET /transactions/calculate
```

### Description

Calculate FIFO capital gains, balances, base costs, and detailed transaction calculations.

### Response (Example)

```json
{
  "transactions": [...],
  "calculations": [...],
  "balances": {...},
  "baseCosts": {...},
  "baseCostSnapshots": {...},
  "capitalGains": {...}
}
```

---

## 4. **Delete All Transactions**

```
DELETE /transactions
```

### Description

Deletes all stored transactions.

### Response

```json
{
  "success": true,
  "message": "All transactions deleted successfully"
}
```

---

## 5. **Get Tax Year Report**

```
GET /reports/tax-year/{year}
```

### Description

Get detailed tax report for a specific tax year.

### Path Parameters

- `year` (integer): The tax year (e.g., 2026).

### Response

```json
{
  "taxYear": 2026,
  "capitalGains": {
    "BTC": 9000,
    "TOTAL": 9000
  },
  "disposals": [
    {
      "type": "TRADE",
      "from": "BTC",
      "to": "ETH",
      "date": "2025-05-05",
      "soldQuantity": 0.13333333,
      "proceeds": 20000,
      "cost": 11000,
      "gain": 9000,
      "lots": [
        {
          "asset": "BTC",
          "quantity": 0.1,
          "unitPriceZar": 80000,
          "date": "2024-11-01",
          "cost": 8000
        },
        {
          "asset": "BTC",
          "quantity": 0.03333333,
          "unitPriceZar": 90000,
          "date": "2024-11-02",
          "cost": 3000
        }
      ],
      "taxYear": 2026
    }
  ],
  "openingBaseCosts": {
    "BTC": {
      "quantity": 0.3,
      "cost": 26000
    }
  },
  "closingBaseCosts": {
    "BTC": {
      "quantity": 0.46666667,
      "cost": 45000
    },
    "ETH": {
      "quantity": 10,
      "cost": 20000
    }
  }
}
```

### Errors

- **400 Bad Request** — Invalid year

```json
{
  "error": "Invalid tax year"
}
```

- **404 Not Found** — No report found (handled by route if implemented)

---

# Internal Logic Notes

- **FIFO Calculation**: Uses First-In-First-Out logic to calculate cost basis for sales/trades.
- **Tax Year**: Determined by date of transaction, typically calendar year.
- **Capital Gains**: Calculated per asset and summed as total for tax reporting.
- **Base Costs**: Snapshot of holdings at start and end of tax years for accurate tax cost basis.

---

# Deployment Notes

- The app connects to a MySQL database (configured via environment variables).
- Docker Compose setup uses two services:
  - `php`: The API service exposed on port 8000
  - `mysql`: MySQL database on port 3307 (mapped to 3306 inside container)

- Retry logic on DB connection to ensure availability during container startup.

---

# Usage Examples

### Import Transactions (using curl)

```bash
curl -X POST http://localhost:8000/api/transactions/import \
     -H "Content-Type: application/json" \
     -d '[{"wallet":"default","type":"BUY","assetFrom":null,"assetTo":"BTC","quantity":0.1,"unitPriceZar":80000,"feeZar":0,"assetFromMarketPriceZar":null,"executedAt":"2024-11-01 00:00:00"}]'
```

### Get Tax Year Report

```bash
curl -X GET http://localhost:8000/api/reports/tax-year/2026
```
