import React, {useEffect, useRef} from 'react';
import {View, Text, Image, StyleSheet, StatusBar, Animated} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
import {COLORS} from '../utils/colors';

const SplashScreen = ({onFinish}) => {
  const fadeAnim = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    const timer = setTimeout(() => {
      Animated.timing(fadeAnim, {
        toValue: 0,
        duration: 400,
        useNativeDriver: true,
      }).start(() => {
        onFinish();
      });
    }, 3000);
    return () => clearTimeout(timer);
  }, [onFinish, fadeAnim]);

  return (
    <Animated.View style={[styles.container, {opacity: fadeAnim}]}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.primaryDark} />
      <SafeAreaView style={styles.inner}>
        <View style={styles.logoContainer}>
          <Image source={require('../assets/logo.png')} style={styles.logo} />
        </View>
        <Text style={styles.title}>Absensi KPPN</Text>
        <Text style={styles.subtitle}>Sistem Monitoring Kehadiran</Text>
      </SafeAreaView>
    </Animated.View>
  );
};

const styles = StyleSheet.create({
  container: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: COLORS.primary,
    zIndex: 999,
  },
  inner: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoContainer: {
    width: 140,
    height: 140,
    borderRadius: 30,
    backgroundColor: 'rgba(255,255,255,0.2)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 24,
    overflow: 'hidden',
  },
  logo: {
    width: 110,
    height: 110,
    borderRadius: 24,
  },
  title: {
    fontSize: 28,
    fontWeight: '800',
    color: COLORS.white,
    letterSpacing: 0.5,
  },
  subtitle: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.75)',
    marginTop: 6,
  },
});

export default SplashScreen;