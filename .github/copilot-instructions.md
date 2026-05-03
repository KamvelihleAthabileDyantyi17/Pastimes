# Pastimes Codebase Guide

## Architecture Overview
Pastimes is a procedural PHP/MySQL web application for a second-hand fashion marketplace. No frameworks used—pure PHP with PDO for database access. Structure separates presentation (PHP pages), business logic (process/ files), and shared code (includes/).

Key components:
- **Pages** (`*.php`): Handle routing and rendering, include `includes/header.php` and `includes/footer.php`
- **Process** (`process/*.php`): Handle form submissions, validation, and database operations
- **Includes** (`includes/`): Reusable functions, auth helpers, DB connection
- **Assets** (`css/`, `js/`): Static files for styling and client-side logic

Data flows: User actions → POST to process file → DB updates → Redirect with flash message → Page reload

## Critical Workflows
- **Setup**: Run on XAMPP (Apache + MySQL). Database: `pastimes_db` (root/no password). Execute `setup.php` for schema creation.
- **Development**: Edit PHP files directly, refresh browser. No build step.
- **File Uploads**: Images to `uploads/` directory, validate MIME type, size (<5MB), use `uniqid()` for filenames.
- **Authentication**: Session-based. Use `requireLogin()` for protected pages, `requireAdmin()` for admin-only.

## Project Conventions
- **Input Sanitization**: Always use `sanitize()` from `includes/functions.php` for user inputs.
- **Database Queries**: Use PDO prepared statements via `getDB()`. Fetch mode: `PDO::FETCH_ASSOC`.
- **Error Handling**: Set flash messages with `setFlash('error', 'msg')`, redirect back.
- **URL Generation**: Use `BASE_URL` constant for links, calculated dynamically in `includes/db.php`.
- **Image Handling**: Check `file_exists()` before displaying; fallback to emoji placeholders based on title keywords.
- **Status Management**: Items have statuses ('Available', 'Sold', 'Traded'); users have roles ('user', 'admin') and statuses ('Active', 'Banned', 'Suspended').
- **Notifications**: Stored in DB, unread count via `unreadNotifCount()`.

## Key Files
- `includes/db.php`: Singleton PDO connection, BASE_URL definition
- `includes/auth.php`: Session helpers, login checks, flash messages
- `includes/functions.php`: Utility functions (sanitize, formatPrice, timeAgo, badges)
- `process/login_process.php`: Example of form validation and session setup
- `index.php`: Homepage with DB queries for featured items and stats

## Patterns to Follow
- Process files: Check `$_SERVER['REQUEST_METHOD'] === 'POST'`, validate inputs, execute DB operations, set flash, redirect.
- Pages: Require includes at top, use `currentUser()` for user data, embed PHP in HTML with `<?= ?>`.
- Trade/Cart Logic: Complex multi-table operations (e.g., trades involve Products, Trades, Notifications tables).
- Admin Actions: Bypass ownership checks for deletions/bans.

Avoid direct SQL without prepared statements. Always regenerate session ID on login.</content>
<parameter name="filePath">c:\Users\Student\Desktop\Work\Pastimes\.github\copilot-instructions.md