# Sistem Absensi KPPN

Sistem absensi berbasis GPS terintegrasi antara aplikasi mobile (React Native) dan web monitoring (Laravel).

## Struktur Proyek

```
Absensi_Kppn/
├── backend/          # Laravel Backend (API + Web Admin)
└── mobile/           # React Native Mobile App
```

---

## Backend (Laravel)

### Fitur
- REST API dengan JWT Authentication
- Web monitoring admin dashboard
- Manajemen karyawan
- Data absensi real-time
- Laporan & export CSV
- Manajemen permohonan izin
- Konfigurasi kantor & radius GPS

### Instalasi Backend

```bash
cd backend

# Install dependencies
composer install

# Copy .env
cp .env.example .env

# Generate key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Setup database (SQLite default, atau ubah ke MySQL di .env)
# Untuk MySQL: DB_CONNECTION=mysql, DB_HOST=127.0.0.1, dll.

# Jalankan migrasi & seeder
php artisan migrate:fresh --seed

# Buat symlink storage
php artisan storage:link

# Jalankan server
php artisan serve
```


## Mobile App (React Native)

### Fitur
- Login dengan JWT
- Absen masuk & keluar berbasis GPS
- Validasi radius jarak dari kantor (Haversine formula)
- Foto selfie saat absensi
- Riwayat absensi per bulan
- Pengajuan izin/sakit/cuti
- Profil karyawan & ganti password
- Dashboard dengan rekap harian & bulanan

### Instalasi Mobile

#### Prasyarat
- Node.js >= 18
- React Native CLI
- Android Studio (untuk Android)
- Xcode (untuk iOS)

```bash
cd mobile

# Install dependencies
npm install

# iOS (Mac only)
cd ios && pod install && cd ..
npx react-native run-ios

# Android
npx react-native run-android
```


### Izin Aplikasi
- **GPS/Lokasi**: Diperlukan untuk absensi berbasis koordinat
- **Kamera**: Untuk foto selfie saat absensi

---

## Arsitektur Sistem

```
┌─────────────────────────────────────────────────────┐
│                  React Native App                    │
│  ┌──────────┐  ┌──────────┐  ┌──────────────────┐  │
│  │  Login   │  │ Absensi  │  │  Riwayat & Izin  │  │
│  │  Screen  │  │  (GPS)   │  │     Screen       │  │
│  └──────────┘  └──────────┘  └──────────────────┘  │
│           │          │               │               │
│           └──────────┴───────────────┘               │
│                   API Service (axios)                 │
└──────────────────────┬──────────────────────────────┘
                       │ JWT Token
                       │ HTTPS
┌──────────────────────▼──────────────────────────────┐
│                 Laravel Backend                       │
│  ┌──────────────┐         ┌────────────────────────┐ │
│  │  REST API    │         │    Web Admin Panel     │ │
│  │  /api/*      │         │    /admin/*            │ │
│  └──────┬───────┘         └───────────┬────────────┘ │
│         │                             │               │
│  ┌──────▼─────────────────────────────▼────────────┐ │
│  │              Database (SQLite/MySQL)              │ │
│  │  users | employees | offices | attendances |     │ │
│  │  leave_requests                                  │ │
│  └──────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────┘
```

## Cara Kerja Absensi GPS

1. Karyawan membuka app → pilih kantor
2. App mengambil koordinat GPS real-time
3. Sistem menghitung jarak dengan **Haversine Formula**:
   ```
   Jarak = 2R × arcsin(√(sin²(Δlat/2) + cos(lat1)×cos(lat2)×sin²(Δlon/2)))
   ```
4. Jika jarak ≤ radius kantor → absensi diizinkan
5. Jika melewati jam toleransi → status "Terlambat"
6. Data tersimpan dengan koordinat lengkap untuk audit

## Database Schema

```
users           - Akun login (admin/karyawan)
employees       - Data profil karyawan
offices         - Lokasi kantor & konfigurasi jam kerja
attendances     - Data absensi harian dengan koordinat GPS
leave_requests  - Permohonan izin/sakit/cuti
```
