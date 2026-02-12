# Transactions API Documentation (Complete with Auth)

---

## 0. User Registration

- **URL:** `/api/register`
- **Method:** `POST`
- **Headers:** `Content-Type: application/json`
- **Body:** JSON object with user registration details:

| Field      | Type   | Description            |
| ---------- | ------ | ---------------------- |
| `email`    | string | User email (unique)    |
| `password` | string | User password (secure) |

**Example curl:**

```bash
curl -X POST http://localhost:8000/api/register \
-H "Content-Type: application/json" \
-d '{
  "email": "user@example.com",
  "password": "Password123!"
}'
```

---

## 1. User Login

- **URL:** `/api/login`
- **Method:** `POST`
- **Headers:** `Content-Type: application/json`
- **Body:** JSON object with login credentials:

| Field      | Type   | Description           |
| ---------- | ------ | --------------------- |
| `email`    | string | Registered user email |
| `password` | string | User password         |

- **Response:** Returns a JWT token on successful login.

**Example curl:**

```bash
curl -X POST http://localhost:8000/api/login \
-H "Content-Type: application/json" \
-d '{
  "email": "user@example.com",
  "password": "Password123!"
}'
```

---

## 2. Get All Transactions

- **URL:** `/api/transactions`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer {JWT_TOKEN}`
- **Description:** Retrieve all transactions for the authenticated user.

**Example curl:**

```bash
curl http://localhost:8000/api/transactions \
-H "Authorization: Bearer {JWT_TOKEN}"
```

---

## 3. Add Transactions

- **URL:** `/api/transactions`
- **Method:** `POST`
- **Headers:**
  - `Authorization: Bearer {JWT_TOKEN}`
  - `Content-Type: application/json`

- **Body:** JSON array of transaction objects with these fields:

| Field          | Type   | Description                                           |
| -------------- | ------ | ----------------------------------------------------- |
| `date`         | string | Transaction date (YYYY-MM-DD)                         |
| `type`         | string | Transaction type (`BUY`, `SELL`, `TRADE`, `TRANSFER`) |
| `sellCoin`     | string | Coin sold or currency paid                            |
| `sellAmount`   | float  | Amount sold or currency paid                          |
| `buyCoin`      | string | Coin bought or currency received                      |
| `buyAmount`    | float  | Amount bought or currency received                    |
| `pricePerCoin` | float  | Price per unit of coin (in `sellCoin` currency)       |

**Example curl:**

```bash
curl -X POST http://localhost:8000/api/transactions \
-H "Authorization: Bearer {JWT_TOKEN}" \
-H "Content-Type: application/json" \
-d '[
  {
    "date": "2024-11-01",
    "type": "BUY",
    "sellCoin": "ZAR",
    "sellAmount": 8000,
    "buyCoin": "BTC",
    "buyAmount": 0.1,
    "pricePerCoin": 80000
  },
  {
    "date": "2025-05-05",
    "type": "TRADE",
    "sellCoin": "BTC",
    "sellAmount": 0.133,
    "buyCoin": "ETH",
    "buyAmount": 10,
    "pricePerCoin": 2000
  }
]'
```

---

## 4. Calculate FIFO and Capital Gains

- **URL:** `/api/transactions/calculate`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer {JWT_TOKEN}`
- **Description:** Runs FIFO calculations on all transactions for the authenticated user and returns balances, transaction calculations, capital gains by tax year, and base cost snapshots.

**Example curl:**

```bash
curl http://localhost:8000/api/transactions/calculate \
-H "Authorization: Bearer {JWT_TOKEN}"
```

---

## 5. Delete All Transactions

- **URL:** `/api/transactions`
- **Method:** `DELETE`
- **Headers:** `Authorization: Bearer {JWT_TOKEN}`
- **Description:** Deletes all transactions for the authenticated user.

**Example curl:**

```bash
curl -X DELETE http://localhost:8000/api/transactions \
-H "Authorization: Bearer {JWT_TOKEN}"
```

---

## 6. Get Tax Year Report

- **URL:** `/api/reports/tax-year/{year}`
- **Method:** `GET`
- **Headers:** `Authorization: Bearer {JWT_TOKEN}`
- **Description:** Retrieves a capital gains report for a specific tax year for the authenticated user. Replace `{year}` with the desired year (e.g., 2025).

**Example curl:**

```bash
curl http://localhost:8000/api/reports/tax-year/2025 \
-H "Authorization: Bearer {JWT_TOKEN}"
```

---

### Notes

- All endpoints except `/api/register` and `/api/login` require a valid JWT token passed in the `Authorization` header.
- Tokens expire based on your JWT settings (e.g., expiration time in your JwtHelper).
- Ensure the token is included exactly as: `Authorization: Bearer {token}`.
- Use `Content-Type: application/json` for requests with JSON bodies.
