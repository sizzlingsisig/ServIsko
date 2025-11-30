<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  provider: { type: Object, default: () => ({}) }
})

const providerProfile = computed(() => props.provider.provider_profile ?? {})
const name = computed(() => props.provider.name ?? '')
const avatar = computed(() => {
  // No profile picture field in provider object—fall back to initials
  const initials = name.value.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) || 'NA'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&background=6d0019&color=fff`
})

const links = computed(() => providerProfile.value.links ?? [])
const skills = computed(() => providerProfile.value.skills ?? [])
const services = computed(() => props.provider.services ?? [])
const bookmarked = ref(false)

const toggleBookmark = () => bookmarked.value = !bookmarked.value
</script>

<template>
  <div v-if="!provider || !provider.name" class="max-w-2xl mx-auto py-20 text-center text-gray-400">
    Loading profile...
  </div>
  <div v-else class="max-w-4xl mx-auto flex gap-6 p-5">
    <!-- Left column -->
    <div class="w-1/4 flex flex-col gap-8 px-[20px]">
      <!-- Profile Pic -->
      <div class="flex items-center justify-center">
        <img
          :src="avatar"
          alt="Avatar"
          class="w-32 h-32 rounded-full object-cover border shadow"
        />
      </div>

      <!-- Bio and Links (bio missing in this sample, so hide) -->
      <div>
        <div class="font-bold text-lg mb-2">Bio</div>
        <div class="text-gray-400 text-sm mb-2">No bio provided.</div>
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
    <div class="flex-1 flex flex-col gap-6 px-[20px]">
      <!-- Name, Bookmark -->
      <div class="flex justify-between items-center">
        <div>
          <div class="font-extrabold text-2xl">{{ name }}</div>
          <!-- No location in sample, so skip -->
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
</template>

<style scoped>
.bg-primary-100 { background-color: #f3e6eb; }
.text-primary-600 { color: #6d0019; }
.bg-primary-600 { background-color: #6d0019; }
.hover\:bg-primary-700:hover { background-color: #490010 !important; }
</style>
