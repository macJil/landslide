CREATE DATABASE IF NOT EXISTS baguio_multi_barangay;
USE baguio_multi_barangay;

-- 1. Users Table (Role-Based Access: Admin vs User)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Hashed password using password_hash()
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Initial System Accounts
-- Note: Replace password hashes with real password_hash() values in production
INSERT INTO users (full_name, username, email, password, role) VALUES 
('Barangay Admin', 'admin', 'admin@baguio.gov.ph', 'admin', 'admin'),
('Juan Dela Cruz', 'user', 'juan@gmail.com', 'user', 'user');

-- 2. Barangays Table
CREATE TABLE barangays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barangay_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO barangays (id, barangay_name) VALUES 
(1, 'Barangay Irisan'),
(2, 'Barangay Fort Del Pilar (PMA)');

-- 3. Streets/Areas Table
CREATE TABLE barangay_streets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barangay_id INT NOT NULL,
    street_name VARCHAR(100) NOT NULL,
    purok_zone VARCHAR(50) NOT NULL,
    latitude DECIMAL(8,5) NOT NULL,
    longitude DECIMAL(8,5) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (barangay_id) REFERENCES barangays(id) ON DELETE CASCADE
);

INSERT INTO barangay_streets (barangay_id, street_name, purok_zone, latitude, longitude) VALUES 
(1, 'Irisan-Asin Road Cut', 'Purok 1', 16.41250, 120.56520),
(1, 'Naguilian Road Slope Area', 'Purok 4', 16.41890, 120.57110),
(2, 'PMA Road Main Slope', 'Sector A', 16.36750, 120.62250);

-- 4. Telemetry & Analysis Logs
CREATE TABLE landslide_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    street_id INT,
    rain_rate DECIMAL(5,2) NOT NULL,
    daily_rain DECIMAL(6,2) NOT NULL,
    soil_moisture DECIMAL(4,3) NOT NULL,
    risk_category ENUM('LOW', 'NORMAL', 'MEDIUM', 'HIGH') NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (street_id) REFERENCES barangay_streets(id) ON DELETE CASCADE
);

-- 5. Citizen Reports Linked to Users Table
CREATE TABLE alerts_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- Linked to users.id
    street_id INT NOT NULL, -- Linked to barangay_streets.id
    house_landmark VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (street_id) REFERENCES barangay_streets(id) ON DELETE CASCADE
);