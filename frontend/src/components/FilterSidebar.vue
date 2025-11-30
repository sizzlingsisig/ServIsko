<script setup>
import { ref, reactive, watch } from 'vue'
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
]

const ratingOptions = [
  { label: '5 Stars', value: 5 },
  { label: '4 Stars', value: 4 },
  { label: '3 Stars', value: 3 },
  { label: '2 Stars', value: 2 },
  { label: '1 Star', value: 1 },
  { label: '0 Stars', value: 0 },
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

const clearCategoryFilters = () => {
  localFilters.categories = []
  emitUpdate()
}

const clearBudgetFilter = () => {
  localFilters.minBudget = []
  localFilters.maxBudget = []
  budgetRange.value = [0, 10000]
  emitUpdate()
}

const clearRatingFilter = () => {
  localFilters.minRating = 5
  emitUpdate()
}
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6 sticky top-24">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold text-gray-800">Filter Services</h3>
    </div>

    <!-- Categories Section -->
    <div class="flex items-center justify-between mb-3">
      <h4 class="font-semibold text-gray-700 flex items-center gap-2">
        <i class="pi pi-list text-[#6d0019]"></i>
        Categories
      </h4>
      <button
        @click="clearCategoryFilters"
        class="text-xs text-[#6d0019] hover:underline font-semibold"
      >
        Clear
      </button>
    </div>
    <div>
      <div class="flex flex-col gap-2 mb-7">
        <label
          v-for="option in categoryOptions"
          :key="option.value"
          class="flex items-center gap-2 cursor-pointer"
        >
          <input
            type="checkbox"
            :value="option.value"
            v-model="localFilters.categories"
            class="accent-[#6d0019] w-4 h-4"
            @change="emitUpdate"
          />
          <span class="text-gray-800">{{ option.label }}</span>
        </label>
      </div>
    </div>

    <!-- Budget Range Section -->
    <div class="flex items-center justify-between mb-3">
      <h4 class="font-semibold text-gray-700 flex items-center gap-2 mb-3">
        <i class="pi pi-wallet text-[#6d0019]"></i>
        Budget Range
      </h4>
      <button
        @click="clearBudgetFilter"
        class="text-xs text-[#6d0019] hover:underline font-semibold"
      >
        Clear
      </button>
    </div>
    <div>
      <Slider v-model="budgetRange" :min="0" :max="10000" :step="100" range class="mb-4" />

      <div class="flex items-center justify-between text-sm mb-5">
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

    <div class="flex items-center justify-between mb-3">
      <h4 class="font-semibold text-gray-700 flex items-center gap-2">
        <i class="pi pi-star-fill text-yellow-400"></i>
        Rating
      </h4>
      <button
        @click="clearRatingFilter"
        class="text-xs text-[#6d0019] hover:underline font-semibold"
      >
        Clear
      </button>
    </div>
    <div>
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
    <div class="bg-[#f9f5f7] border border-[#e8d5dc] rounded-lg p-4 mt-5">
      <p class="text-sm text-gray-700 mb-2">
        <i class="pi pi-info-circle text-[#6d0019] mr-2"></i>
        <strong>Pro Tip:</strong> Refine your search with multiple filters to find the perfect
        service listing.
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
