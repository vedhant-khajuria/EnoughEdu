# Set up EnoughEdu on enoughedu.vedhant.in

This package is for the subdomain's own folder. Your main portfolio can continue
using its existing document root.

## 1. Create or check the subdomain

1. Sign in to cPanel and open **Domains**.
2. If `enoughedu.vedhant.in` exists, note its **Document Root**. Use that exact
   directory throughout this guide.
3. Otherwise, choose **Create a New Domain**, enter `enoughedu.vedhant.in`, and
   clear **Share document root**. Choose an independent folder such as
   `/home/YOUR_CPANEL_USER/enoughedu.vedhant.in` if your host allows it.
4. If DNS is managed elsewhere, create the `enoughedu` record there using the
   destination supplied by your host. Enable SSL/AutoSSL for this subdomain.
5. In MultiPHP Manager or Select PHP Version, select PHP 8.3 or another compatible
   version and enable the extensions listed in the README.

Some hosts restrict document-root locations. The directory shown in cPanel is
authoritative; do not substitute the main `public_html` directory.

[cPanel's subdomain instructions](https://support.cpanel.net/hc/en-us/articles/360052780313-How-to-create-a-subdomain)
explain the separate document-root setting.

## 2. Upload the application

1. Back up any existing files in the subdomain folder.
2. In File Manager, open that folder and enable **Settings → Show Hidden Files**.
3. Upload `enoughedu-cpanel.zip` and extract it **inside that folder**.
4. Confirm `index.php` and `.htaccess` appear immediately inside the document
   root, alongside `app`, `assets`, `config`, `database`, and `storage`.
5. Remove the uploaded ZIP after extraction. Keep a copy on your computer.

The ZIP has no outer wrapper folder. Do not create another `public_html` or
`enoughedu` directory inside the document root.

Use directory permissions of 755 and file permissions of 644 as a starting point.
PHP needs to write to `uploads/avatars`, `storage/cache`, `storage/logs`, and
`storage/resource-library/previews` when creating previews. Use 600 or 640 for
`config/config.php` if your host's PHP process can read it. Do not use 777.

## 3. Create a separate database

Open **MySQL Databases** or **Database Wizard**. Create a database and database
user, assign the user to that database, and grant the required privileges. Record
the complete names including the cPanel prefix.

Choose one installation path:

### A. Start with a clean installation

1. In phpMyAdmin, select the new empty database.
2. Import `database/schema.sql` once. It includes the resource-library tables.
3. Do not import the historical migration files after a fresh schema import.

This creates the schema, branches, plans, and tool catalogue. It does not create
an administrator, customer accounts, resource listings, or purchase history.
The uploaded PDFs alone do not create database listings.

### B. Copy the content of the working website

The supplied ZIPs do **not** contain a live database export. `database.sql` was
an installation schema. To preserve your current resource titles, settings,
accounts, and content, export the working site's database with phpMyAdmin and
import it into the **new database**. Do not point this copy at the original site's
database.

Then import `database/migrations/2026-09-28-resource-library.sql`. It creates
missing resource-library tables without deleting existing tables. Older migration
files are retained as references; apply one only if its changes are missing from
your database. Re-running old ALTER TABLE statements can fail on existing columns.

Finally import `database/portfolio-settings.sql`. This changes the support email,
updates common saved domain references, clears the old Android download link, and
pauses new resource subscriptions. Review other URLs in Admin: page content,
external resource links, banners, and any custom email address saved in the old DB.
Existing paid membership records still need careful review before payments are
enabled on the copy; do not duplicate a live payment workflow unintentionally.

## 4. Enter private configuration

Edit `config/config.php` in File Manager. The hosting ZIP already includes a blank
template. If using the GitHub source, copy `config/config.example.php` first.

- `database`: enter the actual host, database name, username, and password.
- `app.url`: keep `https://enoughedu.vedhant.in` without a trailing slash.
- `app.debug`: keep `false` on hosting.
- `mail.from_email`, `mail.reply_to`, and `admin_security.otp_email`: use
  `hello@vedhant.in` and verify that you can receive mail there.
- `security.cookie_secure`: keep `true` with HTTPS enabled.

Do not upload this configured file to GitHub. The `.gitignore` excludes it.
The credentials from the original installation were deliberately not copied.

## 5. Create your administrator

On a fresh installation:

1. Visit `https://enoughedu.vedhant.in/signup` and register with
   `hello@vedhant.in`, your name, and a unique password.
2. Finish onboarding if prompted.
3. In phpMyAdmin, select the **new** database, open **SQL**, and run:

```sql
UPDATE users
SET role = 'admin', status = 'active'
WHERE email = 'hello@vedhant.in';
```

4. Confirm exactly your account was updated. Log out, log in again, and visit
   `https://enoughedu.vedhant.in/admin`.

There is no shared default admin password. If you imported the working database,
use its existing administrator instead; the email replacement script does not
change user login addresses.

## 6. Set up email

Create or confirm the `hello@vedhant.in` mailbox. Use cPanel Email Deliverability
to check SPF and DKIM for `vedhant.in`, and review the domain's DMARC record.
The application uses PHP `mail()`, not SMTP credentials from the configuration.
Ask the host to permit this From address and route mail correctly if the mailbox
is hosted elsewhere.

Test registration mail and password reset. An administrator password change also
requires a code sent to `hello@vedhant.in`. If the host disables PHP `mail()`, an
SMTP transport must be implemented before relying on those workflows.

## 7. Configure optional integrations

### Google sign-in

Use your Google OAuth web client with:

- Authorized origin: `https://enoughedu.vedhant.in`
- Redirect URI: `https://enoughedu.vedhant.in/auth/google/callback`

Enter the client ID and secret in `google_oauth`. Email/password login remains
available without Google OAuth.

### Gemini career tools

Set `ai.api_key` and `ai.model` using a model available to your Google project.
The old package's model name was not carried forward as a verified default.
Keep the provided compatible endpoint unless your chosen service requires a
different endpoint. Test one resume, LinkedIn profile, and cover letter.

### Razorpay payments

Start with test credentials. Enter `key_id`, `key_secret`, and `webhook_secret` in
`razorpay`. Add the new site URL in your provider account as required.

- Regular checkout webhook: `https://enoughedu.vedhant.in/payment/razorpay/webhook`
- Resource membership webhook: `https://enoughedu.vedhant.in/resources/membership/webhook`

Regular checkout handles `payment.captured` and `payment.failed`. Resource
memberships reconcile subscription/payment events against the provider API; use
subscription events for the membership endpoint, with the same configured webhook
secret. Confirm Subscriptions access in the provider account before enabling
resource memberships in Admin → Resources. Verify a captured test payment, failed
payment, webhook delivery, access grant, and subscription cancellation before using
live keys. Existing paid gates remain enabled; moving to a portfolio subdomain
does not make every tool free. Admin can change individual tool prices to zero.

### Android app and analytics

The supplied APK was compiled and had no Android source. It is excluded from the
new packages. Rebuild and test an app for this subdomain before uploading it to
`downloads/` and saving its URL in Admin. The old analytics and advertising tags
were removed; add your chosen property separately if you want tracking here.

## 8. Restore or publish the resource library

The hosting ZIP includes the original PDFs and prepared previews in
`storage/resource-library`. Keep their filenames and preview hashes unchanged.
On a fresh DB, add each resource through Admin → Resources, choose its branches,
enter its exact PDF filename, and publish it. Existing preview images should be
reused automatically. The resource inventory in `docs/resource-inventory.csv`
lists filenames, page counts, and hashes.

For new PDFs without prepared previews, the server needs Imagick with PDF support.
Alternatively prepare JPEG pages named `1.jpg`, `2.jpg`, etc. inside
`previews/<PDF SHA-256>/`, with a `manifest.json` containing `{"pages": N}`.
No preview conversion utility was included in the original archives.

If copying the working database, its resource records and `resource_files` mapping
should already connect the files. Verify previews and protected downloads on the
new host. The GitHub package intentionally has no PDFs or previews to restore.

## 9. Add the reminder cron job

In cPanel **Cron Jobs**, schedule once daily, for example at 09:00. Substitute the
actual document root and the PHP CLI path provided by your host:

```text
/usr/local/bin/php /home/YOUR_CPANEL_USER/enoughedu.vedhant.in/cron/subscription-reminders.php
```

Run it manually once before enabling the schedule. Cron uses the same config file
as the site. Do not run both the original site's reminders and this copy against
the same customers unless that is intentional.

## 10. Verify before sharing the link

- The homepage, `/tools`, `/resources`, `/about`, and `/contact` load over HTTPS.
- HTTP redirects to the new HTTPS domain; the main portfolio still opens normally.
- `/config/config.php`, `/app/bootstrap.php`, `/storage/resource-library/README.txt`,
  `/database/schema.sql`, and `/cron/subscription-reminders.php` return **403**.
  Check with a browser network panel or `curl -I`. A blank 200 response is not proof
  of protection. Fix server support for `.htaccess` before entering live secrets.
- Register a student, log out/in, reset a password, and open the admin account.
- Check a resource preview, a blocked unpaid download, and an authorized download.
- Test whichever external integrations you enabled.
- Check the contact address, policy pages, canonical URLs, and sitemap.
- Add a project link to `https://enoughedu.vedhant.in` from your portfolio.

The local checks in `docs/VALIDATION.md` do not replace these hosting checks.

## Troubleshooting

**500 error:** inspect cPanel Errors. Confirm PHP/extensions and Apache override
support. If the host rejects an `Options` directive, ask which equivalent server
setting is needed; do not remove the access-denial rules.

**Homepage opens but other routes 404:** confirm the root `.htaccess` was extracted
and `mod_rewrite` is enabled.

**Database unavailable:** check the prefixed DB/user names, password, host, and user
privileges. Confirm you imported the schema into that database.

**Redirect loop:** check SSL and any proxy's HTTPS forwarding configuration.
The rules expect the origin request to report HTTPS correctly.

**Old email or domain still appears:** imported database content can override file
defaults. Run the portfolio settings script and review saved page content.

**Admin resources are empty:** PDFs do not supply the missing live database rows.
Import the working DB or add resource listings through the admin form.
