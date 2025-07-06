import React, { useState, useEffect } from "react";
import { Link, NavLink, useNavigate } from "react-router-dom";
import { motion } from "framer-motion";
import {
  ShoppingCart,
  User,
  Menu,
  X,
  ShieldCheck,
  LogOut,
  UserCircle,
  LayoutDashboard,
  ListOrdered,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { ThemeToggle } from "@/components/ui/theme-toggle";
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem } from "@/components/ui/dropdown-menu";
import { useCart } from "@/hooks/use-cart";
import { useToast } from "@/components/ui/use-toast";

const navLinks = [
  { href: '/', label: 'Home' },
  { href: '/store', label: 'Store' },
  { href: '/services', label: 'Services' },
  { href: '/about', label: 'About Us' },
  { href: '/contact', label: 'Contact' },
];


const Header = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [userRole, setUserRole] = useState("");
  const { getTotalItems } = useCart();
  const navigate = useNavigate();

  useEffect(() => {
    const token = localStorage.getItem("authToken");
    const role = localStorage.getItem("userRole");
    setIsAuthenticated(!!token);
    setUserRole(role || "");
  }, []);

  const handleLogout = () => {
    localStorage.removeItem("authToken");
    localStorage.removeItem("userRole");
    setIsAuthenticated(false);
    navigate("/");
    window.location.reload();
  };

  const activeLinkStyle = {
    color: '#1d7d69',
    fontWeight: '600',
  };

  return (
    <header className="bg-background/80 backdrop-blur-sm sticky top-0 z-50 border-b border-border">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-20">
          <Link to="/" className="flex items-center gap-2">
            <motion.div whileHover={{ rotate: 15 }}>
              <ShieldCheck className="h-8 w-8 text-[#1d7d69]" />
            </motion.div>
            <span className="text-2xl font-bold tracking-tight text-[#1d7d69]">Sreemeditec</span>
          </Link>

          {/* Nav Links */}
          <nav className="hidden md:flex items-center gap-6">
            {navLinks.map((link) => (
              <NavLink
                key={link.href}
                to={link.href}
                className="text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
                style={({ isActive }) => isActive ? activeLinkStyle : undefined}
              >
                {link.label}
              </NavLink>
            ))}
          </nav>

          {/* Right Side */}
          <div className="flex items-center gap-3">
            <ThemeToggle />

            {/* Cart */}
            <Link to="/cart">
              <Button variant="ghost" size="icon" className="relative hover:bg-[#f0fdfa]">
                <ShoppingCart className="h-5 w-5 text-muted-foreground" />
                {getTotalItems() > 0 && (
                  <span className="absolute -top-1 -right-1 bg-[#1d7d69] text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                    {getTotalItems()}
                  </span>
                )}
              </Button>
            </Link>

            {/* Auth Dropdown */}
            {isAuthenticated ? (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" size="icon">
                    <UserCircle className="h-5 w-5 text-muted-foreground" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem onClick={() => navigate(userRole === "admin" ? "/admin-dashboard" : "/profile")}>
                    {userRole === "admin" ? <LayoutDashboard className="mr-2 h-4 w-4" /> : <User className="mr-2 h-4 w-4" />}
                    {userRole === "admin" ? "Admin Dashboard" : "Profile"}
                  </DropdownMenuItem>
                  {userRole !== "admin" && (
                    <DropdownMenuItem onClick={() => navigate("/orders")}>
                      <ListOrdered className="mr-2 h-4 w-4" /> Orders
                    </DropdownMenuItem>
                  )}
                  <DropdownMenuItem onClick={handleLogout}>
                    <LogOut className="mr-2 h-4 w-4" /> Logout
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            ) : (
              <Link to="/login">
                <Button>
                  <User className="mr-2 h-4 w-4" /> Login
                </Button>
              </Link>
            )}

            {/* Mobile Hamburger */}
            <button className="md:hidden p-2" onClick={() => setIsMenuOpen(!isMenuOpen)}>
              {isMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      {isMenuOpen && (
        <motion.div
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -20 }}
          className="md:hidden bg-background border-t border-border"
        >
          <nav className="flex flex-col items-center gap-4 py-4">
            {navLinks.map((link) => (
              <NavLink
                key={link.href}
                to={link.href}
                onClick={() => setIsMenuOpen(false)}
                className="text-lg font-medium text-muted-foreground hover:text-primary"
                style={({ isActive }) => isActive ? activeLinkStyle : undefined}
              >
                {link.label}
              </NavLink>
            ))}

            <div className="flex flex-col items-center gap-4 mt-4 w-full px-4">
              <Link to="/cart" className="w-full">
                <Button variant="ghost" className="w-full" onClick={() => setIsMenuOpen(false)}>
                  <ShoppingCart className="mr-2 h-5 w-5" /> View Cart
                </Button>
              </Link>

              {!isAuthenticated ? (
                <Link to="/login" className="w-full">
                  <Button className="w-full" onClick={() => setIsMenuOpen(false)}>
                    <User className="mr-2 h-4 w-4" /> Login
                  </Button>
                </Link>
              ) : (
                <>
                  <Button className="w-full" onClick={() => { navigate(userRole === "admin" ? "/admin-dashboard" : "/profile"); setIsMenuOpen(false); }}>
                    {userRole === "admin" ? <LayoutDashboard className="mr-2 h-4 w-4" /> : <User className="mr-2 h-4 w-4" />}
                    {userRole === "admin" ? "Admin Dashboard" : "Profile"}
                  </Button>
                  {userRole !== "admin" && (
                    <Button className="w-full" onClick={() => { navigate("/orders"); setIsMenuOpen(false); }}>
                      <ListOrdered className="mr-2 h-4 w-4" /> Orders
                    </Button>
                  )}
                  <Button className="w-full" variant="destructive" onClick={() => { handleLogout(); setIsMenuOpen(false); }}>
                    <LogOut className="mr-2 h-4 w-4" /> Logout
                  </Button>
                </>
              )}
            </div>
          </nav>
        </motion.div>
      )}
    </header>
  );
};

export default Header;
