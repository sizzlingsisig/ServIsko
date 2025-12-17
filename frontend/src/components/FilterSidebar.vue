<script setup>
import { ref, reactive, watch } from 'vue'
import InputText from 'primevue/inputtext'
import Slider from 'primevue/slider'

const props = defineProps({
  filters: {
    type: Object,
    required: true,
  },
  categories: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update'])

const localFilters = reactive({
  category: props.filters.category ?? null,
  minBudget: props.filters.minBudget ?? 0,
  maxBudget: props.filters.maxBudget ?? 10000,
  minRating: props.filters.minRating ?? 5,
  sort_by: props.filters.sort_by ?? 'newest',
})

const sortOptions = [
  { label: 'Newest', value: 'newest' },
  { label: 'Oldest', value: 'oldest' },
  { label: 'Price: Low to High', value: 'price_low' },
  { label: 'Price: High to Low', value: 'price_high' },
  { label: 'Top Rated', value: 'rating' },
]

const budgetRange = ref([localFilters.minBudget, localFilters.maxBudget])

function handleMinBudgetInput(e) {
  let val = Number(e.target.value)
  if (isNaN(val)) val = 0
  localFilters.minBudget = val
  budgetRange.value[0] = val
  emitUpdate()
}
function handleMaxBudgetInput(e) {
  let val = Number(e.target.value)
  if (isNaN(val)) val = 10000
  localFilters.maxBudget = val
  budgetRange.value[1] = val
  emitUpdate()
}
const ratingOptions = [
  { label: '5 Stars', value: 5 },
  { label: '4 Stars', value: 4 },
  { label: '3 Stars', value: 3 },
  { label: '2 Stars', value: 2 },
  { label: '1 Star', value: 1 },
]

const showAllCategories = ref(false)

const emitUpdate = () => {
  emit('update', {
    category: localFilters.category,
    minBudget: localFilters.minBudget,
    maxBudget: localFilters.maxBudget,
    minRating: localFilters.minRating,
    sort_by: localFilters.sort_by,
  })
}

watch(budgetRange, (newValue) => {
  localFilters.minBudget = newValue[0]
  localFilters.maxBudget = newValue[1]
  emitUpdate()
}, { deep: true })

watch(() => localFilters.category, () => { emitUpdate() })
watch(() => localFilters.minRating, () => { emitUpdate() })
watch(() => localFilters.sort_by, () => { emitUpdate() })

const clearCategoryFilters = () => {
  localFilters.category = null
  emitUpdate()
}
const clearBudgetFilter = () => {
  localFilters.minBudget = 0
  localFilters.maxBudget = 10000
  budgetRange.value = [0, 10000]
  emitUpdate()
}
const clearRatingFilter = () => {
  localFilters.minRating = 5
  emitUpdate()
}
</script>

<template>
  <div class="bg-white rounded-lg shadow p-4 md:p-6 sticky top-24 w-full">
    <div class="flex items-center justify-between mb-6">
      <h3 class="text-xl font-bold text-gray-800">Filter Services</h3>
    </div>


    <!-- Categories Single-Select -->
    <div class="flex items-center justify-between mb-3">
      <h4 class="font-semibold text-gray-700 flex items-center gap-2">
        <i class="pi pi-list text-[#6d0019]"></i>
        Categories
      </h4>
      <button @click="clearCategoryFilters" class="text-xs text-[#6d0019] hover:underline font-semibold">Clear</button>
    </div>
    <div>
      <div v-if="categories.length" class="mb-7">
        <div style="max-height: 300px; overflow-y: auto;" class="flex flex-col gap-2">
          <label v-for="cat in showAllCategories ? categories : categories.slice(0, 5)" :key="cat.id" class="flex items-center gap-2 cursor-pointer">
            <input type="radio" :value="cat.id" v-model="localFilters.category" class="accent-[#6d0019] w-4 h-4" @change="emitUpdate"/>
            <span class="text-gray-800">{{ cat.name }}</span>
          </label>
        </div>
        <div v-if="categories.length > 5" class="text-center mt-2">
          <button class="text-xs text-[#6d0019] hover:underline font-semibold" @click="showAllCategories = !showAllCategories">
            {{ showAllCategories ? 'Show Less' : 'Show All (' + categories.length + ')' }}
          </button>
        </div>
      </div>
      <div v-else class="text-gray-400 text-sm mb-7">No categories available.</div>
    </div>
    <!-- Budget Range -->
    <div class="flex items-center justify-between mb-3">
      <h4 class="font-semibold text-gray-700 flex items-center gap-2 mb-3">
        <i class="pi pi-wallet text-[#6d0019]"></i>
        Budget Range
      </h4>
      <button @click="clearBudgetFilter" class="text-xs text-[#6d0019] hover:underline font-semibold">Clear</button>
    </div>
    <div>
      <Slider v-model="budgetRange" :min="0" :max="10000" :step="100" range class="mb-4"/>
      <div class="flex items-center justify-between text-sm mb-5">
        <div class="flex flex-col items-start">
          <p class="text-gray-600 mb-1">Min</p>
          <InputText
            :value="localFilters.minBudget"
            type="number"
            min="0"
            max="10000"
            class="w-24 text-xl font-bold text-[#6d0019]"
            @input="handleMinBudgetInput"
          />
        </div>
        <div class="text-gray-400">to</div>
        <div class="flex flex-col items-end">
          <p class="text-gray-600 mb-1">Max</p>
          <InputText
            :value="localFilters.maxBudget"
            type="number"
            min="0"
            max="10000"
            class="w-24 text-xl font-bold text-[#6d0019]"
            @input="handleMaxBudgetInput"
          />
        </div>
      </div>
    </div>
    <!-- Rating -->
    <div class="flex items-center justify-between mb-3">
      <h4 class="font-semibold text-gray-700 flex items-center gap-2">
        <i class="pi pi-star-fill text-yellow-400"></i>
        Rating
      </h4>
      <button @click="clearRatingFilter" class="text-xs text-[#6d0019] hover:underline font-semibold">Clear</button>
    </div>
    <div>
      <div class="space-y-2">
        <label v-for="option in ratingOptions" :key="option.value" class="flex items-center gap-3 cursor-pointer">
          <input type="radio" :value="option.value" v-model="localFilters.minRating" class="w-4 h-4 accent-[#6d0019]" />
          <span class="text-gray-700">{{ option.label }}</span>
        </label>
      </div>
    </div>
    <div class="bg-[#f9f5f7] border border-[#e8d5dc] rounded-lg p-4 mt-5">
      <p class="text-sm text-gray-700 mb-2">
        <i class="pi pi-info-circle text-[#6d0019] mr-2"></i>
        <strong>Pro Tip:</strong> Refine your search with multiple filters to find the perfect service listing.
      </p>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-slider) { width: 100%; }
:deep(.p-slider .p-slider-handle) { background-color: #6d0019; border-color: #6d0019; }
:deep(.p-slider .p-slider-range) { background-color: #6d0019; }
</style>
