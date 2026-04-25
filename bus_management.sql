-- Create Database
CREATE DATABASE IF NOT EXISTS bus_management;
USE bus_management;

-- =========================
-- Admin Table
-- =========================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

INSERT INTO admins (username, password) VALUES
('admin', 'admin123');

-- =========================
-- Drivers Table (NEW)
-- =========================
CREATE TABLE drivers (
    driver_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    phone VARCHAR(15),
    license_number VARCHAR(50)
);

-- =========================
-- Routes Table (FIXED)
-- =========================
CREATE TABLE routes (
    route_id INT AUTO_INCREMENT PRIMARY KEY,
    route_name VARCHAR(100) NOT NULL,
    start_point VARCHAR(100),
    end_point VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- Buses Table (FIXED)
-- =========================
CREATE TABLE buses (
    bus_id INT AUTO_INCREMENT PRIMARY KEY,
    bus_number VARCHAR(50) NOT NULL UNIQUE,
    capacity INT NOT NULL,
    driver_id INT,
    route_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE SET NULL,
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE SET NULL
);

-- =========================
-- Students Table (FIXED)
-- =========================
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    address TEXT,
    bus_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(bus_id) ON DELETE SET NULL
);

-- =========================
-- Bookings Table (FIXED)
-- =========================
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    bus_id INT,
    booking_date DATE,
    status VARCHAR(50) DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (bus_id) REFERENCES buses(bus_id) ON DELETE CASCADE
);

-- =========================
-- Sample Data
-- =========================

INSERT INTO drivers (name, phone, license_number) VALUES
('Ramesh Patil', '9876543210', 'LIC123'),
('Suresh Kale', '9123456780', 'LIC456');

INSERT INTO routes (route_name, start_point, end_point) VALUES
('Route A', 'Station', 'College'),
('Route B', 'City Center', 'Campus');

INSERT INTO buses (bus_number, capacity, driver_id, route_id) VALUES
('MH12AB1234', 40, 1, 1),
('MH12CD5678', 35, 2, 2);

INSERT INTO students (name, email, phone, address, bus_id) VALUES
('Amit Sharma', 'amit@gmail.com', '9876543210', 'Pune', 1),
('Priya Singh', 'priya@gmail.com', '9123456780', 'Mumbai', 2);

INSERT INTO bookings (student_id, bus_id, booking_date) VALUES
(1, 1, CURDATE()),
(2, 2, CURDATE());