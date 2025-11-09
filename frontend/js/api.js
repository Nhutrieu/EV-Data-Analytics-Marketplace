// API Configuration
const API_BASE = 'http://localhost/ev-marketplace/api';

// Generic API call function
async function apiCall(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE}/${endpoint}`, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'API call failed');
        }
        
        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Dashboard API
export const dashboardAPI = {
    getStats: () => apiCall('dashboard_stats.php')
};

// Datasets API
export const datasetsAPI = {
    getAll: (search = '') => apiCall(`datasets.php?search=${encodeURIComponent(search)}`),
    getById: (id) => apiCall(`datasets.php?id=${id}`),
    create: (data) => apiCall('datasets.php', {
        method: 'POST',
        body: JSON.stringify(data)
    }),
    update: (id, data) => apiCall('datasets.php', {
        method: 'PUT',
        body: JSON.stringify({ id, ...data })
    }),
    delete: (id) => apiCall('datasets.php', {
        method: 'DELETE',
        body: JSON.stringify({ id })
    })
};

// Pricing Policy API
export const pricingAPI = {
    get: () => apiCall('pricing_policy.php'),
    save: (data) => apiCall('pricing_policy.php', {
        method: 'POST',
        body: JSON.stringify(data)
    })
};

// User Profile API
export const userAPI = {
    getProfile: () => apiCall('user_profile.php'),
    updateProfile: (data) => apiCall('user_profile.php', {
        method: 'PUT',
        body: JSON.stringify(data)
    })
};