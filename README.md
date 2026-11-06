# 🚑 RapidAid Ambulance Service

## 1. Tentang Program

**RapidAid Ambulance Service** adalah aplikasi berbasis web yang digunakan untuk membantu proses **layanan pemesanan dan pengelolaan ambulans secara digital**.

Aplikasi ini dibuat untuk mempermudah masyarakat ketika membutuhkan ambulans, mulai dari melakukan permintaan ambulans, menentukan lokasi penjemputan, melihat status permintaan, melakukan pembayaran, hingga melihat riwayat layanan.

Selain digunakan oleh pengguna, RapidAid juga menyediakan **Admin Panel** yang digunakan oleh admin untuk mengelola data dan aktivitas layanan ambulans.

Secara sederhana, sistem RapidAid memiliki dua pengguna utama:

* 👤 **User/Pengguna** → menggunakan layanan ambulans.
* 👨‍💼 **Admin** → mengelola seluruh data dan aktivitas sistem.

---

# 2. Tujuan Program

RapidAid dibuat dengan beberapa tujuan utama:

1. Mempermudah masyarakat dalam melakukan permintaan ambulans.
2. Membantu pengguna menentukan lokasi penjemputan dengan bantuan peta.
3. Menampilkan informasi status permintaan ambulans.
4. Membantu pengguna melihat dan mengelola riwayat permintaan.
5. Menyediakan sistem berlangganan layanan ambulans.
6. Menyediakan sistem pembayaran dan upload bukti pembayaran.
7. Membantu admin mengelola data pengguna.
8. Membantu admin mengelola data ambulans dan driver.
9. Membantu admin mengelola data rumah sakit.
10. Membantu admin memantau permintaan ambulans.
11. Menyediakan laporan dan statistik layanan.
12. Menyediakan sistem notifikasi untuk pengguna.

---

# 3. Gambaran Umum Sistem

Alur penggunaan RapidAid secara umum adalah:

```text
                 RAPIDAID
                    │
          ┌─────────┴─────────┐
          │                   │
        USER                ADMIN
          │                   │
          ▼                   ▼
     Login/Register       Admin Login
          │                   │
          ▼                   ▼
    User Dashboard      Admin Dashboard
          │                   │
     ┌────┼────┐        ┌─────┼─────┐
     │    │    │        │     │     │
     ▼    ▼    ▼        ▼     ▼     ▼
  Request Track History Users Ambulance Reports
  Ambulance Request       Drivers Hospitals
     │                    Payments
     ▼                    Requests
  Lokasi GPS
     │
     ▼
  Pilih Ambulans
     │
     ▼
  Permintaan
     │
     ▼
  Pembayaran
     │
     ▼
  Tracking
     │
     ▼
  Selesai
```

---

# 4. Struktur Folder Program

Struktur utama aplikasi RapidAid adalah:

```text
rapidaid/
│
├── admin/
├── api/
├── assets/
├── auth/
├── config/
├── includes/
├── uploads/
├── user/
│
├── index.php
├── database.sql
└── README.md
```

Setiap folder memiliki fungsi yang berbeda.

---

# 5. Folder `admin/`

Folder:

```text
admin/
```

digunakan untuk menyimpan seluruh halaman yang berhubungan dengan **Administrator**.

Admin memiliki akses untuk mengelola data utama yang digunakan oleh sistem RapidAid.

Strukturnya:

```text
admin/
├── dashboard.php
├── users.php
├── ambulances.php
├── drivers.php
├── hospitals.php
├── requests.php
├── payments.php
├── subscriptions.php
├── reports.php
├── _sidebar.php
└── _topbar.php
```

### `dashboard.php`

Digunakan sebagai **halaman utama admin**.

Halaman ini dapat menampilkan informasi seperti:

* jumlah pengguna
* jumlah ambulans
* jumlah driver
* jumlah rumah sakit
* jumlah permintaan ambulans
* jumlah pembayaran
* statistik layanan

Dashboard menjadi halaman pertama setelah admin berhasil login.

---

### `users.php`

Digunakan untuk mengelola data pengguna.

Admin dapat melihat dan mengelola:

* nama pengguna
* email
* nomor telepon
* status pengguna
* data akun lainnya

