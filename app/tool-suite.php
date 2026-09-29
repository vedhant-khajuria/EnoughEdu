<?php
declare(strict_types=1);
require_once __DIR__ . '/tool-experience.php';

function tool_definition(string $slug): ?array
{
    $definitions = [
        'cgpa-calculator' => [
            'CGPA Calculator',
            'Calculate weighted CGPA using grade points and credits.',
            'grades',
        ],
        'sgpa-calculator' => [
            'SGPA Calculator',
            'Calculate your semester grade point average.',
            'grades',
        ],
        'percentage-calculator' => [
            'Percentage Calculator',
            'Calculate percentage, score and grade instantly.',
            'percentage',
        ],
        'attendance-calculator' => [
            'Attendance Calculator',
            'Know how many classes you must attend—or can safely miss.',
            'attendance',
        ],
        'gpa-converter' => [
            'GPA Converter',
            'Convert between 10-point, 4-point and percentage scales.',
            'gpa',
        ],
        'marks-predictor' => [
            'Marks Predictor',
            'Estimate your final score from internal and external assessments.',
            'marks',
        ],
        'backlog-impact-calculator' => [
            'Backlog Impact Calculator',
            'Estimate how clearing a backlog changes your CGPA.',
            'backlog',
        ],
        'resume-builder' => [
            'Resume Builder',
            'Generate a clear ATS-friendly engineering resume.',
            'resume',
        ],
        'ats-resume-checker' => [
            'ATS Resume Checker',
            'Check structure, keywords, readability and measurable impact.',
            'ats',
        ],
        'linkedin-generator' => [
            'LinkedIn Profile Generator',
            'Create a focused headline, About section and skills list.',
            'linkedin',
        ],
        'cover-letter-generator' => [
            'Cover Letter Generator',
            'Draft a tailored application in seconds.',
            'cover',
        ],
        'plagiarism-remover' => [
            'Originality Rewriter',
            'Rewrite supplied text in a clearer original structure.',
            'rewrite',
        ],
        'grammar-corrector' => [
            'Grammar Corrector',
            'Fix common grammar, spacing and punctuation issues.',
            'grammar',
        ],
        'paraphrasing-tool' => [
            'Paraphrasing Tool',
            'Rephrase text while preserving its meaning.',
            'paraphrase',
        ],
        'citation-generator' => [
            'Citation Generator',
            'Generate APA, MLA or IEEE citations.',
            'citation',
        ],
        'semester-planner' => [
            'Semester Planner',
            'Create and track semester milestones locally.',
            'tracker',
        ],
        'daily-study-planner' => [
            'Daily Study Planner',
            'Build a focused study plan for today.',
            'tracker',
        ],
        'assignment-tracker' => [
            'Assignment Tracker',
            'Track assignments, deadlines and completion.',
            'tracker',
        ],
        'habit-tracker' => ['Habit Tracker', 'Build consistent academic habits.', 'tracker'],
        'goal-tracker' => ['Goal Tracker', 'Turn degree goals into visible progress.', 'tracker'],
        'pomodoro-timer' => [
            'Pomodoro Timer',
            'Use focused work and recovery intervals.',
            'pomodoro',
        ],
        'exam-countdown' => [
            'Exam Countdown',
            'Track time remaining until your next exam.',
            'countdown',
        ],
        'unit-converter' => [
            'Engineering Unit Converter',
            'Convert across a comprehensive practical library of engineering units.',
            'units',
        ],
        'formula-library' => [
            'Engineering Formula Library',
            'Search a comprehensive formula reference across engineering disciplines.',
            'formulas',
        ],
        'scientific-calculator' => [
            'Scientific Calculator',
            'Calculate scientific expressions, powers and trigonometry.',
            'scientific',
        ],
        'semester-gpa-planner' => [
            'Semester GPA Planner',
            'Find the SGPA needed to reach your target CGPA.',
            'gpa-plan',
        ],
        'image-format-converter' => [
            'Image Format Converter',
            'Convert JPG, PNG and WebP images privately in your browser.',
            'image-converter',
        ],
        'image-resizer-compressor' => [
            'Image Resizer & Compressor',
            'Resize and compress JPG, PNG or WebP images without uploading them.',
            'image-resizer',
        ],
        'image-to-pdf' => [
            'Image to PDF',
            'Combine JPG, PNG and WebP images into one downloadable PDF locally.',
            'image-pdf',
        ],
        'pdf-merger' => [
            'PDF Merger',
            'Combine multiple PDF files in your chosen order without uploading them.',
            'pdf-merge',
        ],
        'pdf-splitter' => [
            'PDF Splitter',
            'Extract selected pages into a new PDF entirely in your browser.',
            'pdf-split',
        ],
    ];
    return isset($definitions[$slug]) ? array_merge([$slug], $definitions[$slug]) : null;
}

function tool_seo_terms(string $slug): array
{
    $terms = [
        'cgpa-calculator' => [
            'online CGPA calculator',
            'engineering CGPA calculator',
            'credit-based GPA calculator',
        ],
        'sgpa-calculator' => [
            'online SGPA calculator',
            'semester GPA calculator',
            'credit and grade calculator',
        ],
        'percentage-calculator' => [
            'online percentage calculator',
            'marks percentage calculator',
            'score percentage tool',
        ],
        'attendance-calculator' => [
            'attendance percentage calculator',
            '75 percent attendance calculator',
            'classes needed calculator',
        ],
        'gpa-converter' => [
            'CGPA to GPA converter',
            'GPA to percentage converter',
            '10 point to 4 point GPA',
        ],
        'marks-predictor' => [
            'exam marks predictor',
            'final score calculator',
            'internal external marks calculator',
        ],
        'backlog-impact-calculator' => [
            'backlog CGPA calculator',
            'CGPA improvement calculator',
            'backlog clearance grade planner',
        ],
        'resume-builder' => [
            'AI resume builder for engineering students',
            'ATS resume maker',
            'downloadable resume PDF generator',
        ],
        'ats-resume-checker' => [
            'ATS resume checker',
            'resume keyword checker',
            'engineering resume score',
        ],
        'linkedin-generator' => [
            'AI LinkedIn profile generator',
            'LinkedIn headline generator',
            'LinkedIn About section generator',
        ],
        'cover-letter-generator' => [
            'AI cover letter generator',
            'engineering internship cover letter',
            'job application letter generator',
        ],
        'plagiarism-remover' => [
            'originality rewriter',
            'academic text rewriter',
            'rewrite text clearly',
        ],
        'grammar-corrector' => [
            'online grammar corrector',
            'English punctuation checker',
            'student writing correction tool',
        ],
        'paraphrasing-tool' => [
            'online paraphrasing tool',
            'sentence rephraser',
            'academic paragraph rewriter',
        ],
        'citation-generator' => [
            'APA citation generator',
            'MLA citation generator',
            'IEEE citation generator',
        ],
        'semester-planner' => [
            'online semester planner',
            'engineering study planner',
            'semester milestone tracker',
        ],
        'daily-study-planner' => [
            'daily study planner',
            'student timetable maker',
            'today study schedule',
        ],
        'assignment-tracker' => [
            'online assignment tracker',
            'student deadline tracker',
            'college homework planner',
        ],
        'habit-tracker' => [
            'student habit tracker',
            'daily study streak tracker',
            'academic routine planner',
        ],
        'goal-tracker' => [
            'student goal tracker',
            'academic progress tracker',
            'engineering degree goal planner',
        ],
        'pomodoro-timer' => [
            'online Pomodoro timer',
            'study focus timer',
            '25 minute student timer',
        ],
        'exam-countdown' => [
            'exam countdown timer',
            'days until exam calculator',
            'semester exam countdown',
        ],
        'unit-converter' => [
            'engineering unit converter',
            'SI unit converter',
            'mechanical electrical civil unit conversion',
        ],
        'formula-library' => [
            'engineering formula library',
            'search engineering formulas',
            'mechanical electrical civil formulas',
        ],
        'scientific-calculator' => [
            'online scientific calculator',
            'trigonometry calculator',
            'engineering expression calculator',
        ],
        'semester-gpa-planner' => [
            'target CGPA calculator',
            'required SGPA calculator',
            'semester GPA planner',
        ],
        'image-format-converter' => [
            'JPG to PNG converter',
            'PNG to JPG converter',
            'WebP image converter',
        ],
        'image-resizer-compressor' => [
            'image resizer online',
            'compress JPG PNG WebP',
            'private image compressor',
        ],
        'image-to-pdf' => ['image to PDF converter', 'JPG to PDF', 'PNG to PDF without upload'],
        'pdf-merger' => ['merge PDF files', 'combine PDFs without upload', 'private PDF merger'],
        'pdf-splitter' => [
            'split PDF pages',
            'extract pages from PDF',
            'private PDF page extractor',
        ],
    ];
    return $terms[$slug] ?? [];
}

