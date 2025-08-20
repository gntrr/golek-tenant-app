# Golek Tenant App

Aplikasi pemesanan booth event dengan alur pemesanan modern, metode pembayaran ganda (Midtrans & Transfer Bank), serta panel admin berbasis Filament. Saat ini fitur log/manajemen di admin sudah tersedia namun belum semua akses dibuka secara final.

## Ringkasan Fitur

- Landing page dengan hero dan galeri 3 event aktif
- Jelajah event dan daftar booth per event (tata letak dua kolom, peta venue jika ada)
- Pemesanan booth:
	- Form data tenant, membuat Order + menahan (ON_HOLD) booth selama durasi tertentu
	- Email Invoice otomatis saat Order dibuat
- Metode pembayaran:
	- Midtrans Snap (sementara akses masih sandbox): redirect ke Snap, webhook update status (dengan quick-verify saat return)
	- Transfer Bank: VA lokal (simulasi), unggah bukti pembayaran, admin verifikasi
	- Satu baris Payment per Order (provider bisa berganti: MIDTRANS/BANK_TRANSFER)
	- Halaman Cek Status Pembayaran (berdasarkan Invoice)
- Email:
	- Invoice saat Order dibuat
	- Receipt saat pembayaran berhasil (Midtrans settlement atau admin verifikasi transfer bank)
- Admin (Filament):
	- Order read-only dengan aksi bulk (Tandai Lunas/Menunggu/Dibatalkan) + relasi Payments & Payment Proofs
	- Dashboard widgets (ringkas)
	- Settings (toggle pembayaran, fallback banner, instruksi bank)
	- Webhook Logs (read-only), Email Logs (pencatatan pengiriman email)

Catatan: 
- Beberapa bagian manajemen/log di admin masih dalam tahap penyempurnaan akses dan UX. 
- Cron job untuk handle order dengan status "ON_HOLD" masih dalam tahap konfigurasi.
- Poin 1 juga masih dalam tahap konfigurasi, terutama untuk log dari Midtrans

---

## Deploy & Menjalankan di Lokal

Prasyarat:
- PHP 8.2+ dan Composer
- Node.js 18+ (atau Bun) untuk aset Vite
- PostgreSQL
- Opsional: Redis (session/queue) jika butuh scale-out

Langkah cepat:
1. Salin env dan generate key
	 ```cmd
	 copy .env.example .env
	 php artisan key:generate
	 ```