---

### `ambulances.php`

Digunakan untuk mengelola data ambulans.

Contohnya:

* nomor kendaraan
* jenis ambulans
* status ambulans
* informasi kendaraan

Status ambulans dapat digunakan untuk mengetahui apakah kendaraan sedang:

```text
Available
Busy
Maintenance
```

---

### `drivers.php`

Digunakan untuk mengelola data pengemudi ambulans.

Data yang dapat dikelola antara lain:

* nama driver
* nomor telepon
* identitas driver
* status driver

---

### `hospitals.php`

Digunakan untuk mengelola data rumah sakit.

Informasi rumah sakit dapat berupa:

* nama rumah sakit
* alamat
* nomor telepon
* lokasi
* informasi lainnya

---

### `requests.php`

Digunakan untuk mengelola **permintaan ambulans**.

Admin dapat melihat permintaan yang dilakukan oleh pengguna dan melakukan proses seperti:

* melihat detail permintaan
* melihat lokasi penjemputan
* menentukan ambulans
* menentukan driver
* memperbarui status permintaan

Contoh status:

```text
Pending
Assigned
On The Way
Picked Up
Completed
Cancelled
```

---

### `payments.php`

Digunakan untuk mengelola pembayaran pengguna.

Admin dapat:

* melihat pembayaran
* melihat bukti pembayaran
* mengonfirmasi pembayaran
* mengubah status pembayaran

---

### `subscriptions.php`

Digunakan untuk mengelola paket berlangganan RapidAid.

Contoh paket:

```text
Basic
Premium
Family
```

---

### `reports.php`

Digunakan untuk menampilkan laporan sistem.

Contohnya:

* laporan jumlah permintaan
* laporan pendapatan
* laporan pembayaran
* statistik penggunaan ambulans

Data dapat ditampilkan dalam bentuk tabel maupun grafik.

---

### `_sidebar.php`

Berisi bagian **sidebar/menu navigasi admin**.

Contohnya:

```text
Dashboard
Users
Ambulances
Drivers
Hospitals
Requests
Payments
Subscriptions
Reports
Logout
```

File ini dibuat terpisah agar sidebar dapat digunakan pada beberapa halaman admin.

---

### `_topbar.php`

Berisi bagian atas halaman admin.

Biasanya digunakan untuk menampilkan:

* nama admin
* notifikasi
* menu profile
* tombol navigasi

---

# 6. Folder `api/`

Folder:

```text
api/
```

digunakan untuk menyimpan endpoint yang menangani **komunikasi antara JavaScript dengan server PHP**.

API digunakan ketika halaman membutuhkan data dari server tanpa harus melakukan reload halaman secara keseluruhan.

Contohnya:

```text
JavaScript
    │
    ▼
   API
    │
    ▼
 PHP + Database
    │
    ▼
   JSON
    │
    ▼
JavaScript
```

API dapat digunakan untuk kebutuhan seperti:

* mengambil data
* menyimpan data
* memperbarui status
* mengambil lokasi
* reverse geocoding
* proses AJAX

---

# 7. Folder `assets/`

Folder:

```text
assets/
```

digunakan untuk menyimpan file pendukung tampilan website.

Strukturnya:

```text
assets/
├── css/
│   └── main.css
│
└── js/
    └── main.js
```

---

## `assets/css/main.css`

File ini berisi CSS utama aplikasi.

Digunakan untuk mengatur:

* warna
* layout
* ukuran
* typography
* button
* card
* navbar
* sidebar
* responsive design
* dark mode
* animasi
* glassmorphism

---

## `assets/js/main.js`

File JavaScript utama aplikasi.

Digunakan untuk membuat halaman menjadi interaktif.

Contohnya:

* membuka/tutup menu
* dark mode
* validasi form
* interaksi tombol
* AJAX
* notifikasi
* interaksi komponen halaman

---

# 8. Folder `auth/`

Folder:

```text
auth/
```

digunakan untuk menangani **autentikasi pengguna dan admin**.

Strukturnya:

```text
auth/
├── login.php
├── register.php
├── admin-login.php
├── forgot-password.php
└── logout.php
```

---

