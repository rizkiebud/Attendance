import React, {useState} from 'react';
import {SafeAreaProvider} from 'react-native-safe-area-context';
import {AuthProvider} from './src/context/AuthContext';
import {NotificationProvider} from './src/context/NotificationContext';
import AppNavigator from './src/navigation/AppNavigator';
import SplashScreen from './src/screens/SplashScreen';
import ErrorBoundary from './src/components/ErrorBoundary';

const App = () => {
  const [showSplash, setShowSplash] = useState(true);

  return (
    <ErrorBoundary onRetry={() => setShowSplash(true)}>
      <SafeAreaProvider>
        <AuthProvider>
          <NotificationProvider>
            <AppNavigator />
            {showSplash && <SplashScreen onFinish={() => setShowSplash(false)} />}
          </NotificationProvider>
        </AuthProvider>
      </SafeAreaProvider>
    </ErrorBoundary>
  );
};

export default App;
