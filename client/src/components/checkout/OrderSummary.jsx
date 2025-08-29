import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CheckCircle } from 'lucide-react';

const OrderSummary = ({ cartItems, getCartTotal, isProcessing }) => {
  const subtotal = getCartTotal();
  const tax = subtotal * 0.18;
  const total = subtotal + tax;

  return (
    <Card className="sticky top-24">
      <CardHeader>
        <CardTitle>Order Summary</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="space-y-3">
          {cartItems.map((item) => (
            <div key={item.id} className="flex justify-between">
              <div className="flex-1">
                <p className="font-medium text-sm">{item.name}</p>
                <p className="text-sm text-gray-600">Qty: {item.quantity}</p>
              </div>
              <p className="font-medium">₹{(item.price * item.quantity).toFixed(2)}</p>
            </div>
          ))}
        </div>

        <div className="border-t pt-4 space-y-2">
          <div className="flex justify-between">
            <span>Subtotal</span>
            <span>₹{subtotal.toFixed(2)}</span>
          </div>
          <div className="flex justify-between">
            <span>Shipping</span>
            <span className="text-green-600">Free</span>
          </div>
          <div className="flex justify-between">
            <span>Tax (18% GST)</span>
            <span>₹{tax.toFixed(2)}</span>
          </div>
          <div className="flex justify-between font-bold text-lg border-t pt-2">
            <span>Total</span>
            <span>₹{total.toFixed(2)}</span>
          </div>
        </div>

        <Button
          type="submit"
          disabled={isProcessing}
          className="w-full btn-primary text-lg py-3"
        >
          {isProcessing ? (
            <>
              <div className="loading-spinner mr-2"></div>
              Processing...
            </>
          ) : (
            <>
              <CheckCircle className="w-5 h-5 mr-2" />
              Complete Order
            </>
          )}
        </Button>

        <div className="text-sm text-gray-600 space-y-1">
          <p>✓ Free shipping and installation</p>
          <p>✓ 30-day return policy</p>
          <p>✓ 2-year manufacturer warranty</p>
        </div>
      </CardContent>
    </Card>
  );
};

export default OrderSummary;