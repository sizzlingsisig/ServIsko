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

const createdAt = computed(() => props.service.created_at ? formatDate(props.service.created_at) : null)
const expiresAt = computed(() =>
  props.service.expires_at ? formatDate(props.service.expires_at) : null
)
const isExpired = computed(() => !!props.service.is_expired)

function formatDate(dateStr) {
  // Use vanilla JS Date formatting
  const date = new Date(dateStr)
  // "Nov 10, 2025 14:30"
  return `${date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })} ${date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })}`
}

const viewDetails = (evt) => {
  evt?.stopPropagation?.()
  router.push(`/listings/${props.service.id}`)
}
</script>

<template>
  <!-- Grid Card -->
  <div
    v-if="layout === 'grid'"
    class="bg-white rounded-lg border border-gray-300 hover:border-primary-500 shadow-sm hover:shadow-md cursor-pointer flex flex-col h-[400px]"
    @click="viewDetails"
  >
    <div class="p-6 flex flex-col flex-1 overflow-hidden">
      <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 leading-snug" tabindex="0">
        {{ title }}
      </h3>
      <div class="flex gap-2 text-xs text-gray-500 mb-1">
        <span>Created: <strong>{{ createdAt }}</strong></span>
        <span v-if="expiresAt">
          <span>&bull; Expires: <strong>{{ expiresAt }}</strong></span>
        </span>
        <span v-else>
          <span>&bull; No Expiry</span>
        </span>
        <span v-if="isExpired" class="text-[#b91c1c] font-bold ml-2"><i class="pi pi-clock"></i> Expired</span>
      </div>
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
        <span v-for="tag in tags" :key="tag.id ?? tag" class="bg-gray-50 border border-gray-200 text-gray-600 text-xs px-2 py-1 rounded">
          {{ tag.name ?? tag }}
        </span>
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
    class="bg-white rounded-lg border border-gray-300 hover:border-primary-500 shadow-sm hover:shadow-md cursor-pointer flex h-[200px] min-h-[200px] sm:h-[200px] sm:min-h-[200px] flex-col sm:flex-row w-full"
    @click="viewDetails"
  >
    <div class="flex-1 flex flex-col justify-between p-4 sm:p-5">
      <div>
        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1 line-clamp-1 leading-snug break-words max-w-full" tabindex="0">{{ title }}</h3>
        <div class="flex flex-wrap gap-1 sm:gap-2 text-xs text-gray-500 mb-1">
          <span>Created: <strong>{{ createdAt }}</strong></span>
          <span v-if="expiresAt">
            <span>&bull; Expires: <strong>{{ expiresAt }}</strong></span>
          </span>
          <span v-else>
            <span>&bull; No Expiry</span>
          </span>
          <span v-if="isExpired" class="text-[#b91c1c] font-bold ml-2"><i class="pi pi-clock"></i> Expired</span>
        </div>
        <p class="text-gray-600 mb-2 line-clamp-2 break-words whitespace-normal w-full max-w-full h-[2.8em] overflow-hidden text-xs sm:text-sm" style="word-break: break-all;" tabindex="0">{{ description }}</p>
      </div>
      <div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-2">
          <div class="flex items-center gap-3">
            <div class="w-7 h-7 rounded-full bg-gray-800 text-white flex items-center justify-center text-sm font-bold" :aria-label="`Provider: ${seeker}`">{{ seeker.charAt(0).toUpperCase() }}</div>
            <span class="text-xs sm:text-sm text-gray-700 max-w-[140px] truncate break-words" tabindex="0">{{ seeker }}</span>
          </div>
          <div class="flex items-center gap-2 text-sm" aria-label="Rating">
            <i class="pi pi-star-fill text-yellow-400"></i>
            <span class="font-semibold">4.1</span>
            <span class="text-gray-500 text-xs">(4.1)</span>
          </div>
        </div>
        <div class="flex flex-wrap gap-0.5 mt-1">
          <span class="bg-gray-100 border border-gray-200 text-gray-500 text-[10px] px-1.5 py-0.5 rounded-full whitespace-nowrap">{{ category }}</span>
          <span v-for="tag in visibleTags" :key="tag.id ?? tag" class="bg-gray-50 border border-gray-100 text-gray-500 text-[10px] px-1.5 py-0.5 rounded truncate max-w-[60px] sm:max-w-[80px] break-words">
            {{ tag.name ?? tag }}
          </span>
          <span v-if="moreTagsCount > 0" class="bg-gray-50 border border-gray-100 text-gray-400 text-[10px] px-1.5 py-0.5 rounded truncate max-w-[40px] sm:max-w-[60px]">+{{ moreTagsCount }} more</span>
        </div>
      </div>
    </div>
    <div class="w-full sm:w-[180px] flex flex-col h-auto sm:h-full border-t sm:border-t-0 sm:border-l border-gray-200">
      <div class="bg-primary-500 text-white px-6 py-3 flex items-center justify-center text-base sm:text-lg font-bold select-none min-h-[48px] sm:min-h-[64px]" style="flex: 0 0 auto">{{ budget }}</div>
      <div class="flex-1 flex items-end">
        <div class="w-full p-4 sm:p-5">
          <button @click.stop="viewDetails" class="w-full flex items-center justify-center gap-2 border border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white rounded-md px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold transition-colors" tabindex="0" aria-label="View Details">
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
