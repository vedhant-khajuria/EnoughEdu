-- Add the tables used by the resource library. Safe to rerun on this schema.
CREATE TABLE IF NOT EXISTS resource_branches (
    material_kind ENUM('note', 'resource') NOT NULL,
    material_id BIGINT UNSIGNED NOT NULL,
    branch_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (material_kind, material_id, branch_id),
    INDEX (branch_id),
    CONSTRAINT fk_library_branch FOREIGN KEY (branch_id) REFERENCES branches (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS resource_files (
    material_kind ENUM('note', 'resource') NOT NULL,
    material_id BIGINT UNSIGNED NOT NULL,
    pdf_name VARCHAR(190) NOT NULL,
    preview_key CHAR(64) NOT NULL,
    page_count SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (material_kind, material_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS resource_memberships (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    gateway_id VARCHAR(100) NULL UNIQUE,
    plan_id VARCHAR(100) NOT NULL,
    amount_paise INT UNSIGNED NOT NULL,
    status VARCHAR(30) NOT NULL,
    trial_end BIGINT UNSIGNED NULL,
    consent_at BIGINT UNSIGNED NOT NULL,
    access_until BIGINT UNSIGNED NOT NULL DEFAULT 0,
    setup_payment_id VARCHAR(100) NULL,
    created_at BIGINT UNSIGNED NOT NULL,
    updated_at BIGINT UNSIGNED NOT NULL,
    INDEX (user_id, id),
    CONSTRAINT fk_resource_member FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS resource_payment_receipts (
    payment_id VARCHAR(100) PRIMARY KEY,
    membership_id BIGINT UNSIGNED NOT NULL,
    amount_paise INT UNSIGNED NOT NULL,
    paid_at BIGINT UNSIGNED NOT NULL,
    access_until BIGINT UNSIGNED NOT NULL DEFAULT 0,
    INDEX (membership_id),
    CONSTRAINT fk_resource_receipt FOREIGN KEY (membership_id) REFERENCES resource_memberships (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT IGNORE INTO
    resource_branches (material_kind, material_id, branch_id)
SELECT
    'note',
    id,
    branch_id
FROM
    notes
WHERE
    branch_id IS NOT NULL;

INSERT IGNORE INTO
    resource_branches (material_kind, material_id, branch_id)
SELECT
    'resource',
    id,
    branch_id
FROM
    resources
WHERE
    branch_id IS NOT NULL;
