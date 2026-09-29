-- EnoughEdu: individual note/resource pricing and purchase grants.
-- Import once on an existing database after the earlier migrations.
ALTER TABLE notes
ADD COLUMN price DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER is_premium;

ALTER TABLE resources
ADD COLUMN price DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER is_premium;

CREATE TABLE user_materials (
    user_id BIGINT UNSIGNED NOT NULL,
    material_type ENUM('note', 'resource') NOT NULL,
    material_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, material_type, material_id),
    INDEX idx_user_material_order (order_id),
    CONSTRAINT fk_um_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_um_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
