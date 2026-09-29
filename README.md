# EnoughEdu

An engineering student toolkit by **Vedhant Khajuria**, hosted as a project at
[enoughedu.vedhant.in](https://enoughedu.vedhant.in).

EnoughEdu brings together academic calculators, study planners, a PDF resource
library, career tools, and student accounts. It runs on PHP and MySQL, with plain
JavaScript in the browser. There is no Node build step on the hosting server.

[Portfolio](https://vedhant.in) · [Contact](mailto:hello@vedhant.in)

## Deploy on cPanel

Start with [SETUP.md](SETUP.md). The entire application belongs in the document
root assigned to **enoughedu.vedhant.in**. Its `index.php`, `.htaccess`, `app/`,
`config/`, `assets/`, and `storage/` sit together. No sibling application folder
is needed.

Use a separate database for this copy. Configuration ships with placeholders;
there are no working passwords or API keys in this repository.

## Features

- CGPA, SGPA, attendance, percentage, and other academic calculators.
- Semester planning, tasks, exams, academic records, and student dashboards.
- Resume, LinkedIn, and cover-letter tools with optional Gemini integration.
- Image conversion, image-to-PDF, PDF merging, and splitting in the browser.
- Resource previews, protected PDF downloads, and membership access.
- Email/password accounts and optional Google sign-in.
- Razorpay checkout, payment verification, and administration screens.

## Requirements

- PHP 8.1 or newer; this package was syntax-checked with PHP 8.3.
- PDO MySQL, cURL, OpenSSL, JSON, mbstring, and fileinfo extensions.
- MySQL 8 or MariaDB 10.5 or newer.
- Apache 2.4 or a compatible cPanel web server that enforces `.htaccess`.
- PHP mail delivery for password resets, purchase emails, and admin security codes.
- Imagick with PDF support only when generating new PDF previews on the server.

## Source layout

| Path                        | Purpose                                                   |
| --------------------------- | --------------------------------------------------------- |
| `index.php`                 | Front controller and homepage                             |
| `app/`                      | Accounts, tools, payments, resources, and email templates |
| `assets/`                   | Styles, browser scripts, logos, and bundled pdf-lib       |
| `config/config.example.php` | Configuration template                                    |
| `includes/`                 | Resource-library integration and legacy fallback          |
| `database/schema.sql`       | Fresh installation schema                                 |
| `database/migrations/`      | Historical and resource-library updates                   |
| `storage/resource-library/` | Private PDFs and rendered previews                        |
| `cron/`                     | Subscription reminder command                             |
| `docs/`                     | Migration notes and validation results                    |

The private directories are protected by `.htaccess`. Keep those files when
uploading. A server that ignores these rules must not serve this layout.

## GitHub and hosting packages

The hosting ZIP includes the supplied resource PDFs and their prepared previews.
The GitHub ZIP contains the source, documentation, and empty storage directories.
It excludes `config/config.php`, PDFs, preview images, uploaded avatars, and the
Android binary. To deploy from GitHub, copy `config/config.example.php` to
`config/config.php` and restore your private resource files separately.

Extract the GitHub ZIP before uploading its contents to a repository. Uploading
the ZIP itself would store an archive rather than a browsable source tree. Git
or GitHub Desktop can publish the extracted directory while preserving dotfiles.

## Development

Edit the PHP, CSS, and JavaScript files directly. The application files have been
formatted for readability; the vendor library and its license remain intact.
The package uses existing feature names and functions to keep changes easy to
compare with the supplied version.

Run the path and resource checks with `php tests/deployment.php` after copying the
example configuration. They do not need a database. Apache access rules and live
integrations must also be checked on hosting as described in the setup guide.

## Third-party materials

The bundled pdf-lib retains its MIT license in `assets/js/vendor/`.
Resource PDFs are distributed only in the private hosting package. Their authors
retain their rights; including them in the original archive does not establish a
license to republish them in a public source repository.
