import React from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight } from 'lucide-react';

const Hero = () => {
  return (
    <div style={{ backgroundColor: '#1d7d69' }} className="relative text-white overflow-hidden">
      {/* Optional Background Pattern */}
      <div style={{ opacity: 0.1 }} className="absolute inset-0">
        <div className="absolute right-0 bottom-0 w-1/2 h-1/2 bg-white rounded-full translate-x-1/2 translate-y-1/2"></div>
        <div className="absolute left-0 top-0 w-1/3 h-1/3 bg-white rounded-full -translate-x-1/2 -translate-y-1/2"></div>
      </div>

      <div className="relative container mx-auto px-6 py-20">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-10 items-center min-h-[540px]">
          {/* Left Content */}
          <div className="space-y-6 z-10">
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
              Medical Equipment Solutions to Power Modern Healthcare
            </h1>
            <p className="text-lg md:text-xl text-white/90 max-w-lg">
              We deliver cutting-edge reliable medical equipment for today’s healthcare needs.
            </p>
            <div className="flex flex-wrap gap-4">
              <Link
                to="/contact"
                className="bg-white text-[#1d7d69] hover:bg-gray-100 font-semibold py-3 px-6 rounded-md transition"
              >
                Get Started
              </Link>
              <Link
                to="/store"
                className="flex items-center border border-white hover:bg-white/10 text-white font-semibold py-3 px-6 rounded-md transition"
              >
                Our Store <ArrowRight size={18} className="ml-2" />
              </Link>
            </div>
          </div>

          {/* Right Image */}
          <div className="flex justify-center md:justify-end z-10">
            <img
              src="/download.jpeg"
              alt="Technology Solutions"
              className="rounded-lg shadow-xl object-cover"
              style={{ maxHeight: '400px', width: '100%', height: 'auto' }}
            />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Hero;
