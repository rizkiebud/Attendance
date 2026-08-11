import React, {useState, useEffect} from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  StatusBar,
  Alert,
  TextInput,
  Modal,
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

const PersetujuanIzinScreen = () => {
  const [leaves, setLeaves] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [approvingId, setApprovingId] = useState(null);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);

  // Modal tolak
  const [rejectTarget, setRejectTarget] = useState(null);
  const [catatan, setCatatan] = useState('');
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    fetchPending(1, true);
  }, []);

  const fetchPending = async (pageNum = 1, reset = false) => {
    if (isLoading) return;
    setIsLoading(true);
    try {
      const response = await leaveService.getPending(pageNum);
      const {data: newData, last_page} = response.data.data;
      setLeaves(prev => (reset ? newData : [...prev, ...newData]));
      setHasMore(pageNum < last_page);
    } catch (err) {
      console.error('Pending leave fetch error:', err);
    } finally {
      setIsLoading(false);
    }
  };

  const loadMore = () => {
    if (hasMore && !isLoading) {
      const nextPage = page + 1;
      setPage(nextPage);
      fetchPending(nextPage);
    }
  };

  const handleApprove = item => {
    Alert.alert(
      'Setujui Permohonan',
      `Yakin menyetujui izin ${item.employee?.user?.name || 'karyawan'} (${JENIS_COLORS[item.jenis]?.label || item.jenis})?`,
      [
        {text: 'Batal', style: 'cancel'},
        {
          text: 'Setujui',
          onPress: async () => {
            setApprovingId(item.id);
            try {
              await leaveService.approve(item.id);
              setLeaves(prev => prev.filter(i => i.id !== item.id));
              Alert.alert('Berhasil', 'Permohonan telah disetujui');
            } catch (err) {
              Alert.alert(
                'Gagal',
                err.response?.data?.message || 'Terjadi kesalahan',
              );
            } finally {
              setApprovingId(null);
            }
          },
        },
      ],
    );
  };

  const openReject = item => {
    setRejectTarget(item);
    setCatatan('');
  };

  const handleReject = async () => {
    if (!catatan.trim()) {
      Alert.alert('Perhatian', 'Catatan wajib diisi saat menolak izin');
      return;
    }
    setSubmitting(true);
    try {
      await leaveService.reject(rejectTarget.id, catatan.trim());
      setLeaves(prev => prev.filter(i => i.id !== rejectTarget.id));
      setRejectTarget(null);
      Alert.alert('Berhasil', 'Permohonan telah ditolak');
    } catch (err) {
      Alert.alert('Gagal', err.response?.data?.message || 'Terjadi kesalahan');
    } finally {
      setSubmitting(false);
    }
  };

  const renderItem = ({item}) => {
    const jenis = JENIS_COLORS[item.jenis] || {bg: '#f1f5f9', text: '#64748b', label: item.jenis};
    const durasi = Math.round(
      (new Date(item.tanggal_selesai) - new Date(item.tanggal_mulai)) / (1000 * 60 * 60 * 24) + 1
    );

    return (
      <View style={styles.card}>
        <View style={styles.cardHeader}>
          <View style={[styles.jenisBadge, {backgroundColor: jenis.bg}]}>
            <Text style={[styles.jenisText, {color: jenis.text}]}>{jenis.label}</Text>
          </View>
          <View style={styles.pendingBadge}>
            <Text style={styles.pendingText}>Menunggu</Text>
          </View>
        </View>

        <View style={styles.cardBody}>
          <View style={styles.infoRow}>
            <Icon name="person-outline" size={14} color={COLORS.gray} />
            <Text style={styles.infoText}>{item.employee?.user?.name || '-'}</Text>
          </View>
          <View style={styles.infoRow}>
            <Icon name="business-outline" size={14} color={COLORS.gray} />
            <Text style={styles.infoText}>
              {item.employee?.jabatan || '-'} · {item.employee?.departemen || '-'}
            </Text>
          </View>
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
        </View>

        <View style={styles.actionRow}>
          <TouchableOpacity
            style={[styles.actionBtn, styles.rejectBtn]}
            onPress={() => openReject(item)}>
            <Icon name="close" size={16} color={COLORS.danger} />
            <Text style={styles.rejectText}>Tolak</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.actionBtn, styles.approveBtn]}
            onPress={() => handleApprove(item)}
            disabled={approvingId === item.id}>
            {approvingId === item.id ? (
              <ActivityIndicator size="small" color={COLORS.white} />
            ) : (
              <>
                <Icon name="checkmark" size={16} color={COLORS.white} />
                <Text style={styles.approveText}>Setujui</Text>
              </>
            )}
          </TouchableOpacity>
        </View>
      </View>
    );
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.primaryDark} />

      <View style={styles.header}>
        <Text style={styles.headerTitle}>Persetujuan Izin</Text>
        <Text style={styles.headerSub}>Permohonan yang menunggu keputusan</Text>
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
              <Icon name="checkmark-done-circle-outline" size={60} color={COLORS.border} />
              <Text style={styles.emptyText}>Tidak ada permohonan menunggu</Text>
            </View>
          }
        />
      )}

      {/* Modal Tolak */}
      <Modal
        visible={!!rejectTarget}
        transparent
        animationType="fade"
        onRequestClose={() => setRejectTarget(null)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Tolak Permohonan</Text>
            <Text style={styles.modalSub}>
              {rejectTarget?.employee?.user?.name} —{' '}
              {rejectTarget ? formatTanggal(rejectTarget.tanggal_mulai) : ''}
            </Text>
            <TextInput
              style={styles.modalInput}
              placeholder="Catatan penolakan (wajib)"
              placeholderTextColor={COLORS.gray}
              value={catatan}
              onChangeText={setCatatan}
              multiline
              editable={!submitting}
            />
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={[styles.modalBtn, styles.modalCancel]}
                onPress={() => setRejectTarget(null)}
                disabled={submitting}>
                <Text style={styles.modalCancelText}>Batal</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.modalBtn, styles.modalConfirm]}
                onPress={handleReject}
                disabled={submitting}>
                {submitting ? (
                  <ActivityIndicator size="small" color={COLORS.white} />
                ) : (
                  <Text style={styles.modalConfirmText}>Tolak Izin</Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: COLORS.background},
  header: {backgroundColor: COLORS.primary, padding: 16},
  headerTitle: {fontSize: 20, fontWeight: '700', color: COLORS.white},
  headerSub: {fontSize: 12, color: 'rgba(255,255,255,0.8)', marginTop: 2},
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
  pendingBadge: {paddingHorizontal: 12, paddingVertical: 4, borderRadius: 20, backgroundColor: '#fef3c7'},
  pendingText: {fontSize: 12, fontWeight: '700', color: '#d97706'},
  cardBody: {},
  infoRow: {flexDirection: 'row', alignItems: 'flex-start', gap: 8, marginBottom: 6},
  infoText: {flex: 1, fontSize: 13, color: COLORS.dark},
  actionRow: {flexDirection: 'row', gap: 10, marginTop: 12},
  actionBtn: {
    flex: 1,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    gap: 4,
    paddingVertical: 10,
    borderRadius: 10,
  },
  rejectBtn: {backgroundColor: COLORS.dangerLight},
  rejectText: {color: COLORS.danger, fontWeight: '700', fontSize: 14},
  approveBtn: {backgroundColor: COLORS.success},
  approveText: {color: COLORS.white, fontWeight: '700', fontSize: 14},
  emptyContainer: {alignItems: 'center', padding: 40},
  emptyText: {color: COLORS.gray, marginTop: 12, fontSize: 14},
  modalOverlay: {flex: 1, backgroundColor: 'rgba(0,0,0,0.4)', justifyContent: 'center', padding: 24},
  modalCard: {backgroundColor: COLORS.white, borderRadius: 14, padding: 18},
  modalTitle: {fontSize: 17, fontWeight: '700', color: COLORS.dark},
  modalSub: {fontSize: 13, color: COLORS.gray, marginTop: 4},
  modalInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 10,
    padding: 12,
    marginTop: 14,
    minHeight: 80,
    textAlignVertical: 'top',
    color: COLORS.dark,
  },
  modalActions: {flexDirection: 'row', gap: 10, marginTop: 16},
  modalBtn: {flex: 1, alignItems: 'center', paddingVertical: 12, borderRadius: 10},
  modalCancel: {backgroundColor: COLORS.grayLight},
  modalCancelText: {color: COLORS.dark, fontWeight: '600'},
  modalConfirm: {backgroundColor: COLORS.danger},
  modalConfirmText: {color: COLORS.white, fontWeight: '700'},
});