<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/composables/axios'
import { useAuthStore } from '@/stores/AuthStore'

const authStore = useAuthStore()

const route = useRoute()
const listing = ref(null)
const loading = ref(true)
const error = ref(null)
const roles = ref([])

async function fetchListing() {
  loading.value = true
  error.value = null
  try {
    const id = route.params.id
    const endpoint = authStore.isAuthenticated
      ? `/seeker/listings/${id}`
      : `/listings/${id}`
    const response = await api.get(endpoint)

    if (response.data.success) {
      listing.value = response.data.data
      roles.value = listing.value.user_current_role || []
      console.log('Fetched listing:', listing.value)
    } else {
      throw new Error(response.data.message || 'Failed to fetch listing')
    }
  } catch (e) {
    console.error('Fetch error:', e)
    error.value = e.response?.data?.message || e.message || 'Failed to load listing'
  } finally {
    loading.value = false
  }
}

onMounted(fetchListing)
</script>

<template>
  <div v-if="loading" class="p-8 text-center">
    <i class="pi pi-spin pi-spinner text-4xl text-primary-500"></i>
    <p class="mt-4 text-gray-600">Loading listing...</p>
  </div>
  <div v-else-if="listing" class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6">
      <!-- Breadcrumbs - More subtle -->
      <nav class="flex items-center text-sm text-gray-500 mb-6">
        <router-link to="/" class="hover:text-[#6d0019] transition">
          <i class="pi pi-home"></i>
        </router-link>
        <i class="pi pi-angle-right text-gray-400 text-xs mx-2"></i>
        <router-link to="/listings" class="hover:text-[#6d0019] transition"> Listings </router-link>
        <i class="pi pi-angle-right text-gray-400 text-xs mx-2"></i>
        <span class="text-gray-400 truncate max-w-xs">{{ listing.title }}</span>
      </nav>

      <!-- Listing Header - Cleaner -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-[#6d0019] mb-3">{{ listing.title }}</h1>

        <div class="flex items-center gap-4 mb-4">
          <!-- Avatar -->
          <div
            class="w-12 h-12 bg-[#6d0019] text-white rounded-full flex items-center justify-center font-bold text-lg"
          >
            {{ listing.owner_name?.charAt(0)?.toUpperCase() || 'U' }}
          </div>

          <!-- User Info -->
          <div>
            <div class="font-semibold text-gray-900 text-lg">
              {{ listing.owner_name || 'Unknown User' }}
            </div>
            <div class="flex items-center gap-2 text-sm">
              <i class="pi pi-star-fill text-yellow-400"></i>
              <span class="font-semibold">{{ listing.user?.rating || '0.0' }}</span>
              <span class="text-gray-500">({{ listing.user?.review_count || 0 }} reviews)</span>
            </div>
          </div>
        </div>

        <!-- Tags - More subtle -->
        <div v-if="listing.tags && listing.tags.length > 0" class="flex flex-wrap gap-2">
          <span
            v-for="tag in listing.tags"
            :key="tag.id"
            class="px-3 py-1 bg-pink-50 text-[#6d0019] rounded-full text-xs font-medium border border-pink-200"
          >
            #{{ tag.name }}
          </span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Content -->
        <div class="lg:col-span-2 space-y-4">
          <!-- About Section - Simplified -->
          <div class="bg-white rounded-lg border border-gray-200 p-6 pb-50">
            <div class="flex items-center gap-2 mb-4">
              <i class="pi pi-info-circle text-[#6d0019]"></i>
              <h2 class="text-lg font-bold text-gray-900 ">About this listing</h2>
            </div>
            <p class="text-gray-700 leading-relaxed break-words whitespace-pre-line">
              {{ listing.description || 'No description provided.' }}
            </p>
          </div>

          <!-- Info Cards - Side by side, minimal -->
          <div class="grid grid-cols-2 gap-4">
            <!-- Category -->
            <div v-if="listing.category" class="bg-white rounded-lg border border-gray-200 p-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-pink-50 flex items-center justify-center">
                  <i class="pi pi-folder text-[#6d0019]"></i>
                </div>
                <div>
                  <div class="text-xs text-gray-500 mb-0.5">Category</div>
                  <div class="font-semibold text-gray-900 text-sm">{{ listing.category.name }}</div>
                </div>
              </div>
            </div>

            <!-- Status -->
            <div class="bg-white rounded-lg border border-gray-200 p-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                  <i class="pi pi-check-circle text-green-600"></i>
                </div>
                <div>
                  <div class="text-xs text-gray-500 mb-0.5">Status</div>
                  <div class="font-semibold text-green-600 text-sm">Active</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Timeline - Cleaner -->
          <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center gap-2 mb-4">
              <i class="pi pi-clock text-[#6d0019]"></i>
              <h3 class="text-lg font-bold text-gray-900">Timeline</h3>
            </div>
            <div class="space-y-3">
              <!-- Created -->
              <div class="flex items-center justify-between py-2">
                <div class="flex items-center gap-3">
                  <i class="pi pi-calendar-plus text-gray-400"></i>
                  <span class="text-gray-700 font-medium text-sm">Created</span>
                </div>
                <span class="font-semibold text-gray-900 text-sm">
                  {{
                    new Date(listing.created_at).toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'short',
                      day: 'numeric',
                    })
                  }}
                </span>
              </div>

              <!-- Expires -->
              <div v-if="listing.expires_at" class="flex items-center justify-between py-2">
                <div class="flex items-center gap-3">
                  <i class="pi pi-calendar-times text-red-400"></i>
                  <span class="text-gray-700 font-medium text-sm">Expires</span>
                </div>
                <span class="font-semibold text-red-600 text-sm">
                  {{
                    new Date(listing.expires_at).toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'short',
                      day: 'numeric',
                    })
                  }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Pricing Card -->
        <div class="lg:col-span-1">
          <!-- Main Pricing Card -->
          <div class="bg-[#6d0019] rounded-lg shadow-md p-6 sticky top-6">
            <div class="mb-6">
              <div class="text-sm text-pink-100 mb-1">Budget</div>
              <div class="text-4xl font-bold text-white">
                ₱{{ listing.budget?.toLocaleString() || '0.00' }}
              </div>
            </div>

            <div class="space-y-3 mb-6 pb-6 border-b border-pink-900/30">
              <div class="flex justify-between items-center text-sm">
                <span class="text-pink-100">Delivery Time</span>
                <span class="font-semibold text-white">To be discussed</span>
              </div>
              <div class="flex justify-between items-center text-sm">
                <span class="text-pink-100">Revisions</span>
                <span class="font-semibold text-white">To be discussed</span>
              </div>
            </div>

            <button
              v-if="roles?.includes('service-provider')"
              class="w-full bg-white hover:bg-gray-100 text-[#6d0019] font-bold py-3 px-4 rounded-lg transition-colors mb-3 flex items-center justify-center gap-2"
            >
              <i class="pi pi-send"></i>
              <span>Apply To Listing</span>
            </button>
            <button
              v-else
              class="w-full bg-white text-[#6d0019] font-bold py-3 px-4 rounded-lg mb-3 flex items-center justify-center gap-2 opacity-50 cursor-not-allowed"
              disabled
              title="Only authenticated service providers can apply to listings."
            >
              <i class="pi pi-send"></i>
              <span>Apply To Listing</span>
            </button>

            <button
              class="w-full bg-transparent border-2 border-white/30 hover:border-white text-white font-semibold py-3 px-4 rounded-lg transition-all flex items-center justify-center gap-2"
            >
              <i class="pi pi-bookmark"></i>
              <span>Save Listing</span>
            </button>
          </div>

          <!-- Info Card - Below pricing -->
          <div class="bg-blue-50 rounded-lg border border-blue-200 p-5 mt-4">
            <div class="flex gap-3">
              <i class="pi pi-info-circle text-blue-600 text-xl flex-shrink-0 mt-1"></i>
              <div>
                <h4 class="font-bold text-gray-900 mb-2 text-sm">Need more information?</h4>
                <p class="text-xs text-gray-600 mb-3 leading-relaxed">
                  Contact the provider to discuss your specific requirements and get a customized
                  quote.
                </p>
                <button
                v-if="roles?.includes('service-provider')"
                  class="text-[#6d0019] hover:text-[#590015] font-semibold text-xs flex items-center gap-1 transition"
                >
                  <span>Send a message</span>
                  <i class="pi pi-arrow-right text-xs"></i>
                </button>
                <button
                  v-else
                  class="text-[#6d0019] font-semibold text-xs flex items-center gap-1 opacity-50 cursor-not-allowed"
                  disabled
                  title="Only authenticated service providers can contact the provider."
                >
                  <span>Send a message</span>
                  <i class="pi pi-arrow-right text-xs"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
