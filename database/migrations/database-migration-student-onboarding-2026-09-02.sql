-- Run once on an existing EnoughEdu database before uploading the onboarding update.
ALTER TABLE users
ADD COLUMN university_id VARCHAR(80) NULL AFTER university,
ADD COLUMN university_city VARCHAR(100) NULL AFTER university_id,
ADD COLUMN university_region VARCHAR(120) NULL AFTER university_city,
ADD COLUMN study_goal VARCHAR(80) NULL AFTER graduation_year,
ADD COLUMN biggest_challenge VARCHAR(80) NULL AFTER study_goal,
ADD COLUMN learning_style VARCHAR(50) NULL AFTER biggest_challenge,
ADD COLUMN exam_timeline VARCHAR(40) NULL AFTER learning_style,
ADD COLUMN weekly_study_hours TINYINT UNSIGNED NULL AFTER study_goal,
ADD COLUMN preferred_study_time VARCHAR(40) NULL AFTER weekly_study_hours,
ADD COLUMN target_cgpa DECIMAL(3, 2) NULL AFTER preferred_study_time,
ADD COLUMN onboarding_completed_at DATETIME NULL AFTER target_cgpa;

-- Existing students keep their current dashboard. Only accounts registered after
-- this migration are sent through the new first-login onboarding flow.
UPDATE users
SET
    onboarding_completed_at = NOW()
WHERE
    role = 'student'
    AND onboarding_completed_at IS NULL;
