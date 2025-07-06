
import React, { createContext, useContext, useState, useEffect } from 'react';
import { supabase } from '@/lib/customSupabaseClient';
import { toast } from '@/components/ui/use-toast';

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
    const getSessionAndProfile = async () => {
      const { data: { session } } = await supabase.auth.getSession();
      if (session?.user) {
        const { data: profile } = await supabase
          .from('profiles')
          .select('*')
          .eq('id', session.user.id)
          .single();
        setUser({ ...session.user, ...profile });
      }
      setLoading(false);
    };

    getSessionAndProfile();

    const { data: authListener } = supabase.auth.onAuthStateChange(
      async (event, session) => {
        if (session?.user) {
          const { data: profile } = await supabase
            .from('profiles')
            .select('*')
            .eq('id', session.user.id)
            .single();
          setUser({ ...session.user, ...profile });
        } else {
          setUser(null);
        }
        setLoading(false);
      }
    );

    return () => {
      authListener.subscription.unsubscribe();
    };
  }, []);

  const login = async (email, password) => {
    const { error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) {
      if (error.message === 'Email not confirmed') {
        const customError = new Error('Email not confirmed');
        customError.code = 'EMAIL_NOT_CONFIRMED';
        throw customError;
      }
      toast({ title: "Login failed", description: error.message, variant: "destructive" });
      throw error;
    }
    toast({ title: "Welcome back!", description: "You have successfully logged in." });
  };

  const register = async (userData) => {
    const { email, password, name, phone, address } = userData;
    const { error } = await supabase.auth.signUp({ 
      email, 
      password,
      options: {
        data: {
          name: name,
          phone: phone,
          address: address,
        }
      }
    });

    if (error) {
      toast({ title: "Registration failed", description: error.message, variant: "destructive" });
      throw error;
    }
    
    toast({ title: "Account created!", description: "Welcome to Sreemeditec. Please check your email to verify your account." });
  };

  const logout = async () => {
    const { error } = await supabase.auth.signOut();
    if (error) {
      toast({ title: "Logout failed", description: error.message, variant: "destructive" });
      throw error;
    }
    setUser(null);
    toast({ title: "Logged out", description: "You have been successfully logged out." });
  };

  const updateProfile = async (updates) => {
    if (!user) throw new Error("No user logged in");
    const { name, phone, address } = updates;
    const { error } = await supabase
      .from('profiles')
      .update({ name, phone, address, updated_at: new Date().toISOString() })
      .eq('id', user.id);

    if (error) {
      toast({ title: "Update failed", description: error.message, variant: "destructive" });
      throw error;
    }

    const { data: profile } = await supabase.from('profiles').select('*').eq('id', user.id).single();
    setUser(prevUser => ({ ...prevUser, ...profile }));
    toast({ title: "Profile updated", description: "Your profile has been successfully updated." });
  };

  const changePassword = async (currentPassword, newPassword) => {
    const { error } = await supabase.auth.updateUser({ password: newPassword });
    if (error) {
      toast({ title: "Password change failed", description: error.message, variant: "destructive" });
      throw error;
    }
    toast({ title: "Password changed", description: "Your password has been successfully updated." });
  };

  const forgotPassword = async (email) => {
    const { error } = await supabase.auth.resetPasswordForEmail(email, {
      redirectTo: `${window.location.origin}/update-password`,
    });
    if (error) {
      toast({ title: "Reset failed", description: error.message, variant: "destructive" });
      throw error;
    }
    toast({ title: "Reset email sent", description: "Check your email for password reset instructions." });
  };
  
  const resendConfirmationEmail = async (email) => {
    const { error } = await supabase.auth.resend({
      type: 'signup',
      email: email,
    });
    if (error) {
      toast({ title: "Failed to resend email", description: error.message, variant: "destructive" });
      throw error;
    }
    toast({ title: "Confirmation email sent", description: "Please check your inbox." });
  };

  const value = {
    user,
    loading,
    login,
    register,
    logout,
    updateProfile,
    changePassword,
    forgotPassword,
    resendConfirmationEmail,
  };

  return (
    <AuthContext.Provider value={value}>
      {!loading && children}
    </AuthContext.Provider>
  );
};