function tool_catalog_price(string $slug): float
{
    foreach (TOOLS as $tools) {
        foreach ($tools as $tool) {
            if ($tool[1] === $slug) {
                return (float) preg_replace('/[^0-9.]/', '', (string) $tool[2]);
            }
        }
    }
    return 0.0;
}

function tool_related(string $slug, int $limit = 4): array
{
    foreach (TOOLS as $category => $tools) {
        $slugs = array_column($tools, 1);
        if (!in_array($slug, $slugs, true)) {
            continue;
        }
        $out = [];
        foreach ($tools as $tool) {
            if ($tool[1] !== $slug) {
                $out[] = ['name' => $tool[0], 'slug' => $tool[1]];
                if (count($out) >= $limit) {
                    break;
                }
            }
        }
        return $out;
    }
    return [];
}

function tool_seo_profile(string $slug, string $name, string $description, float $price): array
{
    $terms = tool_seo_terms($slug);
    $ai = in_array($slug, ['resume-builder', 'linkedin-generator', 'cover-letter-generator'], true);
    $local = in_array(
        $slug,
        [
            'image-format-converter',
            'image-resizer-compressor',
            'image-to-pdf',
            'pdf-merger',
            'pdf-splitter',
        ],
        true,
    );
    $seoDescription =
        $description .
        ' ' .
        ($ai
            ? 'Use AI assistance with your own accurate details.'
            : ($local
                ? 'Files are processed privately in your browser without cloud upload.'
                : 'Built for engineering and college students in India.'));
    $faqs = [
        ['question' => 'What does the ' . $name . ' do?', 'answer' => $description],
        [
            'question' => 'Who is the ' . $name . ' for?',
            'answer' =>
                'It is designed for engineering students, college students and learners who need a focused online ' .
                strtolower($name) .
                '.',
        ],
        [
            'question' => 'How do I use the ' . $name . ' on EnoughEdu?',
            'answer' =>
                'Enter your information or choose files first. Free tools require no payment. Premium tools ask you to unlock access just before generating the final result or download. AI career tools require sign-in.',
        ],
    ];
    if ($ai) {
        $faqs[] = [
            'question' => 'Does this tool use AI?',
            'answer' =>
                'Yes. This EnoughEdu career tool sends only the details you submit to Google’s AI service. Review the generated text and never enter passwords, payment information or confidential documents.',
        ];
    }
    if ($local) {
        $faqs[] = [
            'question' => 'Are my files uploaded?',
            'answer' =>
                'No. Selected files are processed in your browser memory and downloaded directly to your device. EnoughEdu does not receive the source files.',
        ];
    }
    return [
        'title' => $name . ' Online for Students',
        'description' => text_limit($seoDescription, 165),
        'terms' => $terms,
        'faqs' => $faqs,
        'price' => $price,
        'ai' => $ai,
        'local' => $local,
    ];
}

function tool_structured_data(string $slug, string $name, array $seo): string
{
    $url = url('/tools/' . $slug);
    $faqEntities = [];
    foreach ($seo['faqs'] as $faq) {
        $faqEntities[] = [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
        ];
    }
    return json_ld([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                '@id' => $url . '#application',
                'name' => $name,
                'url' => $url,
                'description' => $seo['description'],
                'applicationCategory' => 'EducationalApplication',
                'applicationSubCategory' => 'Engineering student tool',
                'operatingSystem' => 'Web browser',
                'browserRequirements' => 'Requires a modern web browser',
                'inLanguage' => 'en-IN',
                'keywords' => $seo['terms'],
                'provider' => ['@type' => 'Organization', 'name' => 'EnoughEdu', 'url' => url('/')],
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $url,
                    'price' => number_format((float) $seo['price'], 2, '.', ''),
                    'priceCurrency' => 'INR',
                    'availability' => 'https://schema.org/InStock',
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'EnoughEdu',
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Student Tools',
                        'item' => url('/tools'),
                    ],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $url],
                ],
            ],
            ['@type' => 'FAQPage', 'mainEntity' => $faqEntities],
        ],
    ]);
}

function tool_discovery_content(string $slug, string $name, string $description, array $seo): void
{
    $related = tool_related($slug);
    $price = (float) $seo['price'];
    $priceLabel =
        $price > 0
            ? 'Individual access: ₹' .
                number_format($price, $price === (float) (int) $price ? 0 : 2)
            : 'Free to use';
    ?>
    <section class="section tool-discovery">
<div class="container">
<div class="section-head">
<span class="eyebrow">About this EnoughEdu tool</span>
<h2>Use <?= e(
        $name,
    ) ?> with a clear purpose</h2>
<p><?= e(ai_public_text($description)) ?></p>
</div>
<div class="assurance-grid">
<article class="panel">
<span class="eyebrow">1 · Open</span>
<h3>Choose this tool</h3>
<p class="muted">Open the workspace and prepare your inputs. Free tools have no payment step; premium access is checked at the final action.</p>
</article>
<article class="panel">
<span class="eyebrow">2 · Enter</span>
<h3>Add your information</h3>
<p class="muted"><?= $seo['local'] ? 'Select files that stay in browser memory.' : 'Enter accurate values or text for the result you need.' ?></p>
</article>
<article class="panel">
<span class="eyebrow">3 · Review</span>
<h3>Check your result</h3>
<p class="muted">Review the output, then copy, save or download it when available.</p>
</article>
</div>
    <?php if (
        $seo['terms']
    ): ?><div class="panel" style="margin-top:20px">
<div class="panel-head">
<div>
<h3>Common uses</h3>
<p class="muted">Students use this EnoughEdu tool for:</p>
</div>
<span class="tag"><?= e(
    $priceLabel,
) ?></span>
</div>
<div class="card-meta"><?php foreach (
    $seo['terms']
    as $term
): ?><span class="tag"><?= e($term) ?></span><?php endforeach; ?></div>
</div><?php endif; ?>
    <div class="section-head" style="margin-top:50px">
<span class="eyebrow">Questions about <?= e(
        $name,
    ) ?></span>
<h2>Helpful answers before you begin</h2>
</div>
<div class="faq"><?php foreach ($seo['faqs'] as $faq): ?><div class="faq-item">
<button class="faq-q" type="button"><?= e($faq['question']) ?><span>＋</span>
</button>
<div class="faq-a"><?= e($faq['answer']) ?></div>
</div><?php endforeach; ?></div>
    <?php if (
        $related
    ): ?><div class="panel-head" style="margin-top:48px">
<div>
<h2>Related EnoughEdu tools</h2>
<p class="muted">Explore more tools in the same student toolkit.</p>
</div>
<a class="card-link" href="/tools">View all tools →</a>
</div>
<div class="card-grid"><?php foreach (
    $related
    as $item
): ?><a class="card" href="/tools/<?= e($item['slug']) ?>">
<h3><?= e(
    $item['name'],
) ?></h3>
<span class="card-link">View tool →</span>
</a><?php endforeach; ?></div><?php endif; ?></div>
</section>
<?php
}

