
import React, { useState, useEffect } from 'react';
import { Helmet } from 'react-helmet-async';
import { motion } from 'framer-motion';
import { Package, Truck, CheckCircle, Clock, Eye } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useAuth } from '@/contexts/AuthContext';
import { useCart } from '@/contexts/CartContext';
import { toast } from '@/components/ui/use-toast';
import { api } from '@/lib/api';

const OrderHistory = () => {
  const { user } = useAuth();
  const { addToCart } = useCart();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (user) {
      loadOrders();
    }
  }, [user]);

  const loadOrders = async () => {
    try {
      setLoading(true);
      const response = await api.getOrders();
      if (response.success) {
        setOrders(response.orders || []);
      }
    } catch (error) {
      console.error('Failed to load orders:', error);
      toast({
        title: "Error",
        description: "Failed to load order history.",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const handleReorder = async (order) => {
    try {
      // Add all items from the order back to cart
      for (const item of order.items) {
        await addToCart({
          id: item.product_id,
          name: item.product_name,
          price: item.price
        }, item.quantity);
      }
      
      toast({
        title: "Items added to cart",
        description: "All items from this order have been added to your cart.",
      });
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to add items to cart.",
        variant: "destructive",
      });
    }
  };

  const handleDownloadInvoice = (orderId) => {
    const order = orders.find(o => o.id === orderId);
    if (order) {
      const invoiceContent = `
        SREEMEDITEC INVOICE
        ==================
        Order #: ${order.id}
        Date: ${new Date(order.created_at).toLocaleDateString()}
        Customer: ${order.shipping_name}
        
        Items:
        ${order.items.map(item => `${item.product_name} x${item.quantity} - ₹${(item.price * item.quantity).toFixed(2)}`).join('\n')}
        
        Subtotal: ₹${order.subtotal.toFixed(2)}
        Tax: ₹${order.tax.toFixed(2)}
        Total: ₹${order.total.toFixed(2)}
      `;
      
      const blob = new Blob([invoiceContent], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `invoice-${orderId}.txt`;
      a.click();
      URL.revokeObjectURL(url);
      
      toast({
        title: "Invoice downloaded",
        description: "Your invoice has been downloaded successfully.",
      });
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'pending':
        return <Clock className="w-5 h-5" />;
      case 'shipped':
        return <Truck className="w-5 h-5" />;
      case 'delivered':
        return <CheckCircle className="w-5 h-5" />;
      default:
        return <Package className="w-5 h-5" />;
    }
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'shipped':
        return 'bg-blue-100 text-blue-800';
      case 'delivered':
        return 'bg-green-100 text-green-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <p className="text-gray-600">Please log in to view your order history.</p>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="loading-spinner"></div>
      </div>
    );
  }

  return (
    <>
      <Helmet>
        <title>Order History - Track Your Medical Equipment Orders | Sreemeditec</title>
        <meta name="description" content="View and track your medical equipment orders, delivery status, and order history with Sreemeditec." />
      </Helmet>

      <div className="min-h-screen bg-gray-50 py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="space-y-8"
          >
            {/* Header */}
            <div className="text-center space-y-4">
              <h1 className="text-3xl font-bold text-gray-900">Order History</h1>
              <p className="text-gray-600">Track and manage your medical equipment orders</p>
            </div>

            {orders.length === 0 ? (
              <motion.div
                initial={{ opacity: 0, y: 50 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8 }}
                className="text-center space-y-6"
              >
                <div className="w-24 h-24 mx-auto bg-gray-200 rounded-full flex items-center justify-center">
                  <Package className="w-12 h-12 text-gray-400" />
                </div>
                <h2 className="text-2xl font-bold text-gray-900">No orders yet</h2>
                <p className="text-gray-600 max-w-md mx-auto">
                  You haven't placed any orders yet. Browse our medical equipment store to find the products you need.
                </p>
                <Link to="/store">
                  <Button className="btn-primary">
                    Start Shopping
                  </Button>
                </Link>
              </motion.div>
            ) : (
              <div className="space-y-6">
                {orders.map((order, index) => (
                  <motion.div
                    key={order.id}
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6, delay: index * 0.1 }}
                  >
                    <Card className="card-hover">
                      <CardHeader>
                        <div className="flex justify-between items-start">
                          <div>
                            <CardTitle className="text-lg">Order #{order.id}</CardTitle>
                            <p className="text-sm text-gray-600">
                              Placed on {new Date(order.created_at).toLocaleDateString('en-IN', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                              })}
                            </p>
                          </div>
                          <Badge className={getStatusColor(order.status)}>
                            <div className="flex items-center space-x-1">
                              {getStatusIcon(order.status)}
                              <span className="capitalize">{order.status}</span>
                            </div>
                          </Badge>
                        </div>
                      </CardHeader>
                      <CardContent className="space-y-4">
                        {/* Order Items */}
                        <div className="space-y-3">
                          {order.items && order.items.map((item, idx) => (
                            <div key={idx} className="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                              <img 
                                alt={item.product_name}
                                className="w-12 h-12 object-cover rounded-md"
                                src="https://images.unsplash.com/photo-1658204212985-e0126040f88f" 
                              />
                              <div className="flex-1">
                                <h4 className="font-medium text-gray-900">{item.product_name}</h4>
                                <p className="text-sm text-gray-600">Quantity: {item.quantity}</p>
                              </div>
                              <p className="font-medium">₹{(item.price * item.quantity).toFixed(2)}</p>
                            </div>
                          ))}
                        </div>

                        {/* Order Summary */}
                        <div className="border-t pt-4">
                          <div className="flex justify-between items-center mb-2">
                            <span className="text-gray-600">Subtotal:</span>
                            <span>₹{parseFloat(order.subtotal || 0).toFixed(2)}</span>
                          </div>
                          <div className="flex justify-between items-center mb-2">
                            <span className="text-gray-600">Tax (18% GST):</span>
                            <span>₹{parseFloat(order.tax || 0).toFixed(2)}</span>
                          </div>
                          <div className="flex justify-between items-center mb-2">
                            <span className="text-gray-600">Shipping:</span>
                            <span className="text-green-600">Free</span>
                          </div>
                          <div className="flex justify-between items-center font-bold text-lg border-t pt-2">
                            <span>Total:</span>
                            <span>₹{parseFloat(order.total || 0).toFixed(2)}</span>
                          </div>
                        </div>

                        {/* Shipping Address */}
                        <div className="bg-gray-50 p-3 rounded-lg">
                          <h4 className="font-medium text-gray-900 mb-1">Shipping Address</h4>
                          <p className="text-sm text-gray-600">{order.shipping_name}</p>
                          <p className="text-sm text-gray-600">{order.shipping_address}</p>
                          <p className="text-sm text-gray-600">{order.shipping_phone}</p>
                        </div>

                        {/* Actions */}
                        <div className="flex flex-col sm:flex-row gap-3">
                          <Link to={`/track-order/${order.id}`}>
                            <Button
                              variant="outline"
                              className="flex items-center w-full sm:w-auto"
                            >
                              <Eye className="w-4 h-4 mr-2" />
                              View Details
                            </Button>
                          </Link>
                          {order.status !== 'delivered' && (
                            <Link to={`/track-order/${order.id}`}>
                              <Button
                                variant="outline"
                                className="flex items-center w-full sm:w-auto"
                              >
                                <Truck className="w-4 h-4 mr-2" />
                                Live Tracking
                              </Button>
                            </Link>
                          )}
                          {order.status === 'delivered' && (
                            <Button
                              variant="outline"
                              className="flex items-center w-full sm:w-auto"
                              onClick={() => handleReorder(order)}
                            >
                              <Package className="w-4 h-4 mr-2" />
                              Reorder
                            </Button>
                          )}
                          <Button
                            variant="outline"
                            className="flex items-center w-full sm:w-auto"
                            onClick={() => handleDownloadInvoice(order.id)}
                          >
                            Download Invoice
                          </Button>
                        </div>
                      </CardContent>
                    </Card>
                  </motion.div>
                ))}
              </div>
            )}
          </motion.div>
        </div>
      </div>
    </>
  );
};

export default OrderHistory;
