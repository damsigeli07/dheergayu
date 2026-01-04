-- Migration: Add treatment_booking_id column to consultationforms table
-- This column will store the reference to the treatment booking for easier data retrieval

ALTER TABLE consultationforms ADD COLUMN treatment_booking_id INT NULL AFTER appointment_id;

-- Add foreign key constraint if treatment_bookings table exists
ALTER TABLE consultationforms ADD CONSTRAINT fk_consultation_booking 
FOREIGN KEY (treatment_booking_id) REFERENCES treatment_bookings(booking_id) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Add index for faster lookups
CREATE INDEX idx_treatment_booking_id ON consultationforms(treatment_booking_id);
