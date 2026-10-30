# Spesifikasi Fitur: Pencatatan Belanja Pegawai di Koperasi

Dokumen ini adalah spesifikasi sekaligus prompt untuk AI coding. Ditulis berdasarkan
`SYSTEM_ARCHITECTURE.md` (bukan dari membaca kode), jadi **nama tabel, kolom, enum, dan
class di bawah ini adalah usulan yang harus dicocokkan dengan kode asli.**

---

## 0. Instruksi untuk AI coding (baca dulu)

1. **Sebelum menulis kode, baca codebase yang ada**: migrations, model Order/Invoice/User,
   enum status, Policy, `TenantScope`, `AuditLogger`, dan aksi `markPaid`. Lalu tulis ringkasan
   singkat: (a) bagian spesifikasi ini yang sudah ada di kode, (b) yang berbeda dari asumsi,
   (c) rencana perubahan. **Berhenti dan tunggu konfirmasi** sebelum lanjut ke implementasi.
2. Ikuti konvensi yang sudah dipakai di proyek (Livewire, Policy, enum, `AuditLogger`,
   transaksi DB). Jangan mengubah alur checkout, pembayaran, atau tenant lain di luar yang
   dibutuhkan fitur ini.
3. Kerjakan **per tahap** (bagian 8), satu tahap per sesi, dengan test di tiap tahap.
4. Semua nominal disimpan sebagai **integer rupiah** (tanpa desimal). Semua perhitungan bulan
   memakai zona waktu **Asia/Jakarta**.
5. Jika ada yang ambigu atau bertentangan dengan kode, **tanyakan**, jangan menebak.

## 1. Tujuan

Koperasi sekolah mewajibkan pegawai berbelanja dengan total minimum per bulan, dan nilai
minimumnya berbeda per jabatan. Aplikasi harus mencatat total belanja tiap pegawai per bulan
dari dua jalur dan membandingkannya dengan minimum jabatannya.

- **Jalur A (app):** pegawai memesan dari perangkatnya, bayar QRIS manual atau tunai, lalu
  petugas koperasi memverifikasi pembayaran.
- **Jalur B (kasir):** pegawai belanja langsung di koperasi, petugas mencatat nominalnya di app.
- Aplikasi **hanya merekam dan melaporkan**. Konsekuensi jika kurang dari minimum ada di luar app.

## 2. Aturan hitung (inti fitur)

Total belanja pegawai U pada bulan M =

```
SUM(subtotal order milik U di tenant koperasi)
  WHERE pembayaran lunas
    AND tidak dibatalkan / di-void
    AND waktu lunas (paid_at) berada di bulan M (Asia/Jakarta, [awal bulan, awal bulan berikutnya))
+ SUM(penyesuaian U di tenant koperasi untuk bulan M)
```

- Pakai subtotal **order tenant koperasi**, bukan total invoice (satu invoice bisa berisi
  tenant lain).
- Biaya layanan/biaya lain **tidak** dihitung; hanya harga barang. (Konfirmasi dengan pemilik.)
- Bulan ditentukan oleh **waktu lunas**, bukan waktu pesan dibuat.
- Tampilkan juga angka terpisah **"menunggu verifikasi"** = order koperasi milik U yang
  pembayarannya belum diverifikasi dan belum dibatalkan. Angka ini tidak ikut total resmi.

## 3. Perubahan data (usulan, sesuaikan dengan skema asli)

| Tabel | Perubahan |
|---|---|
| `positions` (baru) | `id`, `name`, `is_active`, timestamps |
| `users` | + `position_id` (nullable, FK), + `employee_number` (NIP, nullable, unique) bila belum ada |
| `spending_targets` (baru) | `id`, `position_id`, `tenant_id` (koperasi), `amount` (unsigned bigint), `effective_from` (date), `effective_to` (date, nullable), `created_by`. Periode: bulanan. |
| `orders` | + `source` (`app`/`counter`, default `app`), + `entered_by` (nullable, FK users), + alasan pembatalan bila belum ada. Pakai status batal yang sudah ada jika cocok. |
| `spending_adjustments` (baru) | `id`, `user_id`, `tenant_id`, `period` (`YYYY-MM`), `amount` (signed integer), `reason`, `created_by`, timestamps |
| `spending_closings` (baru) | `id`, `tenant_id`, `period` (`YYYY-MM`), `closed_at`, `closed_by`; unique (`tenant_id`, `period`) |
| `spending_closing_items` (baru) | `closing_id`, `user_id`, `position_id`, `target_amount`, `total_amount`, `is_met`; unique (`closing_id`, `user_id`) |

Catatan:
- `spending_targets` memakai tanggal berlaku agar perubahan target tidak mengubah bulan lalu.
  Target yang berlaku untuk bulan M = baris dengan `effective_from` terbaru yang ≤ awal M.
- Entri kasir (`source = counter`) tidak punya rincian barang. Jika `orders`/`order_items` mewajibkan
  item, gunakan satu baris generik "Belanja kasir" dengan harga = nominal. Periksa constraint asli.
- Tambahkan index untuk query total: (`buyer_id`, `tenant_id`, `payment_status`, `paid_at`).

## 4. Peran dan hak akses

