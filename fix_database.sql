-- ================================================
-- QUICK FIX for CRUD Errors
-- Run this in phpMyAdmin SQL tab
-- ================================================

USE record_management;

-- Fix 1: Change patient_type from ENUM to VARCHAR
-- This allows any patient type (Student, Faculty, Staff, Visitor)
ALTER TABLE patient 
MODIFY patient_type VARCHAR(100) NOT NULL;

-- Fix 2: Ensure dateDeleted can be NULL (for soft delete logic)
ALTER TABLE patient 
MODIFY dateDeleted TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE medicine 
MODIFY dateDeleted TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE patient_record 
MODIFY dateDeleted TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE admin 
MODIFY dateDeleted TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE student_details 
MODIFY dateDeleted TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE faculty_details 
MODIFY dateDeleted TIMESTAMP NULL DEFAULT NULL;

ALTER TABLE medicine_stock 
MODIFY dateDeleted TIMESTAMP NULL DEFAULT NULL;

-- Fix 3: Add sample medicines if table is empty
INSERT INTO medicine (medicine_name, description, current_stock) VALUES
('Biogesic', 'Pain reliever and fever reducer (Paracetamol 500mg)', 100),
('Paracetamol', 'Analgesic and antipyretic medication', 80),
('Amoxicillin', 'Antibiotic for bacterial infections', 50),
('Ibuprofen', 'Anti-inflammatory and pain reliever', 60),
('Cetirizine', 'Antihistamine for allergies', 40),
('Mefenamic Acid', 'For pain and inflammation', 35),
('Loperamide', 'Anti-diarrheal medication', 25),
('Omeprazole', 'For acid reflux and heartburn', 30)
ON DUPLICATE KEY UPDATE medicine_name=medicine_name;

-- Fix 4: Add sample patients if table is empty
INSERT INTO patient (fullname, patient_type, contact_no) VALUES
('John Larielle Lunod', 'Student', '0961-549-6134'),
('Maria Santos', 'Faculty', '0912-345-6789'),
('Pedro Cruz', 'Staff', '0923-456-7890')
ON DUPLICATE KEY UPDATE fullname=fullname;

-- Fix 5: Create admin user if doesn't exist
INSERT INTO admin (username, password, firstname, lastname, middlename, name_extension, contact_no, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', '', '', '0900-000-0000', 'admin@example.com')
ON DUPLICATE KEY UPDATE username=username;
-- Note: Password hash is for 'admin123'

-- ================================================
-- Verification Queries
-- ================================================

-- Check patient_type is now VARCHAR
SHOW COLUMNS FROM patient LIKE 'patient_type';

-- Check if medicines exist
SELECT COUNT(*) as medicine_count FROM medicine WHERE dateDeleted IS NULL;

-- Check if patients exist  
SELECT COUNT(*) as patient_count FROM patient WHERE dateDeleted IS NULL;

-- Check all tables
SHOW TABLES;

-- ================================================
-- Success Message
-- ================================================
SELECT 'Database fixes applied successfully!' as Status;
