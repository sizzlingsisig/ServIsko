<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  provider: { type: Object, required: true },
  layout: { type: String, default: 'grid' }
})

const router = useRouter()

const profile = computed(() => props.provider.profile ?? {})
const providerProfile = computed(() => props.provider.provider_profile ?? {})
const name = computed(() => props.provider.name ?? '')
const location = computed(() => profile.value.location ?? '')
const bio = computed(() => profile.value.bio ?? '')
const avatar = computed(() => {
  const pic = profile.value.profile_picture
  if (pic) {
    // If already absolute URL, use as is; else prefix your domain
    return pic.startsWith('http') ? pic : `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}${pic}`
  }
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name.value)}&background=6d0019&color=fff`
})
const skills = computed(() => providerProfile.value.skills ?? [])
const links = computed(() => providerProfile.value.links ?? [])

const showPopover = ref(false)

const mainSkills = computed(() => skills.value.slice(0, 3))
const extraSkills = computed(() => skills.value.slice(3))

const allSkillNames = computed(() =>
  skills.value.map(skill => skill.name).join(', ')
)

const goToDetails = () => {
  router.push(`/providers/${props.provider.id}`)
}
</script>

<template>
  <div class="bg-white rounded-lg border border-gray-300 shadow p-0 relative flex flex-col h-full">
    <!-- Header -->
    <div class="bg-primary-500 rounded-t-lg px-4 py-3 flex items-center gap-3">
      <img :src="avatar" alt="Avatar" class="w-11 h-11 rounded-full object-cover border border-white shadow" />
      <div class="flex-1">
        <div class="font-bold text-lg text-white truncate">{{ name }}</div>
        <div v-if="location" class="text-xs text-white opacity-80 truncate">{{ location }}</div>
      </div>
    </div>
    <hr class="border-t border-gray-200 mx-4" />
    <div class="px-4 py-3 flex flex-col flex-1">
      <!-- Skills with popover on extra -->
      <div v-if="skills.length" class="mb-3 flex flex-wrap items-center gap-2">
        <span class="font-semibold text-xs text-gray-600 mr-2">Skills:</span>
        <template v-for="skill in mainSkills" :key="skill.id">
          <span class="inline-block bg-gray-100 px-2 py-1 rounded text-xs text-gray-700">{{ skill.name }}</span>
        </template>
        <span
          v-if="extraSkills.length"
          class="inline-block bg-gray-200 px-2 py-1 rounded text-xs text-gray-500 cursor-pointer"
          @mouseenter="showPopover = true"
          @mouseleave="showPopover = false"
          style="position:relative;"
        >
          ...<span class="ml-1">+{{ extraSkills.length }}</span>
          <!-- Popover -->
          <div
            v-if="showPopover"
            class="absolute z-10 left-1/2 transform -translate-x-1/2 mt-2 w-max min-w-[160px] bg-white border border-gray-300 shadow-lg rounded p-2 text-xs"
            style="white-space:normal;"
          >
            <div class="mb-1 font-bold text-gray-700">All Skills:</div>
            <span v-for="skill in skills" :key="skill.id" class="inline-block bg-gray-100 px-2 py-1 rounded mr-1 mb-1">{{ skill.name }}</span>
          </div>
        </span>
      </div>

      <!-- Bio -->
      <div v-if="bio" class="mb-3 text-gray-700 text-sm">{{ bio }}</div>

      <!-- Links -->
      <div v-if="links.length" class="mb-3 flex flex-wrap items-center gap-2">
        <span class="font-semibold text-xs text-gray-600 mr-2">Links:</span>
        <span v-for="link in links" :key="link.id" class="inline-block">
          <a :href="link.url" target="_blank" class="text-xs text-primary-500 underline px-1">{{ link.title }}</a>
        </span>
      </div>

      <!-- View Profile button at bottom right -->
      <div class="flex-1"></div>
      <div class="flex justify-end mt-3">
        <button
          class="bg-black text-white px-4 py-2 text-sm font-semibold rounded hover:bg-gray-800 transition"
          @click.prevent="goToDetails"
        >
          View Profile
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.bg-primary-500 {
  background: #6d0019;
}
.min-w-\[160px\] {
  min-width: 160px;
}
</style>
