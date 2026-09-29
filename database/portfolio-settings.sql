-- Run only on the new portfolio database, after importing its data.
INSERT INTO
    settings (
        setting_key,
        setting_value,
        setting_group,
        is_public
    )
VALUES
    ('support_email', 'hello@vedhant.in', 'general', 1),
    ('android_app_url', '', 'site', 1),
    ('resource_membership_enabled', '0', 'payments', 0)
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value);

-- Update text settings, including page content saved through the admin editor.
UPDATE settings
SET
    setting_value =
REPLACE
    (
        REPLACE
            (
                REPLACE
                    (
                        setting_value,
                        'info@enoughedu.in',
                        'hello@vedhant.in'
                    ),
                    'support@enoughedu.in',
                    'hello@vedhant.in'
            ),
            'https://enoughedu.in',
            'https://enoughedu.vedhant.in'
    );
