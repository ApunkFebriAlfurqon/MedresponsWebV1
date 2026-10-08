-- ============================================================
-- RapidAid Ambulance Service - Database Schema
-- Version: 1.0.0
-- ============================================================

CREATE DATABASE IF NOT EXISTS rapidaid_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rapidaid_db;

-- ============================================================
-- TABLE: admins
-- ============================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT NULL,
    role ENUM('superadmin','admin','operator') DEFAULT 'admin',
    status ENUM('active','inactive') DEFAULT 'active',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    date_of_birth DATE DEFAULT NULL,
    gender ENUM('male','female','other') DEFAULT NULL,
    blood_type ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    emergency_contact_name VARCHAR(100) DEFAULT NULL,
    emergency_contact_phone VARCHAR(20) DEFAULT NULL,
    email_verified TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: subscription_plans
-- ============================================================
CREATE TABLE subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    duration_days INT NOT NULL DEFAULT 30,
    max_members INT DEFAULT 1,
    features JSON,
    is_popular TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    color VARCHAR(30) DEFAULT '#e53e3e',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: subscriptions
-- ============================================================
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    status ENUM('active','expired','pending','cancelled') DEFAULT 'pending',
    start_date DATE,
    end_date DATE,
    auto_renew TINYINT(1) DEFAULT 0,
    family_members JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id)
);

-- ============================================================
-- TABLE: hospitals
-- ============================================================
CREATE TABLE hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100),
    phone VARCHAR(30),
    email VARCHAR(150),
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    emergency_capacity INT DEFAULT 0,
    specializations VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: drivers
-- ============================================================
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    employee_id VARCHAR(50) UNIQUE,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20) NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    license_expiry DATE,
    address TEXT,
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('available','on_duty','off_duty','inactive') DEFAULT 'available',
    total_trips INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 5.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: ambulances
-- ============================================================
CREATE TABLE ambulances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_number VARCHAR(50) UNIQUE NOT NULL,
    vehicle_type ENUM('BLS','ALS','neonatal','bariatric') DEFAULT 'BLS',
    make VARCHAR(100),
    model VARCHAR(100),
    year INT,
    color VARCHAR(50),
    driver_id INT DEFAULT NULL,
    status ENUM('available','on_duty','maintenance','inactive') DEFAULT 'available',
    equipment TEXT DEFAULT NULL,
    last_service_date DATE DEFAULT NULL,
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE: emergency_requests
-- ============================================================
CREATE TABLE emergency_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_code VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    ambulance_id INT DEFAULT NULL,
    driver_id INT DEFAULT NULL,
    hospital_id INT DEFAULT NULL,
    pickup_address TEXT NOT NULL,
    pickup_latitude DECIMAL(10,8) DEFAULT NULL,
    pickup_longitude DECIMAL(11,8) DEFAULT NULL,
    destination_address TEXT,
    patient_name VARCHAR(150),
    patient_age INT,
    condition_description TEXT,
    status ENUM('pending','accepted','on_the_way','arrived','completed','cancelled') DEFAULT 'pending',
    priority ENUM('low','medium','high','critical') DEFAULT 'medium',
    assigned_at DATETIME DEFAULT NULL,
    pickup_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    response_time_minutes INT DEFAULT NULL,
    notes TEXT,
    rating INT DEFAULT NULL,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (ambulance_id) REFERENCES ambulances(id) ON DELETE SET NULL,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    subscription_id INT DEFAULT NULL,
    emergency_request_id INT DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('bank_transfer','e_wallet','credit_card','cash') DEFAULT 'bank_transfer',
    payment_channel VARCHAR(100) DEFAULT NULL,
    status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    paid_at DATETIME DEFAULT NULL,
    proof_image VARCHAR(255) DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE SET NULL,
    FOREIGN KEY (emergency_request_id) REFERENCES emergency_requests(id) ON DELETE SET NULL
);

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','success','warning','danger','emergency') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Default superadmin (password: admin123)
INSERT INTO admins (name, email, password, phone, role) VALUES
('Super Admin', 'admin@rapidaid.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08001234567', 'superadmin'),
('Operator 1', 'operator@rapidaid.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08001234568', 'operator');

-- Subscription Plans
INSERT INTO subscription_plans (name, slug, price, duration_days, max_members, features, is_popular, color) VALUES
('Basic', 'basic', 25000, 30, 1, '["Priority Support","Emergency Request History","24/7 Hotline Access","Basic Response Time"]', 0, '#38b2ac'),
('Premium', 'premium', 75000, 30, 1, '["Faster Dispatch","Live GPS Tracking","Hospital Recommendations","Priority Response","Emergency History","Dedicated Support"]', 1, '#e53e3e'),
('Family', 'family', 150000, 30, 5, '["Up to 5 Family Members","Priority Ambulance","Premium 24/7 Support","Live GPS Tracking","All Premium Features","Family Health Reports"]', 0, '#667eea');

