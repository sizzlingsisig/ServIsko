<script setup>
import { ref, reactive, onMounted, watch, onBeforeUnmount } from 'vue'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Button from 'primevue/button'
import FilterSidebar from '@/components/FilterSidebar.vue'
import ProviderCard from '@/components/ProviderCard.vue'
import Dialog from 'primevue/dialog'
import api from '@/composables/axios'
import { useToast } from 'primevue/usetoast'

const toast = useToast()
const showMobileFilters = ref(false)
const layout = ref('grid')
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(12)

const filters = reactive({
  search: '',
  location: '',
  skill: [],
  category_id: null,
  min_price: null,
  max_price: null,
  sort_by: 'newest',
})

const providers = ref([])

// Debounce utility
const DEBOUNCE_DELAY = 500
let searchTimeout = null
const debounce = (func, delay) => {
  let timeoutId
  const debounced = function (...args) {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => func.apply(this, args), delay)
  }
  debounced.cancel = () => clearTimeout(timeoutId)
  return debounced
}

const debouncedSearchLoad = debounce(() => {
  currentPage.value = 1
  loadProviders()
}, DEBOUNCE_DELAY)

const loadProviders = async () => {
  try {
    loading.value = true
    const params = {
      search: filters.search,
      location: filters.location,
      skill: filters.skill,
      category_id: filters.category_id,
      min_price: filters.min_price,
      max_price: filters.max_price,
      sort_by: filters.sort_by,
      page: currentPage.value,
      per_page: itemsPerPage.value,
    }
    Object.keys(params).forEach((key) => {
      if (
        params[key] === null ||
        params[key] === '' ||
        typeof params[key] === 'undefined' ||
        (Array.isArray(params[key]) && params[key].length === 0)
      ) {
        delete params[key]
      }
    })
    const response = await api.get('/providers', { params })
    if (response.data.success && response.data.data) {
      const paginatedData = response.data.data
      providers.value = paginatedData.data || []
      totalRecords.value = paginatedData.total || 0
    } else {
      providers.value = []
      totalRecords.value = 0
      toast.add({
        severity: 'warn',
        summary: 'No providers found',
        detail: 'Try adjusting your filters.',
        life: 4000,
      })
    }
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Failed to load providers',
      detail: 'Please try again later.',
      life: 4000,
    })
    providers.value = []
    totalRecords.value = 0
  } finally {
    loading.value = false
  }
}

// Watch search separately with debounce
watch(
  () => filters.search,
  () => {
    debouncedSearchLoad()
  },
)

// Watch all non-search filters with immediate loading
watch(
  () => [
    filters.location,
    filters.skill,
    filters.category_id,
    filters.min_price,
    filters.max_price,
    filters.sort_by,
  ],
  () => {
    debouncedSearchLoad.cancel()
    currentPage.value = 1
    loadProviders()
  },
)

// Watch pagination separately (no page reset needed)
watch(
  () => [currentPage.value, itemsPerPage.value],
  () => {
    loadProviders()
  },
)

const handleFilterChange = (newFilters) => {
  Object.assign(filters, newFilters)
}

const handleSortChange = (value) => {
  filters.sort_by = value
}

const handlePageChange = (event) => {
  currentPage.value = Math.floor(event.first / event.rows) + 1
  itemsPerPage.value = event.rows
}

onMounted(() => {
  loadProviders()
})

