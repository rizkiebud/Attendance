import React, {useState, useCallback} from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Alert,
  TextInput,
  ActivityIndicator,
  StatusBar,
  Image,
} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/Ionicons';
import {useFocusEffect} from '@react-navigation/native';
import {useAuth} from '../context/AuthContext';
import {authService, BASE_URL} from '../services/api';
import {COLORS} from '../utils/colors';

const ProfilScreen = () => {
  const {user, employee, logout, refreshUser} = useAuth();
  const [showChangePw, setShowChangePw] = useState(false);
  const [pwLama, setPwLama] = useState('');
  const [pwBaru, setPwBaru] = useState('');
  const [pwBaruConf, setPwBaruConf] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  useFocusEffect(
    useCallback(() => {
      refreshUser();
    }, []),
  );

  const handleLogout = () => {
    Alert.alert('Konfirmasi Logout', 'Apakah Anda yakin ingin keluar?', [
      {text: 'Batal', style: 'cancel'},
      {
        text: 'Keluar',
        style: 'destructive',
        onPress: async () => {
          await logout();
        },
      },
    ]);
  };

  const handleChangePassword = async () => {
    if (!pwLama || !pwBaru || !pwBaruConf) {
      Alert.alert('Perhatian', 'Semua field harus diisi');
      return;
    }
    if (pwBaru !== pwBaruConf) {
      Alert.alert('Perhatian', 'Konfirmasi password tidak sesuai');
      return;
    }
    if (pwBaru.length < 6) {
      Alert.alert('Perhatian', 'Password minimal 6 karakter');
      return;
    }

    setIsLoading(true);
    try {
      await authService.changePassword(pwLama, pwBaru, pwBaruConf);
      Alert.alert('Berhasil!', 'Password berhasil diubah');
      setShowChangePw(false);
      setPwLama('');
      setPwBaru('');
      setPwBaruConf('');
    } catch (err) {
      const msg = err.response?.data?.message || 'Gagal mengubah password';
      Alert.alert('Gagal', msg);
    } finally {
      setIsLoading(false);
    }
  };

  const InfoItem = ({icon, label, value}) => (
    <View style={styles.infoItem}>
      <View style={styles.infoIcon}>
        <Icon name={icon} size={18} color={COLORS.primary} />
      </View>
      <View style={styles.infoContent}>
        <Text style={styles.infoLabel}>{label}</Text>
        <Text style={styles.infoValue}>{value || '-'}</Text>
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.primary} />

      {/* Header */}
      <View style={styles.header}>
        {employee?.foto ? (
          <Image
            source={{uri: `${BASE_URL}/storage/${employee.foto}`}}
            style={styles.avatarImg}
          />
        ) : (
          <View style={styles.avatar}>
            <Icon name="person" size={36} color={COLORS.white} />
          </View>
        )}
        <Text style={styles.name}>{employee?.nama || user?.name}</Text>
        <Text style={styles.email}>{user?.email}</Text>
        <View style={styles.roleBadge}>
          <Text style={styles.roleText}>
            {employee?.jabatan || 'Karyawan'}
          </Text>
        </View>
      </View>

      <ScrollView style={styles.scroll} contentContainerStyle={{paddingBottom: 32}}>

        {/* Info Karyawan */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Informasi Karyawan</Text>
          <InfoItem icon="id-card-outline" label="No. ID" value={employee?.nip} />
          <InfoItem icon="briefcase-outline" label="Posisi" value={employee?.jabatan} />
          <InfoItem icon="business-outline" label="Departemen" value={employee?.departemen} />
          <InfoItem icon="call-outline" label="Telepon" value={employee?.telepon} />
          <InfoItem icon="location-outline" label="Alamat" value={employee?.alamat} />
        </View>

        {/* Akun */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Akun</Text>
          <InfoItem icon="mail-outline" label="Email" value={user?.email} />

          <TouchableOpacity
            style={styles.menuItem}
            onPress={() => setShowChangePw(!showChangePw)}>
            <Icon name="key-outline" size={18} color={COLORS.primary} />
            <Text style={styles.menuText}>Ubah Password</Text>
            <Icon
              name={showChangePw ? 'chevron-up' : 'chevron-down'}
              size={18}
              color={COLORS.gray}
            />
          </TouchableOpacity>

          {showChangePw && (
            <View style={styles.changePwContainer}>
              <TextInput
                style={styles.pwInput}
                placeholder="Password Lama"
                placeholderTextColor={COLORS.gray}
                secureTextEntry
                value={pwLama}
                onChangeText={setPwLama}
              />
              <TextInput
                style={styles.pwInput}
                placeholder="Password Baru (min. 6 karakter)"
                placeholderTextColor={COLORS.gray}
                secureTextEntry
                value={pwBaru}
                onChangeText={setPwBaru}
              />
              <TextInput
                style={styles.pwInput}
                placeholder="Konfirmasi Password Baru"
                placeholderTextColor={COLORS.gray}
                secureTextEntry
                value={pwBaruConf}
                onChangeText={setPwBaruConf}
              />
              <TouchableOpacity
                style={styles.savePwBtn}
                onPress={handleChangePassword}
                disabled={isLoading}>
                {isLoading ? (
                  <ActivityIndicator color={COLORS.white} size="small" />
                ) : (
                  <Text style={styles.savePwBtnText}>Simpan Password</Text>
                )}
              </TouchableOpacity>
            </View>
          )}
        </View>

        {/* Logout */}
        <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout}>
          <Icon name="log-out-outline" size={20} color={COLORS.danger} />
          <Text style={styles.logoutText}>Keluar</Text>
        </TouchableOpacity>

        <Text style={styles.versionText}>Absensi KPPN v1.0.0</Text>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {flex: 1, backgroundColor: COLORS.background},
  header: {
    backgroundColor: COLORS.primary,
    padding: 24,
    alignItems: 'center',
  },
  avatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: 'rgba(255,255,255,0.2)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
    borderWidth: 3,
    borderColor: 'rgba(255,255,255,0.4)',
  },
  avatarImg: {
    width: 80,
    height: 80,
    borderRadius: 40,
    marginBottom: 12,
    borderWidth: 3,
    borderColor: 'rgba(255,255,255,0.4)',
  },
  name: {fontSize: 20, fontWeight: '800', color: COLORS.white},
  email: {fontSize: 13, color: 'rgba(255,255,255,0.8)', marginTop: 4},
  roleBadge: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 16,
    paddingVertical: 6,
    borderRadius: 20,
    marginTop: 10,
  },
  roleText: {color: COLORS.white, fontWeight: '600', fontSize: 13},
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
  cardTitle: {fontSize: 14, fontWeight: '700', color: COLORS.gray, marginBottom: 12, textTransform: 'uppercase', letterSpacing: 0.5},
  infoItem: {flexDirection: 'row', alignItems: 'flex-start', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: COLORS.grayLight},
  infoIcon: {width: 36, height: 36, borderRadius: 10, backgroundColor: COLORS.primaryLight, alignItems: 'center', justifyContent: 'center', marginRight: 12},
  infoContent: {flex: 1},
  infoLabel: {fontSize: 12, color: COLORS.gray},
  infoValue: {fontSize: 14, fontWeight: '600', color: COLORS.dark, marginTop: 2},
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    borderTopWidth: 1,
    borderTopColor: COLORS.grayLight,
  },
  menuText: {flex: 1, fontSize: 14, fontWeight: '600', color: COLORS.dark, marginLeft: 12},
  changePwContainer: {
    backgroundColor: COLORS.grayLight,
    borderRadius: 10,
    padding: 12,
    gap: 10,
  },
  pwInput: {
    backgroundColor: COLORS.white,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
    paddingHorizontal: 14,
    paddingVertical: 10,
    fontSize: 14,
    color: COLORS.dark,
  },
  savePwBtn: {
    backgroundColor: COLORS.primary,
    borderRadius: 10,
    padding: 12,
    alignItems: 'center',
  },
  savePwBtnText: {color: COLORS.white, fontWeight: '700'},
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: COLORS.white,
    borderRadius: 14,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1.5,
    borderColor: COLORS.danger,
  },
  logoutText: {fontSize: 16, fontWeight: '700', color: COLORS.danger},
  versionText: {textAlign: 'center', color: COLORS.gray, fontSize: 12},
});

export default ProfilScreen;
