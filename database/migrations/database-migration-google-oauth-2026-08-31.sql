-- Apply this once if database.sql was imported before Google sign-in was added.
ALTER TABLE users
ADD COLUMN google_sub VARCHAR(255) NULL UNIQUE AFTER password;
