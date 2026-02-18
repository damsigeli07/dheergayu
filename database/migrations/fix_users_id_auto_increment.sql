-- Fix users.id so it auto-increments (stops new users getting id = 0).
-- Run this once in MySQL (e.g. phpMyAdmin) on database dheergayu_db.
-- If you already have rows with id = 0, run the UPDATE block first (once per 0 row), then the ALTER.

-- 1) Fix any existing rows with id = 0 (give them a unique id above current max)
SET @next_id = (SELECT COALESCE(MAX(id), 0) + 1 FROM users);
UPDATE users SET id = @next_id WHERE id = 0 LIMIT 1;
-- If you had multiple rows with id = 0, run the two lines above again until no rows are updated.

-- 2) Make id auto-increment so new inserts get the next id
ALTER TABLE users MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT;
