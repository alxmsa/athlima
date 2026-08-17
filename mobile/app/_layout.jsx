import { useEffect } from 'react';
import { Stack, router } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { AuthProvider, useAuth } from '../context/AuthContext';

function RootLayoutNav() {
  const { token, loading } = useAuth();

  useEffect(() => {
    if (loading) return;

    if (token) {
      // Connecté → aller vers les tabs
      router.replace('/(tabs)');
    } else {
      // Non connecté → aller vers login
      router.replace('/(auth)/login');
    }
  }, [token, loading]);

  return (
    <>
      <StatusBar style="light" />
      <Stack screenOptions={{ headerShown: false }}>
        <Stack.Screen name="(auth)" />
        <Stack.Screen name="(tabs)" />
      </Stack>
    </>
  );
}

export default function RootLayout() {
  return (
    <AuthProvider>
      <RootLayoutNav />
    </AuthProvider>
  );
}