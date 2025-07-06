import React, { useState } from 'react';
import { Helmet } from 'react-helmet-async';
import { motion } from 'framer-motion';
import { Send, FileText, User, Mail, Phone, Building } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useAuth } from '@/contexts/AuthContext';
import { toast } from '@/components/ui/use-toast';

const QuoteRequest = () => {
  const { user } = useAuth();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [formData, setFormData] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '',
    company: '',
    equipmentType: '',
    quantity: '',
    budget: '',
    timeline: '',
    requirements: '',
    additionalInfo: ''
  });

  const equipmentTypes = [
    'Cardiology Equipment',
    'Surgical Instruments',
    'Diagnostic Equipment',
    'Patient Monitoring Systems',
    'ICU Equipment',
    'Laboratory Equipment',
    'Radiology Equipment',
    'Emergency Equipment',
    'Hospital Furniture',
    'Other (Please specify in requirements)'
  ];

  const budgetRanges = [
    'Under ₹50,000',
    '₹50,000 - ₹1,00,000',
    '₹1,00,000 - ₹5,00,000',
    '₹5,00,000 - ₹10,00,000',
    '₹10,00,000 - ₹25,00,000',
    'Above ₹25,00,000'
  ];

  const timelineOptions = [
    'Immediate (Within 1 week)',
    'Within 1 month',
    'Within 3 months',
    'Within 6 months',
    'More than 6 months'
  ];

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSelectChange = (name, value) => {
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      // Simulate form submission
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Save quote request to localStorage
      const quotes = JSON.parse(localStorage.getItem('sreemeditec_quotes') || '[]');
      const newQuote = {
        id: Date.now().toString(),
        ...formData,
        userId: user?.id || null,
        timestamp: new Date().toISOString(),
        status: 'pending'
      };
      quotes.push(newQuote);
      localStorage.setItem('sreemeditec_quotes', JSON.stringify(quotes));

      // Also save to messages for admin dashboard
      const messages = JSON.parse(localStorage.getItem('sreemeditec_messages') || '[]');
      const newMessage = {
        id: Date.now().toString(),
        name: formData.name,
        email: formData.email,
        phone: formData.phone,
        company: formData.company,
        service: 'Request Quote',
        message: `Equipment: ${formData.equipmentType}\nQuantity: ${formData.quantity}\nBudget: ${formData.budget}\nTimeline: ${formData.timeline}\n\nRequirements: ${formData.requirements}\n\nAdditional Info: ${formData.additionalInfo}`,
        timestamp: new Date().toISOString(),
        status: 'new'
      };
      messages.push(newMessage);
      localStorage.setItem('sreemeditec_messages', JSON.stringify(messages));

      toast({
        title: "Quote request submitted!",
        description: "We'll review your requirements and get back to you within 24 hours with a detailed quote.",
      });

      // Reset form
      setFormData({
        name: user?.name || '',
        email: user?.email || '',
        phone: user?.phone || '',
        company: '',
        equipmentType: '',
        quantity: '',
        budget: '',
        timeline: '',
        requirements: '',
        additionalInfo: ''
      });
    } catch (error) {
      toast({
        title: "Failed to submit quote request",
        description: "Please try again later.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <>
      <Helmet>
        <title>Request Quote - Get Custom Medical Equipment Pricing | Sreemeditec</title>
        <meta name="description" content="Request a custom quote for medical equipment from Sreemeditec. Get competitive pricing and expert recommendations for your healthcare facility." />
      </Helmet>

      <div className="min-h-screen bg-gray-50 py-12">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="space-y-8"
          >
            {/* Header */}
            <div className="text-center space-y-4">
              <div className="w-16 h-16 mx-auto bg-gradient-to-br from-teal-600 to-teal-700 rounded-full flex items-center justify-center">
                <FileText className="w-8 h-8 text-white" />
              </div>
              <h1 className="text-3xl font-bold text-gray-900">Request a Quote</h1>
              <p className="text-gray-600 max-w-2xl mx-auto">
                Get a customized quote for your medical equipment needs. Our experts will provide competitive pricing and recommendations tailored to your requirements.
              </p>
            </div>

            {/* Quote Form */}
            <Card className="shadow-xl border-0">
              <CardHeader>
                <CardTitle>Tell us about your requirements</CardTitle>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                  {/* Contact Information */}
                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold text-gray-900">Contact Information</h3>
                    <div className="grid md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="name">Full Name *</Label>
                        <div className="relative">
                          <User className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                          <Input
                            id="name"
                            name="name"
                            value={formData.name}
                            onChange={handleInputChange}
                            required
                            placeholder="Enter your full name"
                            className="pl-10"
                          />
                        </div>
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="email">Email Address *</Label>
                        <div className="relative">
                          <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                          <Input
                            id="email"
                            name="email"
                            type="email"
                            value={formData.email}
                            onChange={handleInputChange}
                            required
                            placeholder="Enter your email"
                            className="pl-10"
                          />
                        </div>
                      </div>
                    </div>

                    <div className="grid md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="phone">Phone Number *</Label>
                        <div className="relative">
                          <Phone className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                          <Input
                            id="phone"
                            name="phone"
                            value={formData.phone}
                            onChange={handleInputChange}
                            required
                            placeholder="Enter your phone number"
                            className="pl-10"
                          />
                        </div>
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="company">Company/Hospital Name</Label>
                        <div className="relative">
                          <Building className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                          <Input
                            id="company"
                            name="company"
                            value={formData.company}
                            onChange={handleInputChange}
                            placeholder="Enter company or hospital name"
                            className="pl-10"
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Equipment Requirements */}
                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold text-gray-900">Equipment Requirements</h3>
                    
                    <div className="grid md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="equipmentType">Equipment Type *</Label>
                        <Select value={formData.equipmentType} onValueChange={(value) => handleSelectChange('equipmentType', value)}>
                          <SelectTrigger>
                            <SelectValue placeholder="Select equipment type" />
                          </SelectTrigger>
                          <SelectContent>
                            {equipmentTypes.map((type) => (
                              <SelectItem key={type} value={type}>
                                {type}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="quantity">Quantity Required</Label>
                        <Input
                          id="quantity"
                          name="quantity"
                          value={formData.quantity}
                          onChange={handleInputChange}
                          placeholder="e.g., 5 units, 1 set"
                        />
                      </div>
                    </div>

                    <div className="grid md:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="budget">Budget Range</Label>
                        <Select value={formData.budget} onValueChange={(value) => handleSelectChange('budget', value)}>
                          <SelectTrigger>
                            <SelectValue placeholder="Select budget range" />
                          </SelectTrigger>
                          <SelectContent>
                            {budgetRanges.map((range) => (
                              <SelectItem key={range} value={range}>
                                {range}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="timeline">Required Timeline</Label>
                        <Select value={formData.timeline} onValueChange={(value) => handleSelectChange('timeline', value)}>
                          <SelectTrigger>
                            <SelectValue placeholder="Select timeline" />
                          </SelectTrigger>
                          <SelectContent>
                            {timelineOptions.map((option) => (
                              <SelectItem key={option} value={option}>
                                {option}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                    </div>
                  </div>

                  {/* Detailed Requirements */}
                  <div className="space-y-4">
                    <h3 className="text-lg font-semibold text-gray-900">Detailed Requirements</h3>
                    
                    <div className="space-y-2">
                      <Label htmlFor="requirements">Specific Requirements *</Label>
                      <Textarea
                        id="requirements"
                        name="requirements"
                        value={formData.requirements}
                        onChange={handleInputChange}
                        required
                        placeholder="Please describe your specific requirements, features needed, technical specifications, etc."
                        rows={4}
                      />
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="additionalInfo">Additional Information</Label>
                      <Textarea
                        id="additionalInfo"
                        name="additionalInfo"
                        value={formData.additionalInfo}
                        onChange={handleInputChange}
                        placeholder="Any additional information that might help us provide a better quote (installation requirements, training needs, warranty preferences, etc.)"
                        rows={3}
                      />
                    </div>
                  </div>

                  <Button
                    type="submit"
                    disabled={isSubmitting}
                    className="w-full btn-primary text-lg py-3"
                  >
                    {isSubmitting ? (
                      <>
                        <div className="loading-spinner mr-2"></div>
                        Submitting Request...
                      </>
                    ) : (
                      <>
                        <Send className="w-5 h-5 mr-2" />
                        Submit Quote Request
                      </>
                    )}
                  </Button>
                </form>
              </CardContent>
            </Card>

            {/* What Happens Next */}
            <Card>
              <CardContent className="p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">What happens next?</h3>
                <div className="grid md:grid-cols-3 gap-6">
                  <div className="text-center space-y-2">
                    <div className="w-12 h-12 mx-auto bg-teal-100 rounded-full flex items-center justify-center">
                      <span className="text-teal-600 font-bold">1</span>
                    </div>
                    <h4 className="font-medium">Review</h4>
                    <p className="text-sm text-gray-600">Our experts review your requirements within 2 hours</p>
                  </div>
                  <div className="text-center space-y-2">
                    <div className="w-12 h-12 mx-auto bg-teal-100 rounded-full flex items-center justify-center">
                      <span className="text-teal-600 font-bold">2</span>
                    </div>
                    <h4 className="font-medium">Quote</h4>
                    <p className="text-sm text-gray-600">Receive a detailed quote with recommendations within 24 hours</p>
                  </div>
                  <div className="text-center space-y-2">
                    <div className="w-12 h-12 mx-auto bg-teal-100 rounded-full flex items-center justify-center">
                      <span className="text-teal-600 font-bold">3</span>
                    </div>
                    <h4 className="font-medium">Consultation</h4>
                    <p className="text-sm text-gray-600">Schedule a consultation to discuss your needs in detail</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </motion.div>
        </div>
      </div>
    </>
  );
};

export default QuoteRequest;