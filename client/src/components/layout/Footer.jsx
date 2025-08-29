
import React from 'react';
import { Link } from 'react-router-dom';
import { ShieldCheck, Facebook, Twitter, Linkedin } from 'lucide-react';

const Footer = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-muted text-muted-foreground border-t">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div className="md:col-span-1">
            <Link to="/" className="flex items-center gap-2 mb-4">
                <ShieldCheck className="h-8 w-8 text-primary" />
                <span className="text-xl font-bold text-foreground">Sreemeditec</span>
            </Link>
            <p className="text-sm">High-quality medical equipment and services you can trust.</p>
          </div>
          
          <div>
            <p className="font-semibold text-foreground mb-4">Quick Links</p>
            <ul className="space-y-2">
              <li><Link to="/about" className="text-sm hover:text-primary transition-colors">About Us</Link></li>
              <li><Link to="/store" className="text-sm hover:text-primary transition-colors">Store</Link></li>
              <li><Link to="/services" className="text-sm hover:text-primary transition-colors">Services</Link></li>
              <li><Link to="/contact" className="text-sm hover:text-primary transition-colors">Contact</Link></li>
            </ul>
          </div>
          
          <div>
            <p className="font-semibold text-foreground mb-4">Support</p>
            <ul className="space-y-2">
              <li><Link to="/faq" className="text-sm hover:text-primary transition-colors">FAQ</Link></li>
              <li><Link to="/privacy" className="text-sm hover:text-primary transition-colors">Privacy Policy</Link></li>
              <li><Link to="/terms" className="text-sm hover:text-primary transition-colors">Terms of Service</Link></li>
            </ul>
          </div>

          <div>
             <p className="font-semibold text-foreground mb-4">Follow Us</p>
             <div className="flex items-center space-x-4">
                <a href="#" className="text-muted-foreground hover:text-primary transition-colors"><Facebook className="h-5 w-5" /></a>
                <a href="#" className="text-muted-foreground hover:text-primary transition-colors"><Twitter className="h-5 w-5" /></a>
                <a href="#" className="text-muted-foreground hover:text-primary transition-colors"><Linkedin className="h-5 w-5" /></a>
             </div>
          </div>
        </div>

        <div className="mt-8 pt-8 border-t border-border/50 text-center text-sm">
          <p>&copy; {currentYear} Sreemeditec. All rights reserved.</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
  