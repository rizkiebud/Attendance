# Setup & Cara Menjalankan Aplikasi Mobile

## Prasyarat

| Tool | Versi | Download |
|------|-------|----------|
| Node.js | >= 18 | https://nodejs.org |
| JDK | 17 atau 21 | https://adoptium.net |
| Android Studio | Latest | https://developer.android.com/studio |
| React Native CLI | Latest | `npm install -g react-native-cli` |

## Langkah 1 — Install Dependencies

```bash
cd mobile
npm install
```

## Langkah 2 — Konfigurasi Android SDK

Buat file `android/local.properties` (copy dari `local.properties.example`):

```
# Windows
sdk.dir=C\:\Users\YourName\AppData\Local\Android\Sdk

# Mac/Linux
sdk.dir=/Users/yourname/Library/Android/sdk
```

## Langkah 3 — Konfigurasi URL API Backend

Edit `src/services/api.js`:

```js
// Android Emulator → gunakan ini:
export const BASE_URL = 'http://10.0.2.2:8000';

// Perangkat fisik → ganti dengan IP komputer Anda:
export const BASE_URL = 'http://192.168.x.x:8000';

// iOS Simulator:
export const BASE_URL = 'http://localhost:8000';
```

## Langkah 4 — Jalankan Metro Bundler

```bash
# Terminal 1
cd mobile
npm start
```

## Langkah 5 — Build & Run Android

```bash
# Terminal 2 (pastikan emulator/device sudah aktif)
cd mobile
npm run android
```

## Langkah 5 (alternatif) — Build APK Debug

```bash
cd mobile/android
./gradlew assembleDebug

# APK tersedia di:
# android/app/build/outputs/apk/debug/app-debug.apk
```

---

## Troubleshooting

### Error: "SDK location not found"
→ Buat file `android/local.properties` dengan path Android SDK Anda.

### Error: "JAVA_HOME not set"
```bash
# Mac/Linux
export JAVA_HOME=/path/to/jdk

# Windows
set JAVA_HOME=C:\Program Files\Java\jdk-21
```

### Error pada react-native-permissions
Pastikan AndroidManifest.xml sudah memiliki permission yang diperlukan (sudah disertakan).

### Metro bundler error "Cannot find module"
```bash
cd mobile
npm install
# Lalu clear cache:
npm start -- --reset-cache
```

### Ikons tidak muncul (react-native-vector-icons)
Pastikan `apply from: "../../node_modules/react-native-vector-icons/fonts.gradle"` ada di `android/app/build.gradle` (sudah disertakan).

---

## Struktur Paket Android

```
android/
├── app/
│   ├── build.gradle              # Konfigurasi build per-app
│   ├── debug.keystore            # Keystore untuk debug signing
│   ├── proguard-rules.pro
│   └── src/main/
│       ├── AndroidManifest.xml   # Permissions & app config
│       ├── assets/               # Font & aset statis
│       ├── java/com/absensikppnmobile/
│       │   ├── MainActivity.kt   # Entry point React Native
│       │   └── MainApplication.kt
│       └── res/
│           ├── drawable/
│           ├── mipmap-*/         # App icons (semua densitas)
│           └── values/
│               ├── strings.xml
│               └── styles.xml
├── build.gradle                  # Konfigurasi build project
├── gradle.properties             # Properties Gradle
├── gradlew / gradlew.bat         # Gradle wrapper
└── settings.gradle               # Module & plugin settings
```
