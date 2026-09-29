-- EnoughEdu production schema for MySQL 8 / MariaDB 10.5+
-- Import from cPanel > phpMyAdmin after creating a database and user.
SET NAMES utf8mb4;

SET
    FOREIGN_KEY_CHECKS = 0;

CREATE TABLE branches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    short_name VARCHAR(30),
    description TEXT,
    icon VARCHAR(100),
    sort_order SMALLINT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    google_sub VARCHAR(255) NULL UNIQUE,
    role ENUM('student', 'editor', 'admin') DEFAULT 'student',
    status ENUM('pending', 'active', 'suspended') DEFAULT 'active',
    branch_id INT UNSIGNED NULL,
    semester TINYINT UNSIGNED DEFAULT 1,
    university VARCHAR(160),
    university_id VARCHAR(80),
    university_city VARCHAR(100),
    university_region VARCHAR(120),
    graduation_year YEAR,
    study_goal VARCHAR(80),
    biggest_challenge VARCHAR(80),
    learning_style VARCHAR(50),
    exam_timeline VARCHAR(40),
    weekly_study_hours TINYINT UNSIGNED,
    preferred_study_time VARCHAR(40),
    target_cgpa DECIMAL(3, 2),
    onboarding_completed_at DATETIME NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    bio VARCHAR(500),
    email_verified_at DATETIME NULL,
    email_verification_token VARCHAR(64),
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_status (status),
    CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES branches (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (token),
    CONSTRAINT fk_resets_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE tools (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    category ENUM(
        'academic',
        'career',
        'writing',
        'productivity',
        'engineering',
        'media',
        'ai'
    ) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    price DECIMAL(10, 2) DEFAULT 0.00,
    is_free BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    access_key VARCHAR(120),
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    sort_order SMALLINT DEFAULT 0,
    meta_title VARCHAR(190),
    meta_description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(220) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    branch_id INT UNSIGNED,
    semester TINYINT UNSIGNED,
    subject VARCHAR(160),
    note_type ENUM(
        'semester',
        'handwritten',
        'formula',
        'cheatsheet'
    ) DEFAULT 'semester',
    description TEXT,
    file_path VARCHAR(255),
    external_url VARCHAR(500),
    preview_path VARCHAR(255),
    file_size BIGINT UNSIGNED DEFAULT 0,
    download_count INT UNSIGNED DEFAULT 0,
    is_premium BOOLEAN DEFAULT FALSE,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    uploaded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT KEY ft_notes (title, subject, description),
    CONSTRAINT fk_notes_branch FOREIGN KEY (branch_id) REFERENCES branches (id) ON DELETE SET NULL,
    CONSTRAINT fk_notes_user FOREIGN KEY (uploaded_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE resources (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(220) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    branch_id INT UNSIGNED NULL,
    semester TINYINT UNSIGNED NULL,
    subject VARCHAR(160),
    resource_type ENUM(
        'paper',
        'lab_manual',
        'viva',
        'mini_project',
        'final_project',
        'coding',
        'interview',
        'internship',
        'other'
    ) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    external_url VARCHAR(500),
    thumbnail VARCHAR(255),
    is_premium BOOLEAN DEFAULT FALSE,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    download_count INT UNSIGNED DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT KEY ft_resources (title, subject, description),
    CONSTRAINT fk_resource_branch FOREIGN KEY (branch_id) REFERENCES branches (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    plan_type ENUM('tool', 'category', 'semester', 'branch', 'full') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    billing_period ENUM('one_time', 'semester', 'monthly', 'yearly') DEFAULT 'one_time',
    features JSON,
    limits_json JSON,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE coupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percent', 'fixed') NOT NULL,
    discount_value DECIMAL(10, 2) NOT NULL,
    min_order_value DECIMAL(10, 2) DEFAULT 0,
    max_uses INT UNSIGNED NULL,
    used_count INT UNSIGNED DEFAULT 0,
    starts_at DATETIME NULL,
    expires_at DATETIME NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    product_type VARCHAR(50) NOT NULL,
    product_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(190) NOT NULL,
    subtotal DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    amount DECIMAL(10, 2) NOT NULL,
    coupon_id BIGINT UNSIGNED NULL,
    status ENUM(
        'pending',
        'paid',
        'failed',
        'refunded',
        'cancelled'
    ) DEFAULT 'pending',
    payment_gateway VARCHAR(50) DEFAULT 'razorpay',
    gateway_reference VARCHAR(190),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (user_id, status),
    INDEX idx_orders_gateway_reference (payment_gateway, gateway_reference),
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_order_coupon FOREIGN KEY (coupon_id) REFERENCES coupons (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    transaction_id VARCHAR(100) NOT NULL UNIQUE,
    gateway_reference VARCHAR(190),
    amount DECIMAL(10, 2) NOT NULL,
    currency CHAR(3) DEFAULT 'INR',
    status ENUM('initiated', 'success', 'failed', 'refunded') DEFAULT 'initiated',
    gateway VARCHAR(50) DEFAULT 'razorpay',
    payment_method VARCHAR(50),
    response_json JSON,
    paid_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id, status),
    CONSTRAINT fk_payment_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NULL,
    order_id BIGINT UNSIGNED NULL,
    scope_type ENUM(
        'semester',
        'branch',
        'degree',
        'category',
        'tool'
    ) NOT NULL,
    scope_id VARCHAR(120),
    starts_at DATETIME NOT NULL,
    expires_at DATETIME NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    auto_renew BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (user_id, status, expires_at),
    CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_sub_plan FOREIGN KEY (plan_id) REFERENCES plans (id) ON DELETE SET NULL,
    CONSTRAINT fk_sub_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE user_tools (
    user_id BIGINT UNSIGNED NOT NULL,
    tool_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL,
    PRIMARY KEY (user_id, tool_id),
    CONSTRAINT fk_ut_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_ut_tool FOREIGN KEY (tool_id) REFERENCES tools (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

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

CREATE TABLE downloads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    item_type ENUM('note', 'resource') NOT NULL,
    item_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(45),
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id, downloaded_at),
    CONSTRAINT fk_download_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE bookmarks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    item_type ENUM('note', 'resource', 'tool') NOT NULL,
    item_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_bookmark (user_id, item_type, item_id),
    CONSTRAINT fk_bookmark_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE planner_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    description TEXT,
    task_type ENUM(
        'study',
        'assignment',
        'exam',
        'habit',
        'goal',
        'career'
    ) DEFAULT 'study',
    subject VARCHAR(160),
    due_at DATETIME NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('todo', 'doing', 'done') DEFAULT 'todo',
    completed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_task_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE exams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    subject VARCHAR(160),
    exam_at DATETIME NOT NULL,
    syllabus TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_exam_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE academic_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    semester TINYINT UNSIGNED NOT NULL,
    sgpa DECIMAL(4, 2),
    credits_earned SMALLINT UNSIGNED DEFAULT 0,
    total_credits SMALLINT UNSIGNED DEFAULT 0,
    backlogs TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_record (user_id, semester),
    CONSTRAINT fk_record_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description VARCHAR(255),
    icon VARCHAR(100),
    criteria_json JSON,
    points SMALLINT UNSIGNED DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE user_achievements (
    user_id BIGINT UNSIGNED NOT NULL,
    achievement_id INT UNSIGNED NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, achievement_id),
    CONSTRAINT fk_ua_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_ua_achievement FOREIGN KEY (achievement_id) REFERENCES achievements (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE activity_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(60),
    entity_id BIGINT UNSIGNED,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id, created_at),
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    action_url VARCHAR(500),
    read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id, read_at),
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE testimonials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    role VARCHAR(160),
    quote TEXT NOT NULL,
    avatar VARCHAR(255),
    rating TINYINT UNSIGNED DEFAULT 5,
    status ENUM('draft', 'published') DEFAULT 'draft',
    sort_order SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE faqs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(80) DEFAULT 'general',
    status ENUM('draft', 'published') DEFAULT 'published',
    sort_order SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(190) NOT NULL,
    subtitle VARCHAR(255),
    cta_text VARCHAR(80),
    cta_url VARCHAR(500),
    image_path VARCHAR(255),
    placement VARCHAR(80) DEFAULT 'homepage',
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    status ENUM('draft', 'active') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value LONGTEXT,
    setting_group VARCHAR(80) DEFAULT 'general',
    is_public BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE homepage_sections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(255),
    subtitle TEXT,
    content_json JSON,
    status ENUM('draft', 'published') DEFAULT 'published',
    sort_order SMALLINT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE community_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(220) NOT NULL,
    body TEXT NOT NULL,
    branch_id INT UNSIGNED NULL,
    status ENUM('pending', 'published', 'hidden') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_post_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_post_branch FOREIGN KEY (branch_id) REFERENCES branches (id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE contact_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
    branches (name, slug, short_name, sort_order)
VALUES
    ('Computer Science Engineering', 'cse', 'CSE', 1),
    (
        'Artificial Intelligence & Machine Learning',
        'ai-ml',
        'AI & ML',
        2
    ),
    ('Information Technology', 'it', 'IT', 3),
    ('Electronics & Communication', 'ece', 'ECE', 4),
    ('Mechanical Engineering', 'mechanical', 'ME', 5),
    ('Civil Engineering', 'civil', 'CE', 6),
    ('Electrical Engineering', 'electrical', 'EE', 7),
    ('Aerospace Engineering', 'aerospace', 'AE', 8),
    ('Data Science', 'data-science', 'DS', 9),
    ('Robotics', 'robotics', 'ROBO', 10),
    ('Mechatronics', 'mechatronics', 'MCT', 11);

INSERT INTO
    plans (
        name,
        slug,
        plan_type,
        price,
        billing_period,
        features
    )
VALUES
    (
        'Single Tool',
        'single-tool',
        'tool',
        99,
        'one_time',
        JSON_ARRAY('Lifetime tool access', 'Future updates')
    ),
    (
        'Semester Pass',
        'semester',
        'semester',
        499,
        'semester',
        JSON_ARRAY(
            'Semester resources',
            'Academic calculators',
            'Planners'
        )
    ),
    (
        'Branch Bundle',
        'branch',
        'branch',
        899,
        'yearly',
        JSON_ARRAY(
            'All eight semesters',
            'Branch tools',
            'Notes and papers'
        )
    ),
    (
        'Degree Pro',
        'degree-pro',
        'full',
        1499,
        'yearly',
        JSON_ARRAY(
            'All tools',
            'All resources',
            'Gemini-assisted career tools',
            'Career suite'
        )
    );

INSERT INTO
    tools (
        name,
        slug,
        category,
        price,
        is_free,
        is_featured,
        status
    )
VALUES
    (
        'CGPA Calculator',
        'cgpa-calculator',
        'academic',
        0,
        1,
        1,
        'active'
    ),
    (
        'SGPA Calculator',
        'sgpa-calculator',
        'academic',
        0,
        1,
        0,
        'active'
    ),
    (
        'Attendance Calculator',
        'attendance-calculator',
        'academic',
        0,
        1,
        1,
        'active'
    ),
    (
        'Resume Builder',
        'resume-builder',
        'career',
        199,
        0,
        1,
        'active'
    ),
    (
        'ATS Resume Checker',
        'ats-resume-checker',
        'career',
        99,
        0,
        0,
        'active'
    ),
    (
        'Semester Planner',
        'semester-planner',
        'productivity',
        149,
        0,
        1,
        'active'
    ),
    (
        'Engineering Formula Library',
        'formula-library',
        'engineering',
        99,
        0,
        0,
        'active'
    );

INSERT INTO
    tools (
        name,
        slug,
        category,
        price,
        is_free,
        is_featured,
        status
    )
VALUES
    (
        'CGPA Calculator',
        'cgpa-calculator',
        'academic',
        99,
        0,
        1,
        'active'
    ),
    (
        'SGPA Calculator',
        'sgpa-calculator',
        'academic',
        99,
        0,
        0,
        'active'
    ),
    (
        'Percentage Calculator',
        'percentage-calculator',
        'academic',
        49,
        0,
        0,
        'active'
    ),
    (
        'Attendance Calculator',
        'attendance-calculator',
        'academic',
        49,
        0,
        1,
        'active'
    ),
    (
        'GPA Converter',
        'gpa-converter',
        'academic',
        49,
        0,
        0,
        'active'
    ),
    (
        'Marks Predictor',
        'marks-predictor',
        'academic',
        79,
        0,
        0,
        'active'
    ),
    (
        'Backlog Impact Calculator',
        'backlog-impact-calculator',
        'academic',
        79,
        0,
        0,
        'active'
    ),
    (
        'Resume Builder',
        'resume-builder',
        'career',
        199,
        0,
        1,
        'active'
    ),
    (
        'ATS Resume Checker',
        'ats-resume-checker',
        'career',
        99,
        0,
        0,
        'active'
    ),
    (
        'LinkedIn Profile Generator',
        'linkedin-generator',
        'career',
        99,
        0,
        0,
        'active'
    ),
    (
        'Cover Letter Generator',
        'cover-letter-generator',
        'career',
        79,
        0,
        0,
        'active'
    ),
    (
        'Originality Rewriter',
        'plagiarism-remover',
        'writing',
        99,
        0,
        0,
        'active'
    ),
    (
        'Grammar Corrector',
        'grammar-corrector',
        'writing',
        79,
        0,
        0,
        'active'
    ),
    (
        'Paraphrasing Tool',
        'paraphrasing-tool',
        'writing',
        79,
        0,
        0,
        'active'
    ),
    (
        'Citation Generator',
        'citation-generator',
        'writing',
        49,
        0,
        0,
        'active'
    ),
    (
        'Semester Planner',
        'semester-planner',
        'productivity',
        149,
        0,
        1,
        'active'
    ),
    (
        'Daily Study Planner',
        'daily-study-planner',
        'productivity',
        79,
        0,
        0,
        'active'
    ),
    (
        'Assignment Tracker',
        'assignment-tracker',
        'productivity',
        79,
        0,
        0,
        'active'
    ),
    (
        'Habit Tracker',
        'habit-tracker',
        'productivity',
        49,
        0,
        0,
        'active'
    ),
    (
        'Goal Tracker',
        'goal-tracker',
        'productivity',
        49,
        0,
        0,
        'active'
    ),
    (
        'Pomodoro Timer',
        'pomodoro-timer',
        'productivity',
        49,
        0,
        0,
        'active'
    ),
    (
        'Exam Countdown',
        'exam-countdown',
        'productivity',
        49,
        0,
        0,
        'active'
    ),
    (
        'Engineering Unit Converter',
        'unit-converter',
        'engineering',
        49,
        0,
        0,
        'active'
    ),
    (
        'Engineering Formula Library',
        'formula-library',
        'engineering',
        99,
        0,
        0,
        'active'
    ),
    (
        'Scientific Calculator',
        'scientific-calculator',
        'engineering',
        79,
        0,
        0,
        'active'
    ),
    (
        'Semester GPA Planner',
        'semester-gpa-planner',
        'engineering',
        79,
        0,
        0,
        'active'
    ),
    (
        'Image Format Converter',
        'image-format-converter',
        'media',
        79,
        0,
        1,
        'active'
    ),
    (
        'Image Resizer & Compressor',
        'image-resizer-compressor',
        'media',
        79,
        0,
        0,
        'active'
    ),
    (
        'Image to PDF',
        'image-to-pdf',
        'media',
        99,
        0,
        1,
        'active'
    ),
    (
        'PDF Merger',
        'pdf-merger',
        'media',
        99,
        0,
        1,
        'active'
    ),
    (
        'PDF Splitter',
        'pdf-splitter',
        'media',
        79,
        0,
        0,
        'active'
    )
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    price = VALUES(price),
    is_free = 0,
    status = 'active';

INSERT INTO
    settings (
        setting_key,
        setting_value,
        setting_group,
        is_public
    )
VALUES
    ('site_name', 'EnoughEdu', 'general', 1),
    ('support_email', 'hello@vedhant.in', 'general', 1),
    ('currency', 'INR', 'payments', 1),
    ('homepage_students_count', '0', 'homepage', 1),
    (
        'announcement_text',
        'An engineering student toolkit by Vedhant Khajuria',
        'homepage',
        1
    ),
    (
        'homepage_headline',
        'Tools and resources for your engineering degree',
        'homepage',
        1
    ),
    (
        'homepage_subtitle',
        'Tools, notes, planners, Gemini-assisted career utilities and academic resources in one organised platform.',
        'homepage',
        1
    ),
    (
        'subscription_expiry_reminder_days',
        '7',
        'email',
        0
    );

INSERT INTO
    faqs (question, answer, sort_order)
VALUES
    (
        'Can I buy one tool?',
        'Yes. Tools can be purchased individually with lifetime access.',
        1
    ),
    (
        'Can I cancel a subscription?',
        'You can stop renewal any time; access continues until expiry.',
        2
    );

INSERT INTO
    achievements (name, slug, description, icon, points)
VALUES
    (
        'Study Streak',
        'study-streak',
        'Complete a study task seven days in a row',
        '🔥',
        100
    ),
    (
        'Note Ninja',
        'note-ninja',
        'Complete twenty focused study sessions',
        '📚',
        150
    ),
    (
        'Planner Pro',
        'planner-pro',
        'Finish ten planned tasks on time',
        '🎯',
        120
    );

SET
    FOREIGN_KEY_CHECKS = 1;

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
