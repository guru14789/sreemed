
import React from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { toast } from '@/components/ui/use-toast';

const UserList = () => {
  const handleNotImplemented = () => {
    toast({
      title: "🚧 This feature isn't implemented yet—but don't worry! You can request it in your next prompt! 🚀",
      description: "User management is coming soon."
    });
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>Users</CardTitle>
        <CardDescription>Manage registered customers.</CardDescription>
      </CardHeader>
      <CardContent>
        <div className="text-center py-10 border-2 border-dashed rounded-lg">
          <p className="text-gray-500">User Management coming soon.</p>
        </div>
      </CardContent>
    </Card>
  );
};

export default UserList;
