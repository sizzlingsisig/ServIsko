import axios from 'axios'
import router from '@/router'
import { useAuthStore } from '@/stores/AuthStore'
import { useToastStore } from '@/stores/toastStore'

const instance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  timeout: 10000,
})

// Request interceptor
instance.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    const token = authStore.getToken()

    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }

    // Don't force Content-Type for FormData or file uploads
    if (!(config.data instanceof FormData)) {
      config.headers['Content-Type'] = 'application/json'
    }

    config.headers['Accept'] = 'application/json'

    return config
  },
  (error) => Promise.reject(error),
)

// Response interceptor
instance.interceptors.response.use(
  (response) => response,
  (error) => {
    const authStore = useAuthStore()
    const toastStore = useToastStore()

    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      authStore.clearUser()
      toastStore.showError('Session expired. Please login again.', 'Unauthorized')
      router.push({ name: 'login' })
      return Promise.reject(error)
    }

    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      toastStore.showError('You do not have permission to access this resource.', 'Forbidden')
      return Promise.reject(error)
    }

    // Handle 429 Rate Limiting
    if (error.response?.status === 429) {
      toastStore.showWarning('Too many requests. Please try again later.', 'Rate Limited')
      return Promise.reject(error)
    }

    // Handle 500+ Server Errors
    if (error.response?.status >= 500) {
      toastStore.showError(
        error.response?.data?.message || 'Server error. Please try again later.',
        'Server Error',
      )
      return Promise.reject(error)
    }

    // Handle network errors
    if (!error.response) {
      toastStore.showError('Network error. Please check your connection.', 'Connection Error')
      return Promise.reject(error)
    }

    // Let component handle other errors
    return Promise.reject(error)
  },
)

export default instance
