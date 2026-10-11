# KantinSMK Go

Marketplace kantin sekolah bergaya GoFood untuk satu SMK. Pembeli (siswa/guru/staf) memesan dari beberapa tenant kantin dalam satu keranjang, membayar sekali (demo QRIS / transfer / tunai), dan mengambil langsung saat pesanan siap.

**Stack:** Laravel 12 · Livewire 3 (+ Alpine bawaan) · MySQL 8 · Tailwind CSS (Play CDN, tanpa build step)

---

## 1. Instalasi

```bash
git clone <repo> kantinsmk-go && cd kantinsmk-go
composer install
cp .env.example .env
php artisan key:generate

# buat database MySQL lalu sesuaikan DB_* di .env
mysql -u root -e "CREATE DATABASE kantinsmk_go CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed
php artisan serve          # http://localhost:8000
```

> Tidak ada `npm install` / Vite. Tailwind dimuat via CDN dan Livewire menyuntikkan asetnya sendiri. Untuk produksi Anda bisa mengganti CDN dengan CSS terkompilasi (lihat bagian 6).

### Akun demo (dari seeder)

| Peran           | Email                 | Password  | Tenant          |
|-----------------|-----------------------|-----------|-----------------|
| Platform Admin  | admin@smkgo.id        | admin123  | —               |
| Tenant Admin    | owner@bu-rina.id      | owner123  | Kantin Bu Rina  |
| Tenant Staf     | staff@bu-rina.id      | staff123  | Kantin Bu Rina  |
| Tenant Admin    | owner@kopsis.id       | owner123  | Koperasi Siswa  |
| Tenant Admin    | owner@jajancorner.id  | owner123  | Jajan Corner    |
| Pembeli (siswa) | siswa@smkgo.id        | buyer123  | —               |
| Pembeli (guru)  | guru@smkgo.id         | guru123   | —               |

Akun admin platform diambil dari `ADMIN_EMAIL` / `ADMIN_PASSWORD` di `.env` (seeder idempoten — aman dijalankan ulang).

---

## 2. Peran & akses

| Peran            | Akses |
|------------------|-------|
| `user`           | Katalog, keranjang multi-tenant, checkout, pembayaran demo, tracking pesanan, ulasan, batalkan pesanan (sebelum diproses) |
| `tenant_staf`    | Papan pesanan tenant sendiri: majukan status *Dikonfirmasi → Disiapkan → Siap → Selesai* |
| `tenant_admin`   | Semua akses staf + dashboard tenant, CRUD menu, buka/tutup tenant, verifikasi bayar tunai, pembatalan/refund, settlement harian |
| `admin`          | Dashboard global, monitor transaksi semua tenant, persetujuan/penangguhan tenant, provisioning & nonaktif akun (tunggal & import massal), audit log |

Tidak ada registrasi publik — semua akun dibuat oleh Platform Admin.

---

## 3. Arsitektur

```
app/
├── Enums/            Role, OrderStatus, PaymentMethod, PaymentStatus (state machine + label UI)
├── Models/
│   ├── Scopes/TenantScope.php        ← isolasi data: tenant role hanya lihat tenant_id miliknya
│   ├── Concerns/BelongsToTenant.php  ← trait: pasang scope + auto-isi tenant_id saat create
│   ├── User, Tenant, Category, Product, Invoice, Order, OrderItem, Review, AuditLog
├── Policies/         OrderPolicy (view/advance/cancel/verifyPayment/review), ProductPolicy
├── Services/
│   ├── CartService.php          keranjang di session (server-side, tanpa JS)
│   ├── CheckoutService.php      SPLIT CHECKOUT: 1 Invoice → N Order per tenant (DB transaction)
│   ├── OrderStatusService.php   transisi status, cancel/refund, sinkron status invoice
│   └── AuditLogger.php
├── Http/
│   ├── Controllers/Auth/LoginController.php  (throttle 5x/menit, cek is_active)
│   └── Middleware/EnsureRole.php             alias `role:admin,tenant_admin,...`
└── Livewire/
    ├── Buyer/   Catalog, TenantMenu, Cart, CartBadge, Checkout, InvoiceShow, Orders, OrderDetail, Account
    ├── Tenant/  OrderBoard, Dashboard, Products, Payments
    └── Admin/   Dashboard, Transactions, Tenants, Users, AuditLogs
resources/views/
├── components/layouts/  base (head + toast), app (pembeli: top nav desktop + bottom nav mobile), panel (tenant/admin: sidebar + bottom nav)
├── components/          icon, flash, status-badge, empty-state, page-header, product-card
├── auth/login.blade.php
└── livewire/…           satu view per komponen
database/
├── migrations/  2 file: core (tenants, users, categories, products) & transaksi (invoices, orders, order_items, reviews, audit_logs)
└── seeders/DatabaseSeeder.php
```

### Alur transaksi

```
Keranjang (session) ──checkout──▶ Invoice (1)  ──▶ Order tenant A ──▶ OrderItem…
                                   payment_method  ──▶ Order tenant B ──▶ OrderItem…
```

* **QRIS / Transfer** → order lahir `pending` (Menunggu Pembayaran). Pembeli menekan *"Saya sudah bayar (simulasi)"* di halaman invoice → invoice `paid`, seluruh order → `confirmed`.
* **Tunai** → order langsung `confirmed`, `payment_status = pending`. Tenant admin menekan *Terima bayar* saat pembeli datang. Order tidak bisa `completed` sebelum lunas.
* Status operasional oleh tenant: `confirmed → preparing → ready → completed`.
* Pembatalan: pembeli (saat `pending`/`confirmed`), tenant admin (tenant sendiri), platform admin. Jika sudah lunas → `payment_status = refunded`.
* Invoice otomatis `paid` bila semua order aktifnya lunas, `cancelled` bila semua order dibatalkan.
* Ulasan (1–5 bintang) hanya setelah `completed`, satu per order.

