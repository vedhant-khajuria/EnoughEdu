<?php
declare(strict_types=1);

function android_download_url(): string
{
    $value = trim((string) setting('android_app_url', ''));
    return strlen($value) <= 2048 &&
        filter_var($value, FILTER_VALIDATE_URL) &&
        strtolower((string) parse_url($value, PHP_URL_SCHEME)) === 'https' &&
        !parse_url($value, PHP_URL_USER) &&
        !parse_url($value, PHP_URL_PASS)
        ? $value
        : '';
}

function app_download_prompt(): void
{
    ?><link rel="stylesheet" href="<?= e(asset_url('/assets/css/app-download.css')) ?>"><?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (
    android_download_url() === '' ||
    preg_match(
        '#^/(download-app|admin|dashboard|checkout|payment|login|signup|onboarding|auth)(/|$)#',
        $path,
    )
) {
    return;
}
?>
    <dialog class="app-prompt" id="app-download-prompt" aria-labelledby="app-prompt-title" aria-describedby="app-prompt-description">
      <button type="button" class="app-prompt-close" data-app-dismiss aria-label="Close download invitation">&times;</button>
      <img src="/assets/img/favicon.svg" width="64" height="64" alt="">
      <p class="app-kicker">ENOUGHEDU FOR ANDROID</p>
      <h2 id="app-prompt-title">Keep EnoughEdu close.</h2>
      <p id="app-prompt-description">Get the Android app and open EnoughEdu straight from your home screen.</p>
      <a class="btn btn-primary btn-block" href="/download-app" data-app-continue>Get the app &darr;</a>
      <button type="button" class="app-prompt-later" data-app-dismiss>Not now, continue browsing</button>
    </dialog>
    <script src="<?= e(asset_url('/assets/js/app-download.js')) ?>" defer>
</script>
    <?php
}

function app_download_page(): void
{
    $download = android_download_url();
    page_start(
        'Download the Android app',
        'Get the EnoughEdu Android app. Find the download link and installation steps in one place.',
    );
    ?>
    <section class="container app-download-hero">
      <div>
        <span class="eyebrow">EnoughEdu for Android</span>
        <h1>Your study space.<br>
<span class="gradient-text">Now in your pocket.</span>
</h1>
        <p class="app-download-lead">Bring EnoughEdu to your home screen. Open your student platform from one familiar place, wherever your day takes you.</p>
        <?php if ($download !== ''): ?>
          <a class="btn btn-primary" href="<?= e(
              $download,
          ) ?>" rel="noopener noreferrer">Download Android app &darr;</a>
          <p class="app-download-note">For Android phones and tablets. Internet access is required to use the website.</p>
        <?php else: ?>
          <span class="app-coming-soon" role="status">Download coming soon</span>
          <p class="app-download-note">We are preparing the app download. You can keep using EnoughEdu in your browser.</p>
        <?php endif; ?>
        <a class="app-browser-link" href="/">Continue on the website &rarr;</a>
      </div>
      <aside class="app-download-card" aria-label="EnoughEdu Android app">
        <img src="/assets/img/favicon.svg" width="88" height="88" alt="EnoughEdu app icon">
        <p class="app-kicker">YOUR ACADEMIC COMPANION</p>
        <h2>EnoughEdu</h2>
        <p>One place to return to.<br>Every semester.</p>
        <div class="app-card-links">
<span>Study resources</span>
<span>Student tools</span>
<span>Your workspace</span>
</div>
        <span class="app-platform">Made for Android</span>
      </aside>
    </section>
    <section class="container app-install-section" aria-labelledby="app-install-title">
      <span class="eyebrow">Getting started</span>
<h2 id="app-install-title">From download to home screen.</h2>
      <div class="app-install-grid">
        <article class="panel">
<span class="app-step">01</span>
<h3>Get the app</h3>
<p>Open this page on your Android device and tap the download button. If it opens an app store, follow the store's installation steps.</p>
</article>
        <article class="panel">
<span class="app-step">02</span>
<h3>Install on Android</h3>
<p>For an APK download, open the downloaded file. If Android asks, allow this browser or file manager to install the app. Keep Play Protect enabled; you can turn that installation permission off afterwards.</p>
</article>
        <article class="panel">
<span class="app-step">03</span>
<h3>Open EnoughEdu</h3>
<p>Find EnoughEdu on your home screen or app list and open it. Need help? Visit our <a href="/contact">contact page</a>.</p>
</article>
      </div>
      <p class="app-download-note">Using an iPhone or a computer? You can access EnoughEdu directly through your browser.</p>
    </section>
    <?php page_end();
}

function admin_app_download_form(): void
{
    ?>
    <section class="panel" style="max-width:900px;margin-top:24px">
      <div class="panel-head">
<div>
<h3>Android app download</h3>
<p class="muted">Set the download button and the two-minute website invitation here.</p>
</div>
<a class="btn btn-secondary btn-sm" href="/download-app" target="_blank" rel="noopener">Preview page &rarr;</a>
</div>
      <form method="post" action="/admin/action">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="app-download-save">
        <div class="field">
<label for="android-app-url">App download link</label>
<input class="input" type="url" id="android-app-url" name="android_app_url" maxlength="2048" placeholder="https://enoughedu.vedhant.in/downloads/EnoughEdu.apk" value="<?= e(
            setting('android_app_url', ''),
        ) ?>" aria-describedby="android-app-help">
<p class="muted" id="android-app-help">Paste an HTTPS APK link or app-store link. Leave blank to show “Download coming soon” and disable the popup. The popup appears once per browser session after two minutes of active public-page browsing.</p>
</div>
        <button class="btn btn-primary">Save app link</button>
      </form>
    </section>
<?php
}

function save_android_app_download(PDO $pdo): void
{
    $value = $_POST['android_app_url'] ?? '';
    if (!is_string($value)) {
        throw new DomainException('Enter a valid HTTPS app link.');
    }
    $value = trim($value);
    if (
        $value !== '' &&
        (strlen($value) > 2048 ||
            !filter_var($value, FILTER_VALIDATE_URL) ||
            strtolower((string) parse_url($value, PHP_URL_SCHEME)) !== 'https' ||
            parse_url($value, PHP_URL_USER) ||
            parse_url($value, PHP_URL_PASS))
    ) {
        throw new DomainException(
            'Enter a complete HTTPS app link, or leave it blank to pause downloads.',
        );
    }
    save_public_setting($pdo, 'android_app_url', $value, 'site');
    flash('success', 'App download link saved.');
    redirect('/admin/settings');
}
