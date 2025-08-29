import React, { createContext, useContext, useState, useEffect } from 'react';
import { toast } from '@/components/ui/use-toast';
import { api } from '@/lib/api';
import { jwtDecode } from 'jwt-decode';

const AuthContext = createContext();

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadUser();
  }, []);

  const loadUser = () => {
    const token = api.getToken();
    if (token) {
      try {
        const decoded = jwtDecode(token);
        setUser(decoded.user || decoded);
      } catch (error) {
        console.error('Invalid token:', error);
        api.removeToken();
      }
    }
    setLoading(false);
  };

  const login = async (email, password) => {
    try {
      await api.login(email, password);
      loadUser();
      toast({
        title: "Login successful",
        description: "Welcome back!",
      });
      return { success: true };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  const register = async (userData) => {
    try {
      await api.register(userData);
      loadUser();
      toast({
        title: "Registration successful",
        description: "Welcome to Sreemeditec!",
      });
      return { success: true };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  const logout = () => {
    api.logout();
    setUser(null);
    toast({
      title: "Logged out",
      description: "You have been successfully logged out.",
    });
  };

  const value = {
    user,
    login,
    register,
    logout,
    loading,
    isAdmin: user ? user.isAdmin : false,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
