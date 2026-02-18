-- Migration: Create staff_info table
-- Stores extra profile data for staff users (linked to users.id).
-- Run this once: source this file in MySQL or run via your migration tool.

CREATE TABLE IF NOT EXISTS staff_info (
    user_id INT NOT NULL PRIMARY KEY,
    age INT NULL,
    address VARCHAR(500) NULL,
    gender VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_staff_info_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_staff_info_user (user_id)
);
