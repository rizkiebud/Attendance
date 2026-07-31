import React, {useState, useEffect, useCallback} from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  ScrollView,
  StatusBar,
  Platform,
} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Geolocation from '@react-native-community/geolocation';
import {request, PERMISSIONS, RESULTS} from 'react-native-permissions';
import {launchCamera, launchImageLibrary} from 'react-native-image-picker';
import Icon from 'react-native-vector-icons/Ionicons';
import {useFocusEffect} from '@react-navigation/native';
import {attendanceService} from '../services/api';
import {COLORS, STATUS_COLORS} from '../utils/colors';
import {hitungJarak, formatJarak, formatTime} from '../utils/helpers';

const AbsensiScreen = () => {
  const [offices, setOffices] = useState([]);
  const [selectedOffice, setSelectedOffice] = useState(null);
  const [currentLocation, setCurrentLocation] = useState(null);
  const [todayAttendance, setTodayAttendance] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isLocating, setIsLocating] = useState(false);
  const [locationError, setLocationError] = useState(null);
  const [fotoUri, setFotoUri] = useState(null);
  const [jarak, setJarak] = useState(null);

  useFocusEffect(
    useCallback(() => {
      loadData();
    }, []),
  );

  const loadData = async () => {
    try {
      const [officesRes, todayRes] = await Promise.all([
        attendanceService.getOffices(),
        attendanceService.getToday(),
      ]);
      const officeList = officesRes.data.data;
      setOffices(officeList);
      setTodayAttendance(todayRes.data.data);
      if (officeList.length > 0 && !selectedOffice) {
        setSelectedOffice(officeList[0]);
      }
    } catch (err) {
      console.error('Load data error:', err);
    }
  };

  const requestLocationPermission = async () => {
    try {
      const permission =
        Platform.OS === 'ios'
          ? PERMISSIONS.IOS.LOCATION_WHEN_IN_USE
          : PERMISSIONS.ANDROID.ACCESS_FINE_LOCATION;

      const result = await request(permission);
      return result === RESULTS.GRANTED;
    } catch {
      // Emulator / permission API failure — assume granted
      return true;
    }
  };

  const getCurrentLocation = async () => {
    setIsLocating(true);
    setLocationError(null);

    const granted = await requestLocationPermission();
    if (!granted) {
      setLocationError('Izin lokasi diperlukan untuk absensi');
      setIsLocating(false);
      return;
    }

    Geolocation.getCurrentPosition(
      position => {
        const {latitude, longitude, accuracy} = position.coords;
        setCurrentLocation({latitude, longitude, accuracy});

        if (selectedOffice) {
          const dist = hitungJarak(
            latitude, longitude,
            parseFloat(selectedOffice.latitude),
            parseFloat(selectedOffice.longitude),
          );
          setJarak(dist);
        }
        setIsLocating(false);
      },
      error => {
        // Fallback: coba tanpa high accuracy
        Geolocation.getCurrentPosition(
          pos => {
            const {latitude, longitude, accuracy} = pos.coords;
            setCurrentLocation({latitude, longitude, accuracy});
            if (selectedOffice) {
              const dist = hitungJarak(
                latitude, longitude,
                parseFloat(selectedOffice.latitude),
                parseFloat(selectedOffice.longitude),
              );
              setJarak(dist);
            }
            setIsLocating(false);
          },
          err => {
            setLocationError('Gagal mendapatkan lokasi: ' + err.message);
            setIsLocating(false);
          },
          {enableHighAccuracy: false, timeout: 20000, maximumAge: 60000},
        );
      },
      {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0,
        forceRequestLocation: true,
        showLocationDialog: true,
      },
    );
  };

  useEffect(() => {
    if (selectedOffice && currentLocation) {
      const dist = hitungJarak(
        currentLocation.latitude,
        currentLocation.longitude,
        parseFloat(selectedOffice.latitude),
        parseFloat(selectedOffice.longitude),
      );
      setJarak(dist);
    }
  }, [selectedOffice, currentLocation]);

  const takeFoto = async () => {
    try {
      const result = await launchCamera({
        mediaType: 'photo',
        quality: 0.7,
        cameraType: 'front',
        saveToPhotos: false,
      });

      if (!result.didCancel && result.assets?.[0]) {
        setFotoUri(result.assets[0].uri);
      } else if (result.errorCode === 'camera_unavailable') {
        // Emulator / no camera — fallback to gallery
        Alert.alert(
          'Kamera Tidak Tersedia',
          'Pilih foto dari galeri?',
          [
            {text: 'Batal', style: 'cancel'},
            {
              text: 'Pilih dari Galeri',
              onPress: async () => {
                const galleryResult = await launchImageLibrary({
                  mediaType: 'photo',
                  quality: 0.7,
                });
                if (!galleryResult.didCancel && galleryResult.assets?.[0]) {
                  setFotoUri(galleryResult.assets[0].uri);
                }
              },
            },
          ],
        );
      }
    } catch (err) {
      Alert.alert('Error', `Gagal membuka kamera: ${err.message}`);
    }
  };

  const handleAbsensi = async (type) => {
    if (!currentLocation) {
      Alert.alert('Perhatian', 'Mohon aktifkan GPS terlebih dahulu');
      return;
    }

    if (!selectedOffice) {
      Alert.alert('Perhatian', 'Pilih kantor terlebih dahulu');
      return;
    }

    if (jarak && jarak > selectedOffice.radius) {
      Alert.alert(
        'Di Luar Jangkauan',
        `Anda berada ${formatJarak(jarak)} dari kantor. Radius maksimal: ${selectedOffice.radius}m`,
      );
      return;
    }

    setIsLoading(true);

    try {
      const formData = new FormData();
      formData.append('latitude', currentLocation.latitude.toString());
      formData.append('longitude', currentLocation.longitude.toString());
      formData.append('office_id', selectedOffice.id.toString());
      formData.append('device_info', Platform.OS + ' ' + Platform.Version);

      if (fotoUri) {
        formData.append('foto', {
          uri: fotoUri,
          type: 'image/jpeg',
          name: 'foto_absensi.jpg',
        });
      }

      let response;
      if (type === 'masuk') {
        response = await attendanceService.checkIn(formData);
      } else {
        response = await attendanceService.checkOut(formData);
      }

      Alert.alert('Berhasil!', response.data.message);
      setFotoUri(null);
      await loadData();
    } catch (err) {
      const data = err.response?.data;
      let msg = 'Terjadi kesalahan';
      if (data?.errors) {
        msg = Object.values(data.errors).flat().join('\n');
      } else if (data?.message) {
        msg = data.message;
      }
      Alert.alert('Gagal', msg);
    } finally {
      setIsLoading(false);
    }
  };

  const isInRadius = currentLocation && selectedOffice && jarak !== null && jarak <= selectedOffice.radius;
  const statusColors = todayAttendance?.status ? STATUS_COLORS[todayAttendance.status] : null;

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.primaryDark} />

      <View style={styles.header}>
        <Text style={styles.headerTitle}>Absensi</Text>
        <Text style={styles.headerSubtitle}>Tandai kehadiran Anda</Text>
      </View>

      <ScrollView style={styles.scroll} contentContainerStyle={{paddingBottom: 24}}>

        {/* Status Hari Ini */}
        {todayAttendance && (
          <View style={[styles.card, {backgroundColor: statusColors?.bg || COLORS.white}]}>
            <View style={styles.cardRow}>
              <Icon name="checkmark-circle" size={22} color={statusColors?.text || COLORS.gray} />
              <Text style={[styles.cardTitle, {color: statusColors?.text}]}>
                Status: {statusColors?.label}
              </Text>
            </View>
            <View style={styles.timeInfoRow}>
              <View style={styles.timeInfoItem}>
                <Text style={styles.timeInfoLabel}>Masuk</Text>
                <Text style={styles.timeInfoValue}>{formatTime(todayAttendance.jam_masuk)}</Text>
              </View>
              <View style={styles.timeInfoItem}>
                <Text style={styles.timeInfoLabel}>Keluar</Text>
                <Text style={styles.timeInfoValue}>
                  {todayAttendance.jam_keluar ? formatTime(todayAttendance.jam_keluar) : '--:--'}
                </Text>
              </View>
              {todayAttendance.jam_keluar && todayAttendance.durasi_kerja && (
                <View style={styles.timeInfoItem}>
                  <Text style={styles.timeInfoLabel}>Durasi</Text>
                  <Text style={[styles.timeInfoValue, {color: COLORS.primary}]}>
                    {todayAttendance.durasi_kerja} jam
                  </Text>
                </View>
              )}
            </View>
          </View>
        )}

        {/* Pilih Kantor */}
        <View style={styles.card}>
          <Text style={styles.cardLabel}>Lokasi Kantor</Text>
          {offices.map(office => (
            <TouchableOpacity
              key={office.id}
              style={[
                styles.officeItem,
                selectedOffice?.id === office.id && styles.officeItemActive,
              ]}
              onPress={() => setSelectedOffice(office)}>
              <Icon
                name="business-outline"
                size={18}
                color={selectedOffice?.id === office.id ? COLORS.primary : COLORS.gray}
              />
              <View style={{flex: 1, marginLeft: 8}}>
                <Text style={[
                  styles.officeName,
                  selectedOffice?.id === office.id && {color: COLORS.primary},
                ]}>
                  {office.nama}
                </Text>
                <Text style={styles.officeRadius}>
                  Radius: {office.radius}m | Jam: {formatTime(office.jam_masuk)} - {formatTime(office.jam_keluar)}
                </Text>
              </View>
              {selectedOffice?.id === office.id && (
                <Icon name="checkmark-circle" size={20} color={COLORS.primary} />
              )}
            </TouchableOpacity>
          ))}
        </View>

        {/* GPS */}
        <View style={styles.card}>
          <Text style={styles.cardLabel}>Lokasi GPS</Text>

          {locationError && (
            <View style={styles.errorBox}>
              <Icon name="warning" size={16} color={COLORS.danger} />
              <Text style={styles.errorText}>{locationError}</Text>
            </View>
          )}

          {currentLocation && (
            <View style={styles.locationInfo}>
              <View style={styles.coordRow}>
                <Icon name="locate" size={16} color={COLORS.primary} />
                <Text style={styles.coordText}>
                  {currentLocation.latitude.toFixed(6)}, {currentLocation.longitude.toFixed(6)}
                </Text>
              </View>
              {jarak !== null && (
                <View style={[
                  styles.jarakBadge,
                  {backgroundColor: isInRadius ? COLORS.successLight : COLORS.dangerLight},
                ]}>
                  <Icon
                    name={isInRadius ? 'checkmark-circle' : 'close-circle'}
                    size={16}
                    color={isInRadius ? COLORS.success : COLORS.danger}
                  />
                  <Text style={[styles.jarakText, {color: isInRadius ? COLORS.success : COLORS.danger}]}>
                    Jarak: {formatJarak(jarak)} {isInRadius ? '(Dalam radius)' : '(Di luar radius)'}
                  </Text>
                </View>
              )}
            </View>
          )}

          <TouchableOpacity style={styles.gpsBtn} onPress={getCurrentLocation} disabled={isLocating}>
            {isLocating ? (
              <ActivityIndicator color={COLORS.white} size="small" />
            ) : (
              <>
                <Icon name="navigate" size={18} color={COLORS.white} />
                <Text style={styles.gpsBtnText}>
                  {currentLocation ? 'Perbarui Lokasi' : 'Aktifkan GPS'}
                </Text>
              </>
            )}
          </TouchableOpacity>
        </View>

        {/* Foto Selfie */}
        <View style={styles.card}>
          <Text style={styles.cardLabel}>Foto Kehadiran (Opsional)</Text>
          <TouchableOpacity style={styles.cameraBtn} onPress={takeFoto}>
            <Icon name={fotoUri ? 'checkmark-circle' : 'camera-outline'} size={22}
              color={fotoUri ? COLORS.success : COLORS.gray} />
            <Text style={styles.cameraBtnText}>
              {fotoUri ? 'Foto sudah diambil' : 'Ambil Foto Selfie'}
            </Text>
          </TouchableOpacity>
        </View>

        {/* Tombol Absensi */}
        {(!todayAttendance?.jam_masuk) && (
          <TouchableOpacity
            style={[
              styles.absenButton,
              {backgroundColor: COLORS.success},
              isLoading && styles.buttonDisabled,
            ]}
            onPress={() => handleAbsensi('masuk')}
            disabled={isLoading}>
            {isLoading ? (
              <ActivityIndicator color={COLORS.white} />
            ) : (
              <>
                <Icon name="log-in-outline" size={22} color={COLORS.white} />
                <Text style={styles.absenButtonText}>Absen Masuk</Text>
              </>
            )}
          </TouchableOpacity>
        )}

        {(todayAttendance?.jam_masuk && !todayAttendance?.jam_keluar) && (
          <TouchableOpacity
            style={[
              styles.absenButton,
              {backgroundColor: COLORS.danger},
              isLoading && styles.buttonDisabled,
            ]}
            onPress={() => handleAbsensi('keluar')}
            disabled={isLoading}>
            {isLoading ? (
              <ActivityIndicator color={COLORS.white} />
            ) : (
              <>
                <Icon name="log-out-outline" size={22} color={COLORS.white} />
                <Text style={styles.absenButtonText}>Absen Keluar</Text>
              </>
            )}
          </TouchableOpacity>
        )}

        {(todayAttendance?.jam_masuk && todayAttendance?.jam_keluar) && (
          <View style={styles.doneContainer}>
            <Icon name="checkmark-circle" size={40} color={COLORS.success} />
            <Text style={styles.doneText}>Absensi hari ini sudah lengkap!</Text>
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: COLORS.background},
  header: {backgroundColor: COLORS.primary, padding: 20, paddingTop: 16},
  headerTitle: {fontSize: 20, fontWeight: '700', color: COLORS.white},
  headerSubtitle: {fontSize: 13, color: 'rgba(255,255,255,0.75)', marginTop: 2},
  scroll: {flex: 1, padding: 16},
  card: {
    backgroundColor: COLORS.white,
    borderRadius: 14,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.06,
    shadowRadius: 6,
    elevation: 3,
  },
  cardLabel: {fontSize: 13, fontWeight: '600', color: COLORS.gray, marginBottom: 12, textTransform: 'uppercase', letterSpacing: 0.5},
  cardRow: {flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 8},
  cardTitle: {fontSize: 15, fontWeight: '700'},
  timeInfoRow: {flexDirection: 'row', gap: 24},
  timeInfoItem: {},
  timeInfoLabel: {fontSize: 12, color: COLORS.gray},
  timeInfoValue: {fontSize: 16, fontWeight: '700', color: COLORS.dark},
  officeItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: COLORS.border,
    marginBottom: 8,
  },
  officeItemActive: {borderColor: COLORS.primary, backgroundColor: COLORS.primaryLight},
  officeName: {fontSize: 14, fontWeight: '600', color: COLORS.dark},
  officeRadius: {fontSize: 12, color: COLORS.gray, marginTop: 2},
  errorBox: {flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: COLORS.dangerLight, padding: 10, borderRadius: 8, marginBottom: 10},
  errorText: {fontSize: 13, color: COLORS.danger, flex: 1},
  locationInfo: {marginBottom: 12},
  coordRow: {flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 8},
  coordText: {fontSize: 12, color: COLORS.secondary},
  jarakBadge: {flexDirection: 'row', alignItems: 'center', gap: 6, padding: 8, borderRadius: 8},
  jarakText: {fontSize: 13, fontWeight: '600'},
  gpsBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: COLORS.primary,
    padding: 12,
    borderRadius: 10,
  },
  gpsBtnText: {color: COLORS.white, fontWeight: '600', fontSize: 14},
  cameraBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    padding: 14,
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: COLORS.border,
    borderStyle: 'dashed',
  },
  cameraBtnText: {fontSize: 14, color: COLORS.secondary},
  absenButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
    padding: 16,
    borderRadius: 14,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 6,
  },
  buttonDisabled: {opacity: 0.6},
  absenButtonText: {color: COLORS.white, fontSize: 17, fontWeight: '700'},
  doneContainer: {alignItems: 'center', padding: 24},
  doneText: {fontSize: 15, color: COLORS.success, fontWeight: '600', marginTop: 8},
});

export default AbsensiScreen;
