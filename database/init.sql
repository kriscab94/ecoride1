-- EcoRide Database Schema
CREATE DATABASE IF NOT EXISTS ecoride;
USE ecoride;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    bio TEXT,
    profile_image VARCHAR(255),
    eco_credits INT DEFAULT 0,
    verification_token VARCHAR(255),
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Vehicles table
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    color VARCHAR(30),
    license_plate VARCHAR(20),
    seats INT NOT NULL,
    fuel_type ENUM('gasoline', 'diesel', 'hybrid', 'electric') NOT NULL,
    consumption_per_100km DECIMAL(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Trips table
CREATE TABLE trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    departure_city VARCHAR(100) NOT NULL,
    departure_address TEXT NOT NULL,
    departure_lat DECIMAL(10, 8),
    departure_lng DECIMAL(11, 8),
    arrival_city VARCHAR(100) NOT NULL,
    arrival_address TEXT NOT NULL,
    arrival_lat DECIMAL(10, 8),
    arrival_lng DECIMAL(11, 8),
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME,
    available_seats INT NOT NULL,
    price_per_seat DECIMAL(8,2) NOT NULL,
    description TEXT,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    distance_km DECIMAL(8,2),
    eco_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Trip participations table
CREATE TABLE participations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    passenger_id INT NOT NULL,
    seats_reserved INT NOT NULL DEFAULT 1,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    pickup_address TEXT,
    pickup_lat DECIMAL(10, 8),
    pickup_lng DECIMAL(11, 8),
    dropoff_address TEXT,
    dropoff_lat DECIMAL(10, 8),
    dropoff_lng DECIMAL(11, 8),
    total_price DECIMAL(8,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (trip_id, passenger_id)
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewed_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    type ENUM('driver_to_passenger', 'passenger_to_driver') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (trip_id, reviewer_id, reviewed_id, type)
);

-- Messages table for trip communication
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT,
    message TEXT NOT NULL,
    is_broadcast BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Eco credits transactions
CREATE TABLE eco_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    trip_id INT,
    type ENUM('earned', 'spent', 'bonus') NOT NULL,
    amount INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE SET NULL
);

-- Insert sample data
INSERT INTO users (email, password, name, phone, eco_credits, email_verified) VALUES
('admin@ecoride.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', '+1234567890', 100, TRUE),
('driver@ecoride.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jean Dupont', '+0987654321', 50, TRUE),
('passenger@ecoride.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie Martin', '+1122334455', 25, TRUE);

INSERT INTO vehicles (user_id, make, model, year, color, license_plate, seats, fuel_type, consumption_per_100km) VALUES
(2, 'Toyota', 'Prius', 2020, 'Blanc', 'AB-123-CD', 4, 'hybrid', 4.5),
(2, 'Renault', 'Zoe', 2022, 'Bleu', 'EF-456-GH', 4, 'electric', 0.0);

INSERT INTO trips (driver_id, vehicle_id, departure_city, departure_address, arrival_city, arrival_address, departure_time, available_seats, price_per_seat, description, distance_km, eco_points) VALUES
(2, 1, 'Paris', '1 Place de la Bastille, 75004 Paris', 'Lyon', '1 Place Bellecour, 69002 Lyon', '2024-01-15 09:00:00', 3, 25.00, 'Trajet écologique Paris-Lyon avec véhicule hybride', 462, 15),
(2, 2, 'Lyon', '1 Place Bellecour, 69002 Lyon', 'Marseille', '1 Vieux Port, 13001 Marseille', '2024-01-20 14:00:00', 3, 20.00, 'Voyage électrique Lyon-Marseille', 314, 20);