CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,

    wallet VARCHAR(50) DEFAULT 'default',

    type ENUM('BUY','SELL','TRADE') NOT NULL,

    asset_from VARCHAR(10),
    asset_to   VARCHAR(10),

    quantity DECIMAL(18,8) NOT NULL,
    unit_price_zar DECIMAL(18,2) NOT NULL,
    fee_zar DECIMAL(18,2) DEFAULT 0,

    asset_from_market_price_zar DECIMAL(18,2) NULL,

    executed_at DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_wallet (wallet),
    INDEX idx_type (type),
    INDEX idx_executed_at (executed_at)
);
