import React from 'react';
import { Code, PenTool, Settings, Cloud, ArrowRight } from 'lucide-react';
import { Link } from 'react-router-dom';

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

const Services = () => {
    return (
        <section id="services" className="py-[80px] bg-[#f9fafb]">
            <div className="max-w-[1200px] mx-auto px-5">
                {/* Section Header */}
                <div className="text-center max-w-[700px] mx-auto mb-16">
                    <h2 className="text-[34px] font-bold text-gray-800 mb-4">Our Services</h2>
                    <p className="text-gray-600 text-[17px] leading-relaxed">
                        We offer a range of high-quality medical equipment and services to help healthcare professionals deliver exceptional care to their patients.
                    </p>
                </div>

                {/* Service Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    {serviceItems.map((service) => (
                        <div
                            key={service.id}
                            className="bg-white rounded-2xl shadow-md hover:shadow-2xl transition duration-300 p-6 group"
                        >
                            <div className="mb-5 transition-transform duration-300 group-hover:-translate-y-1">
                                {service.icon}
                            </div>
                            <h3 className="text-[20px] font-semibold text-gray-800 mb-2">
                                {service.title}
                            </h3>
                            <p className="text-[15px] text-gray-600 mb-4 leading-relaxed">
                                {service.description}
                            </p>
                            <Link
                                to={service.link}
                                className="inline-flex items-center text-[#1d7d69] hover:underline font-semibold text-sm"
                            >
                                Learn more <ArrowRight size={16} className="ml-1" />
                            </Link>
                        </div>
                    ))}
                </div>

                {/* View All Button */}
                <div className="mt-16 text-center">
                    <Link
                        to="/services"
                        className="inline-flex items-center bg-[#1d7d69] text-white text-[15px] font-medium px-6 py-3 rounded-md hover:bg-[#166353] transition-all duration-300"
                    >
                        View All Services <ArrowRight size={18} className="ml-2" />
                    </Link>
                </div>
            </div>
        </section>
    );
};

export default Services;
