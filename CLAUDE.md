# Argonar Construction

## Project
PHP construction tools app (BOQ Generator, Rebar Cutting List). Uses Bootstrap 5, jQuery, PhpSpreadsheet. PayRex for payments. MySQL database.

## Local Dev
- XAMPP stack at `C:\xampp\htdocs\Argonar Construction`
- Local URL: `http://localhost/Argonar%20Construction/`
- MySQL: root, no password, database `argonar_construction`

## Auto-Deploy
- GitHub repo: `kierl-j/Argonar-Construction`
- Commits with `[deploy]` in the message trigger auto-deploy to VPS
- **Production URL**: https://argonar.co
- **Always auto-deploy**: Every commit must include `[deploy]` and be pushed immediately. Do not wait to be asked.

## Database Migrations
When adding/altering tables:
1. Update `setup.sql` with the new schema
2. Create a `migrate_<name>.php` file in project root that runs the migration via PHP
3. Run it locally: `curl -s "http://localhost/Argonar%20Construction/migrate_<name>.php"`
4. Run it on production: `curl -s "https://<production-url>/migrate_<name>.php"` (or use WebFetch)
5. Delete the migration file after it runs successfully on both environments
6. **Do NOT leave migration files deployed** — they are one-time-use

## Key Patterns
- All pages require `includes/db.php` first (helpers, session, PDO)
- Auth: `require_login()` returns user or redirects to login
- Access: `require_access()` checks active subscription or redirects to pricing
- Tools that modify data use both `require_login()` + `require_access()`
- View pages only use `require_login()` (read-only, no subscription needed)
- JS files loaded via `$extraJs = ['filename.js']` before including header
- CRUD pattern: index.php, create.php, edit.php, view.php, delete.php, export.php
- Flash messages: `flash('type', 'message')` then redirect

## Coding Style
- PHP: No frameworks, vanilla PHP + PDO
- JS: jQuery, no build tools
- CSS: Custom properties in `:root`, single `app.css`
- Forms: CSRF via `csrf_field()` / `csrf_check()`
- Naming: snake_case for PHP vars/DB columns, camelCase for JS
