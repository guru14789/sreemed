
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { PlusCircle } from 'lucide-react';
import { toast } from '@/components/ui/use-toast';

const ProductList = () => {
  const handleNotImplemented = () => {
    toast({
      title: "🚧 This feature isn't implemented yet—but don't worry! You can request it in your next prompt! 🚀",
      description: "Product management is coming soon."
    });
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <div>
          <CardTitle>Products</CardTitle>
          <CardDescription>Manage your product inventory.</CardDescription>
        </div>
        <Button size="sm" variant="outline" onClick={handleNotImplemented}>
          <PlusCircle className="h-4 w-4 mr-2" />
          Add Product
        </Button>
      </CardHeader>
      <CardContent>
        <div className="text-center py-10 border-2 border-dashed rounded-lg">
          <p className="text-gray-500">Product Management coming soon.</p>
        </div>
      </CardContent>
    </Card>
  );
};

export default ProductList;
