import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import { motion } from 'framer-motion';
import { Minus, Plus, Trash2, ShoppingBag, ArrowLeft } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useCart } from '@/contexts/CartContext';
import { useAuth } from '@/contexts/AuthContext';

const Cart = () => {
  const { cartItems, updateQuantity, removeFromCart, getCartTotal, clearCart } = useCart();
  const { user } = useAuth();
  const navigate = useNavigate();

  if (cartItems.length === 0) {
    return (
      <>
        <Helmet>
          <title>Shopping Cart - Sreemeditec</title>
          <meta name="description" content="Review your selected medical equipment items in your shopping cart before checkout." />
        </Helmet>

        <div className="min-h-screen bg-gray-50 flex items-center justify-center">
          <motion.div
            initial={{ opacity: 0, y: 50 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            className="text-center space-y-6"
          >
            <div className="w-24 h-24 mx-auto bg-gray-200 rounded-full flex items-center justify-center">
              <ShoppingBag className="w-12 h-12 text-gray-400" />
            </div>
            <h1 className="text-3xl font-bold text-gray-900">Your cart is empty</h1>
            <p className="text-gray-600 max-w-md mx-auto">
              Looks like you haven't added any medical equipment to your cart yet. Browse our store to find the products you need.
            </p>
            <Link to="/store">
              <Button className="btn-primary">
                Continue Shopping
              </Button>
            </Link>
          </motion.div>
        </div>
      </>
    );
  }

  const subtotal = getCartTotal();
  const shipping = 0; // It's free in the image
  const total = subtotal + shipping;

  return (
    <>
      <Helmet>
        <title>{`Your Cart (${cartItems.length} items) - Sreemeditec`}</title>
        <meta name="description" content="Review your selected medical equipment items in your shopping cart before checkout." />
      </Helmet>

      <div className="bg-gray-50 py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-8">Your Cart</h1>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <div className="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm space-y-4">
              <div className="hidden md:grid grid-cols-12 gap-4 font-medium text-sm text-gray-500 uppercase border-b pb-4">
                <div className="col-span-5">Product</div>
                <div className="col-span-2 text-right">Price</div>
                <div className="col-span-3 text-center">Quantity</div>
                <div className="col-span-2 text-right">Total</div>
              </div>

              {cartItems.map((item) => (
                <div key={item._id} className="grid grid-cols-1 md:grid-cols-12 gap-4 items-center border-b pb-4 last:border-b-0 last:pb-0">
                  <div className="md:col-span-5 flex items-center space-x-4">
                    <img className="w-20 h-20 object-cover rounded-md flex-shrink-0" alt={item.name} src="https://images.unsplash.com/photo-1693264251393-d28f984ca283" />
                    <div>
                      <p className="font-semibold text-gray-900">{item.name}</p>
                      <p className="text-sm text-gray-600">{item.category}</p>
                    </div>
                  </div>

                  <div className="md:col-span-2 text-right font-medium">₹{item.price.toFixed(2)}</div>
                  
                  <div className="md:col-span-3 flex justify-center items-center">
                    <div className="flex items-center border rounded-md">
                      <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => updateQuantity(item._id, item.quantity - 1)}>
                        <Minus className="h-4 w-4" />
                      </Button>
                      <span className="px-3 text-center">{item.quantity}</span>
                      <Button variant="ghost" size="icon" className="h-8 w-8" onClick={() => updateQuantity(item._id, item.quantity + 1)}>
                        <Plus className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                  
                  <div className="md:col-span-2 flex items-center justify-end">
                    <span className="font-medium text-right w-full">₹{(item.price * item.quantity).toFixed(2)}</span>
                     <Button variant="ghost" size="icon" className="text-red-500 hover:bg-red-50 hover:text-red-600" onClick={() => removeFromCart(item._id)}>
                        <Trash2 className="h-5 w-5" />
                      </Button>
                  </div>
                </div>
              ))}

              <div className="mt-6 flex justify-between items-center">
                <Link to="/store">
                  <Button variant="outline">Continue Shopping</Button>
                </Link>
                <Button variant="outline" className="text-red-500 border-red-500 hover:bg-red-50 hover:text-red-600" onClick={clearCart}>
                  Clear Cart
                </Button>
              </div>
            </div>

            <div className="lg:col-span-1">
              <Card className="sticky top-24 shadow-sm">
                <CardHeader>
                  <CardTitle>Order Summary</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="space-y-2">
                    <div className="flex justify-between">
                      <p className="text-gray-600">Subtotal</p>
                      <p className="font-medium">₹{subtotal.toFixed(2)}</p>
                    </div>
                    <div className="flex justify-between">
                      <p className="text-gray-600">Shipping</p>
                      <p className="font-medium text-teal-600">Free</p>
                    </div>
                  </div>
                  <div className="border-t pt-4">
                    <div className="flex justify-between font-bold text-lg">
                      <p>Total</p>
                      <p>₹{total.toFixed(2)}</p>
                    </div>
                  </div>

                  {user ? (
                    <Link to="/checkout" className="w-full">
                      <Button className="w-full btn-primary text-lg h-12">
                        Proceed to Checkout
                      </Button>
                    </Link>
                  ) : (
                    <div className="text-center space-y-3 pt-2">
                      <p className="text-sm text-gray-500">Please sign in to checkout</p>
                      <Button className="w-full btn-primary h-12" onClick={() => navigate('/login')}>
                        Login to Checkout
                      </Button>
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default Cart;