function locked_tool_page(string $slug, string $name, string $description, array $seo): void
{
    page_start($seo['title'], $seo['description']);
    echo tool_structured_data($slug, $name, $seo);
    $price = (float) $seo['price'];
    ?>
    <section class="section">
<div class="container">
<nav class="muted" aria-label="Breadcrumb">
<a href="/">Home</a> · <a href="/tools">Student tools</a> · <?= e(
        $name,
    ) ?></nav>
<div class="feature-lock panel" style="margin-top:24px">
<div class="lock-orbit">
<div class="lock-core">◆</div>
<i>
</i>
<i>
</i>
</div>
<div>
<span class="eyebrow"><?= $seo['ai'] ? 'AI-assisted career tool' : ($seo['local'] ? 'Private browser tool' : 'Engineering student tool') ?></span>
<h1><?= e($name) ?></h1>
<p><?= e(ai_public_text($description)) ?></p>
<div class="feature-preview">
<b>What this tool provides</b>
<span><?= e(implode(' · ', $seo['terms'])) ?></span>
</div><?php if ($price > 0): ?><p>
<span class="tag">Individual access: ₹<?= e(number_format($price, $price === (float) (int) $price ? 0 : 2)) ?></span>
</p><?php endif; ?><div class="hero-actions"><?php if (!user()): ?><a class="btn btn-primary" href="/login">Log in to continue</a>
<a class="btn btn-secondary" href="/signup">Create account</a><?php else: ?><a class="btn btn-primary" href="/checkout?tool=<?= e($slug) ?>">Unlock<?= $price > 0 ? ' for ₹' . e(number_format($price, $price === (float) (int) $price ? 0 : 2)) : '' ?></a>
<a class="btn btn-secondary" href="/pricing">Compare plans</a><?php endif; ?></div>
<small class="muted">You can understand the feature before purchase. Interactive controls appear only after eligible access is confirmed.</small>
</div>
</div>
</div>
</section>
    <?php
    tool_discovery_content($slug, $name, $description, $seo);
    page_end();
}

