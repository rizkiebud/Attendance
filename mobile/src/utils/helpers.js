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
  return `${parseInt(d)} ${months[parseInt(m) - 1]} ${y}`;
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
