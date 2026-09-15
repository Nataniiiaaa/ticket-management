# Ticket Management System

Aplikasi sederhana CRUD untuk Ticket Management dengan mengintegrasikan Data Customer dari API Eksternal. Dibuat untuk Technical Test IT - UMC

## Struktur Project

```
config.php              -> untuk konfigurasi .env dan koneksi database (PDO)
lib/customer_client.php -> fungsi cURL ke Customer API, pegang Bearer Token
api/customers.php       -> endpoint lokal yang dipanggil JS (proxy, token tidak sampai ke browser)
tickets/create.php      -> untuk menambah ticket baru (prepared statement + validasi server)
tickets/delete.php      -> untuk menghapus ticket
tickets/list.php        -> untuk mengambil semua ticket + gabungkan nama customer dari API
tickets/update.php      -> untuk mengambil data ticket yang akan diperbaiki (GET) dan update data (POST)
sql/schema.sql          -> struktur tabel tickets
index.html + js/app.js -> tampilan form, list, dan pemanggilan API via fetch
.env                    -> konfigurasi database & API (tidak di-commit ke git)
```
## Cara Setup

### 1. Database
Buat database MySQL baru namanya `ticket_management`, lalu import file `sql/schema.sql`:
- Buka phpMyAdmin, buat database baru dengan nama `ticket_management`
- Klik database tersebut, pilih tab **Import**
- Pilih file `sql/schema.sql`, klik **Go**

Atau via command line:
```
mysql -u root -p ticket_management < sql/schema.sql
```

### 2. Konfigurasi (.env)
Buat file `.env` di root folder project, isi sesuai environment:
```
API_BASE_URL=<base url Customer API yang diberikan penguji>
API_TOKEN=<bearer token yang diberikan penguji>
DB_HOST=localhost
DB_NAME=ticket_management
DB_USER=root
DB_PASS=
```

### 3. Menjalankan Aplikasi
Taruh folder project ini di dalam `htdocs` (XAMPP), aktifkan Apache dan MySQL lewat XAMPP Control Panel, lalu akses melalui browser:
```
http://localhost/ticket-management/
```

## Catatan Keputusan Desain

- `customer_id` disimpan sebagai referensi ke Customer API, bukan nama customer — karena nama bisa berubah di sisi API, sementara ID tetap valid sebagai penunjuk.
- Update ticket menggunakan method POST (bukan PUT), karena form HTML native hanya mendukung GET/POST.
- Data customer diambil sekali per request list (bukan satu request API per baris ticket) untuk menghindari beban berlebih ke Customer API.
