-- ── Lab Results table ────────────────────────────────────────
-- Run this in phpMyAdmin on the `record_management` database

CREATE TABLE IF NOT EXISTS `lab_results` (
  `lab_id`      int(11)      NOT NULL AUTO_INCREMENT,
  `patient_id`  int(11)      NOT NULL,
  `lab_type`    varchar(100) NOT NULL COMMENT 'X-Ray, ECG, Blood Test, etc.',
  `file_name`   varchar(255) NOT NULL COMMENT 'Original filename shown to user',
  `file_path`   varchar(500) NOT NULL COMMENT 'Stored filename on disk',
  `file_size`   int(11)      DEFAULT NULL COMMENT 'Size in bytes',
  `file_mime`   varchar(100) DEFAULT NULL COMMENT 'MIME type',
  `notes`       text         DEFAULT NULL,
  `uploaded_by` varchar(100) NOT NULL DEFAULT 'admin',
  `dateCreated` timestamp    NOT NULL DEFAULT current_timestamp(),
  `dateDeleted` timestamp    NULL DEFAULT NULL,
  PRIMARY KEY (`lab_id`),
  KEY `idx_patient_id` (`patient_id`),
  KEY `idx_lab_type`   (`lab_type`),
  CONSTRAINT `lab_results_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
