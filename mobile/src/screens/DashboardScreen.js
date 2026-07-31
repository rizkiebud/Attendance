import React, {useState, useEffect, useCallback} from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  StatusBar,
  Image,
} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {useFocusEffect} from '@react-navigation/native';
import {useAuth} from '../context/AuthContext';
import {dashboardService, BASE_URL} from '../services/api';
import {COLORS, STATUS_COLORS} from '../utils/colors';
import {getGreeting, getNamaHari, formatTanggal, formatTime} from '../utils/helpers';

const DashboardScreen = ({navigation}) => {
  const {employee} = useAuth();
  const [data, setData] = useState(null);
  const [isLoading, setIsLoading] = useState(false);

  const fetchDashboard = async () => {
    setIsLoading(true);
    try {
      const response = await dashboardService.get();
      setData(response.data.data);
    } catch (err) {
      console.error('Dashboard error:', err);
    } finally {
      setIsLoading(false);
    }
  };

  useFocusEffect(
    useCallback(() => {
      fetchDashboard();
    }, []),
  );

  const todayAttendance = data?.today_attendance;
  const summary = data?.monthly_summary || {};
  const recentAttendances = data?.recent_attendances || [];

  const StatusCard = ({status}) => {
    const s = STATUS_COLORS[status] || STATUS_COLORS.tidak_hadir;
    return (
      <View style={[styles.statusBadge, {backgroundColor: s.bg}]}>
        <Text style={[styles.statusText, {color: s.text}]}>{s.label}</Text>
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.primaryDark} />

      {/* Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.greeting}>{getGreeting()},</Text>
          <Text style={styles.userName}>{employee?.nama || 'Karyawan'}</Text>
          <Text style={styles.dateText}>
            {data?.current_date ? data.current_date : `${getNamaHari()}, ${formatTanggal(new Date().toISOString().split('T')[0])}`}
          </Text>
        </View>
        <View style={styles.avatarBox}>
          {employee?.foto ? (
            <Image
              source={{uri: `${BASE_URL}/storage/${employee.foto}`}}
              style={styles.avatarImg}
            />
          ) : (
            <Icon name="person" size={28} color={COLORS.white} />
          )}
        </View>
      </View>

      <ScrollView
        style={styles.scroll}
        contentContainerStyle={{paddingBottom: 24}}
        refreshControl={
          <RefreshControl refreshing={isLoading} onRefresh={fetchDashboard} />
        }>

        {/* Absensi Hari Ini */}
        <View style={styles.cardToday}>
          <Text style={styles.sectionTitle}>Absensi Hari Ini</Text>
          {todayAttendance ? (
            <View>
              <View style={styles.timeRow}>
                <View style={styles.timeItem}>
                  <Icon name="log-in-outline" size={20} color={COLORS.success} />
                  <Text style={styles.timeLabel}>Masuk</Text>
                  <Text style={styles.timeValue}>{formatTime(todayAttendance.jam_masuk)}</Text>
                </View>
                <View style={styles.timeDivider} />
                <View style={styles.timeItem}>
                  <Icon name="log-out-outline" size={20} color={COLORS.danger} />
                  <Text style={styles.timeLabel}>Keluar</Text>
                  <Text style={styles.timeValue}>
                    {todayAttendance.jam_keluar ? formatTime(todayAttendance.jam_keluar) : '--:--'}
                  </Text>
                </View>
                <View style={styles.timeDivider} />
                <View style={styles.timeItem}>
                  <Icon name="location-outline" size={20} color={COLORS.info} />
                  <Text style={styles.timeLabel}>Jarak</Text>
                  <Text style={styles.timeValue}>
                    {todayAttendance.jarak_masuk ? Math.round(todayAttendance.jarak_masuk) + 'm' : '-'}
                  </Text>
                </View>
              </View>
              <View style={styles.statusRow}>
                <StatusCard status={todayAttendance.status} />
                {!todayAttendance.jam_keluar && (
                  <TouchableOpacity
                    style={styles.absenBtn}
                    onPress={() => navigation.navigate('Absensi')}>
                    <Text style={styles.absenBtnText}>Absen Keluar</Text>
                  </TouchableOpacity>
                )}
              </View>
            </View>
          ) : (
            <View style={styles.noAbsenContainer}>
              <Icon name="time-outline" size={40} color={COLORS.gray} />
              <Text style={styles.noAbsenText}>Belum absen hari ini</Text>
              <TouchableOpacity
                style={styles.absenBtn}
                onPress={() => navigation.navigate('Absensi')}>
                <Text style={styles.absenBtnText}>Mulai Absen</Text>
              </TouchableOpacity>
            </View>
          )}
        </View>

        {/* Rekap Bulan Ini */}
        <Text style={styles.sectionTitleOut}>Rekap Bulan Ini</Text>
        <View style={styles.summaryGrid}>
          {[
            {key: 'hadir', label: 'Hadir', icon: 'checkmark-circle', color: COLORS.success},
            {key: 'terlambat', label: 'Terlambat', icon: 'warning', color: COLORS.warning},
            {key: 'tidak_hadir', label: 'Tidak Hadir', icon: 'close-circle', color: COLORS.danger},
            {key: 'izin_sakit', label: 'Izin/Sakit', icon: 'document-text', color: COLORS.info},
          ].map(item => (
            <View key={item.key} style={styles.summaryCard}>
              <Icon name={item.icon} size={24} color={item.color} />
              <Text style={styles.summaryValue}>{summary[item.key] || 0}</Text>
              <Text style={styles.summaryLabel}>{item.label}</Text>
            </View>
          ))}
        </View>

        {/* Riwayat Terakhir */}
        {recentAttendances.length > 0 && (
          <>
            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitleOut}>Riwayat Terakhir</Text>
              <TouchableOpacity onPress={() => navigation.navigate('Histori')}>
                <Text style={styles.seeAll}>Lihat Semua</Text>
              </TouchableOpacity>
            </View>
            <View style={styles.historyCard}>
              {recentAttendances.slice(0, 5).map((att, idx) => (
                <View key={att.id} style={[styles.historyItem, idx > 0 && styles.historyItemBorder]}>
                  <View style={styles.historyLeft}>
                    <Text style={styles.historyDate}>{formatTanggal(att.tanggal)}</Text>
                    <Text style={styles.historyTime}>
                      {formatTime(att.jam_masuk)} - {att.jam_keluar ? formatTime(att.jam_keluar) : '--:--'}
                    </Text>
                  </View>
                  <StatusCard status={att.status} />
                </View>
              ))}
            </View>
          </>
        )}
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: COLORS.background},
  header: {
    backgroundColor: COLORS.primary,
    padding: 20,
    paddingTop: 16,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  greeting: {fontSize: 14, color: 'rgba(255,255,255,0.8)'},
  userName: {fontSize: 20, fontWeight: '700', color: COLORS.white, marginTop: 2},
  dateText: {fontSize: 13, color: 'rgba(255,255,255,0.7)', marginTop: 2},
  avatarBox: {
    width: 52,
    height: 52,
    borderRadius: 26,
    backgroundColor: 'rgba(255,255,255,0.2)',
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  avatarImg: {
    width: 52,
    height: 52,
    borderRadius: 26,
  },
  scroll: {flex: 1, padding: 16},
  cardToday: {
    backgroundColor: COLORS.white,
    borderRadius: 16,
    padding: 16,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 3,
  },
  sectionTitle: {fontSize: 15, fontWeight: '700', color: COLORS.dark, marginBottom: 12},
  sectionTitleOut: {fontSize: 15, fontWeight: '700', color: COLORS.dark, marginBottom: 10},
  timeRow: {flexDirection: 'row', alignItems: 'center', marginBottom: 12},
  timeItem: {flex: 1, alignItems: 'center'},
  timeLabel: {fontSize: 12, color: COLORS.gray, marginTop: 4},
  timeValue: {fontSize: 16, fontWeight: '700', color: COLORS.dark, marginTop: 2},
  timeDivider: {width: 1, height: 40, backgroundColor: COLORS.border},
  statusRow: {flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between'},
  noAbsenContainer: {alignItems: 'center', paddingVertical: 12},
  noAbsenText: {color: COLORS.gray, marginTop: 8, marginBottom: 12, fontSize: 14},
  statusBadge: {paddingHorizontal: 10, paddingVertical: 4, borderRadius: 20},
  statusText: {fontSize: 12, fontWeight: '600'},
  absenBtn: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 10,
  },
  absenBtnText: {color: COLORS.white, fontWeight: '600', fontSize: 13},
  summaryGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 16,
  },
  summaryCard: {
    width: '47%',
    backgroundColor: COLORS.white,
    borderRadius: 14,
    padding: 16,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
  },
  summaryValue: {fontSize: 28, fontWeight: '800', color: COLORS.dark, marginTop: 6},
  summaryLabel: {fontSize: 12, color: COLORS.gray, marginTop: 2},
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  seeAll: {fontSize: 13, color: COLORS.primary, fontWeight: '600'},
  historyCard: {
    backgroundColor: COLORS.white,
    borderRadius: 14,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.05,
    shadowRadius: 6,
    elevation: 2,
    overflow: 'hidden',
  },
  historyItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 14,
  },
  historyItemBorder: {borderTopWidth: 1, borderTopColor: COLORS.border},
  historyLeft: {},
  historyDate: {fontSize: 14, fontWeight: '600', color: COLORS.dark},
  historyTime: {fontSize: 12, color: COLORS.gray, marginTop: 2},
});

export default DashboardScreen;
