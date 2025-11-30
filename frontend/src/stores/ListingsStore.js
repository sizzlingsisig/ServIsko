import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/composables/axios'

export const useListingsStore = defineStore('listings', () => {
  // State
  const listings = ref([])
  const total = ref(0)
  const currentPage = ref(1)
  const perPage = ref(15)
  const loading = ref(false)
  const error = ref(null)

  // Computed
  const isEmpty = computed(() => listings.value.length === 0)
  const hasError = computed(() => error.value !== null)

  // Actions
  const fetchListings = async (params = {}) => {
    try {
      loading.value = true
      error.value = null

      // Simple query parameters - no filters for now
      const queryParams = {
        page: params.page || 1,
        per_page: params.per_page || 15,
      }

      const response = await axios.get('/listings', { params: queryParams })

      // Handle Laravel response structure:
      // { success: true, data: { current_page: 1, data: [...], total: 7, per_page: 15 } }
      if (response.data.success) {
        const paginatedData = response.data.data

        // Extract the actual listings array from paginatedData.data
        listings.value = paginatedData.data || []
        total.value = paginatedData.total || 0
        currentPage.value = paginatedData.current_page || 1
        perPage.value = paginatedData.per_page || 15
      } else {
        // Fallback
        listings.value = []
        total.value = 0
      }
    } catch (err) {
      console.error('Error fetching listings:', err)
      error.value = err.response?.data?.message || 'Failed to fetch listings'
      listings.value = []
      total.value = 0
    } finally {
      loading.value = false
    }
  }

  const getListingById = async (id) => {
    try {
      const response = await axios.get(`/listings/${id}`)
      // Handle { success: true, data: {...} }
      if (response.data.success) {
        return response.data.data
      }
      return response.data.data
    } catch (err) {
      console.error('Error fetching listing:', err)
      throw err
    }
  }

  const createListing = async (payload) => {
    try {
      loading.value = true
      const response = await axios.post('/listings', payload)

      // Handle { success: true, message: "...", data: {...} }
      let created = null
      if (response.data.success) {
        created = response.data.data
      } else {
        created = response.data.data
      }

      if (created) {
        // Prepend so user sees it immediately
        listings.value = [created, ...listings.value]
        total.value = total.value + 1
      }

      return created
    } catch (err) {
      console.error('Error creating listing:', err)
      error.value = err.response?.data?.message || 'Failed to create listing'
      throw err
    } finally {
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  return {
    // State
    listings,
    total,
    currentPage,
    perPage,
    loading,
    error,

    // Computed
    isEmpty,
    hasError,

    // Actions
    fetchListings,
    getListingById,
    createListing,
    clearError,
  }
})
