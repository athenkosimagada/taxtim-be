# Taxtim BE – Crypto Tax Backend API

**NOTE:** I DON'T KNOW IF IT HAPPEN ONLY TO MY PC OR NOT BUT WHEN I MAKE CHANGES AND SAVE CODE, I DON'T HAVE TO RESTART THE CONTAINER AGAIN TO RUN THE PROJECT, IT ALSO UPDATE THE CONTAINER RUNNING WITH THE CHNAGES YOU MADE. SO NO NEED TO STROP THE CONTAINER.

A **PHP 8.3 backend API** for managing crypto transactions.
The project is designed as a **Docker-first REST-style service** and exposes a `/transactions` endpoint for creating and retrieving crypto transactions stored in a **MySQL database**.

The API is now fully connected to MySQL and **no longer uses dummy (hard-coded) data**.

---

## 🚀 Project Overview

This project demonstrates:

- A simple PHP JSON API
- Docker Compose–based local development
- MySQL container integration
- Environment-based configuration using `.env`
- Database-backed CRUD operations
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
├── .env
├── src/
│   ├──
│   ├──
│   ├──
│   └──
├── init/
│   └── init.sql
└── README.md
```

---

## 🔐 Environment Configuration (.env)

The project uses a `.env` file to define configuration values shared between **Docker Compose** and the **PHP application**.

Docker Compose automatically loads the `.env` file from the project root.

### Example `.env`

```env
# PHP server config
PHP_HOST=0.0.0.0
PHP_PORT=8000

# MySQL config
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=crypto_tax
MYSQL_USER=crypto_user
MYSQL_PASSWORD=crypto_password
MYSQL_HOST=mysql
MYSQL_PORT=3306
```

> Inside Docker, `MYSQL_HOST` must be `mysql` (the service name), **not** `localhost`.

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

This will:

- Build PHP 8.3 with `pdo_mysql`
- Start MySQL 8
- Create the `crypto_tax` database and tables using `init/init.sql`
- Expose the API at **[http://localhost:8000](http://localhost:8000)**
- **Note:** Wait until the server starts to test the routes

---

## 🌐 API Base URL

```text
http://localhost:8000
```

---

## 🧪 Testing the API with curl

### GET /transactions

Fetch all transactions stored in the database.

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
  }
]
```

---

### POST /transactions

Create a new transaction.

```bash
curl -X POST http://localhost:8000/transactions \
  -H "Content-Type: application/json" \
  -d '{
    "type": "buy",
    "coin": "ETH",
    "amount": 2.5,
    "price": 1800
  }'
```

#### Expected Response

```json
{
  "message": "Transaction created",
  "id": 3
}
```

The transaction is immediately persisted in MySQL and will appear in subsequent `GET /transactions` requests.

---

## 🗄️ Database Initialization

The database schema is created automatically using Docker:

- `schema.sql` and `seed.sql` are executed on first container startup
- Tables are only created if they do not already exist

This ensures safe restarts without data loss.

---

## 🔮 Next Development Steps

- Add PUT / DELETE endpoints
- Add input validation
- Add authentication (JWT)
- Add pagination & filtering
- Add database migrations

---

## 📜 License

MIT License
