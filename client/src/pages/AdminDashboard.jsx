import React from 'react';
import { Helmet } from 'react-helmet-async';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { LayoutDashboard, ShoppingCart, Package, Users } from 'lucide-react';
import DashboardStats from '@/components/admin/DashboardStats';
import OrderList from '@/components/admin/OrderList';
import ProductList from '@/components/admin/ProductList';
import UserList from '@/components/admin/UserList';

const AdminDashboard = () => {
  return (
    <>
      <Helmet>
        <title>Admin Dashboard - Sreemeditec</title>
        <meta name="description" content="Manage Sreemeditec store, orders, products, and users." />
      </Helmet>

      <div className="min-h-screen bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
            <p className="text-gray-600">Welcome back, Admin!</p>
          </div>

          <Tabs defaultValue="dashboard" className="w-full">
            <TabsList className="grid w-full grid-cols-2 md:grid-cols-4">
              <TabsTrigger value="dashboard">
                <LayoutDashboard className="w-4 h-4 mr-2" />
                Dashboard
              </TabsTrigger>
              <TabsTrigger value="orders">
                <ShoppingCart className="w-4 h-4 mr-2" />
                Orders
              </TabsTrigger>
              <TabsTrigger value="products">
                <Package className="w-4 h-4 mr-2" />
                Products
              </TabsTrigger>
              <TabsTrigger value="users">
                <Users className="w-4 h-4 mr-2" />
                Users
              </TabsTrigger>
            </TabsList>
            
            <TabsContent value="dashboard" className="mt-6">
              <DashboardStats />
            </TabsContent>
            <TabsContent value="orders" className="mt-6">
              <OrderList />
            </TabsContent>
            <TabsContent value="products" className="mt-6">
              <ProductList />
            </TabsContent>
            <TabsContent value="users" className="mt-6">
              <UserList />
            </TabsContent>
          </Tabs>
        </div>
      </div>
    </>
  );
};

export default AdminDashboard;