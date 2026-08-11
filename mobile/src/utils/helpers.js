/**
 * Hitung jarak antara dua titik koordinat menggunakan Haversine formula
 * @returns jarak dalam meter
 */
export function hitungJarak(lat1, lon1, lat2, lon2) {
  const R = 6371000; // radius bumi dalam meter
  const dLat = toRad(lat2 - lat1);
  const dLon = toRad(lon2 - lon1);

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(lat1)) *
      Math.cos(toRad(lat2)) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2);

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
}

function toRad(deg) {
  return (deg * Math.PI) / 180;
}

/**
 * Format waktu HH:MM:SS
 */
export function formatTime(timeStr) {
  if (!timeStr) return '-';
  return timeStr.substring(0, 5);
}

/**
 * Format tanggal Indonesia
 * Parse YYYY-MM-DD sebagai UTC agar tidak geser timezone
 */
export function formatTanggal(dateStr) {
  if (!dateStr) return '-';
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
  ];
  const [y, m, d] = dateStr.split('-');
  const mi = parseInt(m, 10) - 1;
  if (!y || !m || !d || isNaN(mi) || mi < 0 || mi > 11) return '-';
  return `${parseInt(d)} ${months[mi]} ${y}`;
}

/**
 * Format durasi HH:MM → "8 jam 30 mnt"
 */
export function formatDurasi(durasi) {
  if (!durasi) return null;
  const [h, m] = String(durasi).split(':');
  const hh = parseInt(h, 10);
  const mm = parseInt(m, 10);
  if (isNaN(hh) || isNaN(mm)) return String(durasi);
  if (mm === 0) return `${hh} jam`;
  return `${hh} jam ${mm} mnt`;
}

/**
 * Format jarak ke string ramah
 */
export function formatJarak(meter) {
  if (meter < 1000) {
    return `${Math.round(meter)} m`;
  }
  return `${(meter / 1000).toFixed(1)} km`;
}

/**
 * Dapatkan greeting berdasarkan jam
 */
export function getGreeting() {
  const hour = new Date().getHours();
  if (hour < 12) return 'Selamat Pagi';
  if (hour < 15) return 'Selamat Siang';
  if (hour < 18) return 'Selamat Sore';
  return 'Selamat Malam';
}

/**
 * Dapatkan nama hari Indonesia
 */
export function getNamaHari() {
  const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  return hari[new Date().getDay()];
}

export function getNamaBulan(bulan) {
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
  ];
  return months[bulan - 1];
}

/**
 * Tanggal lokal YYYY-MM-DD (hindari shift timezone UTC)
 */
export function localDateStr(date = new Date()) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}
