import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import { motion } from 'framer-motion';
import { useAuth } from '@/contexts/AuthContext';
import { useCart } from '@/contexts/CartContext';
import { toast } from '@/components/ui/use-toast';
import { api } from '@/lib/api';
import ContactInfo from '@/components/checkout/ContactInfo';
import ShippingInfo from '@/components/checkout/ShippingInfo';
import PaymentInfo from '@/components/checkout/PaymentInfo';
import OrderSummary from '@/components/checkout/OrderSummary';

const Checkout = () => {
  const { cartItems, getCartTotal, clearCart } = useCart();
  const { user } = useAuth();
  const navigate = useNavigate();
  const [isProcessing, setIsProcessing] = useState(false);
  const [formData, setFormData] = useState({
    email: '',
    firstName: '',
    lastName: '',
    address: '',
    city: '',
    state: '',
    zipCode: '',
    phone: '',
    paymentMethod: 'card'
  });

  useEffect(() => {
    if (user) {
      setFormData(prev => ({
        ...prev,
        email: user.email || '',
        firstName: user.name?.split(' ')[0] || '',
        lastName: user.name?.split(' ').slice(1).join(' ') || '',
        address: user.address || '',
        phone: user.phone || ''
      }));
    }
  }, [user]);

  useEffect(() => {
    if (cartItems.length === 0 && !isProcessing) {
      navigate('/store');
    }
  }, [cartItems, navigate, isProcessing]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSelectChange = (name, value) => {
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!user) {
      toast({
        title: "Please log in",
        description: "You need to be logged in to place an order.",
        variant: "destructive",
      });
      navigate('/login');
      return;
    }

    setIsProcessing(true);

    try {
      // Simulate payment gateway processing
      if (formData.paymentMethod === 'card') {
        // Simulate Razorpay/Stripe payment
        await new Promise(resolve => setTimeout(resolve, 3000));

        // Simulate payment success (90% success rate)
        if (Math.random() < 0.9) {
          const paymentId = 'pay_' + Math.random().toString(36).substr(2, 9);
          await processPaymentSuccess(paymentId);
        } else {
          throw new Error('Payment failed');
        }
      } else {
        // Cash on Delivery
        await processCashOnDelivery();
      }
    } catch (error) {
      toast({
        title: "Payment failed",
        description: "There was an error processing your payment. Please try again.",
        variant: "destructive",
      });
      setIsProcessing(false);
    }
  };

  const processPaymentSuccess = async (paymentId) => {
    try {
      const orderData = {
        shipping_address: `${formData.address}, ${formData.city}, ${formData.state} ${formData.zipCode}`,
        billing_address: `${formData.address}, ${formData.city}, ${formData.state} ${formData.zipCode}`,
        phone: formData.phone,
        notes: `Payment ID: ${paymentId}, Customer: ${formData.firstName} ${formData.lastName}`,
        payment_method: formData.paymentMethod,
        payment_id: paymentId
      };

      const response = await api.createOrder(orderData);

      if (response.success) {
        clearCart();

        toast({
          title: "Payment successful!",
          description: `Your order #${response.order_id} has been confirmed.`,
        });

        navigate(`/order-confirmation/${response.order_id}`);
      } else {
        throw new Error(response.error || 'Order creation failed');
      }
    } catch (error) {
      console.error('Order creation error:', error);
      throw error;
    }
  };

  const processCashOnDelivery = async () => {
    try {
      const orderData = {
        shipping_address: `${formData.address}, ${formData.city}, ${formData.state} ${formData.zipCode}`,
        billing_address: `${formData.address}, ${formData.city}, ${formData.state} ${formData.zipCode}`,
        phone: formData.phone,
        notes: `Cash on Delivery, Customer: ${formData.firstName} ${formData.lastName}`,
        payment_method: 'cod'
      };

      const response = await api.createOrder(orderData);

      if (response.success) {
        clearCart();

        toast({
          title: "Order placed successfully!",
          description: `Your order #${response.order_id} has been confirmed.`,
        });

        navigate(`/order-confirmation/${response.order_id}`);
      } else {
        throw new Error(response.error || 'Order creation failed');
      }
    } catch (error) {
      console.error('Order creation error:', error);
      throw error;
    }
  };

  return (
    <>
      <Helmet>
        <title>Checkout - Complete Your Order | Sreemeditec</title>
        <meta name="description" content="Complete your medical equipment purchase with secure checkout and fast delivery options." />
      </Helmet>

      <div className="min-h-screen bg-gray-50 py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="space-y-8"
          >
            <div className="text-center">
              <h1 className="text-3xl font-bold text-gray-900">Checkout</h1>
              <p className="text-gray-600">Complete your order securely</p>
            </div>

            <form onSubmit={handleSubmit}>
              <div className="grid lg:grid-cols-2 gap-8 items-start">
                <div className="space-y-6">
                  <ContactInfo formData={formData} handleInputChange={handleInputChange} />
                  <ShippingInfo formData={formData} handleInputChange={handleInputChange} handleSelectChange={handleSelectChange} />
                  <PaymentInfo formData={formData} handleSelectChange={handleSelectChange} />
                </div>

                <div>
                  <OrderSummary cartItems={cartItems} getCartTotal={getCartTotal} isProcessing={isProcessing} />
                </div>
              </div>
            </form>
          </motion.div>
        </div>
      </div>
    </>
  );
};

export default Checkout;