onBeforeUnmount(() => {
  debouncedSearchLoad.cancel()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Main Search Bar Section (hidden on mobile, visible on sm+) -->
    <section class="hidden sm:block bg-[#670723] text-white px-2 py-6 sm:px-4 sm:py-8">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">Find Service Providers</h2>
        <div class="flex flex-col gap-3 sm:gap-4 md:flex-row">
          <IconField iconPosition="left" class="flex-1">
            <InputIcon class="pi pi-search" />
            <InputText
              v-model="filters.search"
              placeholder="Search for providers..."
              class="w-full text-base sm:text-lg py-2 sm:py-3"
            />
          </IconField>
          <Button
            label="Search"
            icon="pi pi-search"
            severity="primary"
            @click="debouncedSearchLoad()"
            class="w-full sm:w-auto text-base sm:text-lg py-2 sm:py-3 !bg-black !text-white hover:!bg-[#5a061e]"
          />
        </div>
      </div>
    </section>

    <!-- Floating Action Button for mobile -->
    <button
      class="fixed bottom-6 right-6 z-50 flex sm:hidden items-center justify-center w-14 h-14 rounded-full bg-[#6d0019] text-white shadow-lg hover:bg-[#8a1a2b] transition-all"
      @click="showMobileFilters = true"
      aria-label="Show search and filters"
    >
      <i class="pi pi-sliders-h text-2xl"></i>
    </button>

    <!-- Mobile Filters/Search Modal -->
    <Dialog
      v-model:visible="showMobileFilters"
      modal
      :closable="true"
      class="sm:hidden w-[95vw] max-w-md mx-auto"
      :style="{ top: '10vh' }"
    >
      <template #header>
        <span class="font-bold text-lg">Search & Filters</span>
      </template>
      <div class="flex flex-col gap-4">
        <IconField iconPosition="left" class="flex-1">
          <InputIcon class="pi pi-search" />
          <InputText
            v-model="filters.search"
            placeholder="Search for providers..."
            class="w-full text-base py-2"
          />
        </IconField>
        <FilterSidebar :filters="filters" @update="handleFilterChange" />
        <Button
          label="Apply"
          icon="pi pi-check"
          severity="primary"
          @click="showMobileFilters = false"
          class="w-full text-base py-2"
        />
      </div>
    </Dialog>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-1 py-2 sm:px-2 md:px-4 md:py-8">
      <div class="flex flex-col gap-6 md:flex-row">
        <!-- Left Sidebar - Filters (hidden on mobile) -->
        <div class="hidden md:block w-full md:w-72 flex-shrink-0 mb-4 md:mb-0">
          <FilterSidebar :filters="filters" @update="handleFilterChange" />
        </div>

        <!-- Right Content Area -->
        <div class="flex-1 min-w-0">
          <!-- View Controls and Pagination Info -->
          <div
            class="flex flex-col md:flex-row items-start md:items-center justify-between mb-4 sm:mb-6 gap-3 sm:gap-4"
          >
            <div class="text-gray-600 text-sm sm:text-base">
              Showing
              {{ totalRecords === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1 }}
              -
              {{ Math.min(currentPage * itemsPerPage, totalRecords) }}
              of {{ totalRecords }} providers
            </div>
            <div
              class="flex flex-col md:flex-row items-start md:items-center gap-2 sm:gap-4 w-full md:w-auto"
            >
              <div class="flex items-center gap-2">
                <label class="text-xs sm:text-sm text-gray-600">Sort by:</label>
                <select
                  :value="filters.sort_by"
                  @change="handleSortChange($event.target.value)"
                  class="px-2 py-1 sm:px-3 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm"
                >
                  <option value="newest">Newest</option>
                  <option value="oldest">Oldest</option>
                  <option value="name">Name</option>
                </select>
              </div>
              <div class="flex gap-1">
                <button
                  @click="layout = 'grid'"
                  :class="[
                    'px-2 py-1 sm:px-3 sm:py-2 rounded-lg transition',
                    layout === 'grid'
                      ? 'bg-[#6d0019] text-white'
                      : 'bg-gray-200 text-gray-600 hover:bg-gray-300',
                  ]"
                >
                  <i class="pi pi-th-large"></i>
                </button>
                <button
                  @click="layout = 'list'"
                  :class="[
                    'px-2 py-1 sm:px-3 sm:py-2 rounded-lg transition',
                    layout === 'list'
                      ? 'bg-[#6d0019] text-white'
                      : 'bg-gray-200 text-gray-600 hover:bg-gray-300',
                  ]"
                >
                  <i class="pi pi-list"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Providers Grid/List -->
          <div v-if="loading" class="flex items-center justify-center py-12">
            <i class="pi pi-spin pi-spinner text-4xl text-[#6d0019]"></i>
          </div>
          <div v-else-if="providers.length === 0" class="text-center py-12">
            <i class="pi pi-inbox text-5xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-600 text-lg">No providers found. Try adjusting your filters.</p>
          </div>
          <div
            v-else-if="layout === 'grid'"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <ProviderCard
              v-for="provider in providers"
              :key="provider.id"
              :provider="provider"
              layout="grid"
            />
          </div>
          <div v-else class="space-y-4">
            <ProviderCard
              v-for="provider in providers"
              :key="provider.id"
              :provider="provider"
              layout="list"
            />
          </div>

          <!-- Pagination -->
          <div class="mt-8 flex justify-center w-full">
            <Paginator
              :rows="itemsPerPage"
              :total-records="totalRecords"
              :first="(currentPage - 1) * itemsPerPage"
              :rows-per-page-options="[12, 24, 36]"
              @page="handlePageChange"
              class="flex justify-center w-full"
            ></Paginator>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