-- Sample Users (password: user123)
INSERT INTO users (full_name, email, password, phone, address, blood_type) VALUES
('Budi Santoso', 'budi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08123456789', 'Jl. Raya Darmo No. 10, Surabaya', 'O+'),
('Siti Rahayu', 'siti@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08234567890', 'Jl. Ahmad Yani No. 25, Surabaya', 'A+'),
('Ahmad Fauzi', 'ahmad@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '08345678901', 'Jl. Pemuda No. 5, Surabaya', 'B+');

-- Hospitals
INSERT INTO hospitals (name, address, city, phone, latitude, longitude, emergency_capacity, specializations) VALUES
('RSUD Dr. Soetomo', 'Jl. Mayjen Prof. Dr. Moestopo No.6-8, Surabaya', 'Surabaya', '031-5501011', -7.2756, 112.7420, 50, 'Trauma, Cardiology, Neurology'),
('RS Premier Surabaya', 'Jl. Nginden Semolo No.1-15, Surabaya', 'Surabaya', '031-5936600', -7.3080, 112.7680, 30, 'General, Pediatrics, Surgery'),
('RS Siloam Surabaya', 'Jl. Raya Gubeng No. 70, Surabaya', 'Surabaya', '031-5033000', -7.2637, 112.7528, 40, 'Emergency, ICU, Cardiology'),
('RSUD Bhakti Dharma Husada', 'Jl. Raya Benowo No. 1, Surabaya', 'Surabaya', '031-7413871', -7.2479, 112.6508, 25, 'General, Maternity, Pediatrics');

-- Drivers
INSERT INTO drivers (full_name, employee_id, phone, license_number, license_expiry, status, rating) VALUES
('Eko Prasetyo', 'DRV-001', '08511234567', 'SIM-A-001234', '2026-12-31', 'available', 4.90),
('Wahyu Hidayat', 'DRV-002', '08522345678', 'SIM-A-002345', '2026-08-15', 'available', 4.85),
('Rizky Firmansyah', 'DRV-003', '08533456789', 'SIM-A-003456', '2027-03-20', 'on_duty', 4.95),
('Dani Kusuma', 'DRV-004', '08544567890', 'SIM-A-004567', '2025-11-30', 'available', 4.80);

-- Ambulances
INSERT INTO ambulances (vehicle_number, vehicle_type, make, model, year, color, driver_id, status, latitude, longitude) VALUES
('B 1234 AID', 'ALS', 'Toyota', 'HiAce', 2022, 'White', 1, 'available', -7.2575, 112.7521),
('B 5678 AID', 'BLS', 'Mitsubishi', 'L300', 2021, 'White', 2, 'available', -7.2800, 112.7450),
('B 9012 AID', 'ALS', 'Toyota', 'HiAce', 2023, 'White', 3, 'on_duty', -7.2650, 112.7600),
('B 3456 AID', 'BLS', 'Isuzu', 'Elf', 2020, 'White', 4, 'available', -7.2900, 112.7350);

-- Sample subscriptions
INSERT INTO subscriptions (user_id, plan_id, status, start_date, end_date) VALUES
(1, 2, 'active', '2025-05-01', '2025-05-31'),
(2, 1, 'active', '2025-05-15', '2025-06-14'),
(3, 3, 'expired', '2025-04-01', '2025-04-30');

-- Sample emergency requests
INSERT INTO emergency_requests (request_code, user_id, ambulance_id, driver_id, hospital_id, pickup_address, patient_name, status, priority, created_at) VALUES
('REQ-2025-001', 1, 1, 1, 1, 'Jl. Raya Darmo No. 10, Surabaya', 'Budi Santoso', 'completed', 'high', '2025-05-20 08:30:00'),
('REQ-2025-002', 2, 2, 2, 2, 'Jl. Ahmad Yani No. 25, Surabaya', 'Siti Rahayu', 'completed', 'medium', '2025-05-22 14:15:00'),
('REQ-2025-003', 1, NULL, NULL, NULL, 'Jl. Pemuda No. 15, Surabaya', 'Budi Santoso', 'pending', 'high', NOW());

-- Sample payments
INSERT INTO payments (invoice_number, user_id, subscription_id, amount, payment_method, status, paid_at) VALUES
('INV-2025-0001', 1, 1, 75000, 'bank_transfer', 'paid', '2025-05-01 10:00:00'),
('INV-2025-0002', 2, 2, 25000, 'e_wallet', 'paid', '2025-05-15 11:30:00'),
('INV-2025-0003', 3, 3, 150000, 'credit_card', 'paid', '2025-04-01 09:00:00');

-- Sample notifications
INSERT INTO notifications (user_id, title, message, type) VALUES
(1, 'Subscription Active', 'Your Premium subscription is now active until May 31, 2025.', 'success'),
(1, 'Emergency Request Completed', 'Your emergency request REQ-2025-001 has been completed.', 'info'),
(2, 'Subscription Expiring Soon', 'Your Basic subscription expires in 3 days. Renew now!', 'warning');
