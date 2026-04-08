# PerkSkin — Local setup (educational/demo)

This short guide helps you run the PerkSkin demo locally. The project is educational — sample accounts and passwords are included for testing only.

## Prerequisites
- PHP 8.x installed (PDO and SQLite extensions enabled)
- Terminal (PowerShell / Bash)

## Quick steps
1. Project root: `\PerkSkin`
2. Create / seed the SQLite database:

```bash
php database/seed.php
```

This creates `database/webdb.sqlite` and adds an admin user.

3. Start the built-in PHP development server (serves `public`):

```bash
cd public
php -S localhost:8000
```

4. Open the browser at:

```
http://localhost:8000
```

## Test account (demo)
- Email: `admin@admin.com`
- Password: `admin123`

Use this account to test features such as spinner and quick-sell.

## Admin features and settings
The demo includes admin endpoints and API actions that let you configure the system or edit content (educational/demo use):

- Users & sessions: view user accounts and sessions in `perksin_users` and `perksin_user_sessions`.
- Wallets & top-ups: inspect `perksin_user_wallets` and `perksin_wallet_transactions`; top-ups can be simulated via the `walletAdjust` API.
- Gems & transactions: `perksin_user_gems` and `perksin_gem_transactions`; adjust with `gemsAdjust` API.
- Cases & items: manage cases and their items via `perksin_cases`, `perksin_case_items`, `perksin_item_codes`; admin APIs include `listCases`, `saveCase`, `listProducts`, `saveProduct`.
- Content sections: site content blocks are in `perksin_content_sections`; APIs: `listContentSections`, `saveContentSection`.
- Translations (i18n): override translations in `perksin_translations_kv`; APIs: `listTranslations`, `saveTranslation`.
- Badges, ranks and access: `perksin_badges`, `perksin_user_badges`, `perksin_ranks`. Ranks can be used to control which cases are available to users by setting thresholds.

Notes on admin operations:
- The demo does not provide a full GUI for every admin task; many settings can be changed directly in the database or via the API endpoints (for example: `index.php?page=api&action=saveContentSection`).
- The `rank` icon and `level` system: with `perksin_ranks` and user attributes (e.g. `level`) you can restrict case visibility — change rank thresholds or user levels via DB or admin endpoints.
- Security: this is a demo for learning. Do not use seeded admin credentials in production; use HTTPS and strong auth in real deployments.

## Feature matrix
Below is a concise feature matrix that indicates which features are implemented, partially implemented, or are placeholders/not implemented. Use this for README/GitHub documentation.

- **Authentication**
	- Login (email/password): Implemented (form + API) — Implemented
	- Register: Implemented — Implemented
	- Logout: Implemented — Implemented
	- Remember me: UI present but persistent login not implemented — Partial
	- Two-factor authentication (2FA / TOTP): Implemented (setup with QR code or manual secret, then verify with a 6-digit code) — Implemented

- **User profile**
	- View / Edit profile (getProfile/saveProfile API): Implemented — Implemented
	- Avatar upload: Not implemented — Not implemented

Note: 2FA is intentionally disabled for the seeded demo admin account (`admin@admin.com`), so test the feature with a regular user account.

- **Wallet / Payments**
	- walletBalance: Implemented — Implemented
	- walletTransactions: Implemented — Implemented
	- walletAdjust / Top-up (simulated): Implemented (API) — Implemented (no real payments)
	- Real payment gateway integration: Not implemented — Not implemented

- **Gems**
	- gemsBalance / gemsAdjust / gemTransactions: Implemented — Implemented

- **Spinner / Spins**
	- Spin state (spinState) and adjust (spinAdjust): Implemented — Implemented
	- UI spinner: Implemented — Implemented

- **Cases**
	- List & display cases: Implemented — Implemented
	- Open case / recordCaseHistory: Implemented — Implemented
	- Quick sell: Server flow exists (wallet tx + history) — Implemented (fixed)
	- Stock / item codes (item_codes): Partially implemented — Partial

- **Content & Translations**
	- Content sections (list/save): Implemented — Implemented
	- Translations (DB overrides): Implemented — Implemented

- **Admin / Moderation**
	- Admin API endpoints: multiple admin APIs exist (list/save content, cases, products) — Implemented at API level
	- Admin GUI: Not fully implemented for all operations — Partial
	- Ranks / Badges (data model): Present in DB; UI controls limited — Partial

- **Social logins**
	- Steam / Google / Discord / Facebook buttons: UI placeholders only — Not implemented

- **Other**
	- Events / Missions / Exchange views: Frontend present; backend logic partial or static — Partial
	- Real-time chat: UI placeholder, backend not implemented — Not implemented

If you want this matrix formatted specifically for GitHub (table view, badges, or TODO markers), I can update the README files accordingly.

## Troubleshooting
- "unable to open database file": ensure `database/webdb.sqlite` exists and the PHP process has read/write access.
- SQL syntax issues: SQLite does not support `FOR UPDATE`; remove or change these queries when using SQLite.
- API 401 "unauthorized": make sure you are logged in (session cookie) or use the UI login with the admin credentials.

## Developer notes
- After code edits, refresh the browser or restart the PHP server.
- Run `php database/seed.php` only when you need to create missing tables — avoid running on production data.

---
If you want, I can run the server and test the quick-sell flow for you locally — tell me to proceed.
