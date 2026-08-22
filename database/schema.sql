-- ============================================
-- JourneyHub Database Schema
-- Run this file against MySQL to set up tables.
-- ============================================

CREATE DATABASE IF NOT EXISTS `journeyhub`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `journeyhub`;

-- ============================================
-- Users table (Branch 1 — Authentication)
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `name`       VARCHAR(100)  NOT NULL,
    `email`      VARCHAR(255)  NOT NULL UNIQUE,
    `password`   VARCHAR(255)  NOT NULL,
    `created_at` TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Trips table (Branch 2 — Dashboard & Trips)
-- ============================================
CREATE TABLE IF NOT EXISTS `trips` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`      INT            NOT NULL,
    `name`         VARCHAR(150)   NOT NULL,
    `description`  TEXT,
    `start_date`   DATE           NOT NULL,
    `end_date`     DATE           NOT NULL,
    `cover_image`  VARCHAR(255)   NULL,
    `is_public`    TINYINT(1)     DEFAULT 0,
    `share_token`  VARCHAR(64)    NULL UNIQUE,
    `created_at`   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_trips_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
