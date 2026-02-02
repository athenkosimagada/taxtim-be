-- ============================
-- Schema: transactions table
-- ============================

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,

    type ENUM('BUY', 'SELL', 'TRADE') NOT NULL,
    coin VARCHAR(10) NOT NULL,

    amount DECIMAL(18,8) NOT NULL,
    price  DECIMAL(18,2) NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_coin (coin),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
);
