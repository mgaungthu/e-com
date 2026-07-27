import {
  useEffect,
  useRef,
} from 'react';

import AppRoutes from './routes/AppRoutes';
import { useAuthStore } from './store/authStore';

export default function RootApp() {
  const hasInitialized = useRef(false);

  const initializeAuth = useAuthStore(
    (state) => state.initializeAuth,
  );

  useEffect(() => {
    if (hasInitialized.current) {
      return;
    }

    hasInitialized.current = true;

    void initializeAuth();
  }, [initializeAuth]);

  return <AppRoutes />;
}