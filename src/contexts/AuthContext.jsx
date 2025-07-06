
import React, { createContext, useContext, useState, useEffect } from 'react';
import { toast } from '@/components/ui/use-toast';
import { api } from '@/lib/api';

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
    // Check if user is logged in by validating token
    const token = api.getToken();
    if (token) {
      loadUserProfile();
    } else {
      setLoading(false);
    }
  }, []);

  const loadUserProfile = async () => {
    try {
      const response = await api.getProfile();
      if (response.success) {
        setUser(response.user);
      } else {
        api.removeToken();
      }
    } catch (error) {
      console.error('Failed to load user profile:', error);
      api.removeToken();
    } finally {
      setLoading(false);
    }
  };

  const login = async (email, password) => {
    try {
      const response = await api.login(email, password);
      if (response.success) {
        setUser(response.user);
        toast({
          title: "Login successful",
          description: "Welcome back!",
        });
        return { success: true };
      }
      return { success: false, error: response.error || 'Invalid credentials' };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  const register = async (userData) => {
    try {
      const response = await api.register(userData);
      if (response.success) {
        setUser(response.user);
        toast({
          title: "Registration successful",
          description: "Welcome to Sreemeditec!",
        });
        return { success: true };
      }
      return { success: false, error: response.error || 'Registration failed' };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  const logout = async () => {
    try {
      await api.logout();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      setUser(null);
      api.removeToken();
      toast({
        title: "Logged out",
        description: "You have been successfully logged out.",
      });
    }
  };

  const updateProfile = async (userData) => {
    try {
      const response = await api.updateProfile(userData);
      if (response.success) {
        await loadUserProfile(); // Reload user data
        toast({
          title: "Profile updated",
          description: "Your profile has been updated successfully.",
        });
        return { success: true };
      }
      return { success: false, error: response.error || 'Update failed' };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  const changePassword = async (currentPassword, newPassword) => {
    try {
      const response = await api.request('/auth/change-password', {
        method: 'PUT',
        body: JSON.stringify({
          current_password: currentPassword,
          new_password: newPassword
        }),
      });

      if (response.success) {
        toast({
          title: "Password updated",
          description: "Your password has been changed successfully.",
        });
        return { success: true };
      }
      return { success: false, error: response.error || 'Password change failed' };
    } catch (error) {
      return { success: false, error: error.message };
    }
  };

  const value = {
    user,
    login,
    register,
    logout,
    updateProfile,
    changePassword,
    loading
  };

  return (
    <AuthContext.Provider value={value}>
      {!loading && children}
    </AuthContext.Provider>
  );
};