function tool_fields(string $type): void
{
    if ($type === 'grades') { ?><div id="grade-rows"><?php for (
    $i = 1;
    $i <= 6;
    $i++
): ?><div class="tool-row">
<input class="input" name="subject" placeholder="Subject <?= $i ?>">
<input class="input" type="number" name="grade" min="0" max="10" step=".01" placeholder="Grade point">
<input class="input" type="number" name="credit" min="0" max="20" step=".5" placeholder="Credits">
</div><?php endfor; ?></div><?php } elseif ($type === 'percentage') { ?><div class="form-row">
<div class="field">
<label>Marks obtained</label>
<input class="input" id="marks-obtained" type="number" min="0" step=".01" value="425">
</div>
<div class="field">
<label>Total marks</label>
<input class="input" id="marks-total" type="number" min="1" step=".01" value="500">
</div>
</div><?php } elseif ($type === 'attendance') { ?><div class="form-row">
<div class="field">
<label>Classes held</label>
<input class="input" id="held" type="number" min="0" value="80">
</div>
<div class="field">
<label>Classes attended</label>
<input class="input" id="attended" type="number" min="0" value="62">
</div>
</div>
<div class="field">
<label>Required attendance percentage</label>
<input class="input" id="attendance-target" type="number" min="1" max="100" value="75">
</div><?php } elseif ($type === 'gpa') { ?><div class="form-row">
<div class="field">
<label>Current value</label>
<input class="input" id="gpa-value" type="number" min="0" step=".01" value="8.4">
</div>
<div class="field">
<label>Current scale</label>
<select class="input" id="gpa-scale">
<option value="10">10-point CGPA</option>
<option value="4">4-point GPA</option>
<option value="100">Percentage</option>
</select>
</div>
</div><?php } elseif ($type === 'marks') { ?><div class="form-row">
<div class="field">
<label>Internal marks</label>
<input class="input" id="internal" type="number" value="36">
</div>
<div class="field">
<label>Internal maximum</label>
<input class="input" id="internal-max" type="number" value="40">
</div>
</div>
<div class="form-row">
<div class="field">
<label>Expected external marks</label>
<input class="input" id="external" type="number" value="48">
</div>
<div class="field">
<label>External maximum</label>
<input class="input" id="external-max" type="number" value="60">
</div>
</div><?php } elseif ($type === 'backlog') { ?><div class="form-row">
<div class="field">
<label>Current CGPA</label>
<input class="input" id="current-cgpa" type="number" step=".01" value="7.8">
</div>
<div class="field">
<label>Completed credits</label>
<input class="input" id="completed-credits" type="number" value="92">
</div>
</div>
<div class="form-row">
<div class="field">
<label>Backlog course credits</label>
<input class="input" id="backlog-credits" type="number" value="4">
</div>
<div class="field">
<label>Expected grade point</label>
<input class="input" id="backlog-grade" type="number" min="0" max="10" value="8">
</div>
</div><?php } elseif ($type === 'resume') { ?><div class="form-row">
<div class="field">
<label>Full name</label>
<input class="input" id="profile-name" maxlength="100" required>
</div>
<div class="field">
<label>Email</label>
<input class="input" id="profile-email" type="email" maxlength="160" required>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Phone</label>
<input class="input" id="profile-phone" maxlength="30">
</div>
<div class="field">
<label>Location</label>
<input class="input" id="profile-location" maxlength="100">
</div>
</div>
<div class="form-row">
<div class="field">
<label>LinkedIn URL</label>
<input class="input" id="profile-linkedin" type="url">
</div>
<div class="field">
<label>GitHub or portfolio URL</label>
<input class="input" id="profile-portfolio" type="url">
</div>
</div>
<div class="form-row">
<div class="field">
<label>Degree and branch</label>
<input class="input" id="profile-branch" placeholder="B.Tech, Computer Science">
</div>
<div class="field">
<label>University and graduation</label>
<input class="input" id="profile-education" placeholder="University name · 2027">
</div>
</div>
<div class="field">
<label>Skills</label>
<textarea class="input" id="profile-skills" rows="3" placeholder="Python, React, SQL, Machine Learning">
</textarea>
</div>
<div class="field">
<label>Projects and experience</label>
<textarea class="input" id="profile-projects" rows="7" placeholder="Include responsibilities, technologies and real measurable outcomes. Do not add facts that are not true.">
</textarea>
</div>
<div class="field">
<label>Target role</label>
<input class="input" id="profile-target" placeholder="Software Engineer Intern">
</div>
<div class="field">
<label>Job description (optional)</label>
<textarea class="input" id="profile-job" rows="5" placeholder="Paste the role description for more relevant wording.">
</textarea>
</div><?php } elseif ($type === 'linkedin') { ?><div class="form-row">
<div class="field">
<label>Full name</label>
<input class="input" id="profile-name" maxlength="100" required>
</div>
<div class="field">
<label>Degree, branch and year</label>
<input class="input" id="profile-branch" placeholder="B.Tech CSE, third year">
</div>
</div>
<div class="field">
<label>Skills</label>
<textarea class="input" id="profile-skills" rows="3">
</textarea>
</div>
<div class="field">
<label>Projects and experience</label>
<textarea class="input" id="profile-projects" rows="6">
</textarea>
</div>
<div class="form-row">
<div class="field">
<label>Target role</label>
<input class="input" id="profile-target">
</div>
<div class="field">
<label>Tone</label>
<select class="input" id="profile-tone">
<option>Professional</option>
<option>Confident</option>
<option>Friendly</option>
<option>Concise</option>
</select>
</div>
</div><?php } elseif ($type === 'cover') { ?><div class="form-row">
<div class="field">
<label>Full name</label>
<input class="input" id="profile-name" maxlength="100" required>
</div>
<div class="field">
<label>Role</label>
<input class="input" id="profile-target" required>
</div>
</div>
<div class="form-row">
<div class="field">
<label>Company</label>
<input class="input" id="profile-company" required>
</div>
<div class="field">
<label>Hiring manager (optional)</label>
<input class="input" id="profile-manager">
</div>
</div>
<div class="field">
<label>Degree and background</label>
<input class="input" id="profile-branch">
</div>
<div class="field">
<label>Relevant skills</label>
<textarea class="input" id="profile-skills" rows="3">
</textarea>
</div>
<div class="field">
<label>Evidence, projects and achievements</label>
<textarea class="input" id="profile-projects" rows="6">
</textarea>
</div>
<div class="field">
<label>Job description</label>
<textarea class="input" id="profile-job" rows="6" required>
</textarea>
</div><?php } elseif ($type === 'ats') { ?><div class="field">
<label>Job description</label>
<textarea class="input" id="job-description" rows="5" placeholder="Paste the target job description.">
</textarea>
</div>
<div class="field">
<label>Your resume text</label>
<textarea class="input" id="resume-text" rows="10" placeholder="Paste your resume text.">
</textarea>
</div><?php } elseif (in_array($type, ['rewrite', 'grammar', 'paraphrase'], true)) { ?><div class="field">
<label>Text to improve</label>
<textarea class="input" id="writing-input" rows="11" placeholder="Paste your text here.">
</textarea>
</div>
<div class="form-row">
<div class="field">
<label>Tone</label>
<select class="input" id="writing-tone">
<option>Academic</option>
<option>Clear</option>
<option>Professional</option>
<option>Concise</option>
</select>
</div>
<div class="field">
<label>Strength</label>
<select class="input" id="writing-strength">
<option value="light">Light</option>
<option value="balanced" selected>Balanced</option>
<option value="strong">Strong</option>
</select>
</div>
</div><?php } elseif ($type === 'citation') { ?><div class="form-row">
<div class="field">
<label>Source type</label>
<select class="input" id="citation-type">
<option value="website">Website</option>
<option value="book">Book</option>
<option value="journal">Journal article</option>
</select>
</div>
<div class="field">
<label>Style</label>
<select class="input" id="citation-style">
<option>APA</option>
<option>MLA</option>
<option>IEEE</option>
</select>
</div>
</div>
<div class="field">
<label>Author</label>
<input class="input" id="citation-author" placeholder="A. Sharma">
</div>
<div class="field">
<label>Title</label>
<input class="input" id="citation-title" placeholder="Source title">
</div>
<div class="form-row">
<div class="field">
<label>Publisher / website</label>
<input class="input" id="citation-publisher">
</div>
<div class="field">
<label>Year</label>
<input class="input" id="citation-year" value="<?= date('Y') ?>">
</div>
</div>
<div class="field">
<label>URL</label>
<input class="input" id="citation-url" type="url" placeholder="https://...">
</div><?php } elseif ($type === 'tracker') { ?><div class="form-row">
<div class="field">
<label>Task or milestone</label>
<input class="input" id="tracker-title" placeholder="Complete Unit 3 revision">
</div>
<div class="field">
<label>Due date</label>
<input class="input" id="tracker-date" type="date">
</div>
</div>
<div class="field">
<label>Priority</label>
<select class="input" id="tracker-priority">
<option>High</option>
<option selected>Medium</option>
<option>Low</option>
</select>
</div>
<div id="tracker-items" class="tool-list">
</div><?php } elseif ($type === 'pomodoro') { ?><div class="timer-display" id="timer-display">25:00</div>
<div class="form-row">
<div class="field">
<label>Focus minutes</label>
<input class="input" id="focus-minutes" type="number" min="1" max="120" value="25">
</div>
<div class="field">
<label>Break minutes</label>
<input class="input" id="break-minutes" type="number" min="1" max="60" value="5">
</div>
</div><?php } elseif ($type === 'countdown') { ?><div class="field">
<label>Exam name</label>
<input class="input" id="exam-name" placeholder="End-semester examination">
</div>
<div class="field">
<label>Exam date and time</label>
<input class="input" id="exam-date" type="datetime-local">
</div>
<div class="countdown-grid" id="countdown-grid">
<strong>Set your exam date</strong>
</div><?php } elseif ($type === 'units') { ?><div class="form-row">
<div class="field">
<label>Engineering quantity</label>
<select class="input" id="unit-category">
</select>
</div>
<div class="field">
<label>Value</label>
<input class="input" id="unit-value" type="number" step="any" value="1">
</div>
</div>
<div class="form-row">
<div class="field">
<label>From unit</label>
<select class="input" id="unit-from">
</select>
</div>
<div class="field">
<label>To unit</label>
<select class="input" id="unit-to">
</select>
</div>
</div>
<p class="muted" id="unit-library-count">
</p><?php } elseif ($type === 'formulas') { ?><div class="form-row">
<div class="field">
<label>Search name, symbol or topic</label>
<input class="input" id="formula-search" list="formula-suggestions" autocomplete="off" placeholder="Ohm's law, kinetic energy, stress…">
<datalist id="formula-suggestions">
</datalist>
</div>
<div class="field">
<label>Discipline</label>
<select class="input" id="formula-category">
<option value="">All disciplines</option>
</select>
</div>
</div>
<p class="muted" id="formula-count">
</p>
<div id="formula-results" class="formula-grid">
</div><?php } elseif ($type === 'scientific') { ?><div class="field">
<label>Expression</label>
<input class="input scientific-input" id="scientific-expression" value="sin(30) + sqrt(16) + 2^3" placeholder="Use sin, cos, tan, sqrt, log and ^">
</div>
<p class="muted">Angles use degrees. Constants: pi and e.</p><?php } elseif ($type === 'gpa-plan') { ?><div class="form-row">
<div class="field">
<label>Current CGPA</label>
<input class="input" id="plan-current" type="number" step=".01" value="7.8">
</div>
<div class="field">
<label>Credits completed</label>
<input class="input" id="plan-completed" type="number" value="92">
</div>
</div>
<div class="form-row">
<div class="field">
<label>Target CGPA</label>
<input class="input" id="plan-target" type="number" step=".01" value="8.0">
</div>
<div class="field">
<label>Next semester credits</label>
<input class="input" id="plan-next" type="number" value="24">
</div>
</div><?php } elseif ($type === 'image-converter') { ?><div class="local-file-notice">
<b>Private by design</b>
<span>Your image is processed in this browser and is never uploaded.</span>
</div>
<div class="field">
<label for="local-files">Choose an image</label>
<input class="input file-input" id="local-files" type="file" accept="image/jpeg,image/png,image/webp">
</div>
<div class="form-row">
<div class="field">
<label for="output-format">Output format</label>
<select class="input" id="output-format">
<option value="image/png">PNG</option>
<option value="image/jpeg">JPG</option>
<option value="image/webp">WebP</option>
</select>
</div>
<div class="field">
<label for="image-quality">Quality <span id="quality-value">90%</span>
</label>
<input id="image-quality" type="range" min="10" max="100" value="90">
</div>
</div><?php } elseif ($type === 'image-resizer') { ?><div class="local-file-notice">
<b>Private by design</b>
<span>Your image is processed in this browser and is never uploaded.</span>
</div>
<div class="field">
<label for="local-files">Choose an image</label>
<input class="input file-input" id="local-files" type="file" accept="image/jpeg,image/png,image/webp">
</div>
<div class="form-row">
<div class="field">
<label for="image-width">Width (pixels)</label>
<input class="input" id="image-width" type="number" min="1" max="12000" placeholder="Original width">
</div>
<div class="field">
<label for="image-height">Height (pixels)</label>
<input class="input" id="image-height" type="number" min="1" max="12000" placeholder="Original height">
</div>
</div>
<label class="inline-check">
<input id="lock-aspect" type="checkbox" checked> Keep original aspect ratio</label>
<div class="form-row">
<div class="field">
<label for="output-format">Output format</label>
<select class="input" id="output-format">
<option value="image/jpeg">JPG</option>
<option value="image/png">PNG</option>
<option value="image/webp">WebP</option>
</select>
</div>
<div class="field">
<label for="image-quality">Quality <span id="quality-value">85%</span>
</label>
<input id="image-quality" type="range" min="10" max="100" value="85">
</div>
</div><?php } elseif ($type === 'image-pdf') { ?><div class="local-file-notice">
<b>Private by design</b>
<span>Your images are combined locally and are never uploaded.</span>
</div>
<div class="field">
<label for="local-files">Choose images in page order</label>
<input class="input file-input" id="local-files" type="file" accept="image/jpeg,image/png,image/webp" multiple>
</div>
<div class="form-row">
<div class="field">
<label for="pdf-page-size">Page size</label>
<select class="input" id="pdf-page-size">
<option value="a4">A4</option>
<option value="letter">US Letter</option>
<option value="fit">Fit each image</option>
</select>
</div>
<div class="field">
<label for="pdf-margin">Margin</label>
<select class="input" id="pdf-margin">
<option value="24">Small</option>
<option value="48" selected>Comfortable</option>
<option value="72">Wide</option>
</select>
</div>
</div>
<div class="selected-file-list" id="selected-file-list" aria-live="polite">
</div><?php } elseif ($type === 'pdf-merge') { ?><div class="local-file-notice">
<b>Private by design</b>
<span>Your PDFs are merged locally and are never uploaded.</span>
</div>
<div class="field">
<label for="local-files">Choose PDFs in merge order</label>
<input class="input file-input" id="local-files" type="file" accept="application/pdf,.pdf" multiple>
</div>
<div class="selected-file-list" id="selected-file-list" aria-live="polite">
</div><?php } elseif ($type === 'pdf-split') { ?><div class="local-file-notice">
<b>Private by design</b>
<span>Your PDF is read locally and is never uploaded.</span>
</div>
<div class="field">
<label for="local-files">Choose one PDF</label>
<input class="input file-input" id="local-files" type="file" accept="application/pdf,.pdf">
</div>
<div class="field">
<label for="pdf-pages">Pages to extract</label>
<input class="input" id="pdf-pages" placeholder="Example: 1, 3-5, 8">
<small class="muted">Enter page numbers or ranges. You will get one new PDF containing those pages.</small>
</div><?php }
}

