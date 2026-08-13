import React, {useState, useCallback} from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  StatusBar,
} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {useFocusEffect} from '@react-navigation/native';
import {attendanceService} from '../services/api';
import {COLORS, STATUS_COLORS} from '../utils/colors';
import {formatTanggal, formatTime, getNamaBulan, formatDurasi} from '../utils/helpers';

const HistoriScreen = ({navigation}) => {
  const [attendances, setAttendances] = useState([]);
  const [summary, setSummary] = useState({});
  const [isLoading, setIsLoading] = useState(false);
  const [bulan, setBulan] = useState(new Date().getMonth() + 1);
  const [tahun, setTahun] = useState(new Date().getFullYear());

  useFocusEffect(
    useCallback(() => {
      fetchHistory();
    }, [bulan, tahun]),
  );

  const fetchHistory = async () => {
    setIsLoading(true);
    try {
      const response = await attendanceService.getHistory(bulan, tahun);
      setAttendances(response.data.data.attendances);
      setSummary(response.data.data.summary);
    } catch (err) {
      console.error('History error:', err);
    } finally {
      setIsLoading(false);
    }
  };

  const prevMonth = () => {
    // Batas bawah: Januari 2024 (awal sistem)
    if (tahun <= 2024 && bulan === 1) return;
    if (bulan === 1) {
      setBulan(12);
      setTahun(tahun - 1);
    } else {
      setBulan(bulan - 1);
    }
  };

  const nextMonth = () => {
    const now = new Date();
    const curBulan = now.getMonth() + 1;
    const curTahun = now.getFullYear();
    // Hard-cap: can't navigate past current month
    if (tahun > curTahun || (tahun === curTahun && bulan >= curBulan)) return;
    if (bulan === 12) {
      setBulan(1);
      setTahun(tahun + 1);
    } else {
      setBulan(bulan + 1);
    }
  };

  const StatusBadge = ({status}) => {
    const s = STATUS_COLORS[status] || STATUS_COLORS.tidak_hadir;
    return (
      <View style={[styles.badge, {backgroundColor: s.bg}]}>
        <Text style={[styles.badgeText, {color: s.text}]}>{s.label}</Text>
      </View>
    );
  };

  const renderItem = ({item, index}) => {
    const parts = item.tanggal ? item.tanggal.split('-') : [];
    const day = parts[2]?.replace(/^0/, '') || '--';
    const month = parts[1] ? getNamaBulan(parseInt(parts[1])).substring(0, 3) : '';
    return (
    <TouchableOpacity
      style={styles.historyItem}
      onPress={() => navigation.navigate('DetailAbsensi', {attendance: item})}>
      <View style={styles.dateBox}>
        <Text style={styles.dateDay}>{day}</Text>
        <Text style={styles.dateMonth}>{month}</Text>
      </View>
      <View style={styles.itemInfo}>
        <Text style={styles.itemDate}>{formatTanggal(item.tanggal)}</Text>
        <Text style={styles.itemTime}>
          <Icon name="log-in-outline" size={12} color={COLORS.success} /> {formatTime(item.jam_masuk)}
          {'  '}
          <Icon name="log-out-outline" size={12} color={COLORS.danger} />{' '}
          {item.jam_keluar ? formatTime(item.jam_keluar) : '--:--'}
          {item.durasi_kerja ? (
            <Text style={{color: COLORS.primary}}>{'  '}<Icon name="hourglass-outline" size={11} color={COLORS.primary} /> {formatDurasi(item.durasi_kerja)}</Text>
          ) : null}
        </Text>
        {item.jarak_masuk && (
          <Text style={styles.itemJarak}>
            <Icon name="navigate" size={11} color={COLORS.gray} /> {Math.round(item.jarak_masuk)}m dari kantor
          </Text>
        )}
      </View>
      <StatusBadge status={item.status} />
    </TouchableOpacity>
  );
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.primary} />

      <View style={styles.header}>
        <Text style={styles.headerTitle}>Riwayat Absensi</Text>

        {/* Month Navigation */}
        <View style={styles.monthNav}>
          <TouchableOpacity onPress={prevMonth} style={styles.navBtn}>
            <Icon name="chevron-back" size={20} color={COLORS.white} />
          </TouchableOpacity>
          <Text style={styles.monthText}>{getNamaBulan(bulan)} {tahun}</Text>
          <TouchableOpacity onPress={nextMonth} style={styles.navBtn}>
            <Icon name="chevron-forward" size={20} color={COLORS.white} />
          </TouchableOpacity>
        </View>
      </View>

      {/* Summary */}
      <View style={styles.summaryContainer}>
        {[
          {key: 'hadir', label: 'Hadir', color: COLORS.success},
          {key: 'terlambat', label: 'Telat', color: COLORS.warning},
          {key: 'tidak_hadir', label: 'Alpha', color: COLORS.danger},
          {key: 'izin', label: 'Izin', color: COLORS.info},
          {key: 'sakit', label: 'Sakit', color: '#8b5cf6'},
        ].map(item => (
          <View key={item.key} style={styles.summaryItem}>
            <Text style={[styles.summaryValue, {color: item.color}]}>
              {summary[item.key] || 0}
            </Text>
            <Text style={styles.summaryLabel}>{item.label}</Text>
          </View>
        ))}
      </View>

      {isLoading ? (
        <View style={styles.loading}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
      ) : (
        <FlatList
          data={attendances}
          keyExtractor={item => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.listContent}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Icon name="calendar-outline" size={60} color={COLORS.border} />
              <Text style={styles.emptyText}>Tidak ada data absensi</Text>
              <Text style={styles.emptySubText}>untuk bulan {getNamaBulan(bulan)} {tahun}</Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: COLORS.background},
  header: {backgroundColor: COLORS.primary, padding: 16},
  headerTitle: {fontSize: 20, fontWeight: '700', color: COLORS.white, marginBottom: 12},
  monthNav: {flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between'},
  navBtn: {padding: 4},
  monthText: {fontSize: 16, fontWeight: '600', color: COLORS.white},
  summaryContainer: {
    flexDirection: 'row',
    backgroundColor: COLORS.white,
    padding: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  summaryItem: {flex: 1, alignItems: 'center'},
  summaryValue: {fontSize: 20, fontWeight: '800'},
  summaryLabel: {fontSize: 11, color: COLORS.gray, marginTop: 2},
  loading: {flex: 1, justifyContent: 'center', alignItems: 'center'},
  listContent: {padding: 12, paddingBottom: 24},
  historyItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.white,
    borderRadius: 12,
    padding: 14,
    marginBottom: 8,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 1},
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  dateBox: {
    width: 44,
    height: 44,
    borderRadius: 10,
    backgroundColor: COLORS.primaryLight,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  dateDay: {fontSize: 17, fontWeight: '800', color: COLORS.primary},
  dateMonth: {fontSize: 10, color: COLORS.primary, marginTop: -2},
  itemInfo: {flex: 1},
  itemDate: {fontSize: 14, fontWeight: '600', color: COLORS.dark},
  itemTime: {fontSize: 12, color: COLORS.gray, marginTop: 3},
  itemJarak: {fontSize: 11, color: COLORS.gray, marginTop: 2},
  badge: {paddingHorizontal: 10, paddingVertical: 4, borderRadius: 20},
  badgeText: {fontSize: 11, fontWeight: '700'},
  emptyContainer: {alignItems: 'center', paddingTop: 60},
  emptyText: {fontSize: 16, fontWeight: '600', color: COLORS.gray, marginTop: 16},
  emptySubText: {fontSize: 14, color: COLORS.gray, marginTop: 4},
});

export default HistoriScreen;
