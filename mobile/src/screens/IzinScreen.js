import React, {useState, useEffect, useRef} from 'react';
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
import {leaveService} from '../services/api';
import {COLORS} from '../utils/colors';
import {formatTanggal} from '../utils/helpers';

const JENIS_COLORS = {
  izin: {bg: '#dbeafe', text: '#1d4ed8', label: 'Izin'},
  sakit: {bg: '#f3e8ff', text: '#7c3aed', label: 'Sakit'},
  cuti: {bg: '#dcfce7', text: '#16a34a', label: 'Cuti'},
};

const STATUS_BADGE = {
  menunggu: {bg: '#fef3c7', text: '#d97706', label: 'Menunggu'},
  disetujui: {bg: '#dcfce7', text: '#16a34a', label: 'Disetujui'},
  ditolak: {bg: '#fee2e2', text: '#dc2626', label: 'Ditolak'},
};

const IzinScreen = ({navigation}) => {
  const [leaves, setLeaves] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const pageRef = useRef(1);

  useEffect(() => {
    const unsubscribe = navigation.addListener('focus', () => {
      setLeaves([]);
      pageRef.current = 1;
      setPage(1);
      fetchLeaves(1, true);
    });
    return unsubscribe;
  }, [navigation]);

  const fetchLeaves = async (pageNum = 1, reset = false) => {
    if (isLoading) return;
    setIsLoading(true);
    try {
      const response = await leaveService.getAll(pageNum);
      const {data: newData, last_page} = response.data.data;
      setLeaves(prev => (reset ? newData : [...prev, ...newData]));
      setHasMore(pageNum < last_page);
    } catch (err) {
      console.error('Leave fetch error:', err);
    } finally {
      setIsLoading(false);
    }
  };

  const loadMore = () => {
    if (hasMore && !isLoading) {
      const nextPage = pageRef.current + 1;
      pageRef.current = nextPage;
      setPage(nextPage);
      fetchLeaves(nextPage);
    }
  };

  const renderItem = ({item}) => {
    const jenis = JENIS_COLORS[item.jenis] || {bg: '#f1f5f9', text: '#64748b', label: item.jenis};
    const status = STATUS_BADGE[item.status] || STATUS_BADGE.menunggu;
    const durasi = Math.round(
      (new Date(item.tanggal_selesai) - new Date(item.tanggal_mulai)) / (1000 * 60 * 60 * 24) + 1
    );

    return (
      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <View style={[styles.jenisBadge, {backgroundColor: jenis.bg}]}>
            <Text style={[styles.jenisText, {color: jenis.text}]}>{jenis.label}</Text>
          </View>
          <View style={[styles.statusBadge, {backgroundColor: status.bg}]}>
            <Text style={[styles.statusText, {color: status.text}]}>{status.label}</Text>
          </View>
        </View>

        <View style={styles.cardBody}>
          <View style={styles.infoRow}>
            <Icon name="calendar-outline" size={14} color={COLORS.gray} />
            <Text style={styles.infoText}>
              {formatTanggal(item.tanggal_mulai)}
              {item.tanggal_mulai !== item.tanggal_selesai
                ? ` s/d ${formatTanggal(item.tanggal_selesai)}`
                : ''}
              {' '}({durasi} hari)
            </Text>
          </View>
          <View style={styles.infoRow}>
            <Icon name="document-text-outline" size={14} color={COLORS.gray} />
            <Text style={styles.infoText} numberOfLines={2}>{item.alasan}</Text>
          </View>
          {item.catatan_admin && (
            <View style={[styles.infoRow, {marginTop: 4}]}>
              <Icon name="chatbubble-outline" size={14} color={COLORS.gray} />
              <Text style={[styles.infoText, {color: status.text}]}>
                Admin: {item.catatan_admin}
              </Text>
            </View>
          )}
        </View>
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.primaryDark} />

      <View style={styles.header}>
        <Text style={styles.headerTitle}>Permohonan Izin</Text>
        <TouchableOpacity
          style={styles.addBtn}
          onPress={() => navigation.navigate('AjukanIzin')}>
          <Icon name="add" size={22} color={COLORS.white} />
          <Text style={styles.addBtnText}>Ajukan</Text>
        </TouchableOpacity>
      </View>

      {isLoading && leaves.length === 0 ? (
        <View style={styles.loading}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
      ) : (
        <FlatList
          data={leaves}
          keyExtractor={item => item.id.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.listContent}
          onEndReached={loadMore}
          onEndReachedThreshold={0.3}
          ListFooterComponent={
            isLoading && leaves.length > 0 ? (
              <ActivityIndicator color={COLORS.primary} style={{padding: 16}} />
            ) : null
          }
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Icon name="document-outline" size={60} color={COLORS.border} />
              <Text style={styles.emptyText}>Belum ada permohonan izin</Text>
              <TouchableOpacity
                style={styles.ajukanBtn}
                onPress={() => navigation.navigate('AjukanIzin')}>
                <Text style={styles.ajukanBtnText}>Ajukan Sekarang</Text>
              </TouchableOpacity>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: COLORS.background},
  header: {
    backgroundColor: COLORS.primary,
    padding: 16,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  headerTitle: {fontSize: 20, fontWeight: '700', color: COLORS.white},
  addBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
  },
  addBtnText: {color: COLORS.white, fontWeight: '600', fontSize: 14},
  loading: {flex: 1, justifyContent: 'center', alignItems: 'center'},
  listContent: {padding: 12, paddingBottom: 24},
  card: {
    backgroundColor: COLORS.white,
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.06,
    shadowRadius: 6,
    elevation: 3,
  },
  cardHeader: {flexDirection: 'row', justifyContent: 'space-between', marginBottom: 10},
  jenisBadge: {paddingHorizontal: 12, paddingVertical: 4, borderRadius: 20},
  jenisText: {fontSize: 12, fontWeight: '700'},
  statusBadge: {paddingHorizontal: 12, paddingVertical: 4, borderRadius: 20},
  statusText: {fontSize: 12, fontWeight: '700'},
  cardBody: {},
  infoRow: {flexDirection: 'row', alignItems: 'flex-start', gap: 8, marginBottom: 6},
  infoText: {fontSize: 13, color: COLORS.dark, flex: 1},
  emptyContainer: {alignItems: 'center', paddingTop: 60},
  emptyText: {fontSize: 16, color: COLORS.gray, marginTop: 16, fontWeight: '600'},
  ajukanBtn: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 10,
    marginTop: 16,
  },
  ajukanBtnText: {color: COLORS.white, fontWeight: '700'},
});

export default IzinScreen;
