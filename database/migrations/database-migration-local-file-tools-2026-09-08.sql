-- EnoughEdu local image and PDF tools
-- Import once in phpMyAdmin after the earlier dated migrations.
SET NAMES utf8mb4;

ALTER TABLE tools
MODIFY category ENUM(
    'academic',
    'career',
    'writing',
    'productivity',
    'engineering',
    'media',
    'ai'
) NOT NULL;

INSERT INTO
    tools (
        name,
        slug,
        category,
        description,
        price,
        is_free,
        is_featured,
        status
    )
VALUES
    (
        'Image Format Converter',
        'image-format-converter',
        'media',
        'Convert JPG, PNG and WebP images privately in your browser.',
        79,
        0,
        1,
        'active'
    ),
    (
        'Image Resizer & Compressor',
        'image-resizer-compressor',
        'media',
        'Resize and compress JPG, PNG or WebP images without uploading them.',
        79,
        0,
        0,
        'active'
    ),
    (
        'Image to PDF',
        'image-to-pdf',
        'media',
        'Combine JPG, PNG and WebP images into one downloadable PDF locally.',
        99,
        0,
        1,
        'active'
    ),
    (
        'PDF Merger',
        'pdf-merger',
        'media',
        'Combine multiple PDF files in your chosen order without uploading them.',
        99,
        0,
        1,
        'active'
    ),
    (
        'PDF Splitter',
        'pdf-splitter',
        'media',
        'Extract selected pages into a new PDF entirely in your browser.',
        79,
        0,
        0,
        'active'
    )
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    description = VALUES(description),
    status = 'active';
