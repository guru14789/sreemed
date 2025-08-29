const API_BASE_URL = 'http://localhost:5000/api';

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
        ...(token && { 'x-auth-token': token }),
        ...options.headers,
      },
      ...options,
    };

    try {
      console.log(`Making request to: ${url}`);
      const response = await fetch(url, config);

      const data = await response.json();

      if (!response.ok) {
        console.error(`Request failed: ${response.status} ${response.statusText}`, data);
        throw new Error(data.msg || `Request failed: ${response.status} ${response.statusText}`);
      }

      return data;
    } catch (error) {
      console.error('API request error:', error);
      throw error;
    }
  }

  // Auth methods
  async login(email, password) {
    const response = await this.request('/users/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });

    if (response.token) {
      this.setToken(response.token);
    }

    return response;
  }

  async register(userData) {
    const response = await this.request('/users/register', {
      method: 'POST',
      body: JSON.stringify(userData),
    });

    if (response.token) {
      this.setToken(response.token);
    }

    return response;
  }

  logout() {
    this.removeToken();
  }

  // Orders API
  async getOrders() {
    const response = await this.request('/orders');
    return response;
  }

  async getAllOrders() {
    const response = await this.request('/orders/all');
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
}

export const api = new ApiClient();
export default api;
