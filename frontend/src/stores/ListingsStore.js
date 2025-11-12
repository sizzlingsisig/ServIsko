import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/composables/axios'

export const useListingsStore = defineStore('listings', () => {
  // State
  const listings = ref([])
  const total = ref(0)
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

      // Build query parameters
      const queryParams = {
        page: params.page || 1,
        per_page: params.per_page || 12,
        search: params.search || undefined,
        sort_by: params.sort_by || 'newest',
      }

      // Add filters if provided
      if (params.categories && params.categories.length > 0) {
        queryParams.categories = params.categories.join(',')
      }

      if (params.min_budget !== undefined && params.min_budget > 0) {
        queryParams.min_budget = params.min_budget
      }

      if (params.max_budget !== undefined && params.max_budget > 0) {
        queryParams.max_budget = params.max_budget
      }

      if (params.min_rating !== undefined && params.min_rating > 0) {
        queryParams.min_rating = params.min_rating
      }

      // Remove undefined values
      Object.keys(queryParams).forEach(
        (key) => queryParams[key] === undefined && delete queryParams[key],
      )

      const { data } = await axios.get('/listings', { params: queryParams })

      listings.value = data.data || []
      total.value = data.total || 0
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
      const { data } = await axios.get(`/listings/${id}`)
      return data.data
    } catch (err) {
      console.error('Error fetching listing:', err)
      throw err
    }
  }

  const createListing = async (payload) => {
    try {
      loading.value = true
      const { data } = await axios.post('/listings', payload)
      const created = data.data

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