function functional_tool_page(string $slug): void
{
    $tool = tool_definition($slug);
    if (!$tool) {
        not_found();
        return;
    }
    [$slug, $name, $description, $type] = $tool;
    $pdo = db();
    $price = tool_catalog_price($slug);
    if ($pdo) {
        $q = $pdo->prepare(
            'SELECT name,description,price,is_free FROM tools WHERE slug=? AND status="active" LIMIT 1',
        );
        $q->execute([$slug]);
        $live = $q->fetch();
        if (!$live) {
            not_found();
            return;
        }
        $name = trim((string) ($live['name'] ?? '')) ?: $name;
        $description = trim((string) ($live['description'] ?? '')) ?: $description;
        $price = !empty($live['is_free']) ? 0 : (float) $live['price'];
    }
    $description = ai_public_text(te_description($slug, $description));
    $seo = tool_seo_profile($slug, $name, $description, $price);
    // All tools expose their preparation workspace before the final access check.
    $currentUser = user();
    $storageUser = substr(
        hash(
            'sha256',
            (string) ($currentUser['id'] ?? 0) . '|' . (string) config('security.session_name'),
        ),
        0,
        20,
    );
    page_start($seo['title'], $seo['description']);
    echo tool_structured_data($slug, $name, $seo);
    te_intro($slug, $name, $description, $type);
    te_guide($type);
    ?>
    <?php
    $localTypes = ['image-converter', 'image-resizer', 'image-pdf', 'pdf-merge', 'pdf-split'];
    $isLocalTool = in_array($type, $localTypes, true);
    ?>
    <section class="section">
<div id="tool-workspace" class="container tool-shell tool-workspace" data-tool="<?= e(
        $slug,
    ) ?>" data-type="<?= e($type) ?>" data-user="<?= e($storageUser) ?>">
<a href="/tools" class="muted">← All tools</a>
<div style="margin:25px 0 30px">
<span class="eyebrow"><?= in_array($type, ['resume', 'linkedin', 'cover'], true) ? 'AI-assisted career tool' : ($isLocalTool ? 'Private browser tool' : 'EnoughEdu Student Tool') ?></span>
<h2 style="font-size:28px;margin:17px 0">Your workspace</h2>
<p class="muted"><?= e(ai_public_text($description)) ?> <?= in_array($type, ['resume', 'linkedin', 'cover'], true) ? 'The information you submit is sent securely to Google’s AI service to generate this result. Never enter passwords, payment data or private identification documents.' : ($isLocalTool ? 'Selected files stay in your browser memory; EnoughEdu does not receive or store them.' : 'Your entries stay on this device unless you explicitly save them.') ?></p>
</div>
<div class="tool-app-grid">
<section class="panel tool-input-panel"><?php tool_fields($type); ?><div class="tool-actions">
<button class="btn btn-primary" id="tool-run"><?= $isLocalTool ? 'Process files' : (in_array($type, ['tracker', 'pomodoro'], true) ? ($type === 'tracker' ? 'Add item' : 'Start timer') : (in_array($type, ['resume', 'linkedin', 'cover'], true) ? 'Generate with AI' : 'Calculate / Generate')) ?></button>
<button class="btn btn-secondary" id="tool-reset" type="button">Reset</button>
</div>
</section>
<aside class="result-box tool-output">
<small>RESULT</small>
<div id="tool-result">
<h2 style="margin-top:18px">Ready when you are</h2>
<p><?= $isLocalTool ? 'Choose local files, adjust the options and create your download.' : 'Complete the fields and run the tool.' ?></p>
</div>
<div class="tool-result-actions">
<button class="btn btn-secondary btn-sm" id="copy-result" type="button" hidden>Copy result</button><?php if (in_array($type, ['resume', 'linkedin', 'cover'], true)): ?><button class="btn btn-primary btn-sm" id="download-career-pdf" type="button" hidden>Download PDF</button><?php endif; ?></div>
</aside>
</div>
</div>
</section>
    <?php
    te_dialog($slug);
    tool_discovery_content($slug, $name, $description, $seo);
    ?><script>window.ENOUGHEDU_CSRF='<?= e(csrf_token()) ?>'</script><?php
if ($isLocalTool):
    if (in_array($type, ['image-pdf', 'pdf-merge', 'pdf-split'], true)): ?><script src="<?= e(
    asset_url('/assets/js/vendor/pdf-lib.min.js'),
) ?>" defer>
</script><?php endif; ?><script src="<?= e(
    asset_url('/assets/js/local-file-tools.js'),
) ?>" defer>
</script><?php
else:
     ?><script src="<?= e(
    asset_url('/assets/js/engineering-data.js'),
) ?>" defer>
</script>
<script src="<?= e(
    asset_url('/assets/js/tool-suite.js'),
) ?>" defer>
</script><?php
endif;
page_end();
}

