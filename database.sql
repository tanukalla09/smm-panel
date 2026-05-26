-- SMM Panel Database Schema
-- Import this file into MySQL to set up the database




-- Providers (external API sources like fastxsmm.com)
CREATE TABLE IF NOT EXISTS providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    api_url VARCHAR(255) NOT NULL DEFAULT 'https://fastxsmm.com/api/v2',
    api_key VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

-- Services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT DEFAULT NULL,
    provider_service_id VARCHAR(50) DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'General',
    description TEXT,
    rate DECIMAL(12, 4) NOT NULL COMMENT 'Price per 1000 units charged to user',
    cost_rate DECIMAL(12, 4) DEFAULT NULL COMMENT 'Provider cost per 1000 for profit calculation',
    min_quantity INT NOT NULL DEFAULT 100,
    max_quantity INT NOT NULL DEFAULT 100000,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE SET NULL,
    INDEX idx_services_category (category),
    INDEX idx_services_status (status)
) ENGINE=InnoDB;

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    link VARCHAR(500) NOT NULL,
    quantity INT NOT NULL,
    charge DECIMAL(12, 2) NOT NULL COMMENT 'Amount charged to user',
    cost DECIMAL(12, 2) DEFAULT NULL COMMENT 'Amount paid to provider',
    profit DECIMAL(12, 2) DEFAULT NULL COMMENT 'charge - cost',
    provider_order_id VARCHAR(100) DEFAULT NULL,
    status ENUM('pending', 'processing', 'in_progress', 'completed', 'partial', 'cancelled', 'failed') NOT NULL DEFAULT 'pending',
    start_count INT DEFAULT 0,
    remains INT DEFAULT 0,
    error_message TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status),
    INDEX idx_orders_provider (provider_order_id)
) ENGINE=InnoDB;

-- Transactions (deposits, order charges, refunds)
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT DEFAULT NULL,
    type ENUM('deposit', 'order', 'refund', 'adjustment') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    stripe_session_id VARCHAR(255) DEFAULT NULL,
    stripe_payment_id VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_transactions_user (user_id),
    INDEX idx_transactions_type (type),
    INDEX idx_transactions_status (status)
) ENGINE=InnoDB;

-- Support tickets
CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open', 'closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tickets_user (user_id),
    INDEX idx_tickets_status (status)
) ENGINE=InnoDB;

-- Ticket replies
CREATE TABLE IF NOT EXISTS ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ticket_replies_ticket (ticket_id)
) ENGINE=InnoDB;

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    link VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id),
    INDEX idx_notifications_read (is_read)
) ENGINE=InnoDB;

-- Contact form submissions
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_contacts_status (status)
) ENGINE=InnoDB;

-- App settings (key-value store)
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin user
INSERT INTO users (username, email, password, balance, role, status) VALUES
('admin', 'admin@smmpanel.local', '$2y$10$F2IikLijVPdDKqGghPenYu8.4R7G97gxQvhKE1ZOS7JxvYqgsUm6C', 0.00, 'admin', 'active');

-- Default provider (update api_key in admin panel)
INSERT INTO providers (name, api_url, api_key, status) VALUES
('FastXSMM', 'https://fastxsmm.com/api/v2', 'YOUR_API_KEY_HERE', 'inactive');

-- Sample services
INSERT INTO services (provider_id, provider_service_id, name, category, description, rate, cost_rate, min_quantity, max_quantity, status) VALUES
(1, '1', 'Instagram Followers [High Quality]', 'Instagram', 'Real-looking Instagram followers with fast delivery.', 2.5000, 1.8000, 100, 50000, 'active'),
(1, '2', 'Instagram Likes [Instant]', 'Instagram', 'Instant Instagram likes on your posts.', 0.8000, 0.5000, 50, 100000, 'active'),
(1, '3', 'YouTube Views [High Retention]', 'YouTube', 'High retention YouTube views from real sources.', 3.0000, 2.2000, 100, 1000000, 'active'),
(1, '4', 'YouTube Subscribers', 'YouTube', 'Gradual YouTube subscriber delivery.', 15.0000, 12.0000, 100, 10000, 'active'),
(1, '5', 'TikTok Followers', 'TikTok', 'TikTok followers with refill guarantee.', 4.0000, 3.0000, 100, 50000, 'active'),
(1, '6', 'Twitter/X Followers', 'Twitter', 'Twitter/X profile followers.', 3.5000, 2.5000, 100, 50000, 'active');

