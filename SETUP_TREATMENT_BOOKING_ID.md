# Setup Instructions for treatment_booking_id Persistence

## Database Migration

Run the following SQL migration to add the `treatment_booking_id` column to the `consultationforms` table:

```sql
ALTER TABLE consultationforms ADD COLUMN treatment_booking_id INT NULL AFTER appointment_id;

ALTER TABLE consultationforms ADD CONSTRAINT fk_consultation_booking 
FOREIGN KEY (treatment_booking_id) REFERENCES treatment_bookings(booking_id) 
ON DELETE SET NULL ON UPDATE CASCADE;

CREATE INDEX idx_treatment_booking_id ON consultationforms(treatment_booking_id);
```

Or execute the migration file:
```bash
mysql -u root -p dheergayu_db < database/migrations/add_treatment_booking_id_to_consultationforms.sql
```

## What Changed

### 1. ConsultationFormModel.php
- **INSERT**: Added `treatment_booking_id` column to the INSERT statement
- **UPDATE**: Added `treatment_booking_id` column to the UPDATE statement
- Both now handle `treatment_booking_id` being passed from the controller

### 2. ConsultationFormController.php (No changes needed)
- Already captures `treatment_booking_id` from POST data or session
- Passes it to the model automatically

### 3. AppointmentModel.php
- **getStaffAppointmentsWithConsultationsAndBookings()**: Updated to query `treatment_booking_id` directly from the `consultationforms` table
- Removed regex-based parsing of "Booking #123" from `recommended_treatment`
- Now uses direct foreign key lookup for cleaner, more reliable data retrieval

### 4. staffappointment.php
- No changes needed - continues to use the enriched appointment data with `booking_date` and `slot_time`
- Start button logic remains the same (checks if booking_date >= today)

## Benefits

✅ **Cleaner data model**: `treatment_booking_id` is now a proper database field with foreign key  
✅ **Faster queries**: Direct lookups instead of regex parsing  
✅ **Better referential integrity**: Foreign key constraint ensures data consistency  
✅ **Easier maintenance**: No need to parse strings from `recommended_treatment`  
✅ **Backward compatible**: Fallback to `appointment_datetime` if booking details are missing  

## Testing

After applying the migration, all consultation forms created/updated going forward will persist the `treatment_booking_id`. The staff appointment page will correctly display booking date/time and activate Start buttons for upcoming bookings.