### Isolasi tenant

`TenantScope` dipasang pada `Product` dan `Order`. Jika user login adalah `tenant_admin`/`tenant_staf`, semua query otomatis mendapat `WHERE tenant_id = ?`. Pembeli dan admin platform tidak dibatasi. Ditambah Policy untuk aksi (advance/cancel/verify) sehingga manipulasi ID lewat request Livewire tetap ditolak (403).

---

## 4. Pembayaran = DEMO

Tidak ada gateway nyata. QRIS ditampilkan sebagai pola SVG deterministik dari kode invoice, Virtual Account digenerate acak. Untuk integrasi nyata (Midtrans/Xendit), ganti `InvoiceShow::simulatePayment()` dengan webhook controller yang memanggil `CheckoutService::markInvoicePaid()`.

---

## 5. Perintah tambahan

```bash
php artisan kantin:reset-availability   # set semua menu "tersedia hari ini" (jadwalkan tiap pagi via cron)
php artisan db:seed                     # aman diulang (updateOrCreate)
```

Contoh cron: `0 6 * * * cd /path && php artisan kantin:reset-availability`

---

## 6. Di mana JavaScript digunakan (dan mengapa)

Prinsip: seluruh logika bisnis, validasi, keranjang, dan render dilakukan di server (Blade + Livewire). JavaScript hanya pada titik yang **tidak bisa** diselesaikan tanpa JS:

| # | Lokasi | JS yang dipakai | Alasan | Bisa dihapus? |
|---|--------|-----------------|--------|---------------|
| 1 | `components/layouts/base.blade.php` | **Tailwind Play CDN** (`cdn.tailwindcss.com`) + 6 baris konfigurasi warna/font | Styling tanpa build step (opsi paling ringan untuk setup). | Ya — kompilasi Tailwind ke CSS statis dan ganti `<script>` dengan `<link rel="stylesheet">`. |
| 2 | `@livewireScripts` (semua halaman) | **Livewire 3 runtime** (sudah membundel Alpine.js) | Interaksi tanpa reload halaman: tambah ke keranjang, ubah qty, filter/pencarian live, tab status, modal form, `wire:navigate` (SPA-like), `wire:confirm` (dialog konfirmasi). Tanpa ini tiap aksi butuh full-page reload. | Tidak (inti Livewire). |
| 3 | `wire:poll` pada `buyer/orders`, `buyer/order-detail`, `tenant/order-board` | Polling Livewire (8–10 detik) | Status pesanan "live" untuk pembeli & tenant tanpa WebSocket/Pusher. | Bisa diganti Laravel Echo/Reverb jika ingin real-time sejati. |
| 4 | `components/layouts/base.blade.php` (blok `x-data` toast) | **Alpine.js** (~10 baris, bawaan Livewire) | Notifikasi toast yang muncul lalu hilang otomatis setelah aksi (mis. "Nasi Goreng ditambahkan"). Timer & animasi hanya bisa di sisi klien. | Ya — ganti dengan flash message server-side (`session()->flash`), hilang saat reload. |

Tidak ada file `.js` custom, tidak ada `node_modules`, tidak ada bundler. Keranjang disimpan di **session server**, bukan `localStorage`.

---

## 7. Struktur tabel MySQL (ringkas)

| Tabel | Kolom kunci |
|-------|-------------|
| tenants | name, slug, status(pending/active/suspended), is_open, bank_* |
| users | name, email, identifier(NIS/NIP), password(bcrypt), role, tenant_id, is_active |
| categories | name, slug, sort_order |
| products | tenant_id, category_id, name, price, image_url, is_available_today, deleted_at |
| invoices | code, buyer_id, total, payment_method, payment_status, payment_reference, paid_at |
| orders | code, invoice_id, buyer_id, tenant_id, status, payment_status, subtotal, *_at, cancel_reason |
| order_items | order_id, product_id, product_name(snapshot), unit_price(snapshot), qty, line_total, note |
| reviews | order_id(unique), buyer_id, tenant_id, rating, comment |
| audit_logs | actor_id, action, subject_type, subject_id, meta(json), ip, created_at |

Session/cache memakai driver `file` sehingga tidak butuh tabel tambahan.

---

## 8. Checklist uji manual

1. Login `siswa@smkgo.id` → tambah menu dari **2 tenant berbeda** → keranjang menampilkan 2 grup + info "akan dipisah menjadi 2 order".
2. Checkout **QRIS** → halaman invoice menampilkan QR + tombol *Saya sudah bayar* → klik → status order menjadi *Dikonfirmasi*.
3. Login `staff@bu-rina.id` (tab lain) → papan pesanan hanya menampilkan order Kantin Bu Rina → klik *Tandai Sedang Disiapkan* → *Siap Diambil* → *Selesai*.
4. Kembali ke pembeli → timeline bergerak otomatis (poll) → beri ulasan setelah *Selesai*.
5. Checkout **Tunai** → order langsung *Dikonfirmasi* dengan *Belum Dibayar* → login `owner@bu-rina.id` → menu **Pembayaran** → *Lunas* → *Selesai* baru bisa ditandai.
6. Login `admin@smkgo.id` → buat tenant baru (status pending) → *Setujui* → buat akun `tenant_admin` untuk tenant tersebut → import massal 2 siswa → cek **Audit Log**.
7. Coba akses `/admin` sebagai pembeli → diarahkan kembali ke beranda dengan pesan "tidak memiliki akses".
