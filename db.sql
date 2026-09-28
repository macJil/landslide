-- SmartSlope academic MVP database
-- Target: MySQL 8.0.16+ / MariaDB 10.4+
-- Fresh-install script. It creates a NEW database named smartslope_mvp and
-- does not drop or overwrite the existing baguio_multi_barangay database.
-- If smartslope_mvp was created from an earlier copy of this script, see the
-- commented one-time migration at the end; CREATE TABLE IF NOT EXISTS does not
-- add columns to an already-existing table.
--
-- Scope: one study barangay; admin/user accounts; sourced location risk data;
-- rainfall observations and rule-based risk levels; community reports.
-- No sensor telemetry, payments, subscriptions, AI/ML, or geographic expansion.

CREATE DATABASE IF NOT EXISTS smartslope_mvp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE smartslope_mvp;

-- 1. Accounts used by administrators and (if enabled) registered community users.
-- Never store a plain-text password. Store PHP password_hash() output here.
CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Administrative study area. Seed only the selected barangay for the MVP.
-- Additional barangays can be inserted later without changing the schema.
CREATE TABLE IF NOT EXISTS barangays (
    barangay_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    barangay_name VARCHAR(100) NOT NULL,
    city_name VARCHAR(100) NOT NULL DEFAULT 'Baguio City',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (barangay_id),
    UNIQUE KEY uq_barangay_city_name (city_name, barangay_name),
    CONSTRAINT chk_barangays_active CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Named slope/area locations within the chosen barangay.
-- Baseline susceptibility is separate from a current rainfall-based reading.
-- Coordinates and susceptibility source details may remain NULL until verified.
CREATE TABLE IF NOT EXISTS locations (
    location_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    barangay_id SMALLINT UNSIGNED NOT NULL,
    location_name VARCHAR(150) NOT NULL,
    purok_zone VARCHAR(100) NULL,
    landmark VARCHAR(255) NULL,
    latitude DECIMAL(9, 6) NULL,
    longitude DECIMAL(9, 6) NULL,
    susceptibility_class ENUM(
        'very_high', 'high', 'moderate', 'low', 'debris_flow', 'unknown'
    ) NOT NULL DEFAULT 'unknown',
    hazard_source_name VARCHAR(150) NULL,
    hazard_source_url VARCHAR(500) NULL,
    hazard_source_date DATE NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (location_id),
    UNIQUE KEY uq_location_name_in_area
        (barangay_id, location_name, purok_zone),
    KEY idx_locations_area_active (barangay_id, is_active, location_name),
    CONSTRAINT fk_locations_barangay
        FOREIGN KEY (barangay_id) REFERENCES barangays (barangay_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_locations_coordinates_pair CHECK (
        (latitude IS NULL AND longitude IS NULL)
        OR (latitude IS NOT NULL AND longitude IS NOT NULL)
    ),
    CONSTRAINT chk_locations_latitude CHECK (
        latitude IS NULL OR latitude BETWEEN -90 AND 90
    ),
    CONSTRAINT chk_locations_longitude CHECK (
        longitude IS NULL OR longitude BETWEEN -180 AND 180
    ),
    CONSTRAINT chk_locations_active CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Time-stamped, sourced rainfall observations and the rule-based result.
-- PHP may calculate the 1h, 24h, and 72h totals by summing hourly API rainfall.
-- The totals are stored in millimetres. observed_at is the UTC time for the
-- observation/evaluation and should be converted to Asia/Manila for display.
-- Only save a reading when the source, observation time, and required values