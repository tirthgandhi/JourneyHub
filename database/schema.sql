-- ====================================================================
-- JourneyHub Database Schema
-- Branch: feature/database
-- ====================================================================
-- This script creates the complete database structure for JourneyHub
-- Run this in phpMyAdmin or MySQL CLI to set up the database
-- ====================================================================

-- Create database
CREATE DATABASE IF NOT EXISTS journeyhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE journeyhub;

-- ====================================================================
-- TABLE 1: users
-- Stores user accounts (travelers and admins)
-- ====================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Hashed with password_hash()',
    profile_photo VARCHAR(255) NULL,
    language VARCHAR(10) DEFAULT 'en',
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE 2: trips
-- Stores travel itineraries created by users
-- ====================================================================
CREATE TABLE IF NOT EXISTS trips (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    cover_image VARCHAR(255) NULL,
    is_public BOOLEAN DEFAULT FALSE,
    share_token VARCHAR(100) NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_start_date (start_date),
    INDEX idx_is_public (is_public),
    INDEX idx_share_token (share_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE 3: cities
-- Master list of all destinations (source of truth)
-- NEVER hardcode cities in JSON/JS - always query this table
-- ====================================================================
CREATE TABLE IF NOT EXISTS cities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL,
    state_name VARCHAR(100) NOT NULL,
    country_name VARCHAR(100) NOT NULL,
    cost_index DECIMAL(5,2) DEFAULT 50.00 COMMENT 'Relative cost, 0-100',
    popularity INT DEFAULT 0 COMMENT 'Search ranking weight',
    image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_city_name (city_name),
    INDEX idx_state_name (state_name),
    INDEX idx_country_name (country_name),
    INDEX idx_popularity (popularity),
    INDEX idx_city_country (city_name, country_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE 4: trip_stops
-- Individual city stops within a trip itinerary
-- ====================================================================
CREATE TABLE IF NOT EXISTS trip_stops (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trip_id INT UNSIGNED NOT NULL,
    city_id INT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    stop_order INT UNSIGNED NOT NULL COMMENT 'Order of this stop in the trip sequence',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_trip_id (trip_id),
    INDEX idx_city_id (city_id),
    INDEX idx_stop_order (stop_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE 5: activities
-- Things to do in each city (sightseeing, food, etc.)
-- ====================================================================
CREATE TABLE IF NOT EXISTS activities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    city_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'sightseeing, food, adventure, culture, shopping, entertainment, nature',
    description TEXT NULL,
    cost DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Cost in Indian Rupees (INR)',
    duration VARCHAR(50) NULL COMMENT 'e.g., "2 hours", "Half day", "Full day"',
    image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_city_id (city_id),
    INDEX idx_type (type),
    INDEX idx_cost (cost),
    CONSTRAINT chk_cost_positive CHECK (cost >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE 6: trip_activities
-- Activities scheduled in a trip stop (references activities table)
-- ====================================================================
CREATE TABLE IF NOT EXISTS trip_activities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trip_stop_id INT UNSIGNED NOT NULL,
    activity_id INT UNSIGNED NOT NULL,
    activity_date DATE NOT NULL,
    activity_time TIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_stop_id) REFERENCES trip_stops(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_trip_stop_id (trip_stop_id),
    INDEX idx_activity_id (activity_id),
    INDEX idx_activity_date (activity_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- TABLE 7: expenses
-- Budget tracking for trips
-- ====================================================================
CREATE TABLE IF NOT EXISTS expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trip_id INT UNSIGNED NOT NULL,
    category VARCHAR(50) NOT NULL COMMENT 'transport, stay, activities, meals, other',
    amount DECIMAL(10,2) NOT NULL COMMENT 'Amount in Indian Rupees (INR)',
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_trip_id (trip_id),
    INDEX idx_category (category),
    CONSTRAINT chk_amount_positive CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- BRANCH 6 ADDITIONS: Budget and Calendar Support
-- ====================================================================

-- Add budget column to trips table for budget tracking
ALTER TABLE trips ADD COLUMN budget DECIMAL(10,2) DEFAULT 0.00 AFTER end_date;

-- Add expense_date column to expenses table for calendar/date filtering
ALTER TABLE expenses ADD COLUMN expense_date DATE NULL AFTER amount;

-- ====================================================================
-- Schema creation complete!
-- Next: Run seed.sql to populate with test data
-- ====================================================================

-- ====================================================================
-- DEV RESET ONLY - COMMENTED OUT FOR SAFETY
-- Uncomment ONLY for local development reset
-- ====================================================================
-- DROP DATABASE IF EXISTS journeyhub;