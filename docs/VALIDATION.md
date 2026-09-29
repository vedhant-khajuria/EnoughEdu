# Validation

Local checks performed for the portfolio package:

- PHP 8.3 syntax checks for every PHP file.
- JavaScript syntax checks for every first-party browser script.
- Path checks confirm the unified document root and new URL/contact defaults.
- Resource checks reject traversal, absolute paths, and missing files.
- All 22 supplied PDFs pass the PDF signature and directory-confinement checks.
- Every PDF has a matching SHA-256 preview directory; all 2,715 JPEG pages exist.
- Public-route smoke checks passed for the homepage, About, Contact, Tools, CGPA
  tool, Resources, Pricing, Login, Signup, and Android download page. An unknown
  route returned 404. These checks used the application's no-database fallback.
- Deployment copies of the PDF and JPEG assets match their supplied originals.
- Source scans found no old-domain defaults or original tracking IDs in active code.

The public-route checks used PHP's development server with a temporary router.
That server does not read `.htaccess`; Apache rewrite and access rules were
reviewed but still require verification on cPanel.

No MySQL server, cPanel account, mail service, OAuth credentials, Gemini key, or
Razorpay credentials were connected during these checks. Database import, login
with persisted accounts, email delivery, checkout, paid downloads, and external
provider callbacks have not been verified end to end. Follow the final checklist
in `SETUP.md` on your host.
