<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

const provider = ref(null)
const loading = ref(true)
const error = ref('')

const route = useRoute()
const providerId = computed(() => route.params.id)

const fetchProvider = async (id) => {
  provider.value = null
  loading.value = true
  error.value = ''
  if (!id) {
    error.value = 'No provider specified.'
    loading.value = false
    return
  }
  try {
    const resp = await fetch(`http://localhost:8000/api/providers/${id}`)
    const data = await resp.json()
    if (data.success && data.data) {
      provider.value = data.data
    } else {
      error.value = data.message || 'Provider not found.'
    }
  } catch (e) {
    error.value = 'Error loading provider.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProvider(providerId.value)
})

watch(providerId, (id) => {
  fetchProvider(id)
})

// --- ProviderDetailsCard logic ---
const providerProfile = computed(() => provider.value?.provider_profile ?? {})
const name = computed(() => provider.value?.name ?? '')
const profile = computed(() => provider.value?.profile ?? {})
const avatar = computed(() => {
  if (profile.value?.profile_picture) {
    // Use the image if present, add domain prefix if needed
    return profile.value.profile_picture.startsWith('/')
      ? `http://localhost:8000${profile.value.profile_picture}`
      : profile.value.profile_picture
  }
  // Otherwise, fallback to generated initials avatar
  const initials = name.value.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'NA'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&background=6d0019&color=fff`
})
const bio = computed(() => profile.value?.bio ?? '')
const location = computed(() => profile.value?.location ?? '')
const links = computed(() => providerProfile.value.links ?? [])
const skills = computed(() => providerProfile.value.skills ?? [])
const services = computed(() => provider.value?.services ?? [])
const bookmarked = ref(false)
const toggleBookmark = () => bookmarked.value = !bookmarked.value
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center py-5">
    <div class="w-full max-w-6xl">
      <!-- Loading skeleton -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-8">
        <div class="animate-pulse w-44 h-44 rounded-full bg-gray-200 mb-6"></div>
        <div class="h-6 w-64 bg-gray-200 mb-2 rounded animate-pulse"></div>
        <div class="h-4 w-48 bg-gray-200 mb-2 rounded animate-pulse"></div>
        <div class="h-4 w-60 bg-gray-200 mb-2 rounded animate-pulse"></div>
        <div class="h-4 w-40 bg-gray-200 rounded animate-pulse"></div>
        <span class="mt-6 text-sm text-gray-400">Loading provider details...</span>
      </div>
      <!-- Error -->
      <div v-else-if="error" class="text-center text-red-500 py-8">
        {{ error }}
      </div>
      <!-- Details Card -->
      <div v-else-if="provider && provider.name" class="w-full flex gap-6 p-5">
        <!-- Left column -->
        <div class="w-1/4 flex flex-col gap-8 px-4">
          <!-- Profile Pic -->
          <div class="flex items-center justify-center">
            <img
              :src="avatar"
              alt="Avatar"
              class="w-32 h-32 rounded-full object-cover border shadow"
            />
          </div>
          <!-- Bio and Links -->
          <div>
            <div class="font-bold text-lg mb-2">Bio</div>
            <div class="text-gray-700 text-sm mb-2">{{ bio || 'No bio provided.' }}</div>
            <div v-if="links.length" class="flex flex-col gap-2 mt-2">
              <div class="font-bold text-xs text-gray-600 mb-1">Links</div>
              <span v-for="link in links" :key="link.id" class="inline-flex items-center gap-2">
                <a :href="link.url" target="_blank" class="text-sm text-primary-600 underline">{{ link.title }}</a>
              </span>
            </div>
          </div>
          <!-- Skills -->
          <div>
            <div class="font-bold text-lg mb-2">Skills</div>
            <div v-if="skills.length" class="flex flex-wrap gap-2">
              <span v-for="skill in skills" :key="skill.id" class="bg-gray-100 text-xs text-gray-700 px-2 py-1 rounded">{{ skill.name }}</span>
            </div>
            <div v-else class="text-xs text-gray-400">No skills listed.</div>
          </div>
        </div>
        <!-- Right column -->
        <div class="flex-1 flex flex-col gap-6 px-4">
          <!-- Name, Location, Bookmark -->
          <div class="flex justify-between items-center">
            <div>
              <div class="font-extrabold text-2xl">{{ name }}</div>
              <div class="text-sm text-gray-500">{{ location || 'No location provided' }}</div>
            </div>
            <button
              @click="toggleBookmark"
              class="bg-gray-100 border border-gray-300 rounded-full px-5 py-2 font-bold text-gray-700 hover:bg-primary-100 transition flex items-center"
            >
              <span v-if="bookmarked" class="pi pi-bookmark text-[#6d0019] mr-2"></span>
              <span v-else class="pi pi-bookmark mr-2"></span>
              <span>{{ bookmarked ? 'Bookmarked' : 'Bookmark' }}</span>
            </button>
          </div>
          <!-- Action Buttons -->
          <div class="flex gap-4">
            <button class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded transition">Send Message</button>
            <button class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded border transition">Report User</button>
          </div>
          <!-- Services Offered -->
          <div>
            <div class="font-bold text-lg mb-2">Services Offered</div>
            <div v-if="services.length">
              <div v-for="(service, i) in services" :key="service.id || i" class="mb-3 border-b pb-3 last:border-none">
                <div class="font-semibold text-md">{{ service.title || 'Service #' + (i + 1) }}</div>
                <div class="text-xs text-gray-600">
                  <span v-if="service.category">{{ service.category.name }}</span>
                  <span v-if="service.price" class="ml-2">₱{{ service.price }}</span>
                </div>
                <div class="text-sm mt-1">{{ service.description }}</div>
              </div>
            </div>
            <div v-else class="text-xs text-gray-400">No services listed.</div>
          </div>
        </div>
      </div>
      <!-- Fallback -->
      <div v-else class="max-w-2xl mx-auto py-20 text-center text-gray-400">
        Loading profile...
      </div>
    </div>
  </div>
</template>

<style scoped>
.bg-primary-100 { background-color: #f3e6eb; }
.text-primary-600 { color: #6d0019; }
.bg-primary-600 { background-color: #6d0019; }
.hover\:bg-primary-700:hover { background-color: #490010 !important; }
</style>