function career_ai_api(): never
{
    header('Content-Type: application/json; charset=utf-8');
    if (!user()) {
        http_response_code(401);
        echo json_encode(['error' => 'Log in to use this career tool.']);
        exit();
    }
    if (!is_post()) {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed.']);
        exit();
    }
    if (!isset($_POST['csrf']) && isset($_POST['csrf_token'])) {
        $_POST['csrf'] = $_POST['csrf_token'];
    }
    verify_csrf();
    $mode = trim((string) ($_POST['mode'] ?? ''));
    $slugs = [
        'resume' => 'resume-builder',
        'linkedin' => 'linkedin-generator',
        'cover' => 'cover-letter-generator',
    ];
    if (!isset($slugs[$mode])) {
        http_response_code(422);
        echo json_encode(['error' => 'Unsupported career tool.']);
        exit();
    }
    if (!paid_access($slugs[$mode])) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Purchase this tool or an eligible plan before generating content.',
        ]);
        exit();
    }
    $raw = (string) ($_POST['details'] ?? '');
    if (strlen($raw) > 30000) {
        http_response_code(422);
        echo json_encode([
            'error' => 'Your information is too long. Keep it below 30,000 characters.',
        ]);
        exit();
    }
    $details = json_decode($raw, true);
    if (!is_array($details)) {
        http_response_code(422);
        echo json_encode(['error' => 'Complete the important fields before generating.']);
        exit();
    }
    $detailValue = fn(string $key): string => trim(
        is_scalar($details[$key] ?? null) ? (string) $details[$key] : '',
    );
    $name = $detailValue('name');
    $target = $detailValue('target');
    $skills = $detailValue('skills');
    $projects = $detailValue('projects');
    $invalid = $name === '' || $target === '' || ($skills === '' && $projects === '');
    if ($mode === 'resume') {
        $invalid = $invalid || !filter_var($detailValue('email'), FILTER_VALIDATE_EMAIL);
    }
    if ($mode === 'cover') {
        $invalid =
            $invalid || $detailValue('company') === '' || $detailValue('job_description') === '';
    }
    if ($invalid) {
        http_response_code(422);
        echo json_encode([
            'error' => 'Complete every required field with accurate information before generating.',
        ]);
        exit();
    }
    $key = trim((string) config('ai.api_key'));
    $endpoint = trim((string) config('ai.endpoint'));
    $model = trim((string) config('ai.model'));
    if (!$key) {
        http_response_code(503);
        echo json_encode([
            'error' => 'AI generation is temporarily unavailable. Please try again later.',
        ]);
        exit();
    }
    if (!$endpoint || !$model || !function_exists('curl_init')) {
        http_response_code(503);
        echo json_encode([
            'error' => 'AI configuration or PHP cURL is unavailable on this server.',
        ]);
        exit();
    }
    $schemas = [
        'resume' =>
            '{"headline":"","summary":"","skills":[""],"experience_bullets":[""],"education":""}',
        'linkedin' => '{"headline":"","about":"","skills":[""]}',
        'cover' => '{"letter":""}',
    ];
    $system =
        'You are an expert Indian engineering-student career writer. Create a ' .
        $mode .
        ' using ONLY facts supplied by the user. Never invent employers, dates, degrees, metrics, qualifications, links, achievements, or technologies. Improve clarity and ATS wording without changing truth. Return valid JSON only, with exactly this structure: ' .
        $schemas[$mode] .
        ' No markdown fences. Keep the writing specific, professional, concise and ready for review.';
    $payload = json_encode(
        [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                [
                    'role' => 'user',
                    'content' => json_encode(
                        $details,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    ),
                ],
            ],
            'temperature' => 0.25,
        ],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    );
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => (int) config('ai.timeout', 45),
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    if (!is_string($body) || $status < 200 || $status >= 300) {
        http_response_code(502);
        echo json_encode([
            'error' =>
                'AI could not complete this request. ' .
                ($curlError ?: 'Please try again shortly.'),
        ]);
        exit();
    }
    $response = json_decode($body, true);
    $text =
        (string) ($response['choices'][0]['message']['content'] ??
            ($response['output_text'] ?? ''));
    $text = trim((string) preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text));
    $generated = json_decode($text, true);
    if (!is_array($generated)) {
        http_response_code(502);
        echo json_encode(['error' => 'AI returned an unexpected response. Please generate again.']);
        exit();
    }
    $clean = fn($value, $max) => text_limit(
        trim(strip_tags(is_scalar($value) ? (string) $value : '')),
        $max,
    );
    $out = [];
    if ($mode === 'resume') {
        $out = [
            'headline' => $clean($generated['headline'] ?? '', 180),
            'summary' => $clean($generated['summary'] ?? '', 1200),
            'skills' => array_slice(
                array_values(
                    array_filter(
                        array_map(fn($v) => $clean($v, 100), (array) ($generated['skills'] ?? [])),
                    ),
                ),
                0,
                30,
            ),
            'experience_bullets' => array_slice(
                array_values(
                    array_filter(
                        array_map(
                            fn($v) => $clean($v, 350),
                            (array) ($generated['experience_bullets'] ?? []),
                        ),
                    ),
                ),
                0,
                18,
            ),
            'education' => $clean($generated['education'] ?? '', 500),
        ];
    } elseif ($mode === 'linkedin') {
        $out = [
            'headline' => $clean($generated['headline'] ?? '', 220),
            'about' => $clean($generated['about'] ?? '', 2600),
            'skills' => array_slice(
                array_values(
                    array_filter(
                        array_map(fn($v) => $clean($v, 100), (array) ($generated['skills'] ?? [])),
                    ),
                ),
                0,
                30,
            ),
        ];
    } else {
        $out = ['letter' => $clean($generated['letter'] ?? '', 5000)];
    }
    echo json_encode(
        ['mode' => $mode, 'data' => $out],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    );
    exit();
}

function pdf_safe_text(string $value): string
{
    $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);
        if (is_string($converted)) {
            $value = $converted;
        }
    }
    return preg_replace('/[^\x09\x0A\x0D\x20-\xFF]/', '', $value) ?? '';
}
function pdf_escape(string $value): string
{
    return str_replace(['\\', '(', ')', "\r"], ['\\\\', '\\(', '\\)', ''], pdf_safe_text($value));
}
function pdf_wrap(string $text, int $width): array
{
    $lines = [];
    foreach (preg_split('/\R/u', $text) ?: [] as $paragraph) {
        $wrapped = wordwrap(trim($paragraph), $width, "\n", true);
        foreach (explode("\n", $wrapped) as $line) {
            $lines[] = $line;
        }
        if (trim($paragraph) === '') {
            $lines[] = '';
        }
    }
    return $lines;
}
function resume_pdf_document(array $profile, array $resume): string
{
    $name = pdf_safe_text((string) ($profile['name'] ?? 'Resume'));
    $contact = array_filter([
        (string) ($profile['email'] ?? ''),
        (string) ($profile['phone'] ?? ''),
        (string) ($profile['location'] ?? ''),
        (string) ($profile['linkedin'] ?? ''),
        (string) ($profile['portfolio'] ?? ''),
    ]);
    $pages = [];
    $ops = [];
    $y = 790;
    $newPage = function () use (&$pages, &$ops, &$y): void {
        if ($ops) {
            $pages[] = implode("\n", $ops);
        }
        $ops = [];
        $y = 790;
    };
    $write = function (string $text, int $size = 10, bool $bold = false, int $indent = 0) use (
        &$ops,
        &$y,
        $newPage,
    ): void {
        foreach (pdf_wrap($text, max(35, 95 - $indent)) as $line) {
            if ($y < 55) {
                $newPage();
            }
            $ops[] =
                'BT /' .
                ($bold ? 'F2' : 'F1') .
                ' ' .
                $size .
                ' Tf ' .
                (50 + $indent) .
                ' ' .
                $y .
                ' Td (' .
                pdf_escape($line) .
                ') Tj ET';
            $y -= $size + 5;
        }
    };
    $space = function (int $amount = 7) use (&$y, $newPage): void {
        $y -= $amount;
        if ($y < 55) {
            $newPage();
        }
    };
    $section = function (string $title) use ($write, $space): void {
        $write(strtoupper($title), 11, true);
        $space(2);
    };
    $write($name, 22, true);
    $write(implode('  |  ', array_map('pdf_safe_text', $contact)), 9);
    $space(9);
    if (!empty($resume['headline'])) {
        $write((string) $resume['headline'], 12, true);
        $space();
    }
    if (!empty($resume['summary'])) {
        $section('Professional Summary');
        $write((string) $resume['summary']);
        $space();
    }
    if (!empty($resume['skills'])) {
        $section('Skills');
        $write(implode('  |  ', (array) $resume['skills']));
        $space();
    }
    if (!empty($resume['experience_bullets'])) {
        $section('Projects and Experience');
        foreach ((array) $resume['experience_bullets'] as $bullet) {
            $write('- ' . (string) $bullet, 10, false, 8);
        }
        $space();
    }
    $education = trim((string) ($resume['education'] ?? ''));
    if ($education === '') {
        $education = trim(
            implode(
                ' | ',
                array_filter([
                    (string) ($profile['branch'] ?? ''),
                    (string) ($profile['education'] ?? ''),
                ]),
            ),
        );
    }
    if ($education !== '') {
        $section('Education');
        $write($education);
    }
    if ($ops) {
        $pages[] = implode("\n", $ops);
    }
    $objects = [];
    $add = function (string $object) use (&$objects): int {
        $objects[] = $object;
        return count($objects);
    };
    $font1 = $add('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>');
    $font2 = $add('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>');
    $contentIds = [];
    foreach ($pages as $content) {
        $contentIds[] = $add(
            '<< /Length ' . strlen($content) . ' >>' . "\nstream\n" . $content . "\nendstream",
        );
    }
    $pagesId = count($objects) + 1;
    $pageIds = [];
    foreach ($contentIds as $contentId) {
        $pageIds[] = $add(
            '<< /Type /Page /Parent ' .
                $pagesId .
                ' 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 ' .
                $font1 .
                ' 0 R /F2 ' .
                $font2 .
                ' 0 R >> >> /Contents ' .
                $contentId .
                ' 0 R >>',
        );
    }
    $pageIds = array_map(fn($id) => $id + 1, $pageIds);
    $kids = implode(' ', array_map(fn($id) => $id . ' 0 R', $pageIds));
    array_splice($objects, $pagesId - 1, 0, [
        '<< /Type /Pages /Kids [' . $kids . '] /Count ' . count($pageIds) . ' >>',
    ]);
    $catalog = $add('<< /Type /Catalog /Pages ' . $pagesId . ' 0 R >>');
    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= $index + 1 . " 0 obj\n" . $object . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
    }
    return $pdf .
        'trailer << /Size ' .
        (count($objects) + 1) .
        ' /Root ' .
        $catalog .
        " 0 R >>\nstartxref\n" .
        $xref .
        "\n%%EOF";
}
function resume_pdf_download(): never
{
    if (!user()) {
        http_response_code(401);
        exit('Log in first.');
    }
    if (!is_post()) {
        http_response_code(405);
        exit('Method not allowed.');
    }
    if (!isset($_POST['csrf']) && isset($_POST['csrf_token'])) {
        $_POST['csrf'] = $_POST['csrf_token'];
    }
    verify_csrf();
    if (!paid_access('resume-builder')) {
        http_response_code(403);
        exit('Resume Builder access is required.');
    }
    $profile = json_decode((string) ($_POST['profile'] ?? ''), true);
    $resume = json_decode((string) ($_POST['resume'] ?? ''), true);
    if (!is_array($profile) || !is_array($resume) || empty($profile['name'])) {
        http_response_code(422);
        exit('Generate a resume before downloading.');
    }
    $pdf = resume_pdf_document($profile, $resume);
    $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $profile['name']) ?: 'resume';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '-EnoughEdu-Resume.pdf"');
    header('Content-Length: ' . strlen($pdf));
    header('X-Content-Type-Options: nosniff');
    echo $pdf;
    exit();
}

