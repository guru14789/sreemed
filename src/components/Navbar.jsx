import React, { useState, useEffect } from 'react';
import { NavLink, Link, useNavigate } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';
import {
  Menu, X, ShoppingCart, User, LogOut, Settings, Package, LayoutDashboard, ShieldCheck, FileText
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useAuth } from '@/contexts/AuthContext';
import { useCart } from '@/contexts/CartContext';
import { cn } from '@/lib/utils';
import {
  DropdownMenu, DropdownMenuContent, DropdownMenuItem,
  DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const Navbar = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [isScrolled, setIsScrolled] = useState(false);
  const navigate = useNavigate();
  const { user, logout } = useAuth();
  const { getCartItemsCount } = useCart();

  const navigation = [
    { name: 'Home', href: '/' },
    { name: 'About Us', href: '/about' },
    { name: 'Services', href: '/services' },
    { name: 'Store', href: '/store' },
    { name: 'Contact', href: '/contact' },
  ];

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 10);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const activeLinkStyle = {
    color: '#1d7d69',
    fontWeight: 600,
  };

  return (
    <nav className={cn(
      "sticky top-0 z-50 transition-all duration-300",
      isScrolled ? "bg-white/80 backdrop-blur-lg shadow-md" : "bg-white"
    )}>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-20">
          
          {/* Logo */}
          <Link to="/" className="flex items-center gap-2">
            <motion.div whileHover={{ rotate: 15 }}>
              <ShieldCheck className="h-8 w-8 text-[#1d7d69]" />
            </motion.div>
            <span className="text-2xl font-bold text-gray-800">Sreemeditec</span>
          </Link>

          {/* Desktop Nav */}
          <div className="hidden md:flex md:items-center gap-10">
            {navigation.map((item) => (
              <NavLink
                key={item.name}
                to={item.href}
                className="text-sm font-medium text-gray-600 hover:text-[#1d7d69] transition"
                style={({ isActive }) => isActive ? activeLinkStyle : undefined}
              >
                {item.name}
              </NavLink>
            ))}
          </div>

          {/* Actions */}
          <div className="hidden md:flex items-center gap-4">
            {/* Cart */}
            <Link to="/cart" className="relative text-gray-700 hover:text-[#1d7d69] transition">
              <ShoppingCart className="w-5 h-5" />
              {getCartItemsCount() > 0 && (
                <span className="absolute -top-2 -right-2 h-5 w-5 rounded-full bg-[#1d7d69] text-white text-xs flex items-center justify-center">
                  {getCartItemsCount()}
                </span>
              )}
            </Link>

            {/* User Dropdown or Login */}
            {user ? (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" className="flex items-center gap-2 px-2">
                    <User className="w-5 h-5" />
                    <span className="hidden lg:inline text-sm font-medium">{user.name}</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-56">
                  <DropdownMenuLabel>My Account</DropdownMenuLabel>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem asChild>
                    <Link to="/profile"><Settings className="mr-2 h-4 w-4" /> Profile</Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem asChild>
                    <Link to="/orders"><Package className="mr-2 h-4 w-4" /> Orders</Link>
                  </DropdownMenuItem>
                  {user.role === 'admin' && (
                    <DropdownMenuItem asChild>
                      <Link to="/admin"><LayoutDashboard className="mr-2 h-4 w-4" /> Admin Panel</Link>
                    </DropdownMenuItem>
                  )}
                  <DropdownMenuSeparator />
                  <DropdownMenuItem onClick={handleLogout}>
                    <LogOut className="mr-2 h-4 w-4" /> Logout
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            ) : (
              <Link to="/login">
                <Button variant="ghost" className="text-sm">Login</Button>
              </Link>
            )}

            <Link to="/quote">
              <Button className="bg-[#1d7d69] text-white hover:bg-[#166352] text-sm px-4 py-2">
                <FileText className="mr-2 h-4 w-4" /> Request Quote
              </Button>
            </Link>
          </div>

          {/* Mobile Hamburger */}
          <div className="md:hidden flex items-center">
            <Link to="/cart" className="relative p-2 text-gray-700 hover:text-[#1d7d69] transition-colors mr-1">
              <ShoppingCart className="w-5 h-5" />
              {getCartItemsCount() > 0 && (
                <span className="absolute top-1 right-1 h-4 w-4 rounded-full bg-[#1d7d69] text-white text-xs flex items-center justify-center">
                  {getCartItemsCount()}
                </span>
              )}
            </Link>
            <button onClick={() => setIsOpen(!isOpen)} className="p-2">
              {isOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="md:hidden bg-white shadow-sm"
          >
            <div className="px-4 pt-4 pb-6 space-y-2">
              {navigation.map((item) => (
                <NavLink
                  key={item.name}
                  to={item.href}
                  onClick={() => setIsOpen(false)}
                  className="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-[#1d7d69] hover:bg-gray-100"
                  style={({ isActive }) => isActive ? activeLinkStyle : undefined}
                >
                  {item.name}
                </NavLink>
              ))}
              <div className="border-t pt-4 space-y-2">
                {user ? (
                  <>
                    <NavLink to="/profile" onClick={() => setIsOpen(false)} className="flex items-center px-3 py-2 text-gray-700 hover:text-[#1d7d69] hover:bg-gray-100">
                      <Settings className="mr-2 h-5 w-5" /> Profile
                    </NavLink>
                    <NavLink to="/orders" onClick={() => setIsOpen(false)} className="flex items-center px-3 py-2 text-gray-700 hover:text-[#1d7d69] hover:bg-gray-100">
                      <Package className="mr-2 h-5 w-5" /> Orders
                    </NavLink>
                    {user.role === 'admin' && (
                      <NavLink to="/admin" onClick={() => setIsOpen(false)} className="flex items-center px-3 py-2 text-gray-700 hover:text-[#1d7d69] hover:bg-gray-100">
                        <LayoutDashboard className="mr-2 h-5 w-5" /> Admin Panel
                      </NavLink>
                    )}
                    <button onClick={() => { handleLogout(); setIsOpen(false); }} className="w-full flex items-center px-3 py-2 text-gray-700 hover:text-[#1d7d69] hover:bg-gray-100">
                      <LogOut className="mr-2 h-5 w-5" /> Logout
                    </button>
                  </>
                ) : (
                  <Link to="/login" onClick={() => setIsOpen(false)} className="block">
                    <Button variant="outline" className="w-full">Login</Button>
                  </Link>
                )}
                <Link to="/quote" onClick={() => setIsOpen(false)} className="block">
                  <Button className="w-full bg-[#1d7d69] text-white mt-2">Request Quote</Button>
                </Link>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </nav>
  );
};

export default Navbar;
