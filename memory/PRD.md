# KantinSMK Go — Completion & Hardening PRD

## Original problem statement
Finish & harden the existing **Laravel 12 + Livewire 3 + MySQL** repo (github.com/purplerez/kantin-smk) — a GoFood-style canteen marketplace for ONE SMK, buyer UI as PWA → Android TWA, on shared hosting. No rewrite. Close gaps vs. the master prompt, tighten security, launch-ready.

Locked decisions: keep `wire:poll` (no Pusher/Reverb), DB-backed cart, dedicated `payments` table, cash + manual verification now (Midtrans behind a webhook seam), notifications + settlements as real tables.
User answers to open items: reuse `password_reset_tokens` for onboarding; cash auto-cancel **60 min**; onboarding **one-time link hand-off** (no email now).

## Architecture / environment (this pod)
- Laravel 12 monolith served by `php artisan serve` on **:3000** (supervisor program `laravel`). Kubernetes ingress maps the preview URL to :3000, so the whole Livewire app (incl. `/livewire/update`) is reachable. `/api` is NOT used.
- **MariaDB 10.11** on 127.0.0.1:3306 (supervisor `mariadb`), DB `kantinsmk_go`, `root`/`root`. The template's FastAPI/React programs are stopped.
- PHP 8.2 + Composer installed; `laravel/reverb` dropped and lock re-resolved via `composer update`.
- Reset DB: `cd /app && php artisan migrate:fresh --seed --force`. Preview URL in `/app/memory/test_credentials.md`.

## User personas
- **Buyer** (siswa/guru): browse catalog, multi-tenant cart, checkout, pay (QRIS demo / transfer / cash), track orders, review, cancel before processing.
- **tenant_staf**: own-tenant order board (advance statuses only).
- **tenant_admin**: staf access + dashboard, menu CRUD, open/close, verify cash & transfer, refund, settlements.
- **admin (platform)**: global dashboard, transactions, tenant approval/suspend, user provisioning (single + bulk), settlements, audit log.

## Core requirements (static)
Split checkout (1 invoice → N tenant orders), tenant data isolation, role-guarded routes, DEMO payments with a clean gateway seam, admin-only provisioning (no public signup).

## Implemented in this pass (2026-06)
- **Dead weight removed**: `laravel/reverb` dropped from composer.json; lock cleaned.
- **DB migrations extended** (edited the 2 existing files, no loose files): added `carts`, `cart_items`, `payments`, `tenant_settlements`, `notifications`; added `users.must_change_password`; added `password_reset_tokens` (reused for onboarding).
- **Cart session → DB**: `CartService` now reads/writes `carts`/`cart_items` keyed by `buyer_id` (persists across devices/sessions — verified). Cart cleared inside the checkout transaction.
- **Payments split**: dedicated `payments` row per invoice (server-computed amount). `CheckoutService::markInvoicePaid()` is the single seam; syncs invoice + payment + orders + notifications. Buyer self-confirm limited to **QRIS demo**; **transfer & cash verified only by tenant_admin/admin** (backend policy). `PaymentWebhookController::handle()` stub for Midtrans (CSRF-exempt) — verified marks invoice paid.
- **Anti-race at checkout**: products re-fetched with `lockForUpdate`, availability re-checked and totals recomputed server-side; never trusts client qty/amount. Split-checkout runs in one DB transaction (partial failure rolls back the whole invoice). Refund path sets `payment_status = refunded`.
- **Admin-provisioned onboarding**: set-password via one-time token (`Str::random(64)`, **hash only** in `password_reset_tokens`, 48h TTL, single-use). Admin sees a one-time activation link (`/aktivasi/{token}`) per user + per-row "Aktivasi" button. `must_change_password` forces `/ganti-password` on first login (EnsureRole gate). Verified: valid→form, bad/expired/used→clear invalid message.
- **Policies**: added `TenantPolicy` + `PaymentPolicy` (role AND tenant_id match). `tenant_staf` blocked from verify/cancel/refund at policy level.
- **PWA/TWA foundation**: `public/manifest.json` (theme #00AA13, standalone, icons 192/512), minimal network-first `public/sw.js` (no offline logic), `public/.well-known/assetlinks.json` placeholder, PWA meta/link + SW registration in `base.blade.php`.
- **Notifications**: `notifications` table + `NotificationService`; order/payment events pushed to buyer & tenant; surfaced via a poll-refreshed bell in buyer + panel layouts.
- **Settlements**: `tenant_settlements` + `SettlementService` + `kantin:generate-settlements`; shown in tenant Payments (stored table) and new platform `admin/settlement` page.
- **Edge cases**: cash auto-cancel via `kantin:auto-cancel-unpaid` (60 min). Scheduled through Laravel `Schedule` (for shared-hosting `schedule:run`) AND via platform `.emergent/crons.yml` → `/cron/auto-cancel` & `/cron/settlements` (bearer-secret protected; verified 401 vs run).
- **Security hardening**: login rate limit changed from per-IP `throttle:5,1` to a named **email+IP** limiter (10/min) so a single-SMK NAT doesn't self-lock. Proxy trust enabled for HTTPS behind ingress.

## Verification
Testing agent iteration_1: 11/12 priority flows pass (~95%). Confirmed via curl: all role pages 200, onboarding token states, webhook, cron auth, and the full split-checkout→payment→settlement→notification chain (tinker). Fixed a stale compiled Blade view (`view:clear`) discovered during testing.

## NOT done (by design, per plan)
No Form Request classes (Livewire `rules()`), no TenantScope on Category, no Pusher/Reverb, no long-running queue worker. Tailwind still via Play CDN (compile before go-live).

## Backlog
- P1: Compile Tailwind to static CSS (drop CDN) before production.
- P1: Multi-tenant **transfer** verification currently allows tenant_admin only when the invoice is single-tenant; platform admin covers multi-tenant. Add an admin transfer-verify screen if multi-tenant transfers are common.
- P2: Real Midtrans signature validation in `PaymentWebhookController`.
- P2: Email delivery of activation links (queue:database + cron) if a provider is added.
- P2: Generate real branded PWA icons (current are generated placeholders) and fill `assetlinks.json` with the TWA signing SHA-256.

## Deployment (shared hosting)
`git pull` → `composer install --no-dev` → `php artisan migrate --force` → `config:cache route:cache view:cache`. HTTPS on (secure+httponly cookies). Cron: `* * * * * php artisan schedule:run`. Cloudflare page-cache only on anonymous catalog GETs (bypass cart/checkout/login).
