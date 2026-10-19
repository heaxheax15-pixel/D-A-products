# Stability Checkpoint — 2026-10-19

## Verified by automated smoke test
```
bash tests/smoke.sh
→ PASS: 6 / FAIL: 0
```

## Architecture

**index.php** (75 lines): orchestrator only — bootstrap, variables, includes.
**partials/** (7 files): head, header, footer, whatsapp, modals, scripts.
**sections/** (13 files): hero, why, quality, about, journey, team, story,
featured, products, testimonials, gallery, tips, order.
**tests/smoke.sh**: 5 end-to-end POST scenarios against an isolated
backup of storage/da_honey_shop.sqlite; DB restored after every run.

## Bugs fixed this session

### Bug A — Schema drift (would have broken ALL production orders)
- Live `orders` table had legacy columns `product_name`/`quantity`
  and was missing `updated_at`.
- `submit-order.php` INSERTed into `updated_at` → every real order failed.
- Fix: dropped stale tables locally; `db_sqlite_bootstrap()` recreated
  the current schema. Production MySQL: covered by migration.php.

### Bug B — error_log() TypeError on any DB error
- `submit-order.php:291` called `error_log($e)` with a PDOException
  object. Under strict_types this throws → HTML fatal leaking
  filesystem path + stack trace to the customer.
- Fix: `error_log($e->getMessage())`.

### Bug C — migration.php broken prepare/execute/fetch chain
- `->execute(...)->fetch()` chains on a bool → fatal on first legacy order.
- Fix: split into prepare → execute → fetch, and restructured into
  DML (transactional, idempotent) + DDL (non-transactional, guarded)
  phases.

## Pre-deployment checklist

### 1. Environment (`.env` on production server)
```
APP_ENV=production
APP_DEBUG=false
VIEW_LAYER=v4
APP_URL=https://your-domain.com
TRUSTED_PROXY_IPS=            # leave empty unless behind reverse proxy
DB_HOST=... DB_NAME=... DB_USER=... DB_PASS=...
ADMIN_SECRET_KEY=<32+ char random string>
```

### 2. Migration (MySQL production)
- `includes/migration.php` runs automatically on first boot.
- Verify with: `SHOW COLUMNS FROM orders;`
  Must contain: id, customer_name, customer_phone, customer_address,
  total_price, payment_method, receipt_path, status, notes,
  created_at, updated_at.
  Must NOT contain: product_name, quantity.

### 3. Smoke test post-deployment
```bash
bash tests/smoke.sh
# expected: PASS: 6 / FAIL: 0
```

### 4. Rollback
- Visual layer: `VIEW_LAYER=v3` in .env
- Full deploy: `git revert <commit>` or `git checkout <tag>`

## Known issues (P4/P5 backlog)

- No dedicated `product.php` page (only in-page modal — SEO weakness)
- Cart logic in `includes/cart.php` but no UI (drawer/checkout page)
- `sitemap.php` + `robots.txt` missing
- Backup script + cron job for MySQL
- Order tracking page (`track.php?ref=XXX`)
- Analytics (Umami)
- "Honey Tips" heading in English on Arabic page
- `#about` section: low text/background contrast

## What was NOT touched

- `style.css`, `style.min.css`, `main.js`, `main.min.js` untouched
  (all styling comes from additive `v4.css` override layer)
- Database schema (only the local SQLite dev DB was rebuilt;
  production MySQL is untouched until deploy + auto-migration)
- Order flow logic (only the two real bugs were fixed)
- Admin pages (only the 404 CSS reference was repaired)
