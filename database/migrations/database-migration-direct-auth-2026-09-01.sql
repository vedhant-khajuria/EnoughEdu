-- Removes the old email-verification requirement from an existing EnoughEdu database.
-- Back up the database, then import this file once in phpMyAdmin.
ALTER TABLE users
MODIFY status ENUM('pending', 'active', 'suspended') NOT NULL DEFAULT 'active';

UPDATE users
SET
    status = 'active',
    email_verified_at = COALESCE(email_verified_at, NOW()),
    email_verification_token = NULL
WHERE
    status = 'pending';