## `login.php`

Digunakan untuk login pengguna.

Pengguna memasukkan:

```text
Email
Password
```

Jika data benar, pengguna akan diarahkan ke:

```text
user/dashboard.php
```

---

## `register.php`

Digunakan untuk membuat akun baru.

Pengguna dapat mengisi data seperti:

* nama
* email
* password
* nomor telepon

---

## `admin-login.php`

Digunakan khusus untuk login administrator.

Halaman ini dipisahkan dari login pengguna karena admin memiliki hak akses yang berbeda.

---

## `forgot-password.php`

Digunakan untuk proses ketika pengguna lupa password.

---

## `logout.php`

Digunakan untuk mengakhiri session/login pengguna atau admin.

---

# 9. Folder `config/`

Folder:

```text
config/
```

digunakan untuk menyimpan konfigurasi utama aplikasi.

Strukturnya:

```text
config/
└── database.php
```

---

## `database.php`

File ini digunakan untuk mengatur koneksi aplikasi dengan database MySQL.

Konfigurasi yang digunakan antara lain:

```php
DB_HOST
DB_USER
DB_PASS
DB_NAME
APP_URL
```

Contoh:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rapidaid_db');
```

File ini juga menggunakan **PDO** untuk melakukan komunikasi antara PHP dengan MySQL.

---

# 10. Folder `includes/`

Folder:

```text
includes/
```

digunakan untuk menyimpan fungsi-fungsi bantuan yang digunakan oleh beberapa halaman.

Struktur:

```text
includes/
└── helpers.php
```

---

## `helpers.php`

Berisi fungsi helper yang dapat digunakan berulang kali.

Tujuannya agar kode tidak perlu ditulis berulang pada setiap halaman.

Contohnya dapat digunakan untuk:

* validasi
* format data
* session
* redirect
* keamanan
* format tanggal
* format rupiah

---

# 11. Folder `uploads/`

Folder:

```text
uploads/
```

digunakan untuk menyimpan file yang di-upload oleh pengguna.

Contohnya:

```text
uploads/
├── payment-proof/
├── profile/
└── documents/
```

Salah satu penggunaan pentingnya adalah untuk menyimpan **bukti pembayaran**.

Misalnya pengguna melakukan pembayaran kemudian meng-upload:

```text
bukti-transfer.jpg
```

File tersebut disimpan di folder `uploads/`.

---

# 12. Folder `user/`

Folder:

```text
user/
```

digunakan untuk menyimpan halaman yang hanya digunakan oleh **pengguna yang sudah login**.

Strukturnya:

```text
user/
├── dashboard.php
├── request-ambulance.php
├── track-request.php
├── emergency-history.php
├── subscriptions.php
├── payment.php
├── payments.php
├── notifications.php
├── profile.php
├── _sidebar.php
└── _topbar.php
```

---

## `dashboard.php`

Merupakan halaman utama pengguna.

Dashboard dapat menampilkan:

* informasi akun
* status langganan
* status permintaan ambulans
* informasi layanan
* notifikasi

---

## `request-ambulance.php`

Merupakan salah satu fitur utama RapidAid.

Digunakan ketika pengguna ingin **memesan ambulans**.

Pengguna dapat:

1. Membuka halaman permintaan ambulans.
2. Menentukan lokasi penjemputan.
3. Menggunakan lokasi GPS.
4. Memilih lokasi melalui peta.
5. Melihat alamat berdasarkan koordinat.
6. Mengirim permintaan ambulans.

---

## `track-request.php`

Digunakan untuk melihat **status dan posisi ambulans**.

Contohnya:

```text
Request Created
      ↓
Ambulance Assigned
      ↓
Driver On The Way
      ↓
Ambulance Arrived
      ↓
Patient Picked Up
      ↓