Gunakan role yang sudah ada; periksa nama aslinya di kode.

| Aksi | Pegawai (pembeli) | Staf/kasir koperasi | Admin koperasi |
|---|---|---|---|
| Lihat total & sisa target **miliknya sendiri** | ya | - | - |
| Verifikasi pesanan app (aksi `markPaid` yang sudah ada) | - | ya | ya |
| Catat belanja kasir | - | ya | ya |
| Batalkan (void) entri kasir dengan alasan | - | ya | ya |
| Lihat rekap semua pegawai, ekspor | - | **tidak** | ya |
| Kelola jabatan & target, tutup bulan, buat penyesuaian | - | tidak | ya |

- Total belanja pribadi tidak boleh terlihat oleh pegawai lain atau staf biasa.
- Semua aksi dilindungi Policy dan **diuji dari sisi tenant lain** (tenant lain tidak boleh
  mengakses data ini).

## 5. Alur kasir (Jalur B)

1. Kasir mencari pegawai (nama atau NIP).
2. Sistem menampilkan **pesanan app yang masih terbuka** milik pegawai itu di koperasi.
   - Jika ada dan pegawai membayar pesanan itu → verifikasi memakai aksi `markPaid` yang sudah ada.
     **Tidak boleh membuat entri baru** (mencegah hitungan ganda).
   - Jika tidak ada → lanjut ke langkah 3.
3. Form: nominal, metode bayar (tunai/QRIS), catatan opsional. Layar konfirmasi menampilkan
   nama pegawai dan nominal sebelum disimpan.
4. Simpan sebagai order `source = counter`, status lunas, `paid_at = sekarang`,
   `entered_by = kasir`, dan catat ke audit log.
5. Perlindungan: cegah submit ganda (idempotency/disable tombol), dan beri peringatan
   konfirmasi jika ada entri dengan pegawai + nominal sama dalam 2 menit terakhir.
6. **Batas nilai per transaksi: BELUM DITENTUKAN.** Sediakan satu nilai konfigurasi
   (mis. `koperasi.max_transaction_amount`, `null` = tanpa batas) yang diterapkan di entri
   kasir dan checkout app untuk tenant koperasi. Jangan mengisi angka sendiri.

Entri kasir **tidak boleh diedit**. Koreksi = void dengan alasan wajib, lalu buat entri baru.

## 6. Penutupan bulan

- Admin menutup bulan M untuk tenant koperasi → sistem menyimpan snapshot ke
  `spending_closing_items` (total, target yang berlaku, `is_met`) dalam satu transaksi DB.
- Setelah ditutup, entri dan void untuk bulan itu **terkunci**. Koreksi susulan dibuat sebagai
  `spending_adjustments` yang berlaku di bulan berjalan, dengan alasan dan audit log.
- Pegawai ganti jabatan di tengah bulan: **belum diputuskan.** Default sementara: jabatan yang
  berlaku pada saat penutupan bulan. Tandai sebagai TODO yang perlu dikonfirmasi.

## 7. Tampilan

- **Pegawai:** total terverifikasi bulan ini, angka "menunggu verifikasi", target jabatan,
  sisa kekurangan, dan riwayat transaksi (app + kasir) bulan ini. Mobile-first.
- **Admin:** tabel rekap semua pegawai (nama, jabatan, total, target, status), filter bulan,
  ekspor Excel/CSV; halaman kelola jabatan & target; tombol tutup bulan dengan konfirmasi.
- **Kasir:** halaman cari pegawai + form cepat (antrean di jam istirahat, minimalkan ketukan).

## 8. Tahap pengerjaan

1. **Skema + perhitungan.** Migrations, model, seeder jabatan contoh, service
   `SpendingTotalCalculator` (satu-satunya tempat aturan bagian 2), unit test.
2. **Layar pegawai** (hanya baca).
3. **Entri kasir** (bagian 5), void, audit log.
4. **Rekap admin, ekspor, kelola jabatan/target, penutupan bulan** (bagian 6).

## 9. Test yang wajib ada

- Total hanya menghitung order lunas koperasi; order tenant lain, batal, dan void tidak dihitung.
- Batas bulan: order dipesan tanggal 31 pukul 23.58 tetapi lunas tanggal 1 masuk bulan berikutnya.
- Subtotal koperasi dalam invoice campur tenant dihitung benar.
- Pesanan app terbuka tidak menghasilkan entri kasir ganda.
- Void mengubah total; entri kasir tidak bisa diedit.
- Bulan yang sudah ditutup menolak entri/void baru; penyesuaian masuk ke bulan berjalan.
- Target yang berlaku dipilih berdasarkan tanggal berlaku.
- Otorisasi: pegawai hanya melihat miliknya, staf tidak melihat rekap, tenant lain ditolak.
- Submit ganda dan peringatan duplikat pada entri kasir.

## 10. Belum diputuskan (jangan diisi oleh AI)

- Arti dan nilai "maksimal 1000 per transaksi".
- Nominal minimum tiap jabatan.
- Aturan pegawai ganti jabatan di tengah bulan.
- Apakah biaya layanan ikut dihitung (usulan: tidak).
- Siapa yang berhak melihat rekap semua pegawai (usulan: admin/bendahara saja).
