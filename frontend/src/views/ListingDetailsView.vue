<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/composables/axios'
import ApplicationMessageModal from '@/components/ApplicationMessageModal.vue'
import { useAuthStore } from '@/stores/AuthStore'

const authStore = useAuthStore()

const route = useRoute()
const listing = ref(null)
const loading = ref(true)
const error = ref(null)
const roles = ref([])

const applying = ref(false)
const hasApplied = ref(false)
const isOwner = ref(false)

// Modal state for application
const showApplyModal = ref(false)
const submittingApplication = ref(false)

function openApplyModal() {
  showApplyModal.value = true
}

function closeApplyModal() {
  showApplyModal.value = false
}
// Check if user already applied to this listing
async function checkIfApplied() {
  if (!authStore.isAuthenticated || !listing.value?.id) {
    hasApplied.value = false
    return
  }
  try {
    // Get all applications for this user (paginated)
    const resp = await api.get('/provider/applications', { params: { per_page: 100 } })
    if (resp.data.success && Array.isArray(resp.data.data?.data)) {
      hasApplied.value = resp.data.data.data.some(app => app.listing_id === listing.value.id)
    } else {
      hasApplied.value = false
    }
  } catch (e) {
    hasApplied.value = false
  }
}

async function handleApplicationSubmit(message) {
  if (!authStore.isAuthenticated) {
    // e.g. show toast / redirect to login
    console.warn('Not authenticated')
    return
  }
  if (!listing.value?.id || hasApplied.value) return
  submittingApplication.value = true
  try {
    const endpoint = `/provider/listings/${listing.value.id}/applications`
    const payload = {
      message: message
    }
    const response = await api.post(endpoint, payload)
    if (response.data.success) {
      console.log('Application created:', response.data.data)
      hasApplied.value = true
      showApplyModal.value = false
    } else {
      console.error('Application failed:', response.data.message)
    }
  } catch (e) {
    if (e.response?.status === 409) {
      hasApplied.value = true
      showApplyModal.value = false
    }
    console.error('Application error:', e.response?.data || e)
  } finally {
    submittingApplication.value = false
  }
}


const currentUserId = ref(null)

async function getUserInfo() {
  const response = await api.get('/user')
  if (response.data && response.data.data?.id) {
    currentUserId.value = response.data.data.id   // note: your JSON wraps in data.data
  }
}

async function fetchListing() {
  loading.value = true
  error.value = null
  try {
    const endpoint = `/listings/${route.params.id}`
    const response = await api.get(endpoint)

    if (response.data.success) {
      listing.value = response.data.data

      // ownership check using ref
      isOwner.value =
        currentUserId.value === (listing.value.seeker_user_id || listing.value.user_id)

      if (authStore.isAuthenticated) {
        await checkIfApplied()
      }
    } else {
      throw new Error(response.data.message || 'Failed to fetch listing')
    }
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Failed to load listing'
  } finally {
    loading.value = false
  }
}

async function fetchProfile() {
  try {
    const response = await api.get('/provider/profile')
    console.log('Profile response raw:', response.data)

    if (response.data.success) {
      // adjust depending on your API shape
      const profileData = response.data      // or response.data.data
      roles.value = Array.isArray(profileData.roles) ? profileData.roles : []
      console.log('Fetched profile roles:', roles.value)
      console.log('isAuthenticated:', authStore.isAuthenticated)
    }
  } catch (e) {
    console.error('Profile fetch error status:', e.response?.status)
    console.error('Profile fetch error data:', e.response?.data)
  }
}

onMounted(async () => {
  if (authStore.isAuthenticated) {
    await getUserInfo()
  }
  await fetchListing()
  if (authStore.isAuthenticated) {
    fetchProfile()
  }
})

// Watch for login state or listing change to re-check application
import { watch } from 'vue'
watch([() => authStore.isAuthenticated, () => listing.value?.id], ([isAuth, id]) => {
  if (isAuth && id) checkIfApplied()
  else hasApplied.value = false
})
</script>

<template>
  <!-- Application Modal -->
  <ApplicationMessageModal
    :visible="showApplyModal"
    :submitting="submittingApplication"
    @update:visible="val => showApplyModal = val"
    @submit="handleApplicationSubmit"
  />

  <div v-if="loading" class="flex flex-col justify-center items-center min-h-screen w-full p-8 text-center bg-white">
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
            {{ tag.name }}
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
            <p class="text-gray-700 leading-relaxed break-words whitespace-pre-line max-h-[8.5em] overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 10; line-clamp: 10; -webkit-box-orient: vertical;">
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
          <div class="bg-[#6d0019] rounded-lg shadow-md p-6">
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



            <router-link
              v-if="roles?.includes('service-provider') && isOwner"
              :to="`/listings/${listing.id}/applications`"
              class="w-full bg-blue-100 text-blue-900 font-bold py-3 px-4 rounded-lg mb-3 flex items-center justify-center gap-2 hover:bg-blue-200 transition-colors"
              title="View applications for this listing."
            >
              <i class="pi pi-eye"></i>
              <span>View Applications</span>
            </router-link>
            <button
              v-else-if="roles?.includes('service-provider') && !isOwner"
              :class="[
                'w-full font-bold py-3 px-4 rounded-lg transition-colors mb-3 flex items-center justify-center gap-2',
                hasApplied
                  ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  : 'bg-white hover:bg-gray-100 text-[#6d0019]'
              ]"
              :disabled="applying || hasApplied"
              @click="openApplyModal"
            >
              <i class="pi pi-send"></i>
              <span v-if="hasApplied">Already Applied</span>
              <span v-else>{{ applying ? 'Applying...' : 'Apply To Listing' }}</span>
            </button>

            <router-link
              v-else-if="roles?.includes('service-provider') && isOwner"
              :to="`/listings/${listing.id}/applications`"
              class="w-full bg-blue-100 text-blue-900 font-bold py-3 px-4 rounded-lg mb-3 flex items-center justify-center gap-2 hover:bg-blue-200 transition-colors"
            >
              <i class="pi pi-eye"></i>
              <span>View Applications</span>
            </router-link>

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
              <span>Coming Soon</span>
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
