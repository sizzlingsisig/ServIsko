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

    // Only try to get toast store if we need it
    let toastStore = null
    try {
      toastStore = useToastStore()
    } catch (e) {
      console.warn('Toast store not available in interceptor')
    }

    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      authStore.clearUser()

      // Check if this is a silent 401 (e.g., checking auth status)
      const isSilent = error.config?.silent === true

      if (!isSilent && toastStore) {
        const toast = toastStore.toast || toastStore
        toast.add({
          severity: 'warn',
          summary: 'Session Expired',
          detail: 'Please log in to continue.',
          life: 4000,
        })
      }

      // Only redirect if not already on public pages
      const publicRoutes = ['login', 'register', 'home', 'about']
      const currentRoute = router.currentRoute.value.name

      if (!publicRoutes.includes(currentRoute)) {
        router.push({ name: 'login' })
      }

      return Promise.reject(error)
    }

    // Handle 403 Forbidden
    if (error.response?.status === 403) {
      if (toastStore) {
        const toast = toastStore.toast || toastStore
        toast.add({
          severity: 'error',
          summary: 'Forbidden',
          detail: 'You do not have permission to access this resource.',
          life: 5000,
        })
      }
      return Promise.reject(error)
    }

    // Handle 429 Rate Limiting
    if (error.response?.status === 429) {
      if (toastStore) {
        const toast = toastStore.toast || toastStore
        toast.add({
          severity: 'warn',
          summary: 'Rate Limited',
          detail: 'Too many requests. Please try again later.',
          life: 4000,
        })
      }
      return Promise.reject(error)
    }

    // Handle 500+ Server Errors
    if (error.response?.status >= 500) {
      if (toastStore) {
        const toast = toastStore.toast || toastStore
        toast.add({
          severity: 'error',
          summary: 'Server Error',
          detail: error.response?.data?.message || 'Server error. Please try again later.',
          life: 5000,
        })
      }
      return Promise.reject(error)
    }

    // Handle network errors
    if (!error.response) {
      if (toastStore) {
        const toast = toastStore.toast || toastStore
        toast.add({
          severity: 'error',
          summary: 'Connection Error',
          detail: 'Network error. Please check your connection.',
          life: 5000,
        })
      }
      return Promise.reject(error)
    }

    // Let component handle other errors (like 404, 422 validation errors)
    return Promise.reject(error)
  },
)

export default instance
