-- ====================================================================
-- Clear All Data from JourneyHub Database
-- ====================================================================
-- Run this BEFORE importing seed.sql to avoid duplicate entry errors
-- This clears all data but keeps the table structure intact
-- ====================================================================

USE journeyhub;

-- Temporarily disable foreign key checks to allow truncating tables with dependencies
SET FOREIGN_KEY_CHECKS = 0;

-- Clear all tables in reverse dependency order
TRUNCATE TABLE trip_activities;
TRUNCATE TABLE expenses;
TRUNCATE TABLE trip_stops;
TRUNCATE TABLE trips;
TRUNCATE TABLE users;
TRUNCATE TABLE activities;
TRUNCATE TABLE cities;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ====================================================================
-- Database cleared successfully!
-- Next step: Import seed.sql to populate with fresh test data
-- ====================================================================
