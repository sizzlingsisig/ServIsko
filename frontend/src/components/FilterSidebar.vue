<script setup>
import { ref, reactive, watch } from 'vue'
import Button from 'primevue/button'
import Slider from 'primevue/slider'
import MultiSelect from 'primevue/multiselect'

const props = defineProps({
  filters: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['update'])

const localFilters = reactive({
  categories: props.filters.categories || [],
  minBudget: props.filters.minBudget || 0,
  maxBudget: props.filters.maxBudget || 5000,
  minRating: props.filters.minRating || 0,
})

const categoryOptions = [
  { label: 'Academic Services', value: 'academic' },
  { label: 'Design & Creatives', value: 'design' },
  { label: 'Programming and Tech', value: 'programming' },
  { label: 'Writing', value: 'writing' },
  { label: 'Tutoring & Education', value: 'tutoring' },
  { label: 'Business Services', value: 'business' },
]

const ratingOptions = [
  { label: '5 Stars', value: 5 },
  { label: '4 Stars', value: 4 },
  { label: '3 Stars', value: 3 },
  { label: '0-2 Stars', value: 0 },
]

const budgetRange = ref([localFilters.minBudget, localFilters.maxBudget])

watch(
  budgetRange,
  (newValue) => {
    localFilters.minBudget = newValue[0]
    localFilters.maxBudget = newValue[1]
    emitUpdate()
  },
  { deep: true },
)

watch(
  () => localFilters.categories,
  () => {
    emitUpdate()
  },
  { deep: true },
)

watch(
  () => localFilters.minRating,
  () => {
    emitUpdate()
  },
)

const emitUpdate = () => {
  emit('update', {
    categories: localFilters.categories,
    minBudget: localFilters.minBudget,
    maxBudget: localFilters.maxBudget,
    minRating: localFilters.minRating,
  })
}

const clearFilters = () => {
  localFilters.categories = []
  localFilters.minBudget = 0
  localFilters.maxBudget = 5000
  localFilters.minRating = 0
  budgetRange.value = [0, 5000]
  emitUpdate()
}
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6 sticky top-24">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold text-gray-800">Filter Services</h3>
      <button @click="clearFilters" class="text-sm text-[#6d0019] hover:underline font-semibold">
        Clear
      </button>
    </div>

    <!-- Categories Section -->
    <div class="mb-6">
      <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
        <i class="pi pi-list text-[#6d0019]"></i>
        Categories
      </h4>
      <MultiSelect
        v-model="localFilters.categories"
        :options="categoryOptions"
        option-label="label"
        option-value="value"
        placeholder="Select categories"
        class="w-full"
        display="chip"
        max-selected-labels="2"
      />
      <p class="text-xs text-gray-500 mt-2">{{ localFilters.categories.length }} selected</p>
    </div>

    <!-- Budget Range Section -->
    <div class="mb-6 pb-6 border-b border-gray-200">
      <h4 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
        <i class="pi pi-wallet text-[#6d0019]"></i>
        Budget Range
      </h4>

      <Slider v-model="budgetRange" :min="0" :max="5000" :step="100" range class="mb-4" />

      <div class="flex items-center justify-between text-sm">
        <div>
          <p class="text-gray-600">Min</p>
          <p class="text-xl font-bold text-[#6d0019]">₱{{ localFilters.minBudget }}</p>
        </div>
        <div class="text-gray-400">to</div>
        <div>
          <p class="text-gray-600">Max</p>
          <p class="text-xl font-bold text-[#6d0019]">₱{{ localFilters.maxBudget }}</p>
        </div>
      </div>
    </div>

    <!-- Rating Section -->
    <div class="mb-6 pb-6 border-b border-gray-200">
      <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
        <i class="pi pi-star-fill text-yellow-400"></i>
        Rating
      </h4>

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

    <!-- Additional Info -->
    <div class="bg-[#f9f5f7] border border-[#e8d5dc] rounded-lg p-4">
      <p class="text-sm text-gray-700 mb-2">
        <i class="pi pi-info-circle text-[#6d0019] mr-2"></i>
        <strong>Pro Tip:</strong> Refine your search with multiple filters to find the perfect
        service provider.
      </p>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-multiselect) {
  width: 100%;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
}

:deep(.p-multiselect.p-focus) {
  border-color: #6d0019;
  box-shadow: 0 0 0 0.2rem rgba(109, 0, 25, 0.25);
}

:deep(.p-slider) {
  width: 100%;
}

:deep(.p-slider .p-slider-handle) {
  background-color: #6d0019;
  border-color: #6d0019;
}

:deep(.p-slider .p-slider-range) {
  background-color: #6d0019;
}
</style>
