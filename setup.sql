-- Argonar Construction - Fresh Database Setup
-- Run: mysql -u root < setup.sql

CREATE DATABASE IF NOT EXISTS argonar_construction
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE argonar_construction;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    company VARCHAR(150) DEFAULT NULL,
    is_guest TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- BOQs
CREATE TABLE IF NOT EXISTS boqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    prepared_by VARCHAR(100) DEFAULT NULL,
    checked_by VARCHAR(100) DEFAULT NULL,
    date_prepared DATE DEFAULT NULL,
    markup_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    vat_percentage DECIMAL(5,2) NOT NULL DEFAULT 12,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('draft','final') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- BOQ Items
CREATE TABLE IF NOT EXISTS boq_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    boq_id INT NOT NULL,
    item_no INT NOT NULL DEFAULT 0,
    description VARCHAR(255) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'lot',
    quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
    unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (boq_id) REFERENCES boqs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Subscriptions (payment/access tracking)
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_type ENUM('daily','monthly') NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    payrex_checkout_session_id VARCHAR(100) DEFAULT NULL,
    payrex_payment_intent_id VARCHAR(100) DEFAULT NULL,
    status ENUM('pending','active','expired') NOT NULL DEFAULT 'pending',
    starts_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed admin user (password: admin123)
INSERT INTO users (name, email, password, company) VALUES
('Admin', 'admin@argonar.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Argonar Construction');
