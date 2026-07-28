import React from 'react';
import {NavigationContainer} from '@react-navigation/native';
import {createNativeStackNavigator} from '@react-navigation/native-stack';
import {createBottomTabNavigator} from '@react-navigation/bottom-tabs';
import {ActivityIndicator, View} from 'react-native';
import Icon from 'react-native-vector-icons/Ionicons';

import {useAuth} from '../context/AuthContext';
import {COLORS} from '../utils/colors';

// Screens
import LoginScreen from '../screens/LoginScreen';
import DashboardScreen from '../screens/DashboardScreen';
import AbsensiScreen from '../screens/AbsensiScreen';
import HistoriScreen from '../screens/HistoriScreen';
import IzinScreen from '../screens/IzinScreen';
import ProfilScreen from '../screens/ProfilScreen';
import AjukanIzinScreen from '../screens/AjukanIzinScreen';
import DetailAbsensiScreen from '../screens/DetailAbsensiScreen';

const Stack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

const MainTabs = () => {
  return (
    <Tab.Navigator
      screenOptions={({route}) => ({
        headerShown: false,
        tabBarActiveTintColor: COLORS.primary,
        tabBarInactiveTintColor: COLORS.gray,
        tabBarStyle: {
          backgroundColor: COLORS.white,
          borderTopColor: COLORS.border,
          height: 60,
          paddingBottom: 8,
          paddingTop: 4,
        },
        tabBarLabelStyle: {
          fontSize: 11,
          fontWeight: '600',
        },
        tabBarIcon: ({focused, color, size}) => {
          const icons = {
            Beranda: focused ? 'home' : 'home-outline',
            Absensi: focused ? 'qr-code' : 'qr-code-outline',
            Histori: focused ? 'calendar' : 'calendar-outline',
            Izin: focused ? 'document-text' : 'document-text-outline',
            Profil: focused ? 'person' : 'person-outline',
          };
          return <Icon name={icons[route.name] || 'help'} size={22} color={color} />;
        },
      })}>
      <Tab.Screen name="Beranda" component={DashboardScreen} />
      <Tab.Screen name="Absensi" component={AbsensiScreen} />
      <Tab.Screen name="Histori" component={HistoriScreen} />
      <Tab.Screen name="Izin" component={IzinScreen} />
      <Tab.Screen name="Profil" component={ProfilScreen} />
    </Tab.Navigator>
  );
};

const AppNavigator = () => {
  const {isAuthenticated, isLoading} = useAuth();

  if (isLoading) {
    return (
      <View style={{flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: COLORS.white}}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{headerShown: false}}>
        {!isAuthenticated ? (
          <Stack.Screen name="Login" component={LoginScreen} />
        ) : (
          <>
            <Stack.Screen name="Main" component={MainTabs} />
            <Stack.Screen
              name="AjukanIzin"
              component={AjukanIzinScreen}
              options={{
                headerShown: true,
                title: 'Ajukan Permohonan',
                headerTintColor: COLORS.primary,
                headerBackTitle: 'Kembali',
              }}
            />
            <Stack.Screen
              name="DetailAbsensi"
              component={DetailAbsensiScreen}
              options={{
                headerShown: true,
                title: 'Detail Absensi',
                headerTintColor: COLORS.primary,
                headerBackTitle: 'Kembali',
              }}
            />
          </>
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
};

export default AppNavigator;
