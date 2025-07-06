const API_BASE_URL = import.meta.env.VITE_API_URL || '/api';

class ApiClient {
  constructor() {
    this.baseURL = API_BASE_URL;
  }

  getToken() {
    return localStorage.getItem('token');
  }

  setToken(token) {
    localStorage.setItem('token', token);
  }

  removeToken() {
    localStorage.removeItem('token');
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const token = this.getToken();

    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...(token && { Authorization: `Bearer ${token}` }),
        ...options.headers,
      },
      ...options,
    };

    try {
      console.log(`Making request to: ${url}`);
      const response = await fetch(url, config);

      if (!response.ok) {
        const errorText = await response.text();
        console.error(`Request failed: ${response.status} ${response.statusText}`, errorText);
        throw new Error(`Request failed: ${response.status} ${response.statusText}`);
      }

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('API request error:', error);
      throw error;
    }
  }

  // Auth methods
  async login(email, password) {
    const response = await this.request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });

    if (response.success && response.token) {
      this.setToken(response.token);
    }

    return response;
  }

  async register(userData) {
    const response = await this.request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData),
    });

    if (response.success) {
      this.setToken(response.token);
    }

    return response;
  }

  async logout() {
    await this.request('/auth/logout', { method: 'POST' });
    this.removeToken();
  }

  async getProfile() {
    return this.request('/auth/profile');
  }

  async updateProfile(userData) {
    return this.request('/auth/profile', {
      method: 'PUT',
      body: JSON.stringify(userData),
    });
  }

  async getCart() {
    const response = await this.request('/cart');
    return response;
  }

  async addToCart(productId, quantity = 1) {
    const response = await this.request('/cart', {
      method: 'POST',
      body: JSON.stringify({
        product_id: productId,
        quantity: quantity
      }),
    });
    return response;
  }

  async updateCartItem(itemId, quantity) {
    const response = await this.request(`/cart/${itemId}`, {
      method: 'PUT',
      body: JSON.stringify({ quantity }),
    });
    return response;
  }

  async removeFromCart(itemId) {
    const response = await this.request(`/cart/${itemId}`, {
      method: 'DELETE',
    });
    return response;
  }

  // Orders API
  async getOrders() {
    const response = await this.request('/orders');
    return response;
  }

  async getOrder(orderId) {
    const response = await this.request(`/orders/${orderId}`);
    return response;
  }

  async createOrder(orderData) {
    const response = await this.request('/orders', {
      method: 'POST',
      body: JSON.stringify(orderData),
    });
    return response;
  }

  async updateOrderStatus(orderId, status) {
    const response = await this.request(`/orders/${orderId}`, {
      method: 'PUT',
      body: JSON.stringify({ status }),
    });
    return response;
  }

  // Products API
  async getProducts(filters = {}) {
    const params = new URLSearchParams(filters);
    const response = await this.request(`/products?${params}`);
    return response;
  }

  async getProduct(productId) {
    const response = await this.request(`/products/${productId}`);
    return response;
  }

  async createProduct(productData) {
    const response = await this.request('/products', {
      method: 'POST',
      body: JSON.stringify(productData),
    });
    return response;
  }

  async updateProduct(productId, productData) {
    const response = await this.request(`/products/${productId}`, {
      method: 'PUT',
      body: JSON.stringify(productData),
    });
    return response;
  }

  async deleteProduct(productId) {
    const response = await this.request(`/products/${productId}`, {
      method: 'DELETE',
    });
    return response;
  }

  // Users API (Admin only)
  async getUsers() {
    const response = await this.request('/users');
    return response;
  }

  async getUser(userId) {
    const response = await this.request(`/users/${userId}`);
    return response;
  }

  async updateUser(userId, userData) {
    const response = await this.request(`/users/${userId}`, {
      method: 'PUT',
      body: JSON.stringify(userData),
    });
    return response;
  }

  async deleteUser(userId) {
    const response = await this.request(`/users/${userId}`, {
      method: 'DELETE',
    });
    return response;
  }

  // Contact API
  async submitContact(contactData) {
    const response = await this.request('/contact', {
      method: 'POST',
      body: JSON.stringify(contactData),
    });
    return response;
  }
}

export const api = new ApiClient();
export default api;