Completed
```

Sistem juga dapat menampilkan simulasi pergerakan ambulans.

---

## `emergency-history.php`

Digunakan untuk melihat riwayat permintaan ambulans.

Pengguna dapat melihat:

* tanggal permintaan
* lokasi
* status
* ambulans
* detail permintaan

Tersedia juga fitur pencarian dan export data.

---

## `subscriptions.php`

Digunakan untuk melihat dan memilih paket berlangganan.

Contoh:

```text
Basic
Premium
Family
```

---

## `payment.php`

Digunakan untuk melakukan proses pembayaran.

Pengguna dapat mengirim bukti pembayaran melalui upload file.

---

## `payments.php`

Digunakan untuk melihat riwayat pembayaran pengguna.

Contohnya:

```text
Tanggal
Jenis pembayaran
Jumlah
Status
```

---

## `notifications.php`

Digunakan untuk menampilkan notifikasi.

Contohnya:

```text
Ambulans telah ditugaskan.
Pembayaran berhasil dikonfirmasi.
Permintaan ambulans selesai.
```

---

## `profile.php`

Digunakan untuk melihat dan mengubah informasi profil pengguna.

---

## `_sidebar.php`

Berisi menu navigasi pengguna.

Contohnya:

```text
Dashboard
Request Ambulance
Track Request
Emergency History
Subscriptions
Payments
Notifications
Profile
Logout
```

---

## `_topbar.php`

Berisi bagian atas dashboard pengguna.

Dapat menampilkan:

* nama pengguna
* notifikasi
* profile
* navigasi

---

# 13. `index.php`

File:

```text
index.php
```

merupakan **halaman utama/landing page** RapidAid.

Halaman ini adalah halaman yang pertama kali dilihat ketika membuka:

```text
http://localhost/rapidaid/
```

Landing page dapat berisi:

* informasi RapidAid
* fitur layanan
* informasi paket
* tombol login
* tombol register
* SOS button
* informasi kontak

---

# 14. `database.sql`

File:

```text
database.sql
```

merupakan file database utama aplikasi.

File ini berisi struktur database seperti:

* pembuatan database
* tabel
* relasi tabel
* data awal
* akun admin
* akun demo
* data ambulans
* data driver
* data rumah sakit
* data permintaan
* data pembayaran
* data subscription

File ini diperlukan agar aplikasi dapat berjalan dengan database MySQL.

---

# 15. `README.md`

File:

```text
README.md
```

merupakan dokumentasi project.

Dokumentasi dapat berisi:

* penjelasan program
* tujuan program
* cara instalasi
* struktur folder
* fitur
* teknologi
* konfigurasi
* informasi penggunaan

---

# 16. Sistem User

User merupakan masyarakat yang menggunakan layanan RapidAid.

Alur user:

```text
Register
   ↓
Login
   ↓
Dashboard
   ↓
Request Ambulance
   ↓
Tentukan Lokasi
   ↓
Kirim Permintaan
   ↓
Admin Memproses
   ↓
Ambulance Ditugaskan
   ↓
Track Ambulance
   ↓
Layanan Selesai
   ↓
History
```

---

# 17. Sistem Admin

Admin merupakan pihak yang bertanggung jawab mengelola sistem.

Alur admin:

```text
Admin Login
     ↓
Dashboard
     ↓
Melihat Request
     ↓
Memeriksa Data
     ↓
Menentukan Ambulance
     ↓
Menentukan Driver
     ↓
Memantau Request
     ↓
Konfirmasi Pembayaran
     ↓
Laporan
```

---

# 18. Sistem Permintaan Ambulans

Fitur utama RapidAid adalah permintaan ambulans.

Ketika user melakukan permintaan:

```text
User
 │
 ▼
Request Ambulance
 │
 ▼
Tentukan Lokasi
 │
 ▼
Latitude + Longitude
 │
 ▼
Reverse Geocoding
 │
 ▼
Alamat
 │
 ▼
Kirim Request
 │
 ▼
Database
 │
 ▼
Admin
```

Data permintaan kemudian dapat diproses oleh admin.

---

# 19. Sistem Lokasi dan Peta

RapidAid menggunakan:

* **Leaflet.js**
* **OpenStreetMap**
* **Nominatim**

untuk menangani fitur lokasi.

User dapat menentukan lokasi melalui:

### GPS

User dapat memilih:

```text
Use My Current Location
```

Sistem mengambil koordinat:

```text
Latitude
Longitude
```

### Map

User juga dapat memilih lokasi dengan melakukan klik pada peta.

---

# 20. Reverse Geocoding

Reverse geocoding digunakan untuk mengubah koordinat menjadi alamat yang lebih mudah dibaca.

Contohnya:

```text
Latitude:
-7.7956

