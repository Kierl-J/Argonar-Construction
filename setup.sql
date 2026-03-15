CREATE DATABASE IF NOT EXISTS argonar_construction;
USE argonar_construction;

CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game VARCHAR(50) NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    team_logo VARCHAR(255) DEFAULT '',
    member_1 VARCHAR(100) NOT NULL,
    member_2 VARCHAR(100) NOT NULL,
    member_3 VARCHAR(100) NOT NULL,
    member_4 VARCHAR(100) NOT NULL,
    member_5 VARCHAR(100) NOT NULL,
    payment_proof VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS solo_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game VARCHAR(50) NOT NULL,
    player_name VARCHAR(100) NOT NULL,
    rank_tier VARCHAR(50) NOT NULL,
    preferred_role VARCHAR(50) DEFAULT '',
    payment_proof VARCHAR(255) NOT NULL,
    status ENUM('pending', 'matched', 'approved') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
