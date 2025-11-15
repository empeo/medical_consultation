-- =========================================================
--  Medical Consultation Database Schema (with specialties)
-- =========================================================

-- 1) Create Database
CREATE DATABASE IF NOT EXISTS medical_consultation
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE medical_consultation;

-- =========================================================
-- 2) Specialties Table (مرجع التخصصات)
-- =========================================================
CREATE TABLE specialties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert base specialties
INSERT INTO specialties (code, name_en, name_ar) VALUES
('CARDIO',  'Cardiology',     'القلب'),
('DERMA',   'Dermatology',    'الجلدية'),
('PED',     'Pediatrics',     'الأطفال'),
('ORTHO',   'Orthopedics',    'العظام'),
('NEURO',   'Neurology',      'الأعصاب'),
('PSY',     'Psychiatry',     'الطب النفسي'),
('URO',     'Urology',        'المسالك البولية'),
('GYN',     'Gynecology',     'النساء والتوليد'),
('OPHTHA',  'Ophthalmology',  'العيون'),
('ENT',     'ENT',            'الأنف والأذن والحنجرة');

-- =========================================================
-- 3) Users Table
-- =========================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor', 'superadmin') DEFAULT 'patient',
    specialty_id INT NULL,  -- دكتور ممكن يكون ليه تخصص، المريض غالباً NULL
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_specialty
        FOREIGN KEY (specialty_id) REFERENCES specialties(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 4) Questions Table
-- =========================================================
CREATE TABLE questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    specialty_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_questions_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_questions_specialty
        FOREIGN KEY (specialty_id) REFERENCES specialties(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 5) Answers Table
-- =========================================================
CREATE TABLE answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question_id INT NOT NULL,
    doctor_id INT NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_answers_question
        FOREIGN KEY (question_id) REFERENCES questions(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_answers_doctor
        FOREIGN KEY (doctor_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 6) Sample Data (Admin + Doctors + Patients)
-- =========================================================

-- Super Admin
INSERT INTO users (name, email, password, role)
VALUES (
    'Admin',
    'admin@medical.com',
    '$2y$10$i0hfVOpaNHIYxCI8ez/fdeIn2Kcwa7dpgJrWcZRPOOLYvAvX5I5yq',
    'superadmin'
);

-- Sample Doctors (linked to specialties by code)
INSERT INTO users (name, email, password, role, specialty_id) VALUES
(
    'Dr. Ahmed Hassan',
    'ahmed@medical.com',
    '$2y$10$i0hfVOpaNHIYxCI8ez/fdeIn2Kcwa7dpgJrWcZRPOOLYvAvX5I5yq',
    'doctor',
    (SELECT id FROM specialties WHERE code = 'CARDIO')
),
(
    'Dr. Sarah Mohamed',
    'sarah@medical.com',
    '$2y$10$i0hfVOpaNHIYxCI8ez/fdeIn2Kcwa7dpgJrWcZRPOOLYvAvX5I5yq',
    'doctor',
    (SELECT id FROM specialties WHERE code = 'PED')
);

-- Sample Patients
INSERT INTO users (name, email, password, role) VALUES
(
    'John Doe',
    'john@example.com',
    '$2y$10$i0hfVOpaNHIYxCI8ez/fdeIn2Kcwa7dpgJrWcZRPOOLYvAvX5I5yq',
    'patient'
),
(
    'Jane Smith',
    'jane@example.com',
    '$2y$10$i0hfVOpaNHIYxCI8ez/fdeIn2Kcwa7dpgJrWcZRPOOLYvAvX5I5yq',
    'patient'
);
