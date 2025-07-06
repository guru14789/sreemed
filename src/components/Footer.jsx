import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import {
  Mail, Phone, MapPin,
  Linkedin, Twitter, Instagram, X
} from 'lucide-react';

const Footer = () => {
  const [showTerms, setShowTerms] = useState(false);
  const [showPrivacy, setShowPrivacy] = useState(false);
  const [showRefund, setShowRefund] = useState(false);

  return (
    <footer className="bg-white text-gray-800 relative z-10 border-t border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {/* Company Info */}
          <div className="space-y-4">
            <div className="flex items-center space-x-2">
              <div className="w-8 h-8 bg-gradient-to-br from-[#1d7d69] to-teal-500 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-sm">S</span>
              </div>
              <span className="text-xl font-bold">Sreemeditec</span>
            </div>
            <p className="text-gray-600 text-sm">
              Supporting healthcare with trusted medical technology since 2005. Your vision, our mission.
            </p>
            <div className="flex space-x-4">
              {[Linkedin, Twitter, Instagram].map((Icon, i) => (
                <a key={i} href="#" className="text-gray-500 hover:text-[#1d7d69] transition-colors">
                  <Icon className="w-5 h-5" />
                </a>
              ))}
            </div>
          </div>

          {/* Quick Links */}
          <div className="space-y-4">
            <span className="text-lg font-semibold">Quick Links</span>
            <div className="space-y-2">
              <Link to="/" className="block text-gray-600 hover:text-[#1d7d69] transition-colors text-sm">Home</Link>
              <Link to="/about" className="block text-gray-600 hover:text-[#1d7d69] transition-colors text-sm">About Us</Link>
              <Link to="/store" className="block text-gray-600 hover:text-[#1d7d69] transition-colors text-sm">Shop</Link>
              <Link to="/services" className="block text-gray-600 hover:text-[#1d7d69] transition-colors text-sm">Services</Link>
              <Link to="/contact" className="block text-gray-600 hover:text-[#1d7d69] transition-colors text-sm">Contact</Link>
            </div>
          </div>

          {/* Customer Service */}
          <div className="space-y-4">
            <span className="text-lg font-semibold">Customer Service</span>
            <div className="space-y-2">
              <span className="block text-gray-600 text-sm">Medical Equipment Supply</span>
              <span className="block text-gray-600 text-sm">Installation & Maintenance</span>
              <span className="block text-gray-600 text-sm">Consultation & Procurement</span>
              <span className="block text-gray-600 text-sm">After-Sales Support</span>
              <span className="block text-gray-600 text-sm">Ask for quote</span>
            </div>
          </div>

          {/* Contact Info */}
          <div className="space-y-4">
            <span className="text-lg font-semibold">Contact Info</span>
            <div className="space-y-3">
              <div className="flex items-start space-x-3">
                <MapPin className="w-5 h-5 text-[#1d7d69] mt-0.5 flex-shrink-0" />
                <span className="text-gray-600 text-sm">
                  No:18/2, Rajajinai Koli Street, Rajakipakkam, Chennai-73 Tamilnadu, India.
                </span>
              </div>
              <div className="flex items-center space-x-3">
                <Mail className="w-5 h-5 text-[#1d7d69] flex-shrink-0" />
                <span className="text-gray-600 text-sm">sreemeditec@gmail.com</span>
              </div>
              <div className="flex items-center space-x-3">
                <Phone className="w-5 h-5 text-[#1d7d69] flex-shrink-0" />
                <span className="text-gray-600 text-sm">+91 9884818398</span>
              </div>
            </div>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="border-t border-gray-200 mt-8 pt-8">
          <div className="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <p className="text-gray-500 text-sm">© 2025 Sreemeditec. All rights reserved.</p>
            <div className="flex space-x-6">
              <button onClick={() => setShowRefund(true)} className="text-gray-600 hover:text-[#1d7d69] transition-colors text-sm">Refund Policy</button>
              <button onClick={() => setShowPrivacy(true)} className="text-gray-600 hover:text-[#1d7d69] transition-colors text-sm">Privacy Policy</button>
              <button onClick={() => setShowTerms(true)} className="text-gray-600 hover:text-[#1d7d69] transition-colors text-sm">Terms of Service</button>
            </div>
          </div>
        </div>
      </div>

      {/* Modal Overlay */}
      {(showPrivacy || showTerms || showRefund) && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
          <div className="relative bg-white text-gray-800 rounded-2xl w-[90%] max-w-2xl p-8 shadow-2xl max-h-[80vh] overflow-y-auto">
            <button
              onClick={() => {
                setShowPrivacy(false);
                setShowTerms(false);
                setShowRefund(false);
              }}
              className="absolute top-4 right-4 text-gray-600 hover:text-black transition"
            >
              <X size={22} />
            </button>

            {showPrivacy && (
              <>
                <h2 className="text-2xl font-bold mb-4">Privacy Policy</h2>
                <p className="mb-4">
                  We value your privacy and handle your data responsibly. All personal data is collected only with your
                  consent and used strictly for service-related purposes.
                </p>
                <p className="mb-4">
                  Your information is encrypted and stored securely. You have full control over your data and may
                  request access or deletion at any time.
                </p>
                <p>Contact us for any privacy-related queries at <b>sreemeditec@gmail.com</b>.</p>
              </>
            )}

            {showTerms && (
              <>
                <h2 className="text-2xl font-bold mb-4">Terms of Service</h2>
                <p className="mb-4">
                  By using our services, you agree to comply with all applicable laws. All content provided is owned by
                  Sreemeditec and may not be reproduced without permission.
                </p>
                <p className="mb-4">
                  We reserve the right to update or terminate services at any time. Continued use implies agreement.
                </p>
                <p>Please reach out if you have any concerns regarding these terms.</p>
              </>
            )}

            {showRefund && (
              <>
                <h2 className="text-2xl font-bold mb-4">Refund Policy</h2>
                <p className="mb-4">
                  Refunds are available only for equipment or services with valid proof of issues and within the
                  specified warranty period.
                </p>
                <p className="mb-4">
                  Contact our support team within 7 working days of service/equipment delivery to initiate a refund or replacement process.
                </p>
                <p>For refund-related queries, email us at <b>sreemeditec@gmail.com</b>.</p>
              </>
            )}
          </div>
        </div>
      )}
    </footer>
  );
};

export default Footer;