function career_pdf_document(string $mode, array $profile, array $content): string
{
    $pages = [];
    $ops = [];
    $y = 790;
    $newPage = function () use (&$pages, &$ops, &$y): void {
        if ($ops) {
            $pages[] = implode("\n", $ops);
        }
        $ops = [];
        $y = 790;
    };
    $write = function (string $text, int $size = 10, bool $bold = false, int $indent = 0) use (
        &$ops,
        &$y,
        $newPage,
    ): void {
        foreach (pdf_wrap($text, max(35, 95 - $indent)) as $line) {
            if ($y < 55) {
                $newPage();
            }
            $ops[] =
                'BT /' .
                ($bold ? 'F2' : 'F1') .
                ' ' .
                $size .
                ' Tf ' .
                (50 + $indent) .
                ' ' .
                $y .
                ' Td (' .
                pdf_escape($line) .
                ') Tj ET';
            $y -= $size + 5;
        }
    };
    $space = function (int $amount = 7) use (&$y, $newPage): void {
        $y -= $amount;
        if ($y < 55) {
            $newPage();
        }
    };
    $section = function (string $title) use ($write, $space): void {
        $write(strtoupper($title), 11, true);
        $space(2);
    };
    $name = trim((string) ($profile['name'] ?? '')) ?: 'EnoughEdu Student';
    if ($mode === 'linkedin') {
        $write('LINKEDIN PROFILE DRAFT', 10, true);
        $space(4);
        $write($name, 22, true);
        $context = implode(
            '  |  ',
            array_filter([
                (string) ($profile['branch'] ?? ''),
                (string) ($profile['target'] ?? ''),
            ]),
        );
        if ($context !== '') {
            $write($context, 9);
        }
        $space(12);
        if (!empty($content['headline'])) {
            $section('Headline');
            $write((string) $content['headline'], 12, true);
            $space();
        }
        if (!empty($content['about'])) {
            $section('About');
            $write((string) $content['about']);
            $space();
        }
        if (!empty($content['skills'])) {
            $section('Skills');
            $write(implode('  |  ', array_map('strval', (array) $content['skills'])));
        }
    } else {
        $write('COVER LETTER', 10, true);
        $space(4);
        $write($name, 20, true);
        $context = implode(
            '  |  ',
            array_filter([
                (string) ($profile['target'] ?? ''),
                (string) ($profile['company'] ?? ''),
            ]),
        );
        if ($context !== '') {
            $write($context, 10);
        }
        $write(date('F j, Y'), 9);
        $space(14);
        if (!empty($profile['hiring_manager'])) {
            $write('For: ' . (string) $profile['hiring_manager'], 10, true);
            $space();
        }
        $write((string) ($content['letter'] ?? ''), 11);
    }
    $space(14);
    $write('Generated with AI assistance in EnoughEdu. Review every fact before use.', 8);
    if ($ops) {
        $pages[] = implode("\n", $ops);
    }
    if (!$pages) {
        $pages[] = '';
    }
    $objects = [];
    $add = function (string $object) use (&$objects): int {
        $objects[] = $object;
        return count($objects);
    };
    $font1 = $add('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>');
    $font2 = $add('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>');
    $contentIds = [];
    foreach ($pages as $page) {
        $contentIds[] = $add(
            '<< /Length ' . strlen($page) . ' >>' . "\nstream\n" . $page . "\nendstream",
        );
    }
    $pagesId = count($objects) + 1;
    $pageIds = [];
    foreach ($contentIds as $contentId) {
        $pageIds[] = $add(
            '<< /Type /Page /Parent ' .
                $pagesId .
                ' 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 ' .
                $font1 .
                ' 0 R /F2 ' .
                $font2 .
                ' 0 R >> >> /Contents ' .
                $contentId .
                ' 0 R >>',
        );
    }
    $pageIds = array_map(fn($id) => $id + 1, $pageIds);
    $kids = implode(' ', array_map(fn($id) => $id . ' 0 R', $pageIds));
    array_splice($objects, $pagesId - 1, 0, [
        '<< /Type /Pages /Kids [' . $kids . '] /Count ' . count($pageIds) . ' >>',
    ]);
    $catalog = $add('<< /Type /Catalog /Pages ' . $pagesId . ' 0 R >>');
    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= $index + 1 . " 0 obj\n" . $object . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
    }
    return $pdf .
        'trailer << /Size ' .
        (count($objects) + 1) .
        ' /Root ' .
        $catalog .
        " 0 R >>\nstartxref\n" .
        $xref .
        "\n%%EOF";
}

