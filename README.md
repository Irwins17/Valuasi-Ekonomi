# Valuasi Ekonomi

Aplikasi web berbasis Laravel untuk melakukan **valuasi ekonomi (economic valuation)** terhadap manfaat dan biaya suatu proyek — misalnya proyek lingkungan, konservasi, atau infrastruktur — menggunakan beberapa metode valuasi standar (EOP, TCM, CVM). Aplikasi menghitung **Total Economic Value (TEV)** dan **Benefit-Cost Ratio (BCR)**, mendukung analisis sensitivitas, serta menyediakan dashboard publik untuk transparansi hasil.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Metode Valuasi](#metode-valuasi)
- [Teknologi](#teknologi)
- [Struktur Peran Pengguna](#struktur-peran-pengguna)
- [Instalasi](#instalasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Akun Default](#akun-default-setelah-seeding)
- [Struktur Proyek](#struktur-proyek)
- [Pengujian](#pengujian)
- [Lisensi](#lisensi)

## Fitur Utama

- **Manajemen Proyek** — membuat, mengedit, dan memantau proyek valuasi ekonomi lengkap dengan lokasi (lat/long), status (draft, in progress, completed, published), dan periode waktu.
- **Perhitungan Otomatis TEV & BCR** — menjumlahkan seluruh manfaat (benefits) dan biaya (costs) suatu proyek untuk menghasilkan Total Economic Value dan Benefit-Cost Ratio.
- **Input Data Manfaat & Biaya** — pencatatan manfaat dan biaya per kategori/subkategori dengan sumber data dan metode yang digunakan.
- **Modul Metode Valuasi**:
  - **EOP** (Effect on Production) — dampak produksi sebelum/sesudah terhadap komoditas.
  - **TCM** (Travel Cost Method) — surplus konsumen berdasarkan biaya perjalanan responden.
  - **CVM** (Contingent Valuation Method) — kesediaan membayar (Willingness to Pay) responden.
- **Data Master** — harga pasar komoditas (per proyek maupun umum/global per tahun) dan koefisien lingkungan.
- **Analisis Sensitivitas** — simulasi perubahan TEV dan BCR terhadap variasi tingkat inflasi, penyesuaian harga, dan tingkat diskonto.
- **Ekspor Laporan PDF** — cetak ringkasan proyek beserta rincian manfaat dan biaya ke PDF (menggunakan DomPDF).
- **Audit Log** — pencatatan aktivitas perubahan data secara otomatis melalui trait `Auditable`.
- **Manajemen Pengguna & Peran (RBAC)** — kontrol akses berbasis peran (Administrator, Surveyor, Analyst).
- **Dashboard Publik** — halaman publik yang menampilkan ringkasan TEV, peta sebaran proyek, distribusi manfaat per kategori, distribusi metode valuasi, dan detail proyek yang telah dipublikasikan.
- **Glosarium** — halaman penjelasan istilah-istilah valuasi ekonomi untuk pengunjung publik.

## Metode Valuasi

| Metode | Kepanjangan | Kegunaan |
|---|---|---|
| **EOP** | Effect on Production | Menilai dampak ekonomi terhadap produksi suatu komoditas akibat perubahan kondisi lingkungan/proyek. |
| **TCM** | Travel Cost Method | Mengestimasi nilai ekonomi suatu tempat/jasa lingkungan dari biaya perjalanan yang dikeluarkan pengunjung. |
| **CVM** | Contingent Valuation Method | Mengestimasi nilai ekonomi berdasarkan kesediaan membayar (Willingness to Pay) responden melalui survei. |

## Teknologi

- **Backend**: [Laravel 13](https://laravel.com) (PHP ^8.3)
- **Frontend**: Blade templates, [Tailwind CSS 4](https://tailwindcss.com), [Vite](https://vitejs.dev)
- **Database**: SQLite (default), dapat dikonfigurasi ke MySQL/PostgreSQL
- **PDF Export**: [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)
- **Testing**: PHPUnit
- **Dev Tools**: Laravel Pint (code style), Laravel Pail (log viewer)

## Struktur Peran Pengguna

| Peran (slug) | Deskripsi |
|---|---|
| `admin` | Akses penuh ke seluruh sistem: manajemen proyek, pengguna, data master, dan audit log. |
| `surveyor` | Input data lapangan saja (EOP, TCM, CVM). |
| `analyst` | Verifikasi data dan pelaporan. |

## Instalasi

### Prasyarat

- PHP >= 8.3
- Composer
- Node.js & npm
- Ekstensi PHP yang dibutuhkan Laravel (mbstring, pdo, sqlite3/pdo_mysql, dll.)

### Langkah-langkah

1. **Clone repository**

   ```bash
   git clone <url-repository-anda>
   cd "Valuasi Ekonomi"
   ```

2. **Instal dependensi PHP & JavaScript**

   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Secara default aplikasi menggunakan SQLite. Pastikan file database tersedia:

   ```bash
   touch database/database.sqlite
   ```

   Jika ingin menggunakan MySQL/PostgreSQL, ubah variabel `DB_*` pada file `.env` sesuai kebutuhan.

4. **Jalankan migrasi & seeder**

   ```bash
   php artisan migrate --seed
   ```

   Perintah ini akan membuat tabel-tabel yang dibutuhkan sekaligus mengisi data awal (role, akun pengguna, dan contoh data proyek).

5. **Build asset frontend**

   ```bash
   npm run build
   ```

Alternatif, gunakan skrip Composer bawaan untuk menjalankan seluruh langkah setup sekaligus:

```bash
composer run setup
```

## Menjalankan Aplikasi

### Mode Pengembangan

Menjalankan server, queue listener, log viewer (Pail), dan Vite secara bersamaan:

```bash
composer run dev
```

Atau secara manual:

```bash
php artisan serve
npm run dev
```

Aplikasi dapat diakses di `http://localhost:8000` (atau URL sesuai `APP_URL`).

- Landing page publik: `/`
- Dashboard publik: `/dashboard-publik`
- Glosarium: `/glossary`
- Login admin: `/login`
- Dashboard admin (setelah login): `/admin/dashboard`

## Akun Default (setelah seeding)

| Peran | Email | Password |
|---|---|---|
| Administrator | `admin@valuasi.local` | `admin@123` |
| Surveyor 1 | `surveyor1@valuasi.local` | `surveyor@123` |
| Surveyor 2 | `surveyor2@valuasi.local` | `surveyor@123` |
| Analyst 1 | `analyst1@valuasi.local` | `analyst@123` |

> ⚠️ **Penting**: Ganti seluruh password default ini sebelum aplikasi digunakan pada lingkungan produksi.

## Struktur Proyek

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/              # Controller area admin (proyek, benefit, cost, master data, dll.)
│   │   │   └── Modules/        # Controller modul valuasi: EOP, TCM, CVM
│   │   ├── Auth/                # Login/logout
│   │   └── Public/              # Landing page, dashboard publik, glosarium
│   └── Middleware/
│       └── RoleMiddleware.php   # Middleware kontrol akses berbasis peran
├── Models/                      # Project, Benefit, Cost, EopData, TcmData, CvmData,
│                                 # MarketPrice, EnvironmentalCoefficient, User, Role, AuditLog
└── Traits/
    └── Auditable.php            # Pencatatan otomatis perubahan data ke audit log

database/
├── migrations/                  # Skema tabel
└── seeders/                     # Role & user default, contoh data proyek

routes/
└── web.php                      # Seluruh route publik & admin
```

## Pengujian

Menjalankan test suite:

```bash
composer run test
```

atau

```bash
php artisan test
```

## Lisensi

PKSPL IPB
Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
