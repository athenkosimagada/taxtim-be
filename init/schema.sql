CREATE TABLE IF NOT EXISTS transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    date DATETIME NOT NULL,

    type ENUM('BUY', 'SELL', 'TRADE', 'TRANSFER') NOT NULL,

    sell_coin VARCHAR(10) NOT NULL,
    sell_amount DECIMAL(18,8) NOT NULL,

    buy_coin VARCHAR(10) NOT NULL,
    buy_amount DECIMAL(18,8) NOT NULL,

    price_per_coin DECIMAL(18,2) NOT NULL,
    fiat_currency VARCHAR(5) DEFAULT 'ZAR'
) ENGINE=InnoDB;

CREATE INDEX idx_transactions_date ON transactions (date);
CREATE INDEX idx_transactions_type ON transactions (type);
CREATE INDEX idx_transactions_buy_coin ON transactions (buy_coin);
CREATE INDEX idx_transactions_sell_coin ON transactions (sell_coin);
CREATE INDEX idx_transactions_buy_coin_date ON transactions (buy_coin, date DESC);
CREATE INDEX idx_transactions_type_date ON transactions (type, date DESC);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users (email);
CREATE INDEX idx_users_created_at ON users (created_at DESC);