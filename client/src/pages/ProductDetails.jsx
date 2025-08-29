import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { Helmet } from 'react-helmet-async';
import { motion } from 'framer-motion';
import {
  Star, ShoppingCart, Heart, Minus, Plus, ArrowLeft, Shield, Truck, RotateCcw
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { useCart } from '@/contexts/CartContext';
import { toast } from '@/components/ui/use-toast';

const ProductDetails = () => {
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const [quantity, setQuantity] = useState(1);
  const [selectedImage, setSelectedImage] = useState(0);
  const { addToCart } = useCart();

  useEffect(() => {
    const sampleProduct = {
      id: parseInt(id),
      name: "Yuwell Electric Suction Machine 7A-23A (Incl GST)",
      price: 14999.00,
      originalPrice: 17000.00,
      images: [
        "https://via.placeholder.com/600x400?text=Suction+Machine+Main",
        "https://via.placeholder.com/600x400?text=Suction+Machine+Side",
        "https://via.placeholder.com/600x400?text=Suction+Machine+Back",
        "https://via.placeholder.com/600x400?text=Suction+Machine+Details"
      ],
      category: "General Equipment",
      rating: 4.8,
      reviews: 25,
      badge: "Best Seller",
      inStock: true,
      description: "The Yuwell Electric Suction Machine 7A-23A is a professional-grade medical device designed for efficient suction in various medical procedures.",
      features: [
        "High-performance electric motor",
        "Adjustable suction pressure",
        "Large capacity collection jar",
        "Easy-to-clean design",
        "Quiet operation",
        "Portable with wheels",
        "Safety overflow protection",
        "Medical-grade construction"
      ],
      specifications: {
        "Model": "7A-23A",
        "Power": "220V/50Hz",
        "Max Vacuum": "-0.09MPa",
        "Flow Rate": "≥15L/min",
        "Jar Capacity": "1000ml",
        "Noise Level": "≤60dB",
        "Weight": "8.5kg",
        "Dimensions": "350×280×380mm"
      },
      warranty: "2 years manufacturer warranty",
      shipping: "Free shipping on orders over ₹10,000"
    };

    setProduct(sampleProduct);
  }, [id]);

  const handleAddToCart = () => {
    if (product) addToCart(product, quantity);
  };

  const handleRequestQuote = () => {
    toast({
      title: "Quote Request",
      description: "Your quote request has been submitted. We'll contact you soon!",
    });
  };

  const handleAddToWishlist = () => {
    toast({ title: "🚧 Feature not implemented yet." });
  };

  const incrementQuantity = () => setQuantity(prev => prev + 1);
  const decrementQuantity = () => setQuantity(prev => (prev > 1 ? prev - 1 : 1));

  if (!product) {
    return <div className="min-h-screen flex items-center justify-center">Loading...</div>;
  }

  return (
    <>
      <Helmet>
        <title>{`${product.name} - Medical Equipment | Sreemeditec`}</title>
        <meta name="description" content={product.description} />
      </Helmet>

      <div className="min-h-screen bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 py-8">
          <div className="mb-8">
            <Link to="/store" className="flex items-center text-teal-600 hover:text-teal-700">
              <ArrowLeft className="w-4 h-4 mr-2" /> Back to Store
            </Link>
          </div>

          <div className="grid lg:grid-cols-2 gap-12">
            {/* Image Section */}
            <motion.div initial={{ opacity: 0, x: -50 }} animate={{ opacity: 1, x: 0 }} transition={{ duration: 0.8 }} className="space-y-4">
              <div className="relative">
                <img src={product.images[selectedImage]} alt={product.name} className="w-full h-96 object-cover rounded-lg shadow-lg" />
                {product.badge && <Badge className="absolute top-4 left-4 bg-teal-600 text-white">{product.badge}</Badge>}
              </div>
              <div className="grid grid-cols-4 gap-2">
                {product.images.map((img, i) => (
                  <button
                    key={i}
                    onClick={() => setSelectedImage(i)}
                    className={`rounded-lg ring-2 transition ${
                      selectedImage === i ? 'ring-teal-600' : 'ring-gray-200 hover:ring-teal-300'
                    }`}
                  >
                    <img src={img} alt={`View ${i}`} className="w-full h-20 object-cover rounded-md" />
                  </button>
                ))}
              </div>
            </motion.div>

            {/* Details Section */}
            <motion.div initial={{ opacity: 0, x: 50 }} animate={{ opacity: 1, x: 0 }} transition={{ duration: 0.8 }} className="space-y-6">
              <div>
                <p className="text-sm text-gray-600">{product.category}</p>
                <h1 className="text-3xl font-bold text-gray-900 mb-4">{product.name}</h1>
                <div className="flex items-center space-x-4">
                  <div className="flex">
                    {[...Array(5)].map((_, i) => (
                      <Star
                        key={i}
                        className={`w-5 h-5 ${i < Math.floor(product.rating) ? 'text-yellow-400' : 'text-gray-300'}`}
                        fill={i < Math.floor(product.rating) ? 'currentColor' : 'none'}
                      />
                    ))}
                  </div>
                  <span className="text-sm text-gray-600">({product.reviews} reviews)</span>
                  <Badge variant="outline" className="text-green-600 border-green-600">
                    {product.inStock ? 'In Stock' : 'Out of Stock'}
                  </Badge>
                </div>
              </div>

              <div>
                <div className="flex items-center space-x-3">
                  <span className="text-3xl font-bold text-gray-900">₹{product.price.toFixed(2)}</span>
                  {product.originalPrice && (
                    <span className="text-xl text-gray-500 line-through">₹{product.originalPrice.toFixed(2)}</span>
                  )}
                  {product.originalPrice && (
                    <Badge className="bg-red-100 text-red-600">
                      Save ₹{(product.originalPrice - product.price).toFixed(2)}
                    </Badge>
                  )}
                </div>
                <p className="text-sm text-gray-600">Inclusive of all taxes</p>
              </div>

              <p className="text-gray-600 leading-relaxed">{product.description}</p>

              <div className="flex items-center space-x-4">
                <span className="text-sm font-medium text-gray-700">Quantity:</span>
                <div className="flex border rounded-md">
                  <button onClick={decrementQuantity} className="px-3 py-2 bg-gray-50 hover:bg-gray-100">
                    <Minus className="w-4 h-4" />
                  </button>
                  <span className="px-5 py-2 border-x text-center font-semibold text-gray-700">{quantity}</span>
                  <button onClick={incrementQuantity} className="px-3 py-2 bg-gray-50 hover:bg-gray-100">
                    <Plus className="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div className="flex flex-col sm:flex-row gap-3">
                <Button onClick={handleAddToCart} className="w-full sm:w-auto bg-[#1F8F76] text-white">
                  <ShoppingCart className="w-5 h-5 mr-2" /> Add to Cart
                </Button>
                <Button onClick={handleRequestQuote} variant="outline" className="w-full sm:w-auto">
                  Request Quote
                </Button>
                <Button onClick={handleAddToWishlist} variant="ghost" className="w-full sm:w-auto">
                  <Heart className="w-5 h-5 text-gray-500" />
                </Button>
              </div>

              <div className="grid sm:grid-cols-3 gap-6 pt-6 border-t mt-6">
                {[{
                  icon: <Shield className="w-6 h-6 text-teal-600" />, label: 'Warranty', text: product.warranty
                }, {
                  icon: <Truck className="w-6 h-6 text-teal-600" />, label: 'Free Shipping', text: product.shipping
                }, {
                  icon: <RotateCcw className="w-6 h-6 text-teal-600" />, label: 'Returns', text: '30-day return policy'
                }].map(({ icon, label, text }, idx) => (
                  <div key={idx} className="flex items-start space-x-3">
                    {icon}
                    <div>
                      <p className="text-sm font-semibold text-gray-800">{label}</p>
                      <p className="text-xs text-gray-600">{text}</p>
                    </div>
                  </div>
                ))}
              </div>
            </motion.div>
          </div>

          {/* Features + Specifications */}
          <motion.div initial={{ opacity: 0, y: 50 }} whileInView={{ opacity: 1, y: 0 }} transition={{ duration: 0.8 }} viewport={{ once: true }} className="mt-16 grid md:grid-cols-2 gap-8">
            <Card>
              <CardContent className="p-6">
                <h3 className="text-xl font-bold text-gray-900 mb-4">Key Features</h3>
                <ul className="space-y-3">
                  {product.features.map((f, i) => (
                    <li key={i} className="flex items-start space-x-3">
                      <div className="w-2 h-2 bg-teal-600 rounded-full mt-2" />
                      <span className="text-gray-600">{f}</span>
                    </li>
                  ))}
                </ul>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <h3 className="text-xl font-bold text-gray-900 mb-4">Specifications</h3>
                <div className="space-y-3">
                  {Object.entries(product.specifications).map(([k, v]) => (
                    <div key={k} className="flex justify-between py-2 border-b">
                      <span className="font-medium text-gray-700">{k}:</span>
                      <span className="text-gray-600">{v}</span>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </motion.div>
        </div>
      </div>
    </>
  );
};

export default ProductDetails;
