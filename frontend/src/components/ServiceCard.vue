<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  service: {
    type: Object,
    required: true,
  },
  layout: {
    type: String,
    default: 'grid',
    validator: (v) => ['grid', 'list'].includes(v),
  },
})

const router = useRouter()

const title = computed(() => props.service.title || 'Untitled Service')
const description = computed(() => props.service.description || 'No description available')
const budget = computed(() => {
  const amt = props.service.budget
  return amt ? `₱${parseFloat(amt).toLocaleString()}/hr` : 'TBD'
})
const category = computed(() => props.service.category?.name || 'Uncategorized')
const seeker = computed(() => props.service.seeker?.name || 'Unknown Provider')
const tags = computed(() => props.service.tags || [])

const viewDetails = (evt) => {
  evt?.stopPropagation?.()
  router.push(`/listings/${props.service.id}`)
}
</script>

<template>
  <!-- Grid Card -->
  <div
    v-if="layout === 'grid'"
    class="bg-white rounded-lg border border-gray-300 hover:border-primary-500 shadow-sm hover:shadow-md cursor-pointer flex flex-col h-[380px]"
    @click="viewDetails"
  >
    <div class="p-6 flex flex-col flex-1 overflow-hidden">
      <h3 class="text-lg font-semibold text-gray-900 mb-3 line-clamp-2 leading-snug" tabindex="0">
        {{ title }}
      </h3>
      <p class="text-gray-600 flex-1 mb-3 line-clamp-3" tabindex="0">{{ description }}</p>
      <div class="flex justify-between items-center mb-3">
        <div class="flex items-center gap-3">
          <div class="w-7 h-7 rounded-full bg-gray-800 text-white flex items-center justify-center text-sm font-bold" :aria-label="`Provider: ${seeker}`">{{ seeker.charAt(0).toUpperCase() }}</div>
          <span class="text-sm text-gray-700 max-w-[160px] truncate" tabindex="0">{{ seeker }}</span>
        </div>
        <div class="flex items-center gap-2 text-sm" aria-label="Rating">
          <i class="pi pi-star-fill text-yellow-400"></i>
          <span class="font-semibold">4.1</span>
          <span class="text-gray-500 text-xs">(4.1)</span>
        </div>
      </div>
      <div class="flex flex-wrap gap-2 mb-4">
        <span v-for="tag in [category]" :key="tag" class="bg-white border border-gray-300 text-gray-700 text-xs px-3 py-1 rounded-full whitespace-nowrap">{{ tag }}</span>
      </div>
      <div class="flex gap-3">
        <button @click.stop="viewDetails" class="flex-1 flex items-center justify-center gap-2 border border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white rounded-md px-4 py-2 text-sm font-semibold transition-colors" tabindex="0" aria-label="View Details">
          <i class="pi pi-info-circle"></i> View Details
        </button>
      </div>
    </div>
    <div class="bg-primary-500 text-white px-6 py-3 text-center text-lg font-bold select-none" aria-label="Service price">{{ budget }}</div>
  </div>
  <!-- List Card -->
  <div
    v-else
    class="bg-white rounded-lg border border-gray-300 hover:border-primary-500 shadow-sm hover:shadow-md cursor-pointer flex h-[180px] min-h-[180px]"
    @click="viewDetails"
  >
    <div class="flex-1 flex flex-col justify-between p-5">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-1 leading-snug" tabindex="0">{{ title }}</h3>
        <p class="text-gray-600 mb-2 line-clamp-2" tabindex="0">{{ description }}</p>
      </div>
      <div>
        <div class="flex justify-between items-center mb-2">
          <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-full bg-gray-800 text-white flex items-center justify-center text-sm font-bold" :aria-label="`Provider: ${seeker}`">{{ seeker.charAt(0).toUpperCase() }}</div>
            <span class="text-sm text-gray-700 max-w-[140px] truncate" tabindex="0">{{ seeker }}</span>
          </div>
          <div class="flex items-center gap-2 text-sm" aria-label="Rating">
            <i class="pi pi-star-fill text-yellow-400"></i>
            <span class="font-semibold">4.1</span>
            <span class="text-gray-500 text-xs">(4.1)</span>
          </div>
        </div>
        <div class="flex flex-wrap gap-2">
          <span v-for="tag in [category]" :key="tag" class="bg-white border border-gray-300 text-gray-700 text-xs px-3 py-1 rounded-full whitespace-nowrap">{{ tag }}</span>
        </div>
      </div>
    </div>
    <div class="w-[180px] flex flex-col h-full border-l border-gray-200">
      <div class="bg-primary-500 text-white px-6 py-3 flex items-center justify-center text-lg font-bold select-none min-h-[64px]" style="flex: 0 0 auto">{{ budget }}</div>
      <div class="flex-1 flex items-end">
        <div class="w-full p-5">
          <button @click.stop="viewDetails" class="w-full flex items-center justify-center gap-2 border border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white rounded-md px-3 py-2 text-sm font-semibold transition-colors" tabindex="0" aria-label="View Details">
            <i class="pi pi-info-circle"></i> View Details
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-1, .line-clamp-2, .line-clamp-3 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-1 { -webkit-line-clamp: 1; }
.line-clamp-2 { -webkit-line-clamp: 2; }
.line-clamp-3 { -webkit-line-clamp: 3; }
</style>
