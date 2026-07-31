import React, {useState} from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Alert,
  ActivityIndicator,
  Platform,
} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {leaveService} from '../services/api';
import {COLORS} from '../utils/colors';

const JENIS_OPTIONS = [
  {value: 'izin', label: 'Izin', icon: 'time-outline'},
  {value: 'sakit', label: 'Sakit', icon: 'medical-outline'},
  {value: 'cuti', label: 'Cuti', icon: 'calendar-outline'},
];

const AjukanIzinScreen = ({navigation}) => {
  const [jenis, setJenis] = useState('izin');
  const [tanggalMulai, setTanggalMulai] = useState('');
  const [tanggalSelesai, setTanggalSelesai] = useState('');
  const [alasan, setAlasan] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const validateDate = dateStr => {
    return /^\d{4}-\d{2}-\d{2}$/.test(dateStr);
  };

  const handleSubmit = async () => {
    if (!tanggalMulai || !tanggalSelesai || !alasan.trim()) {
      Alert.alert('Perhatian', 'Semua field wajib diisi');
      return;
    }

    if (!validateDate(tanggalMulai) || !validateDate(tanggalSelesai)) {
      Alert.alert('Format Salah', 'Format tanggal: YYYY-MM-DD (Contoh: 2024-01-15)');
      return;
    }

    if (tanggalSelesai < tanggalMulai) {
      Alert.alert('Perhatian', 'Tanggal selesai tidak boleh sebelum tanggal mulai');
      return;
    }

    setIsLoading(true);
    try {
      const formData = new FormData();
      formData.append('jenis', jenis);
      formData.append('tanggal_mulai', tanggalMulai);
      formData.append('tanggal_selesai', tanggalSelesai);
      formData.append('alasan', alasan.trim());

      await leaveService.create(formData);
      Alert.alert('Berhasil!', 'Permohonan izin berhasil diajukan', [
        {text: 'OK', onPress: () => navigation.goBack()},
      ]);
    } catch (err) {
      const errors = err.response?.data?.errors;
      const message = errors
        ? Object.values(errors).flat().join('\n')
        : err.response?.data?.message || 'Terjadi kesalahan';
      Alert.alert('Gagal', message);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
    <ScrollView keyboardShouldPersistTaps="handled">
      <View style={styles.content}>

        {/* Jenis */}
        <Text style={styles.label}>Jenis Permohonan</Text>
        <View style={styles.jenisContainer}>
          {JENIS_OPTIONS.map(opt => (
            <TouchableOpacity
              key={opt.value}
              style={[
                styles.jenisBtn,
                jenis === opt.value && styles.jenisBtnActive,
              ]}
              onPress={() => setJenis(opt.value)}>
              <Icon
                name={opt.icon}
                size={20}
                color={jenis === opt.value ? COLORS.primary : COLORS.gray}
              />
              <Text style={[
                styles.jenisBtnText,
                jenis === opt.value && {color: COLORS.primary},
              ]}>
                {opt.label}
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        {/* Tanggal Mulai */}
        <Text style={styles.label}>Tanggal Mulai</Text>
        <View style={styles.inputContainer}>
          <Icon name="calendar-outline" size={18} color={COLORS.gray} style={styles.inputIcon} />
          <TextInput
            style={styles.input}
            placeholder="YYYY-MM-DD (contoh: 2024-01-15)"
            placeholderTextColor={COLORS.gray}
            value={tanggalMulai}
            onChangeText={setTanggalMulai}
            keyboardType="numeric"
          />
        </View>

        {/* Tanggal Selesai */}
        <Text style={styles.label}>Tanggal Selesai</Text>
        <View style={styles.inputContainer}>
          <Icon name="calendar-outline" size={18} color={COLORS.gray} style={styles.inputIcon} />
          <TextInput
            style={styles.input}
            placeholder="YYYY-MM-DD (contoh: 2024-01-15)"
            placeholderTextColor={COLORS.gray}
            value={tanggalSelesai}
            onChangeText={setTanggalSelesai}
            keyboardType="numeric"
          />
        </View>

        {/* Alasan */}
        <Text style={styles.label}>Alasan / Keterangan</Text>
        <TextInput
          style={styles.textArea}
          placeholder="Jelaskan alasan permohonan Anda..."
          placeholderTextColor={COLORS.gray}
          value={alasan}
          onChangeText={setAlasan}
          multiline
          numberOfLines={4}
          textAlignVertical="top"
        />

        {/* Info */}
        <View style={styles.infoBox}>
          <Icon name="information-circle-outline" size={16} color={COLORS.info} />
          <Text style={styles.infoText}>
            Permohonan akan diproses oleh admin dalam 1-2 hari kerja.
          </Text>
        </View>

        {/* Submit */}
        <TouchableOpacity
          style={[styles.submitBtn, isLoading && styles.submitBtnDisabled]}
          onPress={handleSubmit}
          disabled={isLoading}>
          {isLoading ? (
            <ActivityIndicator color={COLORS.white} />
          ) : (
            <>
              <Icon name="send-outline" size={18} color={COLORS.white} />
              <Text style={styles.submitBtnText}>Ajukan Permohonan</Text>
            </>
          )}
        </TouchableOpacity>

        <TouchableOpacity style={styles.cancelBtn} onPress={() => navigation.goBack()}>
          <Text style={styles.cancelBtnText}>Batal</Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: COLORS.background},
  content: {padding: 16, paddingBottom: 32},
  label: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.dark,
    marginBottom: 8,
    marginTop: 16,
  },
  jenisContainer: {flexDirection: 'row', gap: 10},
  jenisBtn: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    padding: 12,
    borderRadius: 12,
    borderWidth: 2,
    borderColor: COLORS.border,
    backgroundColor: COLORS.white,
  },
  jenisBtnActive: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.primaryLight,
  },
  jenisBtnText: {fontSize: 13, fontWeight: '600', color: COLORS.gray},
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.white,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: COLORS.border,
    paddingHorizontal: 12,
  },
  inputIcon: {marginRight: 8},
  input: {flex: 1, height: 48, fontSize: 15, color: COLORS.dark},
  textArea: {
    backgroundColor: COLORS.white,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: COLORS.border,
    padding: 14,
    fontSize: 15,
    color: COLORS.dark,
    minHeight: 100,
  },
  infoBox: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 8,
    backgroundColor: '#eff6ff',
    padding: 12,
    borderRadius: 10,
    marginTop: 16,
  },
  infoText: {fontSize: 13, color: COLORS.info, flex: 1},
  submitBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: COLORS.primary,
    padding: 16,
    borderRadius: 14,
    marginTop: 20,
    shadowColor: COLORS.primary,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
  },
  submitBtnDisabled: {opacity: 0.7},
  submitBtnText: {color: COLORS.white, fontSize: 16, fontWeight: '700'},
  cancelBtn: {alignItems: 'center', padding: 16, marginTop: 8},
  cancelBtnText: {color: COLORS.gray, fontSize: 15},
});

export default AjukanIzinScreen;
