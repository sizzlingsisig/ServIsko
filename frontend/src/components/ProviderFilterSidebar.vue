<script setup>
import { reactive, ref, watch, onMounted } from 'vue'
import MultiSelect from 'primevue/multiselect'
import api from '@/composables/axios'

const props = defineProps({
  filters: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['update'])

const localFilters = reactive({
  location: props.filters.location ?? '',
  skill: props.filters.skill ?? [],
  minRating: props.filters.minRating ?? 5,
  sort_by: props.filters.sort_by ?? 'newest',
})

const skills = ref([])

const loadSkills = async () => {
  try {
    const res = await api.get('/skills', {
      params: { per_page: 100 },
    })
    skills.value = res.data.data || []
  } catch (e) {
    console.error('Failed to load skills', e)
    skills.value = []
  }
}

const sortOptions = [
  { label: 'Newest', value: 'newest' },
  { label: 'Oldest', value: 'oldest' },
  { label: 'Name (A–Z)', value: 'name' },
]

const ratingOptions = [
  { label: '5 Stars & up', value: 5 },
  { label: '4 Stars & up', value: 4 },
  { label: '3 Stars & up', value: 3 },
  { label: '2 Stars & up', value: 2 },
  { label: '1 Star & up', value: 1 },
]

const emitUpdate = () => {
  emit('update', {
    location: localFilters.location,
    skill: localFilters.skill,
    minRating: localFilters.minRating,
    sort_by: localFilters.sort_by,
  })
}

watch(
  () => [localFilters.location, localFilters.skill, localFilters.minRating, localFilters.sort_by],
  () => emitUpdate(),
  { deep: true },
)

const clearSkillFilter = () => {
  localFilters.skill = []
  emitUpdate()
}
const clearLocationFilter = () => {
  localFilters.location = ''
  emitUpdate()
}
const clearRatingFilter = () => {
  localFilters.minRating = 5
  emitUpdate()
}

onMounted(() => {
  loadSkills()
})
</script>

<template>
  <div class="bg-white rounded-lg shadow p-4 md:p-6 sticky top-24 w-full">
    <!-- Location -->
    <div class="flex items-center justify-between mb-3">
      <h4 class="font-semibold text-gray-700 flex items-center gap-2">
        <i class="pi pi-map-marker text-[#6d0019]"></i>
        Location
      </h4>
      <button
        @click="clearLocationFilter"
        class="text-xs text-[#6d0019] hover:underline font-semibold"
      >
        Clear
      </button>
    </div>
    <div class="mb-6">
      <input
        v-model="localFilters.location"
        type="text"
        placeholder="e.g. UPV Miagao"
        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#6d0019]"
      />
    </div>

    <!-- Skills -->
    <div class="flex items-center justify-between mb-3">
      <h4 class="font-semibold text-gray-700 flex items-center gap-2">
        <i class="pi pi-tags text-[#6d0019]"></i>
        Skills
      </h4>
      <button
        @click="clearSkillFilter"
        class="text-xs text-[#6d0019] hover:underline font-semibold"
      >
        Clear
      </button>
    </div>
    <div class="mb-6">
      <MultiSelect
        v-model="localFilters.skill"
        :options="skills"
        optionLabel="name"
        optionValue="id"
        display="chip"
        filter
        placeholder="Select skills"
        class="w-full"
      />
    </div>

    <!-- Rating -->
    <div class="flex items-center justify-between mb-3">
      <h4 class="font-semibold text-gray-700 flex items-center gap-2">
        <i class="pi pi-star-fill text-yellow-400"></i>
        Minimum Rating
      </h4>
      <button
        @click="clearRatingFilter"
        class="text-xs text-[#6d0019] hover:underline font-semibold"
      >
        Clear
      </button>
    </div>
    <div class="mb-6">
      <div class="space-y-2">
        <label
          v-for="option in ratingOptions"
          :key="option.value"
          class="flex items-center gap-3 cursor-pointer"
        >
          <input
            type="radio"
            :value="option.value"
            v-model="localFilters.minRating"
            class="w-4 h-4 accent-[#6d0019]"
          />
          <span class="text-gray-700">{{ option.label }}</span>
        </label>
      </div>
    </div>

    <!-- Optional: sort -->
    <div class="mb-4">
      <label class="block text-sm font-semibold text-gray-700 mb-2">Sort by</label>
      <select
        v-model="localFilters.sort_by"
        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#6d0019]"
      >
        <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
          {{ opt.label }}
        </option>
      </select>
    </div>

    <div class="bg-[#f9f5f7] border border-[#e8d5dc] rounded-lg p-4 mt-5">
      <p class="text-sm text-gray-700 mb-2">
        <i class="pi pi-info-circle text-[#6d0019] mr-2"></i>
        <strong>Pro Tip:</strong> Combine skills, location, and rating to quickly find providers.
      </p>
    </div>
  </div>
</template>
