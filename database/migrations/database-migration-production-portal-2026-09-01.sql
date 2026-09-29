-- Converts an existing EnoughEdu installation from demo-style pages to the live portal catalogue.
-- Back up the database, then import this file once in phpMyAdmin.
INSERT INTO
    settings (
        setting_key,
        setting_value,
        setting_group,
        is_public
    )
VALUES
    (
        'announcement_text',
        'Built for ambitious engineering students',
        'homepage',
        1
    ),
    (
        'homepage_headline',
        'Everything an Engineering Student Needs for the Entire Degree',
        'homepage',
        1
    ),
    (
        'homepage_subtitle',
        'Tools, notes, planners, Gemini-assisted career utilities and academic resources in one organised platform.',
        'homepage',
        1
    )
ON DUPLICATE KEY UPDATE
    setting_value = VALUES(setting_value),
    is_public = 1;

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
        'AI Notes Summarizer',
        'ai-notes-summarizer',
        'ai',
        149,
        0,
        1,
        'active'
    ),
    (
        'AI Study Planner',
        'ai-study-planner',
        'ai',
        149,
        0,
        0,
        'active'
    ),
    (
        'AI Doubt Solver',
        'ai-doubt-solver',
        'ai',
        149,
        0,
        0,
        'active'
    ),
    (
        'AI Flashcard Generator',
        'ai-flashcard-generator',
        'ai',
        149,
        0,
        0,
        'active'
    ),
    (
        'AI Quiz Generator',
        'ai-quiz-generator',
        'ai',
        149,
        0,
        0,
        'active'
    ),
    (
        'AI Resume Optimizer',
        'ai-resume-optimizer',
        'ai',
        149,
        0,
        0,
        'active'
    ),
    (
        'AI Study Assistant',
        'ai-study-assistant',
        'ai',
        149,
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
