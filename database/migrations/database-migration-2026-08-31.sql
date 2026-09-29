-- Apply this once if database.sql was imported before the resource-link manager was added.
ALTER TABLE notes
ADD COLUMN external_url VARCHAR(500) NULL AFTER file_path;