Longitude:
110.3695
```

kemudian sistem meminta informasi lokasi kepada OpenStreetMap Nominatim.

Hasilnya dapat berupa:

```text
Area
City
Region
Postal Code
Country
Coordinates
```

Sehingga pengguna tidak perlu mengetik alamat secara manual.

## Alur API dan status request

Halaman permintaan ambulans mengirim koordinat ke endpoint `api/reverse-geocode.php` menggunakan metode `POST` dan body JSON:

```json
{"lat": -7.2575, "lng": 112.7521}
```

Endpoint memeriksa autentikasi dan rentang koordinat, lalu meminta alamat ke Nominatim. Respons sukses berisi alamat dan detail lokasi dalam format JSON, misalnya area, kota, wilayah, kode pos, negara, serta koordinat. Halaman menampilkan alamat pada kolom lokasi penjemputan dan detailnya pada panel data lokasi.

Antarmuka memberi feedback selama proses berlangsung:

* **Loading** — lookup alamat sedang diproses.
* **Sukses** — alamat dan detail lokasi ditampilkan.
* **Gagal** — pesan kesalahan ditampilkan, koordinat tetap digunakan sebagai lokasi penjemputan, dan tombol coba lagi tersedia.

Jika beberapa lookup berjalan berurutan, hasil request yang lebih lama diabaikan agar tidak menimpa pilihan lokasi terbaru. Endpoint dapat mengembalikan status HTTP `400` untuk koordinat tidak valid, `401` jika sesi berakhir, `404` jika alamat tidak ditemukan, `502` jika layanan alamat gagal diakses, atau `503` jika lookup sementara tidak tersedia.

---

# 21. Sistem Pembayaran

RapidAid memiliki sistem pembayaran untuk layanan atau subscription.

Alurnya:

```text
User
 ↓
Pilih Layanan/Paket
 ↓
Payment
 ↓
Upload Bukti
 ↓
Admin Memeriksa
 ↓
Payment Confirmed
```

Bukti pembayaran disimpan pada folder:

```text
uploads/
```

---

# 22. Sistem Subscription

RapidAid menyediakan beberapa paket layanan.

Contohnya:

```text
┌──────────────┐
│ BASIC        │
└──────────────┘

┌──────────────┐
│ PREMIUM      │
└──────────────┘

┌──────────────┐
│ FAMILY       │
└──────────────┘
```

User dapat memilih paket yang sesuai dengan kebutuhan.

---

# 23. Sistem Tracking

RapidAid menyediakan fitur tracking ambulans.

Tujuannya agar user dapat mengetahui perkembangan permintaan ambulans.

Contoh:

```text
🟢 Request Created

      ↓

🟡 Ambulance Assigned

      ↓

🟡 Driver On The Way

      ↓

🟢 Ambulance Arrived

      ↓

🟢 Completed
```

Tracking pada project ini dapat menggunakan simulasi posisi ambulans.

---

# 24. Sistem Notifikasi

Sistem notifikasi digunakan untuk memberikan informasi kepada pengguna.

Contoh:

```text
"Permintaan ambulans berhasil dibuat."

"Ambulans sedang menuju lokasi Anda."

"Pembayaran berhasil dikonfirmasi."

"Permintaan ambulans telah selesai."
```

---

# 25. Sistem Laporan

Admin dapat melihat laporan mengenai aktivitas sistem.

Contohnya:

### Request

```text
Total Request
Request Completed
Request Pending
Request Cancelled
```

### Revenue

```text
Total Revenue
Paid
Pending
```

### Ambulance

```text
Total Ambulance
Available
Busy
Maintenance
```

Data tersebut dapat divisualisasikan menggunakan grafik.

---

# 26. Export Data

RapidAid menyediakan fitur export data ke CSV.

Contohnya admin dapat melakukan export:

```text
Emergency Request
Payment
User
Report
```

Data yang di-export dapat dibuka menggunakan Microsoft Excel atau aplikasi spreadsheet lainnya.

---

# 27. Print dan Invoice

Sistem juga mendukung fitur print/invoice.

Fitur ini dapat digunakan untuk mencetak informasi transaksi atau layanan.

Contohnya:

```text
RapidAid Ambulance Service
--------------------------
Customer : Budi
Service  : Ambulance
Date     : 08 October 2026
Status   : Completed
Payment  : Paid
--------------------------
```

---

# 28. Dark Mode dan Light Mode

RapidAid mendukung dua tampilan:

```text
🌙 Dark Mode
☀️ Light Mode
```

Pengguna dapat menyesuaikan tampilan sesuai kenyamanan.

---

# 29. Responsive Design

Website dibuat responsive sehingga dapat digunakan pada:

```text
📱 Mobile
   ↓
