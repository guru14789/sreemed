
import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { MapPin, Truck, Package, CheckCircle, Clock } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

const LiveTracking = ({ order }) => {
  const [currentLocation, setCurrentLocation] = useState(null);
  const [estimatedTime, setEstimatedTime] = useState(null);

  useEffect(() => {
    // Simulate live tracking updates
    const interval = setInterval(() => {
      if (order.status === 'shipped' || order.status === 'out_for_delivery') {
        simulateLocationUpdate();
      }
    }, 5000);

    return () => clearInterval(interval);
  }, [order.status]);

  const simulateLocationUpdate = () => {
    const locations = [
      { lat: 28.6139, lng: 77.2090, address: "Warehouse, Delhi" },
      { lat: 28.7041, lng: 77.1025, address: "Distribution Center, Gurgaon" },
      { lat: 28.4595, lng: 77.0266, address: "Local Hub, Faridabad" },
      { lat: 28.4089, lng: 77.3178, address: "Out for delivery" },
    ];

    const randomLocation = locations[Math.floor(Math.random() * locations.length)];
    setCurrentLocation(randomLocation);
    setEstimatedTime(Math.floor(Math.random() * 120) + 30); // 30-150 minutes
  };

  const getTrackingSteps = () => {
    const steps = [
      { 
        id: 'confirmed', 
        label: 'Order Confirmed', 
        icon: CheckCircle,
        time: order.created_at,
        completed: true 
      },
      { 
        id: 'processing', 
        label: 'Processing', 
        icon: Package,
        time: order.created_at,
        completed: ['processing', 'shipped', 'out_for_delivery', 'delivered'].includes(order.status)
      },
      { 
        id: 'shipped', 
        label: 'Shipped', 
        icon: Truck,
        time: order.status === 'shipped' ? new Date().toISOString() : null,
        completed: ['shipped', 'out_for_delivery', 'delivered'].includes(order.status)
      },
      { 
        id: 'out_for_delivery', 
        label: 'Out for Delivery', 
        icon: Truck,
        time: order.status === 'out_for_delivery' ? new Date().toISOString() : null,
        completed: ['out_for_delivery', 'delivered'].includes(order.status)
      },
      { 
        id: 'delivered', 
        label: 'Delivered', 
        icon: CheckCircle,
        time: order.status === 'delivered' ? new Date().toISOString() : null,
        completed: order.status === 'delivered'
      }
    ];

    return steps;
  };

  const steps = getTrackingSteps();

  return (
    <div className="space-y-6">
      {/* Live Location */}
      {currentLocation && (order.status === 'shipped' || order.status === 'out_for_delivery') && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center">
              <MapPin className="w-5 h-5 mr-2 text-blue-600" />
              Live Location
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex items-center justify-between">
              <div>
                <p className="font-medium">{currentLocation.address}</p>
                <p className="text-sm text-gray-600">
                  Lat: {currentLocation.lat}, Lng: {currentLocation.lng}
                </p>
              </div>
              {estimatedTime && (
                <Badge variant="outline" className="text-blue-600">
                  ETA: {estimatedTime} mins
                </Badge>
              )}
            </div>
            
            {/* Simulated Map */}
            <div className="mt-4 h-48 bg-gray-200 rounded-lg flex items-center justify-center">
              <div className="text-center">
                <MapPin className="w-8 h-8 mx-auto text-blue-600 mb-2" />
                <p className="text-sm text-gray-600">Live tracking map</p>
                <p className="text-xs text-gray-500">
                  (In production, integrate with Google Maps or similar)
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Tracking Timeline */}
      <Card>
        <CardHeader>
          <CardTitle>Order Timeline</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {steps.map((step, index) => {
              const Icon = step.icon;
              return (
                <motion.div
                  key={step.id}
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ duration: 0.5, delay: index * 0.1 }}
                  className={`flex items-center space-x-4 p-3 rounded-lg ${
                    step.completed 
                      ? 'bg-green-50 border border-green-200' 
                      : 'bg-gray-50 border border-gray-200'
                  }`}
                >
                  <div className={`p-2 rounded-full ${
                    step.completed 
                      ? 'bg-green-600 text-white' 
                      : 'bg-gray-300 text-gray-600'
                  }`}>
                    <Icon className="w-4 h-4" />
                  </div>
                  
                  <div className="flex-1">
                    <h4 className={`font-medium ${
                      step.completed ? 'text-green-800' : 'text-gray-600'
                    }`}>
                      {step.label}
                    </h4>
                    {step.time && (
                      <p className="text-sm text-gray-500">
                        {new Date(step.time).toLocaleString('en-IN')}
                      </p>
                    )}
                  </div>
                  
                  {step.completed && (
                    <CheckCircle className="w-5 h-5 text-green-600" />
                  )}
                </motion.div>
              );
            })}
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default LiveTracking;
