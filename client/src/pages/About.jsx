import React from 'react';
import { Users, Award, TrendingUp } from 'lucide-react';
import { motion } from 'framer-motion';

const AboutPage = () => {
  const values = [
    {
      icon: <Users size={28} />,
      title: 'Quality & Reliability',
      description: 'We prioritize delivering only the highest quality, most reliable medical equipment to help healthcare professionals provide the best patient care.'
    },
    {
      icon: <Award size={28} />,
      title: 'Customer-Centric Service',
      description: 'We are committed to supporting our clients with personalized service, maintenance, and expert guidance to ensure their systems run flawlessly.'
    },
    {
      icon: <TrendingUp size={28} />,
      title: 'Scalable Solutions',
      description: 'Our clients trust us for the durability and consistency of our equipment—tools healthcare teams can count on when it matters most.'
    }
  ];

  return (
    <div className="min-h-screen flex flex-col bg-white">
      <main className="flex-grow">
        {/* Hero Section */}
        <section className="bg-[#1F8F76] text-white py-24 px-6">
          <div className="max-w-5xl mx-auto text-center space-y-6">
            <motion.h1
              initial={{ opacity: 0, y: -20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              className="text-4xl md:text-5xl font-extrabold tracking-tight"
            >
              About Sreemeditec
            </motion.h1>
            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: 0.2 }}
              className="text-lg md:text-xl max-w-3xl mx-auto text-white/90"
            >
              Founded in 2005, we’ve empowered hospitals and clinics with cutting-edge medical equipment
              that supports both patients and caregivers.
            </motion.p>
          </div>
        </section>

        {/* Our Story Section */}
        <section className="py-24 bg-white px-6">
          <div className="max-w-6xl mx-auto grid lg:grid-cols-2 gap-16 items-center">
            <motion.div
              initial={{ opacity: 0, x: -50 }}
              whileInView={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8 }}
              viewport={{ once: true }}
            >
              <img
                src="/public/meet.jpeg"
                alt="Our Story"
                className="rounded-2xl shadow-lg w-full object-cover max-h-[500px]"
              />
            </motion.div>

            <motion.div
              initial={{ opacity: 0, x: 50 }}
              whileInView={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8 }}
              viewport={{ once: true }}
              className="space-y-6"
            >
              <h1 className="text-4xl font-extrabold text-[#1F8F76]">Our Story</h1>
              <h3 className="text-2xl font-semibold text-gray-800">Delivering Trust. Powering Care.</h3>
              <p className="text-gray-700 text-lg leading-relaxed">
                At Sreemeditec, we’re driven by a simple mission: enable healthcare providers with dependable,
                high-performing medical equipment. For nearly two decades, our expertise has helped institutions
                improve patient outcomes, reduce downtime, and ensure safety.
              </p>
              <p className="text-gray-700 text-lg leading-relaxed">
                Our track record of serving 400+ healthcare facilities and delivering 2000+ equipment units has
                made us one of the most trusted names in the medical equipment industry.
              </p>
            </motion.div>
          </div>
        </section>

        {/* Core Values Section */}
        <section className="py-24 bg-gray-50 px-6">
          <div className="max-w-6xl mx-auto text-center">
            <motion.h2
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
              viewport={{ once: true }}
              className="text-3xl md:text-4xl font-bold text-[#1F8F76] mb-6"
            >
              Our Core Values
            </motion.h2>
            <p className="text-gray-600 text-lg max-w-xl mx-auto mb-16">
              These principles guide our service and reflect our commitment to your care.
            </p>

            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-10">
              {values.map(({ icon, title, description }, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 40 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.6, delay: index * 0.2 }}
                  viewport={{ once: true }}
                  className="bg-white p-8 rounded-2xl shadow-md hover:shadow-xl transition duration-300 text-left border-t-4 border-[#1F8F76]"
                >
                  <div className="mb-4 inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#d0f0ea] text-[#1F8F76]">
                    {icon}
                  </div>
                  <h3 className="text-xl font-semibold text-gray-800 mb-2">{title}</h3>
                  <p className="text-gray-600 text-[15px] leading-relaxed">{description}</p>
                </motion.div>
              ))}
            </div>
          </div>
        </section>
      </main>
    </div>
  );
};

export default AboutPage;
