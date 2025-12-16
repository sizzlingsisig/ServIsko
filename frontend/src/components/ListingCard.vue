<script setup>
import { computed, ref } from 'vue'
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
const showTagsTooltip = ref(false)

const title = computed(() => props.service.title || 'Untitled Service')
const description = computed(() => props.service.description || 'No description available')
const budget = computed(() => {
  const amt = props.service.budget
  return amt ? `₱${parseFloat(amt).toLocaleString()}/hr` : 'TBD'
})
const category = computed(() => props.service.category?.name || 'Uncategorized')
const seeker = computed(() => props.service.seeker?.name || 'Unknown Provider')
const tags = computed(() => props.service.tags || [])
const visibleTags = computed(() => tags.value.slice(0, 3))
const remainingTagsCount = computed(() => Math.max(0, tags.value.length - 3))
const isExpired = computed(() => !!props.service.is_expired)

const viewDetails = (evt) => {
  evt?.stopPropagation?.()
  router.push(`/listings/${props.service.id}`)
}
</script>

<template>
  <!-- Grid Card -->
  <div
    v-if="layout === 'grid'"
    class="group bg-white rounded-xl border border-gray-200 hover:border-primary-400 hover:shadow-lg cursor-pointer flex flex-col h-full transition-all duration-200"
    @click="viewDetails"
  >
    <div class="p-6 flex flex-col flex-1 gap-4">
      <!-- Header with Title & Price Chip -->
      <div class="flex items-start gap-3">
        <h3 class="text-lg font-semibold text-gray-900 line-clamp-2 leading-tight flex-1" tabindex="0">
          {{ title }}
        </h3>
        <div class="flex flex-col items-end gap-2 flex-shrink-0">
          <span
            class="bg-primary-500 text-white text-sm font-bold px-3 py-1.5 rounded-full shadow-sm whitespace-nowrap"
            aria-label="Service price"
          >
            {{ budget }}
          </span>
          <span
            v-if="isExpired"
            class="bg-red-50 text-red-600 text-xs px-2.5 py-1 rounded-full font-medium"
          >
            Expired
          </span>
        </div>
      </div>

      <!-- Description -->
      <p class="text-gray-600 text-sm leading-relaxed line-clamp-3" tabindex="0">
        {{ description }}
      </p>

      <!-- Tags with Hover Tooltip -->
      <div class="flex flex-wrap gap-2">
        <span
          v-for="tag in visibleTags"
          :key="tag.id ?? tag"
          class="bg-gray-100 text-gray-700 text-xs px-2.5 py-1 rounded-md"
        >
          {{ tag.name ?? tag }}
        </span>
        <div
          v-if="remainingTagsCount > 0"
          class="relative inline-block"
          @mouseenter="showTagsTooltip = true"
          @mouseleave="showTagsTooltip = false"
        >
          <span
            class="bg-gray-200 text-gray-600 text-xs px-2.5 py-1 rounded-md cursor-help"
          >
            +{{ remainingTagsCount }} more
          </span>
          <div
            v-if="showTagsTooltip"
            class="absolute left-0 top-full mt-2 z-50 bg-white border border-gray-300 rounded-lg shadow-xl p-3 min-w-[200px] max-w-[280px]"
            style="pointer-events: none;"
          >
            <div class="text-xs font-semibold text-gray-700 mb-2">All Tags</div>
            <div class="flex flex-wrap gap-1.5">
              <span
                v-for="tag in tags"
                :key="tag.id ?? tag"
                class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded"
              >
                {{ tag.name ?? tag }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Seeker Info (Name + Category as subheading) -->
      <div class="flex items-center gap-3 mt-auto pt-2 border-t border-gray-100">
        <div
          class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white flex items-center justify-center text-sm font-semibold shadow-sm flex-shrink-0"
          :aria-label="`Provider: ${seeker}`"
        >
          {{ seeker.charAt(0).toUpperCase() }}
        </div>
        <div class="flex flex-col min-w-0 flex-1">
          <span class="text-sm font-semibold text-gray-900 truncate" tabindex="0">
            {{ seeker }}
          </span>
          <span class="text-xs text-gray-500 truncate">
            {{ category }}
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- List Card -->
  <div
    v-else
    class="group bg-white rounded-xl border border-gray-200 hover:border-primary-400 hover:shadow-lg cursor-pointer flex transition-all duration-200 overflow-hidden"
    @click="viewDetails"
  >
    <div class="flex-1 p-5 flex flex-col justify-between min-w-0">
      <!-- Header with Title & Price Chip -->
      <div class="space-y-3">
        <div class="flex items-start gap-3">
          <h3 class="text-base font-semibold text-gray-900 line-clamp-1 flex-1" tabindex="0">
            {{ title }}
          </h3>
          <div class="flex items-center gap-2 flex-shrink-0">
            <span
              class="bg-primary-500 text-white text-sm font-bold px-3 py-1 rounded-full shadow-sm whitespace-nowrap"
              aria-label="Service price"
            >
              {{ budget }}
            </span>
            <span
              v-if="isExpired"
              class="bg-red-50 text-red-600 text-xs px-2 py-0.5 rounded-full font-medium"
            >
              Expired
            </span>
          </div>
        </div>
        <p class="text-gray-600 text-sm line-clamp-2" tabindex="0">
          {{ description }}
        </p>
      </div>

      <!-- Footer with Seeker & Tags -->
      <div class="flex items-center justify-between gap-4 mt-3">
        <div class="flex items-center gap-2.5 min-w-0">
          <div
            class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white flex items-center justify-center text-sm font-semibold flex-shrink-0"
            :aria-label="`Provider: ${seeker}`"
          >
            {{ seeker.charAt(0).toUpperCase() }}
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-sm font-semibold text-gray-900 truncate" tabindex="0">
              {{ seeker }}
            </span>
            <span class="text-xs text-gray-500 truncate">
              {{ category }}
            </span>
          </div>
        </div>

        <!-- Tags with Hover Tooltip -->
        <div class="flex gap-1.5 flex-shrink-0">
          <span
            v-for="tag in visibleTags"
            :key="tag.id ?? tag"
            class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded hidden sm:inline-block"
          >
            {{ tag.name ?? tag }}
          </span>
          <div
            v-if="remainingTagsCount > 0"
            class="relative inline-block"
            @mouseenter="showTagsTooltip = true"
            @mouseleave="showTagsTooltip = false"
          >
            <span
              class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded cursor-help"
            >
              +{{ remainingTagsCount }}
            </span>
            <div
              v-if="showTagsTooltip"
              class="absolute right-0 bottom-full mb-2 z-50 bg-white border border-gray-300 rounded-lg shadow-xl p-3 min-w-[200px] max-w-[280px]"
              style="pointer-events: none;"
            >
              <div class="text-xs font-semibold text-gray-700 mb-2">All Tags</div>
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="tag in tags"
                  :key="tag.id ?? tag"
                  class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded"
                >
                  {{ tag.name ?? tag }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