📱 Tablet
   ↓
💻 Laptop
   ↓
🖥️ Desktop
```

Tampilan akan menyesuaikan ukuran layar perangkat.

---

# 30. Teknologi yang Digunakan

## Frontend

```text
HTML5
CSS3
JavaScript ES6+
```

CSS digunakan untuk:

* layout
* responsive
* animation
* glassmorphism
* dark/light mode

---

## Backend

```text
PHP 7.4+
PDO
OOP
```

PHP bertugas menangani:

* login
* register
* database
* request
* payment
* subscription
* admin
* API

---

## Database

```text
MySQL 5.7+
```

Database digunakan untuk menyimpan data aplikasi.

---

## Maps

```text
Leaflet.js
OpenStreetMap
```

Digunakan untuk menampilkan peta dan menentukan lokasi.

---

## Geocoding

```text
OpenStreetMap Nominatim API
```

Digunakan untuk mengubah koordinat menjadi alamat.

---

## Charts

```text
Chart.js
```

Digunakan untuk membuat grafik pada dashboard dan laporan admin.

---

## Icons

```text
Font Awesome 6
```

Digunakan untuk icon pada tampilan website.

---

## Fonts

```text
Plus Jakarta Sans
Syne
```

Digunakan untuk typography pada tampilan RapidAid.

---

# 31. Keamanan

RapidAid menggunakan beberapa mekanisme keamanan dasar seperti:

* session authentication
* password hashing
* PDO
* prepared statement
* validasi input
* pembatasan akses halaman
* pemisahan akses user dan admin

User biasa tidak seharusnya dapat mengakses halaman administrasi.

---

# 32. Instalasi Singkat

### 1. Buat Database

Buka:

```text
http://localhost/phpmyadmin
```

Buat database:

```text
rapidaid_db
```

Kemudian import:

```text
database.sql
```

---

### 2. Konfigurasi Database

Buka:

```text
config/database.php
```

Sesuaikan:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rapidaid_db');
define('APP_URL', 'http://localhost/rapidaid');
```

---

### 3. Letakkan Project

Untuk XAMPP:

```text
C:/xampp/htdocs/rapidaid
```

Untuk Laragon:

```text
C:/laragon/www/rapidaid
```

---

### 4. Jalankan

Buka:

```text
http://localhost/rapidaid/
```

---

## Akses dari Perangkat Lain di Jaringan Lokal (LAN)

Komputer yang menjalankan RapidAid harus tetap menyala dan terhubung ke jaringan yang sama dengan perangkat lain.

### Jika menggunakan PHP built-in server

1. Cari alamat IPv4 komputer server dengan menjalankan `ipconfig` di Command Prompt, lalu lihat bagian **IPv4 Address** pada adapter Wi-Fi/Ethernet yang sedang digunakan (contoh: `192.168.1.20`).
2. Dari folder `rapidaid`, jalankan server agar menerima koneksi dari perangkat lain:

   ```powershell
   $env:RAPIDAID_APP_URL = "http://192.168.1.20:8000"
   php -S 0.0.0.0:8000
   ```

   Ganti `192.168.1.20` dengan IPv4 komputer server. Variabel `RAPIDAID_APP_URL` perlu menggunakan alamat yang sama agar tautan login dan pengalihan aplikasi menuju komputer server, bukan `localhost` milik perangkat pengunjung.
