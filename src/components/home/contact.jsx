import React from 'react';
import { Phone, Mail, MapPin, ArrowRight } from 'lucide-react';
import { Link } from 'react-router-dom';

const Contact = () => {
    return (
        <section id="contact" className="py-[80px] bg-[#f9fafb]">
            <div className="max-w-[1200px] mx-auto px-5">
                {/* Header */}
                <div className="text-center max-w-[700px] mx-auto mb-16">
                    <h2 className="text-[34px] font-bold text-gray-800 mb-4">Get In Touch</h2>
                    <p className="text-[17px] text-gray-600 leading-relaxed">
                        Looking for quality medical equipment? Contact us for a free consultation today!
                    </p>
                </div>

                {/* Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    {/* Phone */}
                    <div className="bg-white rounded-xl shadow-md hover:shadow-xl p-6 text-center transition-all duration-300 transform hover:-translate-y-1 group">
                        <div className="w-16 h-16 mx-auto flex items-center justify-center bg-[#e0f2f1] text-[#1d7d69] rounded-full mb-4 transition-transform duration-300 group-hover:scale-110">
                            <Phone size={28} />
                        </div>
                        <h3 className="text-xl font-semibold text-gray-800 mb-2">Call Us</h3>
                        <p className="text-gray-600 mb-4 text-[15px]">Our team is here to help during business hours.</p>
                        <a href="tel:+919884818398" className="text-[#1d7d69] font-medium hover:underline text-[15px]">
                            +91 98848 18398
                        </a>
                    </div>

                    {/* Email */}
                    <div className="bg-white rounded-xl shadow-md hover:shadow-xl p-6 text-center transition-all duration-300 transform hover:-translate-y-1 group">
                        <div className="w-16 h-16 mx-auto flex items-center justify-center bg-[#e0f2f1] text-[#1d7d69] rounded-full mb-4 transition-transform duration-300 group-hover:scale-110">
                            <Mail size={28} />
                        </div>
                        <h3 className="text-xl font-semibold text-gray-800 mb-2">Email Us</h3>
                        <p className="text-gray-600 mb-4 text-[15px]">Get in touch with our support team.</p>
                        <a
                            href="https://mail.google.com/mail/?view=cm&fs=1&to=sreemeditec@gmail.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-[#1d7d69] font-medium hover:underline text-[15px]"
                        >
                            sreemeditec@gmail.com
                        </a>
                    </div>

                    {/* Location */}
                    <div className="bg-white rounded-xl shadow-md hover:shadow-xl p-6 text-center transition-all duration-300 transform hover:-translate-y-1 group">
                        <div className="w-16 h-16 mx-auto flex items-center justify-center bg-[#e0f2f1] text-[#1d7d69] rounded-full mb-4 transition-transform duration-300 group-hover:scale-110">
                            <MapPin size={28} />
                        </div>
                        <h3 className="text-xl font-semibold text-gray-800 mb-2">Visit Us</h3>
                        <p className="text-gray-600 mb-4 text-[15px]">Come by our office.</p>
                        <address className="not-italic text-[#1d7d69] font-medium text-[15px] leading-relaxed">
                            No:18/2, Bajanai Koil Street,<br />
                            Rajakilpakkam, Chennai-73,<br />
                            Tamil Nadu, India
                        </address>
                    </div>
                </div>

                {/* Contact Button */}
                <div className="mt-16 text-center">
                    <Link
                        to="/contact"
                        className="inline-flex items-center bg-[#1d7d69] text-white px-6 py-3 rounded-md hover:bg-[#166353] transition-all text-[15px] font-medium"
                    >
                        Contact <ArrowRight size={18} className="ml-2" />
                    </Link>
                </div>
            </div>
        </section>
    );
};

export default Contact;
