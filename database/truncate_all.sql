-- ── Truncate all operational data ───────────────────────────
-- Clears all records while keeping predefined `symptoms` data.
-- Run this in phpMyAdmin on the `record_management` database.

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `activity_log`;
TRUNCATE TABLE `lab_results`;
TRUNCATE TABLE `patient_record`;
TRUNCATE TABLE `patient`;
TRUNCATE TABLE `medicine`;
TRUNCATE TABLE `medicine_stock`;
TRUNCATE TABLE `faculty_details`;
TRUNCATE TABLE `student_details`;

-- NOTE: `symptoms` is intentionally kept (predefined clinic data).
-- Uncomment below ONLY if you also want to clear symptoms:
-- TRUNCATE TABLE `symptoms`;

SET FOREIGN_KEY_CHECKS = 1;
