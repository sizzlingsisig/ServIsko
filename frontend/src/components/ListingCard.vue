<script setup>
import { computed, ref, onMounted, onBeforeUnmount, nextTick } from 'vue'

const popoverCoords = ref({ top: 0, left: 0 })
const popoverActive = ref(false)
let popoverTriggerEl = null

function showPopover(event) {
  popoverActive.value = true
  popoverTriggerEl = event.currentTarget
  nextTick(() => {
    if (popoverTriggerEl) {
      const rect = popoverTriggerEl.getBoundingClientRect()
      popoverCoords.value = {
        top: rect.bottom + window.scrollY + 8,
        left: rect.left + window.scrollX + rect.width / 2
      }
    }
  })
}
function hidePopover() {
  popoverActive.value = false
}

function handleWindowScroll() {
  if (popoverActive.value && popoverTriggerEl) {
    const rect = popoverTriggerEl.getBoundingClientRect()
    popoverCoords.value = {
      top: rect.bottom + window.scrollY + 8,
      left: rect.left + window.scrollX + rect.width / 2
    }
  }
}

onMounted(() => {
  window.addEventListener('scroll', handleWindowScroll, true)
})
onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleWindowScroll, true)
})
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
const showTooltip = ref(false)

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
    class="bg-white rounded-lg border border-gray-300 hover:border-primary-500 shadow-sm hover:shadow-md cursor-pointer flex flex-col h-[400px] sm:h-[400px] min-h-[340px] sm:min-h-[400px] w-full"
    @click="viewDetails"
  >
    <div class="p-4 sm:p-6 flex flex-col overflow-hidden h-full justify-between">
      <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1 leading-snug break-words whitespace-normal" style="word-break:break-word;overflow-wrap:break-word;">
        {{ title }}
      </h3>
      <span class="bg-white border border-gray-300 text-gray-700 text-xs px-2 py-1 rounded-full whitespace-nowrap mb-2 block w-fit">{{ category }}</span>
      <div class="flex items-center gap-1 text-xs text-gray-500 mb-1">
        <span>
          <i class="pi pi-calendar mr-1"></i>
          <strong>Created:</strong> {{ props.service.created_at ? (new Date(props.service.created_at)).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A' }}
        </span>
        <span class="mx-1 text-gray-300">•</span>
        <span>
          <i class="pi pi-clock mr-1"></i>
          <strong>Expires:</strong>
          <template v-if="props.service.expires_at">
            {{ (new Date(props.service.expires_at)).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) }}
          </template>
          <template v-else>
            No Expiry
          </template>
        </span>
        <span v-if="isExpired" class="text-[#b91c1c] font-bold ml-2 flex items-center"><i class="pi pi-clock mr-1"></i>Expired</span>
      </div>
      <p class="text-gray-600 clamp-3 w-full mb-3 text-xs sm:text-sm" tabindex="0">{{ description }}</p>
      <div class="flex flex-col gap-2 mt-auto">
        <div class="flex items-center gap-2 sm:gap-3 mb-2">
          <div class="w-7 h-7 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs sm:text-sm font-bold" :aria-label="`Provider: ${seeker}`">{{ seeker.charAt(0).toUpperCase() }}</div>
          <span class="text-xs sm:text-sm text-gray-700 max-w-[120px] sm:max-w-[160px] truncate" tabindex="0">{{ seeker }}</span>
        </div>
        <div class="flex flex-wrap gap-1 sm:gap-2 mb-2">
          <span v-for="(tag, idx) in tags.slice(0, 2)" :key="tag.id ?? tag" class="bg-gray-50 border border-gray-200 text-gray-600 text-xs px-2 py-1 rounded truncate max-w-[60px] sm:max-w-[80px]">
            {{ tag.name ?? tag }}
          </span>
          <span
            v-if="tags.length > 2"
            class="bg-gray-200 px-2 py-1 rounded text-xs text-gray-500 cursor-pointer relative"
            @mouseenter="showPopover($event)"
            @mouseleave="hidePopover"
            style="position:relative;"
          >
            ...<span class="ml-1">+{{ tags.length - 2 }}</span>
          </span>
          <Teleport to="body">
            <div
              v-if="popoverActive"
              :style="{ position: 'absolute', top: popoverCoords.top + 'px', left: popoverCoords.left + 'px', transform: 'translateX(-50%)', zIndex: 9999 }"
              class="w-max min-w-[160px] bg-white border border-gray-300 shadow-lg rounded p-2 text-xs"
              style="white-space:normal;"
              @mouseenter="popoverActive = true"
              @mouseleave="hidePopover"
            >
              <div class="mb-1 font-bold text-gray-700">All Tags:</div>
              <span v-for="tag in tags" :key="tag.id ?? tag" class="inline-block bg-gray-100 px-2 py-1 rounded mr-1 mb-1">{{ tag.name ?? tag }}</span>
            </div>
          </Teleport>
        </div>
        <div class="flex gap-2 sm:gap-3">
          <button @click.stop="viewDetails" class="flex-1 flex items-center justify-center gap-2 border border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white rounded-md px-2 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold transition-colors" tabindex="0" aria-label="View Details">
            <i class="pi pi-info-circle"></i> View Details
          </button>
        </div>
      </div>
    </div>
    <div class="bg-primary-500 text-white px-4 sm:px-6 py-2 sm:py-3 text-center text-base sm:text-lg font-bold select-none" aria-label="Service price">{{ budget }}</div>
  </div>
  <!-- List Card -->
  <div
    v-else
    class="bg-white rounded-lg border border-gray-300 hover:border-primary-500 shadow-sm hover:shadow-md cursor-pointer flex flex-col sm:flex-row h-auto sm:h-[200px] min-h-[160px] sm:min-h-[200px] w-full"
    @click="viewDetails"
  >
    <div class="flex-1 flex flex-col justify-between p-3 sm:p-5">
      <div>
        <h3 class="text-xs sm:text-lg font-semibold text-gray-900 mb-1 line-clamp-1 leading-snug break-words max-w-full" tabindex="0">{{ title }}</h3>
        <div class="flex flex-wrap gap-2 text-xs text-gray-500 mb-1 items-center">
          <span>Category: {{ category }}</span>
          <span class="mx-1 text-gray-300">•</span>
          <span>
            <i class="pi pi-calendar mr-1"></i>
            <strong>Created:</strong> {{ props.service.created_at ? (new Date(props.service.created_at)).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A' }}
          </span>
          <span class="mx-1 text-gray-300">•</span>
          <span>
            <i class="pi pi-clock mr-1"></i>
            <strong>Expires:</strong>
            <template v-if="props.service.expires_at">
              {{ (new Date(props.service.expires_at)).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) }}
            </template>
            <template v-else>
              No Expiry
            </template>
          </span>
          <span v-if="isExpired" class="text-[#b91c1c] font-bold ml-2 flex items-center"><i class="pi pi-clock mr-1"></i>Expired</span>
        </div>
        <p class="text-gray-600 mb-2 line-clamp-2 break-words whitespace-normal w-full max-w-full h-[2.8em] overflow-hidden text-xs sm:text-sm" style="word-break: break-all;" tabindex="0">{{ description }}</p>
      </div>
      <div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 gap-1 sm:gap-2">
          <div class="flex items-center gap-2 sm:gap-3">
            <div class="w-7 h-7 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs sm:text-sm font-bold" :aria-label="`Provider: ${seeker}`">{{ seeker.charAt(0).toUpperCase() }}</div>
            <span class="text-xs sm:text-sm text-gray-700 max-w-[90px] sm:max-w-[140px] truncate break-words" tabindex="0">{{ seeker }}</span>
          </div>
          <div class="flex items-center gap-1 sm:gap-2 text-xs sm:text-sm" aria-label="Rating">
            <i class="pi pi-star-fill text-yellow-400"></i>
            <span class="font-semibold">4.1</span>
            <span class="text-gray-500 text-xs">(4.1)</span>
          </div>
        </div>
        <div class="flex flex-wrap gap-1 sm:gap-2 mt-1">
          <span v-for="(tag, idx) in tags.slice(0, 2)" :key="tag.id ?? tag" class="bg-gray-50 border border-gray-200 text-gray-600 text-xs px-2 py-1 rounded truncate max-w-[60px] sm:max-w-[80px]">
            {{ tag.name ?? tag }}
          </span>
          <span
            v-if="tags.length > 2"
            class="bg-gray-200 px-2 py-1 rounded text-xs text-gray-500 cursor-pointer relative"
            @mouseenter="showTooltip = true"
            @mouseleave="showTooltip = false"
            style="position:relative;"
          >
            ...<span class="ml-1">+{{ tags.length - 2 }}</span>
            <!-- Popover -->
            <div
              v-if="showTooltip"
              class="absolute left-1/2 top-full mt-1 z-10 bg-white border border-gray-300 rounded shadow-lg p-2 text-xs w-max min-w-[120px]"
              style="white-space:normal;"
            >
              <div class="mb-1 font-bold text-gray-700">All Tags:</div>
              <span v-for="tag in tags" :key="tag.id ?? tag" class="block text-gray-700 mb-1 last:mb-0">{{ tag.name ?? tag }}</span>
            </div>
          </span>
        </div>
      </div>
    </div>
    <div class="w-full sm:w-[180px] flex flex-col h-auto sm:h-full border-t sm:border-t-0 sm:border-l border-gray-200">
      <div class="bg-primary-500 text-white px-3 sm:px-6 py-2 sm:py-3 flex items-center justify-center text-xs sm:text-lg font-bold select-none min-h-[36px] sm:min-h-[64px]" style="flex: 0 0 auto">{{ budget }}</div>
      <div class="flex-1 flex items-end">
        <div class="w-full p-2 sm:p-5">
          <button @click.stop="viewDetails" class="w-full flex items-center justify-center gap-2 border border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white rounded-md px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm font-semibold transition-colors" tabindex="0" aria-label="View Details">
            <i class="pi pi-info-circle"></i> View Details
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
 .clamp-3 {
   display: -webkit-box;
   -webkit-line-clamp: 3;
   line-clamp: 3;
   -webkit-box-orient: vertical;
   overflow: hidden;
   text-overflow: ellipsis;
   word-break: break-word;
   white-space: normal;
 }
</style>
