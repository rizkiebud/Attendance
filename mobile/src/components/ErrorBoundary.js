import React from 'react';
import {View, Text, TouchableOpacity, StyleSheet} from 'react-native';

/**
 * Batas kesalahan global: cegah aplikasi keluar tiba-tiba saat ada error
 * render tak tertangkap. Tampilkan layar error + tombol pulih.
 */
export default class ErrorBoundary extends React.Component {
  state = {hasError: false, message: ''};

  static getDerivedStateFromError(error) {
    return {hasError: true, message: error?.message || String(error)};
  }

  componentDidCatch(error, info) {
    console.error('ErrorBoundary:', error, info?.componentStack);
  }

  reset = () => {
    this.setState({hasError: false, message: ''});
    this.props.onRetry?.();
  };

  render() {
    if (this.state.hasError) {
      return (
        <View style={styles.container}>
          <Text style={styles.title}>Terjadi Kesalahan</Text>
          <Text style={styles.message} numberOfLines={6}>
            {this.state.message || 'Kesalahan tak dikenal'}
          </Text>
          <TouchableOpacity style={styles.btn} onPress={this.reset}>
            <Text style={styles.btnText}>Kembali</Text>
          </TouchableOpacity>
        </View>
      );
    }
    return this.props.children;
  }
}

const styles = StyleSheet.create({
  container: {flex: 1, alignItems: 'center', justifyContent: 'center', padding: 24, backgroundColor: '#f8fafc'},
  title: {fontSize: 18, fontWeight: '700', color: '#1e293b'},
  message: {fontSize: 14, color: '#64748b', textAlign: 'center', marginTop: 12},
  btn: {marginTop: 20, backgroundColor: '#1a56db', paddingHorizontal: 24, paddingVertical: 12, borderRadius: 10},
  btnText: {color: '#fff', fontWeight: '600'},
});