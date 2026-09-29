-- EnoughEdu: retire the standalone AI Hub without deleting historical purchase/payment records.
START TRANSACTION;

UPDATE tools
SET
    status = 'archived',
    is_featured = 0
WHERE
    category = 'ai'
    OR slug IN (
        'ai-notes-summarizer',
        'ai-study-planner',
        'ai-doubt-solver',
        'ai-flashcard-generator',
        'ai-quiz-generator',
        'ai-resume-optimizer',
        'ai-study-assistant'
    );

UPDATE plans
SET
    features = JSON_ARRAY(
        'All tools',
        'All resources',
        'Gemini-assisted career tools',
        'Career suite'
    )
WHERE
    slug = 'degree-pro';

UPDATE settings
SET
    setting_value = 'Tools, notes, planners, Gemini-assisted career utilities and academic resources in one organised platform.'
WHERE
    setting_key = 'homepage_subtitle'
    AND setting_value LIKE '%AI utilities%';

COMMIT;
