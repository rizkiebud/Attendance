import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
} from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';
import {COLORS, STATUS_COLORS} from '../utils/colors';
import {formatTanggal, formatTime} from '../utils/helpers';

const DetailAbsensiScreen = ({route}) => {
  const {attendance} = route.params;
  const status = STATUS_COLORS[attendance.status] || STATUS_COLORS.tidak_hadir;

  const InfoRow = ({icon, label, value, valueColor}) => (
    <View style={styles.infoRow}>
      <Icon name={icon} size={18} color={COLORS.gray} style={styles.infoIcon} />
      <View style={styles.infoContent}>
        <Text style={styles.infoLabel}>{label}</Text>
        <Text style={[styles.infoValue, valueColor && {color: valueColor}]}>{value}</Text>
      </View>
    </View>
  );

  return (
    <ScrollView style={styles.container}>

      {/* Status Header */}
      <View style={[styles.statusHeader, {backgroundColor: status.bg}]}>
        <View style={[styles.statusIcon, {backgroundColor: status.text + '20'}]}>
          <Icon
            name={attendance.status === 'hadir' ? 'checkmark-circle' :
              attendance.status === 'terlambat' ? 'warning' : 'close-circle'}
            size={36}
            color={status.text}
          />
        </View>
        <Text style={[styles.statusLabel, {color: status.text}]}>{status.label}</Text>
        <Text style={[styles.statusDate, {color: status.text + 'CC'}]}>
          {formatTanggal(attendance.tanggal)}
        </Text>
      </View>

      <View style={styles.content}>
        {/* Absen Masuk */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Icon name="log-in" size={20} color={COLORS.success} />
            <Text style={styles.sectionTitle}>Absen Masuk</Text>
          </View>

          {attendance.jam_masuk ? (
            <>
              <InfoRow icon="time-outline" label="Jam Masuk" value={formatTime(attendance.jam_masuk)} />
              <InfoRow icon="location-outline" label="Jarak dari Kantor"
                value={attendance.jarak_masuk ? Math.round(attendance.jarak_masuk) + ' meter' : '-'} />
              {attendance.lat_masuk && (
                <InfoRow icon="navigate-outline" label="Koordinat Masuk"
                  value={`${attendance.lat_masuk}, ${attendance.lng_masuk}`} />
              )}
            </>
          ) : (
            <Text style={styles.noData}>Belum absen masuk</Text>
          )}
        </View>

        {/* Absen Keluar */}
        <View style={styles.section}>
          <View style={styles.sectionHeader}>
            <Icon name="log-out" size={20} color={COLORS.danger} />
            <Text style={styles.sectionTitle}>Absen Keluar</Text>
          </View>

          {attendance.jam_keluar ? (
            <>
              <InfoRow icon="time-outline" label="Jam Keluar" value={formatTime(attendance.jam_keluar)} />
              <InfoRow icon="location-outline" label="Jarak dari Kantor"
                value={attendance.jarak_keluar ? Math.round(attendance.jarak_keluar) + ' meter' : '-'} />
              <InfoRow icon="hourglass-outline" label="Durasi Kerja"
                value={attendance.durasi_kerja ? attendance.durasi_kerja + ' jam' : '-'} />
            </>
          ) : (
            <Text style={styles.noData}>Belum absen keluar</Text>
          )}
        </View>

        {/* Info Kantor */}
        {attendance.office && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <Icon name="business" size={20} color={COLORS.primary} />
              <Text style={styles.sectionTitle}>Informasi Kantor</Text>
            </View>
            <InfoRow icon="business-outline" label="Kantor" value={attendance.office.nama} />
            <InfoRow icon="radio-button-on-outline" label="Radius" value={attendance.office.radius + ' meter'} />
          </View>
        )}

        {/* Keterangan */}
        {attendance.keterangan && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <Icon name="document-text" size={20} color={COLORS.gray} />
              <Text style={styles.sectionTitle}>Keterangan</Text>
            </View>
            <Text style={styles.keterangan}>{attendance.keterangan}</Text>
          </View>
        )}
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: COLORS.background},
  statusHeader: {
    padding: 24,
    alignItems: 'center',
  },
  statusIcon: {
    width: 72,
    height: 72,
    borderRadius: 36,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  statusLabel: {fontSize: 20, fontWeight: '800'},
  statusDate: {fontSize: 14, marginTop: 4},
  content: {padding: 16},
  section: {
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
  sectionHeader: {flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 14},
  sectionTitle: {fontSize: 15, fontWeight: '700', color: COLORS.dark},
  infoRow: {flexDirection: 'row', alignItems: 'flex-start', marginBottom: 12},
  infoIcon: {marginTop: 2, marginRight: 12, width: 20},
  infoContent: {},
  infoLabel: {fontSize: 12, color: COLORS.gray},
  infoValue: {fontSize: 14, fontWeight: '600', color: COLORS.dark, marginTop: 2},
  noData: {fontSize: 14, color: COLORS.gray, fontStyle: 'italic'},
  keterangan: {fontSize: 14, color: COLORS.dark, lineHeight: 20},
});

export default DetailAbsensiScreen;
