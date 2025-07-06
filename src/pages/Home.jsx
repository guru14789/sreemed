import React from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, CheckCircle, Code, PenTool, Settings, Cloud, Phone, Mail, MapPin } from 'lucide-react';
import { motion } from 'framer-motion';

// Animation Variants
const fadeInUp = {
  initial: { opacity: 0, y: 50 },
  whileInView: { opacity: 1, y: 0 },
  transition: { duration: 0.6, ease: 'easeOut' }
};

const staggerContainer = {
  hidden: {},
  show: {
    transition: {
      staggerChildren: 0.2
    }
  }
};

const fadeIn = (delay = 0) => ({
  initial: { opacity: 0, y: 20 },
  whileInView: { opacity: 1, y: 0 },
  transition: { duration: 0.6, delay }
});

const Home = () => {
  const stats = [
    { value: '20+', label: 'Years in Medical Industry' },
    { value: '400+', label: 'Hospitals & Clinics Served' },
    { value: '2000+', label: 'Medical Equipment Delivered' },
    { value: '10+', label: 'Skilled Professionals' }
  ];

  const serviceItems = [
    {
      id: 'software',
      icon: <Code size={40} className="text-[#1d7d69]" />,
      title: "Medical Equipment Supply",
      description: "We offer a broad selection of advanced medical equipment for hospitals.",
      link: '/services#software'
    },
    {
      id: 'design',
      icon: <PenTool size={40} className="text-[#1d7d69]" />,
      title: "Installation & Maintenance",
      description: "Our trained technicians ensure proper installation and provide ongoing maintenance.",
      link: '/services#design'
    },
    {
      id: 'consulting',
      icon: <Settings size={40} className="text-[#1d7d69]" />,
      title: "Consultation & Procurement",
      description: "We help healthcare providers make informed decisions with expert consultation.",
      link: '/services#consulting'
    },
    {
      id: 'cloud',
      icon: <Cloud size={40} className="text-[#1d7d69]" />,
      title: "After-Sales Support",
      description: "We provide reliable after-sales service, including training and technical assistance.",
      link: '/services#cloud'
    }
  ];

  return (
    <>
      {/* Hero Section */}
      <motion.div className="relative bg-gradient-to-r from-[#1d7d69] to-[#21b095] text-white overflow-hidden" {...fadeInUp}>
        <div className="absolute inset-0 opacity-10">
          <div className="absolute right-0 bottom-0 w-1/2 h-1/2 bg-white rounded-full transform translate-x-1/2 translate-y-1/2"></div>
          <div className="absolute left-0 top-0 w-1/3 h-1/3 bg-white rounded-full transform -translate-x-1/2 -translate-y-1/2"></div>
        </div>

        <div className="container mx-auto relative">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-10 items-center min-h-[540px] sm:min-h-[570px] md:min-h-[600px] lg:min-h-[630px] py-12 sm:py-16 lg:py-20">
            <motion.div className="space-y-6" {...fadeIn(0.2)}>
              <h1 className="text-3xl md:text-5xl lg:text-6xl font-bold leading-tight">
                Medical Equipment Solutions to Power Modern Healthcare 
              </h1>
              <p className="text-lg md:text-xl opacity-90 max-w-lg">
                We deliver cutting-edge reliable medical equipment for today’s healthcare needs..
              </p>
              <div className="flex flex-wrap gap-4">
                <Link to="/contact" className="bg-white text-[#1d7d69] hover:bg-gray-100 font-medium py-3 px-6 rounded-md transition-all">
                  Get Started
                </Link>
                <Link to="/store" className="flex items-center text-white border border-white bg-transparent hover:bg-white/10 font-medium py-3 px-6 rounded-md transition-all">
                  Our Store <ArrowRight size={16} className="ml-2" />
                </Link>
              </div>
            </motion.div>

            <motion.div className="flex justify-center lg:justify-end" {...fadeIn(0.4)}>
              <img 
                src="/download.jpeg" 
                alt="Technology Solutions" 
                className="rounded-lg shadow-xl max-w-full h-auto"
                style={{ maxHeight: '400px' }} 
              />
            </motion.div>
          </div>
        </div>
      </motion.div>

      {/* About Section */}
      <motion.section id="about" className="py-[80px] bg-white" {...fadeInUp}>
        <div className="max-w-[1200px] mx-auto px-[20px]">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-[60px] items-center">
            <motion.div className="relative" {...fadeIn(0.2)}>
              <div className="rounded-xl overflow-hidden shadow-2xl">
                <img src="/hero1.jpeg" alt="About Sreemeditec" className="w-full h-auto object-cover" />
              </div>
              <div className="hidden md:flex absolute -bottom-6 -right-6 bg-[#1d7d69] text-white rounded-xl p-6 shadow-xl">
                <div className="grid grid-cols-2 gap-6">
                  {stats.map((stat, index) => (
                    <div key={index} className="text-center">
                      <div className="text-2xl font-bold">{stat.value}</div>
                      <div className="text-sm opacity-80">{stat.label}</div>
                    </div>
                  ))}
                </div>
              </div>
            </motion.div>

            <motion.div className="space-y-6" {...fadeIn(0.3)}>
              <span className="inline-block bg-[#e0f2f1] text-[#1d7d69] text-sm font-medium px-4 py-1 rounded-full">
                About Sreemeditec
              </span>
              <h2 className="text-[32px] leading-snug font-bold text-gray-900">
                Your Trusted Partner for Technology Solutions Since 2005
              </h2>
              <p className="text-gray-600 text-[16px] leading-relaxed">
                <strong className="text-gray-800 font-medium">Delivering Trust. Powering Care.</strong><br />
                For over 20 years, Sreemeditec has been at the heart of healthcare...
              </p>
              <div className="space-y-3">
                {[
                  'Trusted supplier of certified medical technologies',
                  'Dedicated after-sales service & technical support',
                  'Scalable solutions for clinics to multi-specialty hospitals'
                ].map((point, i) => (
                  <div key={i} className="flex items-start">
                    <CheckCircle size={20} className="text-[#1d7d69] mt-1 mr-2 flex-shrink-0" />
                    <p className="text-gray-700">{point}</p>
                  </div>
                ))}
              </div>
              <div className="pt-6 text-center md:text-left">
                <Link to="/about" className="inline-flex items-center bg-[#1d7d69] text-white px-6 py-3 rounded-md hover:bg-[#166353] transition-all">
                  About Us <ArrowRight size={18} className="ml-2" />
                </Link>
              </div>
            </motion.div>
          </div>
        </div>
      </motion.section>

      {/* Services Section */}
      <motion.section id="services" className="py-[80px] bg-[#f9fafb]" {...fadeInUp}>
        <div className="max-w-[1200px] mx-auto px-5">
          <motion.div className="text-center max-w-[700px] mx-auto mb-16" {...fadeIn(0.2)}>
            <h2 className="text-[34px] font-bold text-gray-800 mb-4">Our Services</h2>
            <p className="text-gray-600 text-[17px] leading-relaxed">
              We offer a range of high-quality medical equipment and services to help healthcare professionals...
            </p>
          </motion.div>

          <motion.div variants={staggerContainer} initial="hidden" whileInView="show" viewport={{ once: true }} className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            {serviceItems.map((service, i) => (
              <motion.div key={service.id} variants={fadeIn(i * 0.1)} className="bg-white rounded-2xl shadow-md hover:shadow-2xl transition duration-300 p-6 group">
                <div className="mb-5 transition-transform duration-300 group-hover:-translate-y-1">
                  {service.icon}
                </div>
                <h3 className="text-[20px] font-semibold text-gray-800 mb-2">{service.title}</h3>
                <p className="text-[15px] text-gray-600 mb-4 leading-relaxed">{service.description}</p>
                <Link to={service.link} className="inline-flex items-center text-[#1d7d69] hover:underline font-semibold text-sm">
                  Learn more <ArrowRight size={16} className="ml-1" />
                </Link>
              </motion.div>
            ))}
          </motion.div>

          <motion.div className="mt-16 text-center" {...fadeIn(0.4)}>
            <Link to="/services" className="inline-flex items-center bg-[#1d7d69] text-white text-[15px] font-medium px-6 py-3 rounded-md hover:bg-[#166353] transition-all duration-300">
              View All Services <ArrowRight size={18} className="ml-2" />
            </Link>
          </motion.div>
        </div>
      </motion.section>

      {/* Contact Section */}
      <motion.section id="contact" className="py-[80px] bg-[#f9fafb]" {...fadeInUp}>
        <div className="max-w-[1200px] mx-auto px-5">
          <motion.div className="text-center max-w-[700px] mx-auto mb-16" {...fadeIn(0.2)}>
            <h2 className="text-[34px] font-bold text-gray-800 mb-4">Get In Touch</h2>
            <p className="text-[17px] text-gray-600 leading-relaxed">
              Looking for quality medical equipment? Contact us for a free consultation today!
            </p>
          </motion.div>

          <motion.div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8" variants={staggerContainer} initial="hidden" whileInView="show" viewport={{ once: true }}>
            {[{
              icon: <Phone size={28} />,
              title: "Call Us",
              desc: "Our team is here to help during business hours.",
              link: "tel:+919884818398",
              text: "+91 98848 18398"
            }, {
              icon: <Mail size={28} />,
              title: "Email Us",
              desc: "Get in touch with our support team.",
              link: "mailto:sreemeditec@gmail.com",
              text: "sreemeditec@gmail.com"
            }, {
              icon: <MapPin size={28} />,
              title: "Visit Us",
              desc: "Come by our office.",
              address: `No:18/2, Bajanai Koil Street,\nRajakilpakkam, Chennai-73,\nTamil Nadu, India`
            }].map((item, i) => (
              <motion.div key={i} className="bg-white rounded-xl shadow-md hover:shadow-xl p-6 text-center transition-all duration-300 transform hover:-translate-y-1 group" variants={fadeIn(i * 0.1)}>
                <div className="w-16 h-16 mx-auto flex items-center justify-center bg-[#e0f2f1] text-[#1d7d69] rounded-full mb-4 transition-transform duration-300 group-hover:scale-110">
                  {item.icon}
                </div>
                <h3 className="text-xl font-semibold text-gray-800 mb-2">{item.title}</h3>
                <p className="text-gray-600 mb-4 text-[15px]">{item.desc}</p>
                {item.link ? (
                  <a href={item.link} className="text-[#1d7d69] font-medium hover:underline text-[15px]">
                    {item.text}
                  </a>
                ) : (
                  <address className="not-italic text-[#1d7d69] font-medium text-[15px] leading-relaxed">
                    {item.address}
                  </address>
                )}
              </motion.div>
            ))}
          </motion.div>

          <motion.div className="mt-16 text-center" {...fadeIn(0.3)}>
            <Link to="/contact" className="inline-flex items-center bg-[#1d7d69] text-white px-6 py-3 rounded-md hover:bg-[#166353] transition-all text-[15px] font-medium">
              Contact <ArrowRight size={18} className="ml-2" />
            </Link>
          </motion.div>
        </div>
      </motion.section>
    </>
  );
};

export default Home;
