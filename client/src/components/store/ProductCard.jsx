import React from 'react';
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Star, ShoppingCart, Heart } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { useCart } from '@/contexts/CartContext';
import { toast } from '@/components/ui/use-toast';

const ProductCard = ({ product, index }) => {
  const { addToCart } = useCart();

  const handleAddToCart = (product) => {
    addToCart(product);
  };

  const handleAddToWishlist = () => {
    toast({
      title: "🚧 This feature isn't implemented yet—but don't worry! You can request it in your next prompt! 🚀"
    });
  };

  const getBadgeVariant = (badge) => {
    switch (badge?.toLowerCase()) {
      case 'best seller':
      case 'best-seller':
        return 'default';
      case 'new arrival':
        return 'secondary';
      case 'testing':
        return 'outline';
      default:
        return 'default';
    }
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.6, delay: index * 0.05 }}
    >
      <Card className="h-full card-hover bg-white border-0 shadow-sm">
        <div className="relative">
          <img
            src={product.image}
            alt={product.name}
            className="w-full h-48 object-cover rounded-t-lg"
          />
          {product.badge && (
            <Badge
              variant={getBadgeVariant(product.badge)}
              className="absolute top-2 left-2"
            >
              {product.badge}
            </Badge>
          )}
          <button
            onClick={handleAddToWishlist}
            className="absolute top-2 right-2 p-2 bg-white rounded-full shadow-md hover:bg-gray-50 transition-colors"
          >
            <Heart className="w-4 h-4 text-gray-600" />
          </button>
        </div>
        
        <CardContent className="p-4 space-y-3 flex flex-col justify-between flex-grow">
          <div>
            <Link to={`/product/${product.id}`}>
              <h3 className="font-medium text-gray-900 hover:text-teal-600 transition-colors line-clamp-2 h-12">
                {product.name}
              </h3>
            </Link>
            
            <div className="flex items-center space-x-2 mt-2">
              <div className="flex items-center">
                {[...Array(5)].map((_, i) => (
                  <Star
                    key={i}
                    className={`w-4 h-4 ${
                      i < Math.floor(product.rating)
                        ? 'text-yellow-400 fill-current'
                        : 'text-gray-300'
                    }`}
                  />
                ))}
              </div>
              <span className="text-sm text-gray-600">({product.reviews})</span>
            </div>
          </div>
          
          <div className="space-y-2 pt-2">
            <div className="flex items-center space-x-2">
              <span className="text-lg font-bold text-gray-900 price-highlight">
                ₹{product.price.toFixed(2)}
              </span>
              {product.originalPrice && (
                <span className="text-sm text-gray-500 line-through">
                  ₹{product.originalPrice.toFixed(2)}
                </span>
              )}
            </div>
            
            <Button
              onClick={() => handleAddToCart(product)}
              className="w-full btn-primary"
              size="sm"
            >
              <ShoppingCart className="w-4 h-4 mr-2" />
              Add to cart
            </Button>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
};

export default ProductCard;