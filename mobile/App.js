import React, {useState} from 'react';
import {SafeAreaProvider} from 'react-native-safe-area-context';
import {AuthProvider} from './src/context/AuthContext';
import AppNavigator from './src/navigation/AppNavigator';
import SplashScreen from './src/screens/SplashScreen';

const App = () => {
  const [showSplash, setShowSplash] = useState(true);

  return (
    <SafeAreaProvider>
      <AuthProvider>
        <AppNavigator />
        {showSplash && <SplashScreen onFinish={() => setShowSplash(false)} />}
      </AuthProvider>
    </SafeAreaProvider>
  );
};

export default App;