2. Konfigurasi `.env`
	 - DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
	 - APP_URL (contoh: http://localhost)
	 - MAIL_… (SMTP), FILESYSTEM_DISK (local atau s3), dan kredensial S3 bila diperlukan
	 - MIDTRANS_SERVER_KEY, MIDTRANS_IS_PRODUCTION=false (sandbox)
3. Install dependency backend & frontend
	 ```cmd
	 composer install --no-interaction --prefer-dist
	 npm install
	 ```
4. Migrasi database & storage link
	 ```cmd
	 php artisan migrate
	 php artisan storage:link
	 ```
5. Pastikan permission direktori tulis (jika Linux/WSL)
	 - `storage`, `bootstrap/cache`, `storage/framework/{cache,sessions,views,livewire-tmp}` writable oleh user web (www-data)
6. Jalankan server & assets (opsional jika pakai Laragon)
	 ```cmd
	 php artisan serve
	 npm run dev
	 ```

Catatan: Akun admin demo sudah tersedia. Kredensial login ada di bagian "Akses Demo Admin" di bawah; URL akan dibagikan setelah proses deploy.

---

## Deploy ke Hosting (Nixpacks/Supervisor)

Repo ini sudah menyertakan `nixpacks.toml` yang men-setup:
- Nginx (serve /app/public)
- PHP-FPM
- Laravel queue worker (supervisor)

Hal penting:
- Set `NIXPACKS_PHP_ROOT_DIR=/app/public` agar Nginx mengarah ke `public`
- Jalankan perintah rilis satu kali setiap deploy:
	```cmd
	php artisan migrate --force
	php artisan storage:link
	php artisan optimize
	```
- Pastikan direktori tulis writable (lihat bagian lokal)
- Untuk multi-replica: gunakan Redis untuk session/queue, atau aktifkan sticky session

Environment yang umum (dan kegunaannya):

- App
	- `APP_NAME`: Nama aplikasi (muncul di email/title).
	- `APP_ENV`: `local`/`staging`/`production` untuk perilaku environment.
	- `APP_KEY`: Kunci enkripsi. Wajib di-set via `php artisan key:generate`.
	- `APP_URL`: Base URL aplikasi (dipakai untuk link asset, email, redirect).
	- `APP_DEBUG`: Set `false` di production agar error tidak terekspos.

- Database (PostgreSQL)
	- `DB_CONNECTION=pgsql`: Driver database Postgres.
	- `DB_HOST`: Host Postgres (contoh: `localhost` atau hostname layanan).
	- `DB_PORT=5432`: Port Postgres (default 5432).
	- `DB_DATABASE`: Nama database.
	- `DB_USERNAME`: Username database.
	- `DB_PASSWORD`: Password database.
	- Catatan: Jika hosting memberi `DATABASE_URL`, map nilainya ke variabel di atas atau gunakan helper parsing sesuai platform.

- Queue & Cache & Session
	- `QUEUE_CONNECTION`: `database` (default siap pakai) atau `redis`.
		- Worker sudah dijalankan via supervisor (lihat nixpacks.toml), jadi aman untuk kirim email asinkron dsb.
	- `CACHE_DRIVER`: `file` atau `redis`. Untuk multi-replica disarankan `redis`.
	- `SESSION_DRIVER`: `file` atau `redis`. Untuk multi-replica disarankan `redis` (hindari sesi pindah pod).

- Filesystem (upload permanen)
	- `FILESYSTEM_DISK`: `local` atau `s3`.
		- Jika `s3`, set juga: `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`.
		- Livewire temporary upload tetap memakai disk lokal (default) agar cepat.

- Mail (SMTP)
	- `MAIL_MAILER=smtp`: Gunakan SMTP.
	- `MAIL_HOST`, `MAIL_PORT`: Host/port SMTP.
	- `MAIL_USERNAME`, `MAIL_PASSWORD`: Kredensial SMTP.
	- `MAIL_ENCRYPTION`: `tls`/`ssl` sesuai provider.
	- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`: Identitas pengirim email.

- Midtrans
	- `MIDTRANS_SERVER_KEY`: Server key dari Midtrans.
    - `MIDTRANS_CLIENT_KEY`: Client key dari Midtrans.
	- `MIDTRANS_IS_PRODUCTION`: `false` untuk sandbox, `true` untuk live.
	- Pastikan webhook `POST /webhook/midtrans` dapat diakses publik dan didaftarkan di dashboard Midtrans.

Catatan Livewire upload:
- Batas upload sudah disetel (Nginx 35M, PHP 30M) di `nixpacks.toml`
- Livewire tmp memakai disk local; untuk scale-out pakai sticky session atau ganti SESSION_DRIVER ke redis

---

## Akses Demo Admin

Untuk mengakses demo sistem khususnya di halaman admin, silahkan lakukan login dengan kredensial berikut:
- Email: `admin@example.com`
- Password: `password`

---

## Alur Pemesanan Booth (End-to-End)

1) Pilih Event & Booth
- Pengunjung memilih event, melihat daftar booth (dua kolom, peta venue bila tersedia)
- Klik “Pesan” pada booth yang tersedia

2) Isi Form Data Tenant
- Isi nama, email, telepon, perusahaan/company
- Saat submit, sistem membuat Order dengan status AWAITING_PAYMENT, menghasilkan nomor invoice, menahan booth (ON_HOLD) untuk durasi tertentu, dan mengirim Email Invoice

3) Pilih Metode Pembayaran
- Halaman “Metode Pembayaran” menyediakan:
	- Midtrans Snap (jika di Settings Midtrans enabled)
	- Transfer Bank (unggah bukti)
- Ada halaman terpisah untuk “Cek Status Pembayaran” berdasarkan nomor invoice

4a) Pembayaran via Midtrans
- User diarahkan ke Snap; setelah selesai, webhook Midtrans memperbarui Order/Payment menjadi PAID/SETTLEMENT, menandai booth BOOKED, dan mengirim Receipt email
- Quick-verify di endpoint return mencoba konfirmasi cepat agar status LUNAS terlihat tanpa menunggu webhook

4b) Pembayaran via Transfer Bank
- Sistem membuat/menampilkan nomor VA simulasi dan instruksi transfer
- User mengunggah bukti transfer
- Admin memverifikasi di panel Filament (aksi bulk “Tandai Lunas”), yang akan:
	- Set Order = PAID
	- Set Payment = SETTLEMENT + paid_at
	- Setujui Payment Proof
	- Menandai booth BOOKED
	- Mengirim Receipt email

5) Cek Status & Notifikasi
- User dapat mengecek status pembayaran dari halaman “Cek Status” dengan nomor invoice
- Email Invoice & Receipt dicatat di EmailLog; Webhook dari Midtrans dicatat di WebhookLog

6) Expire/Cancel (opsional)
- Jika pembayaran gagal/kedaluwarsa, status Order/Payment disesuaikan dan booth dikembalikan ke AVAILABLE/ON_HOLD sesuai konteks

---

## Troubleshooting Singkat
- Tombol Midtrans tidak muncul: pastikan Settings → payments.midtrans_enabled = ON
- Receipt tertunda atau tidak terkirim: cek konfigurasi SMTP dan periksa EmailLog di admin
- File upload gagal: cek batas upload (Nginx/PHP) dan permission direktori storage

---

## Keamanan
- Simpan kredensial (DB, SMTP, Midtrans, S3) hanya di `.env` pada environment server
- Jangan commit `.env` ke repository

---

