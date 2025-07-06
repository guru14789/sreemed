import React from 'react';
import { CheckCircle, ArrowRight } from 'lucide-react';
import { Link } from 'react-router-dom';

const About = () => {
    const stats = [
        { value: '20+', label: 'Years in Medical Industry' },
        { value: '400+', label: 'Hospitals & Clinics Served' },
        { value: '2000+', label: 'Medical Equipment Delivered' },
        { value: '10+', label: 'Skilled Professionals' }
    ];

    return (
        <section id="about" className="py-[80px] bg-white">
            <div className="max-w-[1200px] mx-auto px-[20px]">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-[60px] items-center">
                    {/* Image */}
                    <div className="relative">
                        <div className="rounded-xl overflow-hidden shadow-2xl">
                            <img
                                src="/hero1.jpeg"
                                alt="About Sreemeditec"
                                className="w-full h-auto object-cover"
                            />
                        </div>

                        {/* Stats (Desktop) */}
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
                    </div>

                    {/* Content */}
                    <div className="space-y-6">
                        <span className="inline-block bg-[#e0f2f1] text-[#1d7d69] text-sm font-medium px-4 py-1 rounded-full">
                            About Sreemeditec
                        </span>

                        <h2 className="text-[32px] leading-snug font-bold text-gray-900">
                            Your Trusted Partner for Technology Solutions Since 2005
                        </h2>

                        <p className="text-gray-600 text-[16px] leading-relaxed">
                            <strong className="text-gray-800 font-medium">Delivering Trust. Powering Care.</strong><br />
                            For over 20 years, Sreemeditec has been at the heart of healthcare—providing hospitals and clinics with reliable, high-performance medical equipment that supports both caregivers and patients.<br /><br />
                            We believe that quality equipment saves lives, and our mission is to ensure every hospital we serve is equipped with the tools they need to deliver exceptional care.
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

                        {/* Stats (Mobile) */}
                        <div className="grid grid-cols-2 gap-4 mt-8 md:hidden">
                            {stats.map((stat, index) => (
                                <div key={index} className="bg-gray-50 rounded-lg p-4 text-center shadow-sm">
                                    <div className="text-[#1d7d69] text-xl font-bold">{stat.value}</div>
                                    <div className="text-gray-500 text-sm">{stat.label}</div>
                                </div>
                            ))}
                        </div>

                        {/* Button */}
                        <div className="pt-6 text-center md:text-left">
                            <Link
                                to="/about"
                                className="inline-flex items-center bg-[#1d7d69] text-white px-6 py-3 rounded-md hover:bg-[#166353] transition-all"
                            >
                                About Us <ArrowRight size={18} className="ml-2" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};

export default About;
