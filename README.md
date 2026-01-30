# Taxtim BE – Crypto Tax Backend API

A **PHP 8.3 backend API** for managing crypto transactions.
The project is designed as a **Docker-first REST-style service** and currently exposes a `/transactions` endpoint for testing and development.

At this stage, the API uses **dummy (hard-coded) data** to validate routing, container setup, and request handling. The structure is intentionally prepared for replacing dummy data with real database queries.

---

## 🚀 Project Overview

This project demonstrates:

- A simple PHP JSON API
- Docker Compose–based local development
- MySQL container integration
- Clear separation of concerns (Database, Gateway, Controller)
- API testing using `curl`

---

## 🧱 Tech Stack

- **PHP 8.3** (CLI server)
- **MySQL 8** (Docker container)
- **Docker & Docker Compose**
- **PDO (pdo_mysql)**
- **curl** for endpoint testing

---

## 📂 Project Structure

```text
taxtim-be/
├── docker-compose.yml
├── Dockerfile
├── index.php
├── submit.php
├── db.php
├── src/
│   ├── Database.php
│   ├── TransactionGateway.php
│   ├── TransactionController.php
│   └── ErrorHandler.php
├── init/
│   └── init.sql
└── README.md
```

---

## 🐳 Running the Project with Docker

### Requirements

- Docker Desktop
- Docker Compose v2+

No local PHP or MySQL installation is required.

---

### Build and Start Containers

From the project root directory:

```bash
docker compose up --build
```

This command will:

- Build a PHP 8.3 container with `pdo_mysql` enabled
- Start a MySQL 8 container
- Create the `crypto_tax` database
- Expose the API on **[http://localhost:8000](http://localhost:8000)**

---

## 🌐 API Base URL

```text
http://localhost:8000
```

---

## 🧪 Testing the API Endpoints

### GET /transactions

Returns a list of transactions.
(Currently returns **dummy data**, not database records.)

```bash
curl http://localhost:8000/transactions
```

#### Example Response

```json
[
  {
    "id": 1,
    "type": "buy",
    "coin": "BTC",
    "amount": 1,
    "price": 10000,
    "created_at": "2023-01-01 00:00:00"
  },
  {
    "id": 2,
    "type": "sell",
    "coin": "ETH",
    "amount": 2.5,
    "price": 1800,
    "created_at": "2023-01-02 12:30:00"
  }
]
```

---

## 🧪 Dummy Data Notice

The `TransactionGateway` currently returns hard-coded data instead of querying MySQL. This allows the API to be tested without relying on SQL logic.

The gateway is already structured so dummy data can be replaced with real database queries without changing the API endpoints or controllers.

---

## 🔮 Next Development Steps

- Replace dummy data with real SQL queries
- Add POST `/transactions`
- Add input validation
- Add authentication (JWT)
- Add database migrations

---

## 📜 License

MIT License
