import React, { useState } from 'react';
import { Helmet } from 'react-helmet-async';
import { motion } from 'framer-motion';
import { Heart, Shield, Zap, CheckCircle, ArrowRight, X } from 'lucide-react';
import { Link } from 'react-router-dom';

const Services = () => {
  const [activeTab, setActiveTab] = useState('equipment');
  const [showModal, setShowModal] = useState(false);

  const tabs = [
    { id: 'equipment', label: 'Equipment', icon: <Heart className="w-5 h-5" /> },
    { id: 'furniture', label: 'Furniture', icon: <Shield className="w-5 h-5" /> },
    { id: 'pipeline', label: 'Pipeline', icon: <Zap className="w-5 h-5" /> }
  ];

  const tabContent = {
    equipment: {
      title: 'Medical Equipment Supply',
      description:
        'We offer a broad selection of advanced medical equipment for hospitals, clinics, and diagnostic centers.',
      image: '/public/Maria Parham Med.jpeg',
      features: ['Patient monitors', 'Surgical tools', 'Hospital beds', 'Over 500+ products']
    },
    furniture: {
      title: 'Hospital Furniture Solutions',
      description:
        'We provide modular, durable, and ergonomic hospital furniture for every department and room.',
      image: '/public/hospital.jpg',
      features: ['ICU beds', 'Patient chairs', 'Medical trolleys', 'Storage and cabinets']
    },
    pipeline: {
      title: 'Gas Pipeline Installation',
      description:
        'Our expert team ensures compliant, efficient pipeline installation for all medical gas systems.',
      image: '/public/pipeline.jpeg',
      features: ['Oxygen & Vacuum', 'Maintenance included', '24/7 emergency line', 'Safety-first protocol']
    }
  };

  const cards = [
    {
      icon: <Heart className="w-8 h-8 text-[#1d7d69]" />,
      title: 'Medical Equipment Supply',
      description: 'Broad range of medical devices for modern care environments.'
    },
    {
      icon: <Shield className="w-8 h-8 text-[#1d7d69]" />,
      title: 'Installation & Maintenance',
      description: 'End-to-end setup and performance-optimized servicing.'
    },
    {
      icon: <Zap className="w-8 h-8 text-[#1d7d69]" />,
      title: 'Consultation & Procurement',
      description: 'Expert advice to choose and procure the right solution.'
    },
    {
      icon: <CheckCircle className="w-8 h-8 text-[#1d7d69]" />,
      title: 'After-Sales Support',
      description: 'We stand by with support, training, and replacement assistance.'
    }
  ];

  return (
    <>
      <Helmet>
        <title>Services | Sreemeditec</title>
      </Helmet>

      {/* Hero Section */}
      <section className="bg-[#1d7d69] py-20 text-white text-center relative">
        <motion.div initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.6 }}>
          <h1 className="text-4xl md:text-5xl font-bold mb-4">Our Services</h1>
          <p className="text-lg max-w-2xl mx-auto mb-6">
            Comprehensive medical and healthcare infrastructure solutions built for tomorrow’s hospitals.
          </p>
          <button
            onClick={() => setShowModal(true)}
            className="bg-white text-[#1d7d69] px-6 py-2 rounded-md hover:bg-gray-200 font-semibold shadow transition"
          >
            Get Service Support
          </button>
        </motion.div>
      </section>

      {/* Custom Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300">
          <div className="bg-white w-full max-w-md mx-4 sm:mx-0 p-6 rounded-xl shadow-xl relative animate-fadeIn">
            <button
              onClick={() => setShowModal(false)}
              className="absolute top-4 right-4 text-gray-500 hover:text-red-500 transition"
            >
              <X className="w-5 h-5" />
            </button>
            <h2 className="text-2xl font-semibold text-[#1d7d69] mb-1">Request Service Support</h2>
            <p className="text-sm text-gray-500 mb-4">Fill out the form and we’ll respond shortly.</p>
            <form className="space-y-4">
              {[
                { label: 'Name', type: 'text', placeholder: 'Your full name' },
                { label: 'Email', type: 'email', placeholder: 'you@example.com' },
                { label: 'Phone', type: 'tel', placeholder: 'Phone number' }
              ].map((field, idx) => (
                <div key={idx}>
                  <label className="block text-sm font-medium text-gray-700 mb-1">{field.label}</label>
                  <input
                    type={field.type}
                    placeholder={field.placeholder}
                    className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#1d7d69] transition"
                  />
                </div>
              ))}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Issue Description</label>
                <textarea
                  rows={4}
                  placeholder="Describe your issue..."
                  className="w-full border border-gray-300 rounded-lg px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-[#1d7d69] transition"
                ></textarea>
              </div>
              <button
                type="submit"
                className="w-full bg-[#1d7d69] hover:bg-[#166353] text-white font-medium py-2 rounded-lg transition"
              >
                Submit
              </button>
            </form>
          </div>
        </div>
      )}

      {/* Tabs */}
      <section className="bg-white py-10 border-b">
        <div className="flex justify-center space-x-4 sm:space-x-6 flex-wrap px-4">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`flex items-center px-5 py-2 rounded-full text-sm font-medium transition-all duration-200 ${
                activeTab === tab.id
                  ? 'bg-[#1d7d69] text-white shadow-md'
                  : 'bg-gray-100 text-gray-700 hover:bg-[#e6f4f1]'
              }`}
            >
              {tab.icon}
              <span className="ml-2">{tab.label}</span>
            </button>
          ))}
        </div>
      </section>

      {/* Tab Content */}
      <section className="bg-gray-50 py-16">
        <div
          className={`max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center ${
            activeTab === 'furniture' ? 'md:flex-row-reverse' : ''
          }`}
        >
          <motion.div
            initial={{ opacity: 0, x: activeTab === 'furniture' ? -40 : 40 }}
            whileInView={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.7 }}
            className="rounded-xl overflow-hidden shadow-lg"
          >
            <img
              src={tabContent[activeTab].image}
              alt={tabContent[activeTab].title}
              className="w-full h-[350px] object-cover rounded-lg"
            />
          </motion.div>

          <motion.div
            initial={{ opacity: 0, x: activeTab === 'furniture' ? 40 : -40 }}
            whileInView={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.7 }}
            className="space-y-6"
          >
            <h2 className="text-3xl font-bold text-gray-800">{tabContent[activeTab].title}</h2>
            <p className="text-gray-600">{tabContent[activeTab].description}</p>
            <ul className="list-disc pl-6 space-y-1 text-gray-700">
              {tabContent[activeTab].features.map((feature, i) => (
                <li key={i}>{feature}</li>
              ))}
            </ul>
          </motion.div>
        </div>
      </section>

      {/* Service Cards */}
      <section className="py-20 bg-white">
        <div className="text-center mb-12">
          <h2 className="text-3xl font-bold text-gray-800">Comprehensive Service Portfolio</h2>
          <p className="text-gray-600 mt-2">Covering everything from supply to service.</p>
        </div>
        <div className="max-w-6xl mx-auto grid sm:grid-cols-2 lg:grid-cols-4 gap-8 px-4">
          {cards.map((card, index) => (
            <div
              key={index}
              className="bg-gray-50 rounded-xl p-6 text-center shadow hover:shadow-md transition"
            >
              <div className="mb-4">{card.icon}</div>
              <h3 className="text-lg font-semibold text-gray-800">{card.title}</h3>
              <p className="text-sm text-gray-600 mt-2">{card.description}</p>
            </div>
          ))}
        </div>
      </section>

      {/* CTA */}
      <section className="py-20 bg-[#1d7d69] text-white text-center">
        <div className="max-w-xl mx-auto">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">Ready to Start Your Hospital Project?</h2>
          <p className="text-lg mb-6">Contact us for a free consultation. Let’s bring your vision to life.</p>
          <Link to="/contact">
            <button className="bg-white text-[#1d7d69] font-semibold py-3 px-6 rounded hover:bg-gray-100 transition">
              Contact Us <ArrowRight className="inline ml-2 w-5 h-5" />
            </button>
          </Link>
        </div>
      </section>
    </>
  );
};

export default Services;