3. Dari ponsel/komputer lain pada Wi-Fi/LAN yang sama, buka `http://192.168.1.20:8000`.
4. Jika tidak dapat dibuka, izinkan PHP pada **Windows Defender Firewall** untuk jaringan **Private** atau buat aturan inbound TCP untuk port `8000`. Jangan izinkan jaringan Public.

Untuk XAMPP/Laragon, jalankan Apache seperti biasa dan atur `RAPIDAID_APP_URL` ke alamat LAN beserta folder proyek, misalnya `http://192.168.1.20/rapidaid`. Jika belum mengatur environment variable Apache, ubah nilai fallback `$appUrl` di `config/database.php` menjadi URL LAN tersebut. Jika URL tidak diatur, aplikasi menggunakan `http://localhost:8000`.

## Akses dari Internet / Jarak Jauh

Untuk akses di luar jaringan lokal, deploy aplikasi ke hosting/VPS dengan domain dan HTTPS, lalu atur `RAPIDAID_APP_URL` ke URL publik tersebut (contoh: `https://rapid.example.com`). Untuk akses pribadi sementara, gunakan VPN yang menghubungkan perangkat ke jaringan server.

Jangan membuka port server pengembangan PHP (`php -S`) langsung ke internet atau melakukan port-forwarding tanpa pengamanan. Aplikasi ini menyimpan data akun dan informasi darurat; sebelum digunakan publik, gunakan HTTPS, kredensial database khusus (bukan akun default), matikan `APP_DEBUG`, batasi akses database dan direktori upload, serta siapkan pembaruan dan backup. Server pengembangan PHP hanya untuk pengujian.

---

# 33. Halaman yang Tersedia

| Halaman                      | Fungsi              |
| ---------------------------- | ------------------- |
| `index.php`                  | Landing page        |
| `auth/login.php`             | Login user          |
| `auth/register.php`          | Register user       |
| `auth/admin-login.php`       | Login admin         |
| `user/dashboard.php`         | Dashboard user      |
| `user/request-ambulance.php` | Meminta ambulans    |
| `user/track-request.php`     | Tracking ambulans   |
| `user/emergency-history.php` | Riwayat layanan     |
| `user/subscriptions.php`     | Paket subscription  |
| `user/payment.php`           | Pembayaran          |
| `user/payments.php`          | Riwayat pembayaran  |
| `user/notifications.php`     | Notifikasi          |
| `user/profile.php`           | Profil user         |
| `admin/dashboard.php`        | Dashboard admin     |
| `admin/users.php`            | Kelola user         |
| `admin/ambulances.php`       | Kelola ambulans     |
| `admin/drivers.php`          | Kelola driver       |
| `admin/hospitals.php`        | Kelola rumah sakit  |
| `admin/requests.php`         | Kelola permintaan   |
| `admin/payments.php`         | Kelola pembayaran   |
| `admin/subscriptions.php`    | Kelola subscription |
| `admin/reports.php`          | Laporan             |

---

# 34. Kesimpulan

**RapidAid Ambulance Service** merupakan aplikasi web yang dibuat untuk mendigitalisasi proses layanan ambulans.

Program ini menghubungkan **pengguna, admin, ambulans, driver, dan rumah sakit** dalam satu sistem.

Pengguna dapat melakukan permintaan ambulans dan menentukan lokasi melalui peta, sedangkan admin dapat mengelola permintaan, ambulans, driver, rumah sakit, pembayaran, subscription, serta laporan.

Dengan adanya sistem ini, proses layanan ambulans dapat dilakukan secara lebih terstruktur dan informasi layanan dapat dikelola secara terpusat.

Secara keseluruhan, RapidAid memiliki tiga bagian utama:

```text
                 RAPIDAID
                    │
        ┌───────────┼───────────┐
        │           │           │
     FRONTEND     BACKEND    DATABASE
        │           │           │
   HTML/CSS/JS     PHP         MySQL
        │           │           │
        └───────────┼───────────┘
                    │
             Layanan Ambulans
```

Program ini tidak hanya berfungsi sebagai website informasi ambulans, tetapi merupakan **sistem layanan ambulans berbasis web** yang mencakup proses permintaan, lokasi, tracking, pembayaran, subscription, notifikasi, administrasi, dan pelaporan.
