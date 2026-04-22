-- Admin panel tables for TUOI
-- Run this ONCE after running admin/setup.php

CREATE TABLE IF NOT EXISTS site_content (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    content_key  VARCHAR(100) NOT NULL,
    content_value TEXT NOT NULL,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_content_key (content_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS image_order (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    section    VARCHAR(100) NOT NULL,
    filename   VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    UNIQUE KEY uk_section_file (section, filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
