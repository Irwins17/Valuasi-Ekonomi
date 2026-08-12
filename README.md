# Valuasi Ekonomi

Aplikasi web untuk melakukan **valuasi ekonomi (economic valuation)** terhadap manfaat dan biaya suatu proyek — misalnya proyek lingkungan, konservasi, atau infrastruktur. Aplikasi menyediakan 13 modul valuasi (dari Direct Use Value sampai Choice Experiment dan enam formulasi jasa ekosistem), menghitung **Total Economic Value (TEV)**, **Net Present Value (NPV)**, dan **Benefit-Cost Ratio (BCR)**, menjalankan estimasi regresi untuk TCM/CVM/HPM, serta menyediakan dashboard publik untuk transparansi hasil. Dibangun di atas Laravel dengan antarmuka Single Page Application berbasis **Inertia.js + React**.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Modul Valuasi](#modul-valuasi)
- [Asumsi Valuasi & Present Value](#asumsi-valuasi--present-value)
- [Teknologi](#teknologi)
- [Struktur Peran Pengguna](#struktur-peran-pengguna)
- [Instalasi](#instalasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Akun Default](#akun-default-setelah-seeding)
- [Data Demo (Opsional)](#data-demo-opsional)
- [Batas Administrasi (Peta)](#batas-administrasi-peta)
- [Struktur Proyek](#struktur-proyek)
- [Pengujian](#pengujian)
- [Lisensi](#lisensi)

## Fitur Utama

- **Manajemen Proyek** — membuat, mengedit, dan memantau proyek valuasi lengkap dengan lokasi (lat/long), provinsi, batas wilayah (GeoJSON), status (draft, in progress, completed, published), dan periode waktu.
- **Registry Modul per Proyek** — setiap proyek otomatis memiliki katalog 13 modul valuasi bawaan yang dapat diaktifkan/di-draft-kan, disembunyikan dari halaman publik, diberi catatan, atau ditambah modul kustom sendiri.
- **Perhitungan TEV, NPV & BCR** — menjumlahkan seluruh manfaat (benefits) dan biaya (costs) proyek, baik nominal maupun setelah didiskonto ke tahun dasar.
- **Estimasi Statistik Bawaan** — regresi ditulis langsung di PHP (tanpa layanan eksternal):
  - TCM: model permintaan **Poisson**, **Negative Binomial**, atau **OLS** → consumer surplus `−1/β₁`.
  - CVM: **Logit**/**Probit** untuk format dichotomous choice, serta rata-rata WTP untuk format open-ended.
  - HPM: OLS log-linear untuk harga implisit atribut lingkungan.
- **Deteksi Double Counting** — memperingatkan bila dua manfaat berpotensi menghitung nilai ekonomi yang sama (mis. EOP dan Food Production pada komoditas yang sama). Peringatan bersifat informatif, tidak memblokir penyimpanan.
- **Import/Export Data Survei (Excel)** — unggah data TCM/CVM secara massal dari file `.xlsx`/`.csv`, atau ekspor data yang sudah ada untuk diolah lebih lanjut.
- **Data Master** — harga pasar komoditas (per proyek maupun umum/global per tahun) dan koefisien lingkungan.
- **Analisis Sensitivitas** — simulasi interaktif perubahan TEV, NPV, dan BCR terhadap variasi tingkat inflasi, penyesuaian harga, dan tingkat diskonto.
- **Peta & Batas Wilayah** — pemilihan lokasi via peta Leaflet, pemilih batas administrasi (provinsi/kabupaten/kecamatan/desa) dari data GADM/HDX yang diimpor, atau unggah shapefile (`.shp`/`.zip`) sendiri.
- **Ekspor Laporan PDF** — cetak ringkasan proyek beserta rincian manfaat dan biaya ke PDF.
- **Audit Log** — pencatatan aktivitas perubahan data secara otomatis melalui trait `Auditable`, lengkap dengan tampilan diff.
- **Manajemen Pengguna & Peran (RBAC)** — kontrol akses berbasis peran (Administrator, Surveyor, Analyst) yang ditegakkan di setiap route admin.
- **Dashboard Publik** — halaman publik yang menampilkan ringkasan TEV, peta sebaran proyek, distribusi manfaat per kategori, distribusi metode valuasi, dan detail proyek yang telah dipublikasikan.
- **Glosarium** — halaman penjelasan istilah-istilah valuasi ekonomi untuk pengunjung publik.

## Modul Valuasi

Sumber kebenaran katalog modul ada di [ValuationModuleCatalog.php](app/Support/ValuationModuleCatalog.php); skema input dan formula jasa ekosistem ada di [EcosystemServiceSchemas.php](app/Support/EcosystemServiceSchemas.php).

### Modul Metode

| Kode | Nama | Kelompok | 
|---|---|---|---|
| `DUV` | Direct Use Value | Direct Use | 
| `EOP` | Effect on Production | Direct Use | 
| `TCM` | Travel Cost Method | Revealed Preference |
| `CVM` | Contingent Valuation Method | Stated Preference | 
| `HPM` | Hedonic Pricing Method | Revealed Preference | 
| `ABM` | Abatement / Defensive Expenditure | Revealed Preference | 
| `CE` | Choice Experiment | Stated Preference |

### Modul Jasa Ekosistem

| Kode | Jasa | Kategori | Formula |
|---|---|---|---|
| `FOOD` | Food Production | Provisioning |
| `RAWMAT` | Raw Material | Provisioning |
| `GENRES` | Genetic Resources | Provisioning | 
| `CLIMATE` | Climate / Carbon Storage | Regulating | 
| `EROSION` | Erosion Control | Regulating | 
| `WATER` | Water Supply | Regulating |

Modul `HPM`, `ABM`, dan `CE` ditandai sebagai modul lanjutan (*advanced*) dan disembunyikan di balik toggle pada daftar modul.

## Asumsi Valuasi & Present Value

Setiap proyek memiliki **Pengaturan Valuasi** tersendiri (`/admin/projects/{id}` → Pengaturan Valuasi) yang menentukan bagaimana angka nominal didiskonto:

| Pengaturan | Default | Keterangan |
|---|---|---|
| Tahun dasar (`base_year`) | Tahun pembuatan proyek | Titik acuan diskonto |
| Tingkat diskonto (`discount_rate`) | 6,00 % | Dipakai untuk NPV manfaat & biaya |
| Periode analisis (`analysis_period`) | 10 tahun | Horizon perhitungan |
| Mata uang (`currency`) | IDR | Ditampilkan pada laporan |
| Basis nilai EOP (`eop_value_basis`) | `net` | Net (setelah biaya produksi) atau Gross |

Proyek yang belum pernah dikonfigurasi memakai nilai default di atas, dengan tahun dasar = tahun pembuatan proyek sehingga faktor diskonto bernilai 1 dan total nominal tidak berubah.

## Teknologi

### Backend
- **[Laravel 13](https://laravel.com)** (PHP ^8.3)
- **Database**: SQLite (default), dapat dikonfigurasi ke MySQL/PostgreSQL
- **[inertiajs/inertia-laravel](https://inertiajs.com)** 3.x
- **[spatie/laravel-pdf](https://github.com/spatie/laravel-pdf)** — ekspor PDF
- **[maatwebsite/excel](https://github.com/SpartnerNL/Laravel-Excel)** — import/export Excel
- **[tightenco/ziggy](https://github.com/tighten/ziggy)** — route Laravel di sisi React
- **PHPUnit 12**

### Frontend
- **[React 19](https://react.dev)** + **[@inertiajs/react](https://inertiajs.com)**
- **[Vite 7](https://vitejs.dev)** + `laravel-vite-plugin`
- **[Tailwind CSS 4](https://tailwindcss.com)**
- **Chart.js** + `react-chartjs-2` — grafik dashboard
- **Leaflet** — peta lokasi & batas wilayah
- **shpjs** — pembacaan shapefile di browser

### Dev Tools
- Laravel Pint (code style)
- Laravel Pail (log viewer)
- Puppeteer (headless Chrome untuk render PDF)
- concurrently (menjalankan seluruh proses dev sekaligus)

## Struktur Peran Pengguna

| Peran (slug) | Deskripsi |
|---|---|
| `admin` | Akses penuh: manajemen proyek, benefit/cost, seluruh modul, pengguna, data master, dan audit log. |
| `surveyor` | Melihat proyek + input data lapangan seluruh modul (DUV, EOP, TCM, CVM, HPM, ABM, CE, jasa ekosistem), termasuk import/export Excel. |
| `analyst` | Melihat data proyek, menjalankan estimasi TCM/CVM (analysis), dan analisis sensitivitas untuk verifikasi/pelaporan. |

Matriks lengkapnya ditegakkan per route di [routes/web.php](routes/web.php) melalui `RoleMiddleware` (`role:admin`, `role:admin,surveyor`, `role:admin,analyst`).

## Instalasi

### Prasyarat

- PHP >= 8.3
- Composer
- Node.js >= 20 & npm (dibutuhkan Vite 7)
- Ekstensi PHP yang dibutuhkan Laravel (mbstring, pdo, sqlite3/pdo_mysql, zip, gd, dll.)
- Untuk ekspor PDF: Chrome/Chromium headless — terpasang otomatis melalui paket npm `puppeteer`

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

   Seeder bawaan hanya membuat **role dan akun pengguna** — sistem sengaja dimulai dengan data kosong. Lihat [Data Demo](#data-demo-opsional) bila ingin database berisi contoh proyek.

5. **Build asset frontend**

   ```bash
   npm run build
   ```

Alternatif, gunakan skrip Composer bawaan untuk menjalankan langkah setup sekaligus (install, `.env`, key, migrate, npm install, build):

```bash
composer run setup
php artisan db:seed   # skrip setup tidak menjalankan seeder
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

| Halaman | URL |
|---|---|
| Landing page publik | `/` |
| Dashboard publik | `/dashboard-publik` |
| Detail proyek publik | `/project/{id}` |
| Glosarium | `/glossary` |
| Login | `/login` |
| Dashboard admin | `/admin/dashboard` |
| Daftar modul proyek | `/admin/projects/{id}/modules` |
| Analisis sensitivitas | `/admin/sensitivity` |
| Audit log | `/admin/audit-logs` |

## Akun Default (setelah seeding)

| Peran | Email | Password |
|---|---|---|
| Administrator | `admin@valuasi.local` | `admin@123` |
| Surveyor 1 | `surveyor1@valuasi.local` | `surveyor@123` |
| Analyst 1 | `analyst1@valuasi.local` | `analyst@123` |

> ⚠️ **Penting**: Ganti seluruh password default ini sebelum aplikasi digunakan pada lingkungan produksi.

## Data Demo (Opsional)

Seeder demo tidak ikut dijalankan oleh `migrate --seed`. Jalankan manual bila dibutuhkan:

```bash
php artisan db:seed --class=SampleDataSeeder          # contoh proyek + data EOP/TCM/CVM
php artisan db:seed --class=RegionalValuationSeeder   # valuasi lintas wilayah
php artisan db:seed --class=PulauObiEcosystemSeeder   # studi kasus jasa ekosistem Pulau Obi
```

Seeder demo tetap diuji oleh `SeederValuationIntegrityTest` dan `PulauObiEcosystemSeederTest`.

## Batas Administrasi (Peta)

Pemilih batas wilayah pada form proyek membaca tabel `administrative_boundaries` dan geometri terkompresi di `storage/app/boundaries`. Data ini tidak ikut di-repo dan harus diimpor sekali:

```bash
# Sumber utama (disarankan): HDX — BPS/WFP/OCHA ROAP, CC BY-IGO, resolusi penuh
node scripts/hdx-boundaries-to-ndjson.cjs <hdxDir> <outDir>
php artisan boundaries:import --dir=<outDir>

# Sumber alternatif: GADM 4.1 (akademik/non-komersial)
node scripts/gadm-shp-to-geojson.cjs <extractedShpDir> <outDir>
php artisan boundaries:import --dir=<outDir> --level=1 --level=2
```

Perintah artisan terkait:

| Perintah | Fungsi |
|---|---|
| `boundaries:import` | Impor polygon administrasi (opsi `--level=*`, `--dir=`, `--fresh`) |
| `boundaries:derive-papua-provinces` | Membentuk ulang enam provinsi Papua pasca-2022 dari polygon kabupaten |
| `boundaries:extract-geometry` | Memindahkan polygon dari kolom `geojson` lama ke penyimpanan on-disk |

Tanpa data ini, aplikasi tetap berjalan — pengguna dapat menentukan lokasi lewat titik koordinat atau unggah shapefile sendiri.

## Struktur Proyek

```
app/
├── Console/Commands/             # Impor & pemrosesan batas administrasi
├── Exports/                      # Kelas export Excel (TcmDataExport, CvmDataExport)
├── Imports/                      # Kelas import Excel (TcmDataImport, CvmDataImport)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                # Proyek, benefit, cost, master data, sensitivitas, audit
│   │   │   └── Modules/          # DUV, EOP, TCM, CVM, HPM, ABM, CE, ekosistem, registry
│   │   ├── Auth/                 # Login/logout
│   │   └── Public/               # Landing page, dashboard publik, glosarium
│   └── Middleware/
│       ├── HandleInertiaRequests.php  # Data yang dibagikan ke semua halaman Inertia
│       └── RoleMiddleware.php         # Kontrol akses berbasis peran
├── Models/                       # Project, Benefit, Cost, ValuationModule, *Data,
│                                 # TcmAnalysis, CvmAnalysis, ProjectValuationSetting,
│                                 # Ecosystem*, AdministrativeBoundary, User, Role, AuditLog
├── Services/Valuation/           # Inti perhitungan
│   ├── EconomicValuationCalculator.php   # TCM, CVM, EOP
│   ├── EcosystemServiceValuationCalculator.php
│   ├── TravelCostEstimator.php / ContingentValuationEstimator.php / HedonicPriceEstimator.php
│   ├── BenefitCostPresentValues.php      # NPV manfaat & biaya
│   ├── DoubleCountingChecker.php         # Deteksi nilai ganda
│   └── Math/                             # OLS, Poisson, Logit, Probit, Matrix, Distributions
├── Support/                      # Katalog modul, skema jasa ekosistem, provinsi, geometri
└── Traits/
    └── Auditable.php             # Pencatatan otomatis perubahan data ke audit log

resources/
├── js/
│   ├── Pages/                    # Komponen halaman React, mengikuti struktur route
│   │   ├── Admin/                # Dashboard, Projects, Modules/*, MasterData, Users, dll.
│   │   └── Public/               # Landing, Dashboard, Glossary, ProjectDetail
│   ├── Layouts/                  # AdminLayout (sidebar admin), GuestLayout (navbar publik)
│   ├── Components/               # charts/, map/, modules/, ui/
│   ├── data/                     # GeoJSON provinsi & referensi teknik valuasi
│   └── lib/                      # Helper format angka, geo, valuasi, wilayah
├── css/                          # Tailwind + design token CSS
└── views/
    ├── app.blade.php             # Root view Inertia (satu-satunya layout Blade)
    └── pdf/projects/export.blade.php   # Template cetak PDF

database/
├── migrations/                   # Skema tabel
└── seeders/                      # Role & user default + seeder demo opsional

scripts/                          # Konverter GeoJSON/NDJSON untuk boundaries:import
routes/
└── web.php                       # Seluruh route publik & admin (middleware role per grup)
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

Cakupan pengujian:

- **Unit** — kalkulator valuasi ekonomi, valuasi jasa ekosistem, present value, dan matematika regresi (OLS/Poisson/Logit/Probit), serta penyimpanan geometri batas wilayah.
- **Feature** — render halaman Inertia (publik & admin), CRUD proyek, integrasi benefit/cost, perhitungan modul, pengaturan valuasi, hak akses berbasis peran (RBAC), import/export Excel, ekspor PDF, pencarian batas wilayah, eksposur route Ziggy, dan integritas seeder.

## Lisensi

Dikembangkan untuk **PKSPL IPB**.
Kode sumber proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).

Data batas administrasi bersifat opsional dan mengikuti lisensi sumbernya masing-masing: HDX/BPS (CC BY-IGO) atau GADM 4.1 (akademik/non-komersial).
