import axios from 'axios'
import router from '@/router'
import { useAuthStore } from '@/stores/AuthStore'
import { useToastStore } from '@/stores/toastStore'

const instance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers:{
    'Cache-Control': 'no-cache',
  },
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
    const toast = useToastStore().getToast ? useToastStore().getToast() : useToastStore().toast || useToastStore()

    // Handle 401 Unauthorized

    if (error.response?.status === 401) {
      authStore.clearUser()
      toast.add({
        severity: 'error',
        summary: 'Unauthorized',
        detail: 'Session expired. Please login again.',
        life: 5000,
      })
      router.push({ name: 'login' })
      return Promise.reject(error)
    }

    // Handle 403 Forbidden

    if (error.response?.status === 403) {
      toast.add({
        severity: 'error',
        summary: 'Forbidden',
        detail: 'You do not have permission to access this resource.',
        life: 5000,
      })
      return Promise.reject(error)
    }

    // Handle 429 Rate Limiting

    if (error.response?.status === 429) {
      toast.add({
        severity: 'warn',
        summary: 'Rate Limited',
        detail: 'Too many requests. Please try again later.',
        life: 4000,
      })
      return Promise.reject(error)
    }

    // Handle 500+ Server Errors

    if (error.response?.status >= 500) {
      toast.add({
        severity: 'error',
        summary: 'Server Error',
        detail: error.response?.data?.message || 'Server error. Please try again later.',
        life: 5000,
      })
      return Promise.reject(error)
    }

    // Handle network errors

    if (!error.response) {
      toast.add({
        severity: 'error',
        summary: 'Connection Error',
        detail: 'Network error. Please check your connection.',
        life: 5000,
      })
      return Promise.reject(error)
    }

    // Let component handle other errors
    return Promise.reject(error)
  },
)

export default instance