function career_pdf_download(string $mode): never
{
    $tools = ['linkedin' => 'linkedin-generator', 'cover' => 'cover-letter-generator'];
    $labels = ['linkedin' => 'LinkedIn-Profile', 'cover' => 'Cover-Letter'];
    if (!isset($tools[$mode])) {
        http_response_code(404);
        exit('Career PDF type not found.');
    }
    if (!user()) {
        http_response_code(401);
        exit('Log in first.');
    }
    if (!is_post()) {
        http_response_code(405);
        exit('Method not allowed.');
    }
    if (!isset($_POST['csrf']) && isset($_POST['csrf_token'])) {
        $_POST['csrf'] = $_POST['csrf_token'];
    }
    verify_csrf();
    if (!paid_access($tools[$mode])) {
        http_response_code(403);
        exit(
            ($mode === 'linkedin' ? 'LinkedIn Profile Generator' : 'Cover Letter Generator') .
                ' access is required.'
        );
    }
    $profile = json_decode((string) ($_POST['profile'] ?? ''), true);
    $content = json_decode((string) ($_POST['content'] ?? ''), true);
    $valid =
        is_array($profile) && is_array($content) && trim((string) ($profile['name'] ?? '')) !== '';
    if ($mode === 'linkedin') {
        $valid =
            $valid &&
            (trim((string) ($content['headline'] ?? '')) !== '' ||
                trim((string) ($content['about'] ?? '')) !== '');
    } else {
        $valid = $valid && trim((string) ($content['letter'] ?? '')) !== '';
    }
    if (!$valid) {
        http_response_code(422);
        exit('Generate the content before downloading its PDF.');
    }
    $pdf = career_pdf_document($mode, $profile, $content);
    $name = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $profile['name']) ?: 'Student';
    header('Content-Type: application/pdf');
    header(
        'Content-Disposition: attachment; filename="' .
            $name .
            '-EnoughEdu-' .
            $labels[$mode] .
            '.pdf"',
    );
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo $pdf;
    exit();
}

function admin_integration_settings_page(): void
{
    require_admin();
    $geminiReady =
        trim((string) config('ai.api_key')) !== '' && trim((string) config('ai.model')) !== '';
    $googleAuthReady = google_oauth_ready();
    $razorpayReady = razorpay_ready();
    $webhookReady =
        trim((string) config('razorpay.webhook_secret')) !== '' &&
        !str_starts_with((string) config('razorpay.webhook_secret'), 'YOUR_');
    app_start('Admin · Integrations', 'integrations', true);
    ?>
    <div class="kpi-grid">
<div class="kpi">
<span>Google sign-in</span>
<strong><?= $googleAuthReady
        ? 'Ready'
        : 'Keys needed' ?></strong>
<small class="badge <?= $googleAuthReady
    ? ''
    : 'warn' ?>"><?= $googleAuthReady
    ? 'Connected'
    : 'Configuration pending' ?></small>
</div>
<div class="kpi">
<span>Google Gemini</span>
<strong><?= $geminiReady
    ? 'Ready'
    : 'Key needed' ?></strong>
<small class="badge <?= $geminiReady
    ? ''
    : 'warn' ?>"><?= $geminiReady
    ? 'Connected'
    : 'Configuration pending' ?></small>
</div>
<div class="kpi">
<span>Razorpay</span>
<strong><?= $razorpayReady
    ? 'Ready'
    : 'Keys needed' ?></strong>
<small class="badge <?= $razorpayReady && $webhookReady
    ? ''
    : 'warn' ?>"><?= $razorpayReady && $webhookReady
    ? 'Checkout and webhook ready'
    : 'Configuration pending' ?></small>
</div>
<div class="kpi">
<span>Support email</span>
<strong style="font-size:16px"><?= e(
    config('mail.from_email'),
) ?></strong>
<small class="muted">Sender and reply-to</small>
</div>
</div>
    <section class="panel" style="margin-top:18px;max-width:900px">
<div class="panel-head">
<div>
<h3>Sign in with Google</h3>
<p class="muted">Students can create or access their EnoughEdu account with a verified Google identity.</p>
</div>
<span class="badge <?= $googleAuthReady
        ? ''
        : 'warn' ?>"><?= $googleAuthReady
    ? 'Active'
    : 'Waiting for credentials' ?></span>
</div>
<div class="list-item">
<span>Client ID</span>
<b><?= $googleAuthReady
    ? 'Configured securely'
    : 'Not configured' ?></b>
</div>
<div class="list-item">
<span>Client secret</span>
<b><?= $googleAuthReady
    ? 'Configured securely'
    : 'Not configured' ?></b>
</div>
<div class="list-item">
<span>Authorized redirect URI</span>
<code style="overflow-wrap:anywhere"><?= e(
    config('google_oauth.redirect_uri'),
) ?></code>
</div>
<div class="alert" style="background:rgba(37,99,235,.08);color:var(--text);margin-top:20px">
<b>Secure setup:</b> add <code>ENOUGHEDU_GOOGLE_CLIENT_ID</code> and <code>ENOUGHEDU_GOOGLE_CLIENT_SECRET</code> as private server variables. Never put the client secret in HTML or JavaScript.</div>
<a class="btn btn-secondary" href="/login">Preview login page</a>
</section>
    <section class="panel" style="margin-top:18px;max-width:900px">
<div class="panel-head">
<div>
<h3>Google Gemini API</h3>
<p class="muted">Gemini powers the Resume Builder, LinkedIn Profile Generator and Cover Letter Generator. Set the API key and an available model in the server configuration.</p>
</div>
<span class="badge <?= $geminiReady
        ? ''
        : 'warn' ?>"><?= $geminiReady
    ? 'Active'
    : 'Configuration needed' ?></span>
</div>
<div class="list-item">
<span>Provider</span>
<b><?= e(
    config('ai.provider'),
) ?></b>
</div>
<div class="list-item">
<span>Model</span>
<b><?= e(
    config('ai.model'),
) ?></b>
</div>
<div class="list-item">
<span>Endpoint</span>
<code style="overflow-wrap:anywhere"><?= e(
    config('ai.endpoint'),
) ?></code>
</div>
<div class="list-item">
<span>API key</span>
<b><?= $geminiReady
    ? 'Configured securely'
    : 'Not configured' ?></b>
</div>
<div class="alert" style="background:rgba(37,99,235,.08);color:var(--text);margin-top:20px">
<b>Secure setup:</b> create an environment variable named <code>ENOUGHEDU_GEMINI_API_KEY</code> in cPanel. Never store or display the key in the admin browser.</div>
<a class="btn btn-secondary" href="/tools/resume-builder">Test Resume Builder</a>
</section>
    <section class="panel" style="margin-top:18px;max-width:900px">
<div class="panel-head">
<div>
<h3>Razorpay payments</h3>
<p class="muted">EnoughEdu creates orders on the server and unlocks access only after a captured payment passes signature validation.</p>
</div>
<span class="badge <?= $razorpayReady &&
    $webhookReady
        ? ''
        : 'warn' ?>"><?= $razorpayReady && $webhookReady
    ? 'Active'
    : 'Waiting for credentials' ?></span>
</div>
<div class="list-item">
<span>Key ID and secret</span>
<b><?= $razorpayReady
    ? 'Configured securely'
    : 'Not configured' ?></b>
</div>
<div class="list-item">
<span>Webhook secret</span>
<b><?= $webhookReady
    ? 'Configured securely'
    : 'Not configured' ?></b>
</div>
<div class="list-item">
<span>Webhook URL</span>
<code style="overflow-wrap:anywhere"><?= e(
    url('/payment/razorpay/webhook'),
) ?></code>
</div>
<div class="alert" style="background:rgba(37,99,235,.08);color:var(--text);margin-top:20px">
<b>Secure setup:</b> add <code>ENOUGHEDU_RAZORPAY_KEY_ID</code>, <code>ENOUGHEDU_RAZORPAY_KEY_SECRET</code>, and <code>ENOUGHEDU_RAZORPAY_WEBHOOK_SECRET</code> as private server variables. Subscribe the webhook to <code>payment.captured</code> and <code>payment.failed</code>.</div>
<a class="btn btn-secondary" href="/pricing">Preview pricing</a>
</section>
    <?php app_end();
}
