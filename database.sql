-- Hospital Management System Database
-- Compatible with MySQL / MariaDB (XAMPP)
-- Procedural style, no foreign key cascades that break on XAMPP older versions

CREATE DATABASE IF NOT EXISTS hospital_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hospital_management;

-- Users table (all roles)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin','doctor','patient','receptionist') NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    gender ENUM('male','female','other') DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    specialization VARCHAR(100) DEFAULT NULL, -- for doctors
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Doctors availability / schedule
CREATE TABLE IF NOT EXISTS doctor_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_patients INT DEFAULT 20,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (doctor_id)
) ENGINE=InnoDB;

-- Patients extra profile (linked to users)
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    blood_group VARCHAR(5) DEFAULT NULL,
    emergency_contact VARCHAR(20) DEFAULT NULL,
    allergies TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id)
) ENGINE=InnoDB;

-- Appointments
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT DEFAULT NULL,
    status ENUM('pending','confirmed','completed','cancelled','no-show') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL, -- receptionist or patient
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (patient_id),
    INDEX (doctor_id),
    INDEX (appointment_date)
) ENGINE=InnoDB;

-- Queue management (for receptionist)
CREATE TABLE IF NOT EXISTS queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT DEFAULT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    queue_number INT NOT NULL,
    queue_date DATE NOT NULL,
    status ENUM('waiting','called','in-progress','completed','skipped') DEFAULT 'waiting',
    priority ENUM('normal','urgent') DEFAULT 'normal',
    called_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (queue_date),
    INDEX (doctor_id)
) ENGINE=InnoDB;

-- Prescriptions
CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT DEFAULT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    diagnosis TEXT DEFAULT NULL,
    medicines TEXT NOT NULL, -- JSON or plain text list
    dosage_instructions TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    prescribed_date DATE NOT NULL,
    status ENUM('active','completed','cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (patient_id),
    INDEX (doctor_id)
) ENGINE=InnoDB;

-- Medical History
CREATE TABLE IF NOT EXISTS medical_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT DEFAULT NULL,
    record_type ENUM('diagnosis','lab','surgery','allergy','vaccination','other') DEFAULT 'diagnosis',
    title VARCHAR(150) NOT NULL,
    description TEXT,
    record_date DATE NOT NULL,
    attachment VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (patient_id)
) ENGINE=InnoDB;

-- Invoices
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(30) NOT NULL UNIQUE,
    patient_id INT NOT NULL,
    appointment_id INT DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax DECIMAL(10,2) DEFAULT 0.00,
    discount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    status ENUM('unpaid','partial','paid','cancelled') DEFAULT 'unpaid',
    due_date DATE DEFAULT NULL,
    created_by INT DEFAULT NULL, -- receptionist
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (patient_id)
) ENGINE=InnoDB;

-- Payments
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    patient_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','card','online','insurance') DEFAULT 'cash',
    transaction_ref VARCHAR(100) DEFAULT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    received_by INT DEFAULT NULL, -- receptionist
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (invoice_id),
    INDEX (patient_id)
) ENGINE=InnoDB;



-- Doctor Reviews / Ratings (patient -> doctor)
CREATE TABLE IF NOT EXISTS doctor_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    patient_id INT NOT NULL,
    appointment_id INT DEFAULT NULL,
    rating TINYINT NOT NULL,
    review TEXT DEFAULT NULL,
    status ENUM('visible','hidden') DEFAULT 'visible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (doctor_id),
    INDEX (patient_id)
) ENGINE=InnoDB;

-- Queue alerts (receptionist can alert a patient/queue entry)
CREATE TABLE IF NOT EXISTS queue_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    queue_id INT NOT NULL,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    alert_type ENUM('call','reminder','delay','custom') DEFAULT 'call',
    status ENUM('new','sent','dismissed') DEFAULT 'new',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (queue_id),
    INDEX (patient_id),
    INDEX (status)
) ENGINE=InnoDB;

-- Wheelchair assistance requests
CREATE TABLE IF NOT EXISTS wheelchair_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    appointment_id INT DEFAULT NULL,
    request_date DATE NOT NULL,
    location VARCHAR(150) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    status ENUM('requested','assigned','completed','cancelled') DEFAULT 'requested',
    assigned_to INT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (patient_id),
    INDEX (request_date),
    INDEX (status)
) ENGINE=InnoDB;

-- Notices / Announcements
CREATE TABLE IF NOT EXISTS notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    target_role ENUM('all','admin','doctor','patient','receptionist') DEFAULT 'all',
    priority ENUM('low','medium','high') DEFAULT 'medium',
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NOT NULL,
    expires_at DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (target_role)
) ENGINE=InnoDB;

-- Activity Logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (created_at)
) ENGINE=InnoDB;

-- Default Admin (password: admin123 - change after first login)
-- Password is password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, email, password, full_name, role, phone, status) VALUES
('admin', 'admin@hospital.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', '01700000000', 'active');

-- Sample Doctor
INSERT INTO users (username, email, password, full_name, role, phone, specialization, status) VALUES
('dr.smith', 'dr.smith@hospital.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. John Smith', 'doctor', '01711111111', 'General Medicine', 'active');

-- Sample Receptionist
INSERT INTO users (username, email, password, full_name, role, phone, status) VALUES
('reception', 'reception@hospital.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Receptionist', 'receptionist', '01722222222', 'active');

-- Sample Patient
INSERT INTO users (username, email, password, full_name, role, phone, gender, date_of_birth, status) VALUES
('patient1', 'patient1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Patient', 'patient', '01733333333', 'female', '1990-05-15', 'active');

INSERT INTO patients (user_id, blood_group, emergency_contact) VALUES (4, 'A+', '01799999999');

-- Sample availability for doctor (id=2)
INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, max_patients) VALUES
(2, 'Monday', '09:00:00', '13:00:00', 15),
(2, 'Wednesday', '14:00:00', '18:00:00', 15),
(2, 'Friday', '09:00:00', '12:00:00', 10);

-- Sample notice
INSERT INTO notices (title, content, target_role, priority, created_by) VALUES
('Welcome to Hospital Management System', 'This is a demo notice. Please change default passwords after first login.', 'all', 'high', 1);

-- Note: Default password for all sample users is: password
-- (The hash above is the well-known bcrypt hash for "password")
