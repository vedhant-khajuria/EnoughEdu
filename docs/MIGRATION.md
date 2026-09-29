# Notes on this package

The active files from the supplied `public_html.zip` and `enoughedu.zip` have been
combined into one document root. The original archives remain untouched outside
the deliverable folder.

## What changed

- The front controller loads `app/` beside itself. Cron and asset paths use the same root.
- Canonical URLs, the sitemap, mail defaults, OAuth callback, and public contact
  details point to the portfolio subdomain and `hello@vedhant.in`.
- The homepage and About page identify EnoughEdu as Vedhant Khajuria's project.
- PHP, JavaScript, CSS, SVG, and SQL source were formatted for readability.
  Documentation was rewritten around this deployment layout.
- Private folders have Apache denial rules. PDFs are still served through the
  existing access checks; direct HTTP access to storage is blocked.
- The PDF handler's old requirement to live outside the web root was replaced
  with the unified folder layout. Its realpath confinement and PDF signature
  checks remain in place. This requires the supplied Apache rules to be enforced.
- A missing resource-library migration was added, and included in the fresh schema.
- The shared admin password and sample testimonials were removed from the fresh schema.
- Credentials use placeholders. The session cookie has its own portfolio name.
- Original tracking/ad identifiers were removed. The Android download is disabled
  pending a rebuilt and tested app; its binary was excluded from both packages.

## What was kept

The existing calculators, account flows, purchase gates, payment verification,
resource previews, membership logic, and administration screens remain. This is
a deployment and readability update, not a redesign of the platform.

The PDF files and JPEG previews were copied without modification. The vendor
library and its license were preserved. Binary files are not source code and
were not rewritten.

## Files excluded from the deliverables

- The original private `config.php`, which may contain deployment credentials.
- Nested older copies of `content-manager.php` and the patch ZIP. The active
  top-level application version takes precedence.
- Old deployment instructions and patch notes referring to a split directory layout.
- Old `ads.txt` and analytics tags belonging to the original deployment.
- The compiled APK, because its source and new-domain behavior were unavailable.

## Database limitations

The supplied schema does not contain the live site's users, resource listings,
purchase records, or customized settings. The PDFs and preview images cannot
reconstruct those records. Export the working database if you want to retain
them, and restore it into a separate database for this deployment.

The additive migration is based on the table and column references in the supplied
resource handlers. It must be verified against your actual imported database.
Database-driven workflows and provider integrations need the hosting checks in
the setup guide before launch.
