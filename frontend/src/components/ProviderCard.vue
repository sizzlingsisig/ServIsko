<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  provider: { type: Object, required: true },
  layout: { type: String, default: 'grid' }
})

const router = useRouter()
const showSkillsTooltip = ref(false)

const profile = computed(() => props.provider.profile ?? {})
const providerProfile = computed(() => props.provider.provider_profile ?? {})
const name = computed(() => props.provider.name ?? '')
const location = computed(() => profile.value.location ?? '')
const bio = computed(() => profile.value.bio ?? '')
const avatar = computed(() => {
  const pic = profile.value.profile_picture
  if (pic) {
    return pic.startsWith('http') ? pic : `${import.meta.env.VITE_API_URL || 'http://localhost:8000'}${pic}`
  }
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name.value)}&background=6d0019&color=fff`
})
const skills = computed(() => providerProfile.value.skills ?? [])

const visibleSkills = computed(() => skills.value.slice(0, 3))
const remainingSkillsCount = computed(() => Math.max(0, skills.value.length - 3))

const goToDetails = () => {
  router.push(`/providers/${props.provider.id}`)
}
</script>

<template>
  <div
    class="group bg-white rounded-xl border border-gray-200 hover:border-primary-400 hover:shadow-lg cursor-pointer flex flex-col h-full transition-all duration-200"
    @click="goToDetails"
  >
    <!-- Provider Info Section -->
    <div class="px-4 py-3 flex flex-col flex-1 gap-4">
      <!-- Header with Avatar & Name -->
      <div class="flex items-center gap-3">
        <img
          :src="avatar"
          alt="Avatar"
          class="w-12 h-12 rounded-full object-cover border-2 border-primary-100 shadow-sm flex-shrink-0"
        />
        <div class="flex-1 min-w-0">
          <h3 class="text-lg font-semibold text-gray-900 truncate" tabindex="0">
            {{ name }}
          </h3>
          <p v-if="location" class="text-xs text-gray-500 truncate">
            {{ location }}
          </p>
        </div>
      </div>

      <!-- Bio -->
      <p v-if="bio" class="text-gray-600 text-sm leading-relaxed line-clamp-3" tabindex="0">
        {{ bio }}
      </p>

      <!-- Skills with Hover Tooltip -->
      <div v-if="skills.length" class="space-y-2">
        <span class="text-xs font-semibold text-gray-600">Skills</span>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="skill in visibleSkills"
            :key="skill.id"
            class="bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded-md"
          >
            {{ skill.name }}
          </span>
          <div
            v-if="remainingSkillsCount > 0"
            class="relative inline-block"
            @mouseenter="showSkillsTooltip = true"
            @mouseleave="showSkillsTooltip = false"
          >
            <span
              class="bg-gray-200 text-gray-600 text-xs px-2.5 py-1 rounded-md cursor-help"
            >
              +{{ remainingSkillsCount }} more
            </span>
            <div
              v-if="showSkillsTooltip"
              class="absolute left-0 top-full mt-2 z-50 bg-white border border-gray-300 rounded-lg shadow-xl p-3 min-w-[200px] max-w-[280px]"
              style="pointer-events: none;"
            >
              <div class="text-xs font-semibold text-gray-700 mb-2">All Skills</div>
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="skill in skills"
                  :key="skill.id"
                  class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded"
                >
                  {{ skill.name }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>



      <!-- Spacer -->
      <div class="flex-1"></div>

      <!-- View Profile Button -->
      <div class="pt-3 border-t border-gray-100">
        <button
          @click.stop="goToDetails"
          class="w-full bg-gradient-to-r from-primary-500 to-primary-600 text-white px-4 py-2.5 text-sm font-semibold rounded-lg hover:from-primary-600 hover:to-primary-700 transition-all shadow-sm hover:shadow-md"
          tabindex="0"
          aria-label="View provider profile"
        >
          View Profile
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.bg-primary-500 {
  background: #6d0019;
}

.bg-primary-600 {
  background: #5a0015;
}

.border-primary-100 {
  border-color: #fce7eb;
}

.border-primary-400 {
  border-color: #a8002a;
}

.text-primary-500 {
  color: #6d0019;
}

.text-primary-600 {
  color: #5a0015;
}

.hover\:text-primary-600:hover {
  color: #5a0015;
}

.hover\:border-primary-400:hover {
  border-color: #a8002a;
}

.from-primary-500 {
  --tw-gradient-from: #6d0019;
}

.to-primary-600 {
  --tw-gradient-to: #5a0015;
}

.hover\:from-primary-600:hover {
  --tw-gradient-from: #5a0015;
}

.hover\:to-primary-700:hover {
  --tw-gradient-to: #4a0011;
}
</style>
