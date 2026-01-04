-- Migration: Create treatment_progress table
-- This table stores staff treatment session records and progress notes

CREATE TABLE IF NOT EXISTS treatment_progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    booking_id INT,
    progress_notes LONGTEXT COMMENT 'Treatment progress and observations',
    materials_used VARCHAR(255) COMMENT 'Materials used during treatment',
    patient_response VARCHAR(50) COMMENT 'Patient response: excellent, good, moderate, poor',
    observations JSON COMMENT 'Array of observations: no_allergies, normal_skin, relaxed, follow_protocol',
    additional_notes LONGTEXT COMMENT 'Optional additional notes',
    duration_minutes INT COMMENT 'Actual duration of session',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES treatment_bookings(booking_id) ON DELETE SET NULL,
    INDEX idx_appointment (appointment_id),
    INDEX idx_booking (booking_id),
    INDEX idx_created_at (created_at)
);
