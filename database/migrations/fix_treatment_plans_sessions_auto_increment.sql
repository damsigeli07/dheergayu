-- Fix treatment_plans.plan_id and treatment_sessions.session_id so they auto-increment.
-- Run once in MySQL on dheergayu_db if you get "Duplicate entry '0' for key 'PRIMARY'".

-- 1) treatment_plans: fix any plan_id = 0 then add AUTO_INCREMENT
SET @next_plan = (SELECT COALESCE(MAX(plan_id), 0) + 1 FROM treatment_plans);
UPDATE treatment_plans SET plan_id = @next_plan WHERE plan_id = 0 LIMIT 1;
ALTER TABLE treatment_plans MODIFY COLUMN plan_id INT NOT NULL AUTO_INCREMENT;

-- 2) treatment_sessions: fix any PK = 0 then add AUTO_INCREMENT
--    If your PK column is named 'id' instead of 'session_id', use: MAX(id), UPDATE id, MODIFY id.
SET @next_sess = (SELECT COALESCE(MAX(session_id), 0) + 1 FROM treatment_sessions);
UPDATE treatment_sessions SET session_id = @next_sess WHERE session_id = 0 LIMIT 1;
ALTER TABLE treatment_sessions MODIFY COLUMN session_id INT NOT NULL AUTO_INCREMENT;
