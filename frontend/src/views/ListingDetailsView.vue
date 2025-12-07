<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/composables/axios'
import { useAuthStore } from '@/stores/AuthStore'
import { useToastStore } from '@/stores/toastStore'

const authStore = useAuthStore()
const toastStore = useToastStore()

const listingId = ref(1) // TODO: get from props/route
const listing = ref(null)
const loading = ref(true)
const isApplied = ref(false)
const isMessaged = ref(false)

const canApply = computed(() => {
  if (!listing.value) return false
  return (
    !listing.value.is_expired &&
    !isApplied.value &&
    authStore.isAuthenticated &&
    authStore.user?.id !== listing.value.seeker_user_id
  )
})

const canMessage = computed(() => authStore.isAuthenticated && !isMessaged.value)
const canReport = computed(() => authStore.isAuthenticated)

const fetchListing = async () => {
  loading.value = true
  try {
    const response = await api.get(`/listings/${listingId.value}`)
    if (response.data.success && response.data.data) {
      listing.value = response.data.data
    } else {
      toastStore.showError('Listing not found')
      listing.value = null
    }
  } catch (error) {
    toastStore.showError('Failed to fetch listing')
    listing.value = null
  } finally {
    loading.value = false
  }
}

const applyToListing = async () => {
  try {
    const response = await api.post(`/listings/${listing.value.id}/apply`)
    if (response.data.success) {
      isApplied.value = true
      toastStore.showSuccess('Applied successfully!')
    } else {
      toastStore.showError(response.data.message || 'Failed to apply')
    }
  } catch (err) {
    toastStore.showError('Application failed')
  }
}

const messageOwner = async () => {
  try {
    const response = await api.post(`/listings/${listing.value.id}/message`)
    if (response.data.success) {
      isMessaged.value = true
      toastStore.showSuccess('Message sent!')
    } else {
      toastStore.showError(response.data.message || 'Failed to send message')
    }
  } catch (err) {
    toastStore.showError('Failed to send message')
  }
}

const reportListing = async () => {
  try {
    const response = await api.post(`/listings/${listing.value.id}/report`)
    if (response.data.success) {
      toastStore.showSuccess('Listing reported!')
    } else {
      toastStore.showError(response.data.message || 'Report failed')
    }
  } catch (err) {
    toastStore.showError('Report failed')
  }
}

function statusClass(status) {
  if (status === 'expired') return 'bg-gray-300 text-gray-700';
  if (status === 'active') return 'bg-green-100 text-green-700';
  return 'bg-yellow-100 text-yellow-700';
}

onMounted(() => {
  fetchListing()
})
</script>

<template>
  <div v-if="loading" class="max-w-4xl mx-auto flex justify-center items-center h-96 bg-white shadow-md rounded-2xl mt-10">
    <i class="pi pi-spin pi-spinner text-4xl text-primary-500"></i>
  </div>
  <div v-else-if="listing" class="w-full flex flex-col md:flex-row items-stretch">
    <!-- Left column: Post Info -->
    <section class="flex-1 min-w-0 m-0">
      <div class="max-w-4xl bg-white rounded-2xl mx-auto my-6 px-6 md:px-12 py-8 shadow-lg border border-gray-100">
        <header class="mb-5">
          <h1 class="text-3xl md:text-4xl font-bold leading-tight text-primary-500">{{ listing.title }}</h1>
          <div class="flex flex-wrap items-center gap-3 mt-2">
            <span v-if="listing.category"
                  class="px-2 py-1 inline-block bg-gray-100 text-xs text-gray-600 rounded font-semibold">
              {{ listing.category.name }}
            </span>
            <span v-if="listing.status"
                  :class="['px-2 py-1 rounded font-semibold text-xs', statusClass(listing.status)]">
              {{ listing.status.charAt(0).toUpperCase() + listing.status.slice(1) }}
            </span>
          </div>
        </header>
        <div class="space-y-5">
          <div class="bg-gray-50 rounded-xl border px-5 py-4">
            <h2 class="font-bold text-lg text-primary-500 mb-2">About this listing</h2>
            <p class="text-gray-800 whitespace-pre-line leading-relaxed">{{ listing.description }}</p>
          </div>
          <div v-if="listing.budget" class="bg-white border rounded-xl px-5 py-3 flex flex-col items-start mt-5">
            <span class="font-semibold text-primary-500 text-base mb-2">Budget</span>
            <span class="text-2xl font-extrabold mb-0 tracking-wide">
              ₱{{ parseInt(listing.budget).toLocaleString() }}
            </span>
          </div>
        </div>
      </div>
    </section>
    <!-- Right column: Seeker Info and Actions -->
    <aside class="w-full md:w-80 flex flex-col gap-4 items-stretch max-w-xs md:mt-10 md:mr-40 md:ml-auto">
      <div class="bg-primary-500 text-white rounded-2xl py-7 px-6 shadow-lg flex flex-col items-center gap-5 border border-gray-200 relative">
        <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 rounded-full bg-white border border-primary-500 shadow p-2 w-16 h-16 flex items-center justify-center">
          <i class="pi pi-user text-primary-500 text-3xl"></i>
        </div>
        <div class="mt-8 w-full text-left space-y-1">
          <h3 class="text-lg font-bold mb-2 tracking-wide text-white">Listing Owner</h3>
          <div v-if="listing.seeker" class="space-y-1">
            <div class="flex items-center gap-2">
              <span class="font-semibold block">Name:</span>
              <span>{{ listing.seeker.name }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="font-semibold block">Username:</span>
              <span>@{{ listing.seeker.username }}</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="font-semibold block">Email:</span>
              <span>{{ listing.seeker.email }}</span>
            </div>
          </div>
        </div>
        <div class="w-full mt-3 flex flex-col gap-2">
          <button
            class="flex items-center justify-center gap-2 bg-white text-primary-500 border border-primary-500 hover:bg-gray-50 font-semibold py-3 px-4 rounded-full w-full shadow-md transition duration-150 ease-in-out text-base focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 disabled:opacity-60"
            :disabled="!canApply"
            @click="applyToListing"
          >
            <i class="pi pi-check-circle text-lg"></i>
            <span>{{ isApplied ? 'Applied!' : 'Apply to this listing' }}</span>
          </button>
          <button
            class="flex items-center justify-center gap-2 border border-white text-white font-semibold py-3 px-4 rounded-full w-full shadow transition duration-150 ease-in-out bg-transparent hover:bg-white/10 text-base focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 disabled:opacity-60"
            :disabled="!canMessage"
            @click="messageOwner"
          >
            <i class="pi pi-envelope text-lg"></i>
            <span>{{ isMessaged ? 'Message Sent' : 'Send Message to Owner' }}</span>
          </button>
        </div>
      </div>
      <a
        href="#"
        class="block text-xs text-[#c32f2f] hover:underline text-center mt-2 font-semibold"
        @click.prevent="reportListing"
        v-if="canReport"
      >
        <i class="pi pi-exclamation-circle"></i>
        Report Listing
      </a>
    </aside>
  </div>
  <div v-else class="max-w-4xl mx-auto p-8 text-center mt-10 bg-white shadow rounded-2xl">
    <i class="pi pi-inbox text-5xl text-gray-300 mb-4 block"></i>
    <p class="text-gray-600 text-lg">Listing not found.</p>
  </div>
</template>

<style scoped>
aside {
  flex-shrink: 0;
}
</style>
