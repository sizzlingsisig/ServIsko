<script setup>
function handleSortChange(value) {
  filters.sort_by = value
}
function handlePageChange(event) {
  currentPage.value = Math.floor(event.first / event.rows) + 1
  itemsPerPage.value = event.rows
}
import { ref, reactive, onMounted, watch, onBeforeUnmount } from 'vue'
import AddListingModal from '@/components/AddListingModal.vue'
import useToastHelper from '@/composables/useToastHelper'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import ListingCard from '@/components/ListingCard.vue'
import FilterSidebar from '@/components/FilterSidebar.vue'
import api from '@/composables/axios'
import { useAuthStore } from '@/stores/AuthStore'
import { ref as vueRef } from 'vue'
import Dialog from 'primevue/dialog'

const showMobileFilters = vueRef(false)
// define constants
const DEBOUNCE_DELAY = 500
const DEFAULTS = {
  MAX_BUDGET: 10000,
  MIN_RATING: 5,
  ITEMS_PER_PAGE: 12,
}

const authStore = useAuthStore()
const { success, error, warning, info } = useToastHelper()

/* debounce function
TODO: move to composable
*/
const debounce = (func, delay) => {
  let timeoutId
  const debounced = function (...args) {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => func.apply(this, args), delay)
  }
  debounced.cancel = () => clearTimeout(timeoutId)
  return debounced
}

// ref and reactive state
const layout = ref('grid')
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(DEFAULTS.ITEMS_PER_PAGE)

function handleFilterChange(updated) {
  Object.assign(filters, updated)
}


// UI state
const showAddModal = ref(false)
const categories = ref([])
const filteredCategories = ref([])
const tags = ref([])
const filteredTags = ref([])
const listings = ref([])

/* loads listings from API
  also includes filter and pagination handling
 */
const loadListings = async () => {
  try {
    loading.value = true
    const params = {
      category: filters.category,
      search: filters.search,
      minBudget: filters.minBudget,
      maxBudget: filters.maxBudget,
      minRating: filters.minRating,
      sort_by: filters.sort_by,
      page: currentPage.value,
      per_page: itemsPerPage.value,
    }

    const response = await api.get('/listings', { params })
    if (response.data.success && response.data.data) {
      const paginatedData = response.data.data
      let allListings = paginatedData.data || []
      // Filter out listings owned by the current user (if logged in)
      if (authStore.isAuthenticated && authStore.user && authStore.user.id) {
        allListings = allListings.filter(l => l.seeker_user_id !== authStore.user.id)
      }
      listings.value = allListings
      totalRecords.value = allListings.length
    } else {
      listings.value = []
      totalRecords.value = 0
    }
  } catch (err) {
    console.error('Failed to load listings:', err)
    listings.value = []
    totalRecords.value = 0
  } finally {
    loading.value = false
  }
}

// reactive filters
const filters = reactive({
  category: null,
  search: '',
  minBudget: 0,
  maxBudget: DEFAULTS.MAX_BUDGET,
  minRating: DEFAULTS.MIN_RATING,
  sort_by: 'newest',
})


// loads categories from API
const loadCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = (data.data ?? []).slice().sort((a, b) => a.name.localeCompare(b.name))
    filteredCategories.value = categories.value
  } catch (err) {
    console.error('Failed to load categories:', err)
    error('Failed to load categories')
    categories.value = []
    filteredCategories.value = []
  }
}

/* loads tags from API
 */
const loadTags = async () => {
  try {
    const res = await api.get('/seeker/tags')
    const data = res.data
    const rawTags = data.data ?? data
    tags.value = rawTags.map(t => typeof t === 'string' ? t : t.name)
    console.log('Loaded tags:', tags.value)
  } catch (err) {
    console.error('Failed to load tags:', err)
    tags.value = []
  }
}

const debouncedSearchLoad = debounce(() => {
  currentPage.value = 1
  loadListings()
}, DEBOUNCE_DELAY)

// Watch search separately with debounce
watch(
  () => filters.search,
  () => {
    debouncedSearchLoad()
  }
)

// Watch all non-search filters with immediate loading
watch(
  () => [
    filters.category,
    filters.minBudget,
    filters.maxBudget,
    filters.minRating,
    filters.sort_by,
  ],
  () => {
    debouncedSearchLoad.cancel() // Cancel pending search
    currentPage.value = 1
    loadListings()
  }
)

// Watch pagination separately (no page reset needed)
watch(
  () => [currentPage.value, itemsPerPage.value],
  () => {
    loadListings()
  }
)

// Handle tag search filtering
const searchTagsInternal = (query) => {
  if (!query) {
    filteredTags.value = tags.value
  } else {
    filteredTags.value = tags.value.filter(tag =>
      tag.toLowerCase().includes(query.toLowerCase())
    )
  }
}

const debouncedSearchTags = debounce(searchTagsInternal, 300)

const searchTags = (event) => {
  debouncedSearchTags(event.query)
}

// ============================================================================
// Modal Management
// ============================================================================
const openAddModal = async () => {
  if (!authStore.isAuthenticated) {
    error('You must be logged in to add a listing')
    return
  }
  showAddModal.value = true
  await Promise.all([
    loadCategories().catch(() => { categories.value = [] }),
    loadTags().catch(() => { tags.value = [] }),
  ])
}

const submitListing = async (payload) => {
  console.log('Submitting payload:', JSON.stringify(payload, null, 2))
  try {
    info('Creating listing...')
    const response = await api.post('/seeker/listings', payload)
    if (response.data.success) {
      success('Listing created successfully!')
      showAddModal.value = false
      await loadListings()
    } else {
      const msg = response.data.message || 'Failed to create listing'
      error(msg)
    }
  } catch (err) {
    console.error('API error:', err)
    if (err.response?.status === 422) {
      if (err.response.data?.errors) {
        const errors = err.response.data.errors
        const errorMessages = Object.entries(errors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n')
        error(errorMessages, 'Validation Error')
      } else if (err.response.data?.suggestions) {
        const msg = err.response.data.message
        const suggestions = err.response.data.suggestions
        info(`${msg}\nSuggestions: ${suggestions.join(', ')}`, 'Tag Suggestion')
      } else {
        error(err.response.data?.message || 'Validation failed', 'Validation Error')
      }
    } else if (err.response?.status === 500) {
      const serverMsg = err.response.data?.message || 'Server error occurred'
      error(`Server Error: ${serverMsg}`, 'Server Error')
    } else {
      const msg = err.response?.data?.message || err.message || 'Failed to create listing'
      error(msg)
    }
  }
}

// ============================================================================
// Lifecycle Hooks
// ============================================================================
onMounted(() => {
  loadCategories()
  loadListings()
  loadTags()
})

// Cancel pending debounced calls before unmount to prevent memory leaks
onBeforeUnmount(() => {
  debouncedSearchLoad.cancel()
  debouncedSearchTags.cancel()
})
</script>
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Main Search Bar Section (hidden on mobile, visible on sm+) -->
    <section class="hidden sm:block bg-primary-500 text-white px-2 py-6 sm:px-4 sm:py-8">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">Find Listings</h2>
        <div class="flex flex-col gap-3 sm:gap-4 md:flex-row">
          <IconField iconPosition="left" class="flex-1">
            <InputIcon class="pi pi-search" />
            <InputText
              v-model="filters.search"
              placeholder="Search for services..."
              class="w-full text-base sm:text-lg py-2 sm:py-3"
              @input="handleSearch($event.target.value)"
            />
          </IconField>
          <Button
            label="Search"
            icon="pi pi-search"
            severity="primary"
            @click="handleSearch(filters.search)"
            class="w-full sm:w-auto text-base sm:text-lg py-2 sm:py-3"
          />
        </div>
      </div>
    </section>

    <!-- Floating Action Button for mobile -->
    <button
      class="fixed bottom-6 right-6 z-50 flex sm:hidden items-center justify-center w-14 h-14 rounded-full bg-primary-500 text-white shadow-lg hover:bg-primary-600 transition-all"
      @click="showMobileFilters = true"
      aria-label="Show search and filters"
    >
      <i class="pi pi-sliders-h text-2xl"></i>
    </button>

    <!-- Mobile Filters/Search Modal -->
    <Dialog v-model:visible="showMobileFilters" modal :closable="true" class="sm:hidden w-[95vw] max-w-md mx-auto" :style="{ top: '10vh' }">
      <template #header>
        <span class="font-bold text-lg">Search & Filters</span>
      </template>
      <div class="flex flex-col gap-4">
        <IconField iconPosition="left" class="flex-1">
          <InputIcon class="pi pi-search" />
          <InputText
            v-model="filters.search"
            placeholder="Search for services..."
            class="w-full text-base py-2"
            @input="handleSearch($event.target.value)"
          />
        </IconField>
        <FilterSidebar :filters="filters" :categories="categories" @update="handleFilterChange" />
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
          <FilterSidebar :filters="filters" :categories="categories" @update="handleFilterChange" />
        </div>

        <!-- Right Content Area -->
        <div class="flex-1 min-w-0">
          <!-- View Controls and Pagination Info -->
          <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-4 sm:mb-6 gap-3 sm:gap-4">
            <div class="text-gray-600 text-sm sm:text-base">
              Showing
              {{ totalRecords === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1 }}
              -
              {{ Math.min(currentPage * itemsPerPage, totalRecords) }}
              of {{ totalRecords }} services
            </div>
            <div class="flex flex-col md:flex-row items-start md:items-center gap-2 sm:gap-4 w-full md:w-auto">
              <div class="flex items-center gap-2">
                <label class="text-xs sm:text-sm text-gray-600">Sort by:</label>
                <select
                  :value="filters.sort_by"
                  @change="handleSortChange($event.target.value)"
                  class="px-2 py-1 sm:px-3 sm:py-2 border border-gray-300 rounded-lg text-xs sm:text-sm"
                >
                  <option value="newest">Newest</option>
                  <option value="oldest">Oldest</option>
                  <option value="price_low">Price: Low to High</option>
                  <option value="price_high">Price: High to Low</option>
                  <option value="rating">Top Rated</option>
                </select>
              </div>
              <div class="flex gap-1">
                <button
                  @click="layout = 'grid'"
                  :class="[
                    'px-2 py-1 sm:px-3 sm:py-2 rounded-lg transition',
                    layout === 'grid'
                      ? 'bg-primary-500 text-white'
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
                      ? 'bg-primary-500 text-white'
                      : 'bg-gray-200 text-gray-600 hover:bg-gray-300',
                  ]"
                >
                  <i class="pi pi-list"></i>
                </button>
              </div>
              <Button
                v-if="authStore.isAuthenticated"
                class="bg-[#10b981] hover:bg-[#0ea77a] text-white w-full md:w-auto text-xs sm:text-base px-2 py-1 sm:px-4 sm:py-2"
                icon="pi pi-plus"
                label="Add Listing"
                @click="openAddModal"
              />
              <a
                v-if="authStore.isAuthenticated"
                href="javascript:void(0)"
                @click="$router.push('/profile/listings')"
                class="font-semibold text-black hover:text-gray-700 decoration-0 flex items-center gap-2 w-full md:w-auto px-2 py-1 sm:px-4 sm:py-2 rounded transition text-xs sm:text-base"
                style="text-decoration:none;"
              >
                <i class="pi pi-list"></i>
                My Listings
              </a>
              <span
                v-else
                class="text-gray-400 text-xs sm:text-sm font-semibold select-none flex items-center gap-2"
              >
                <i class="pi pi-lock"></i>
                Only logged-in users can add listings
              </span>
            </div>
          </div>

          <!-- Services Grid/List -->
          <div v-if="loading" class="flex items-center justify-center py-12">
            <i class="pi pi-spin pi-spinner text-4xl text-[#6d0019]"></i>
          </div>
          <div v-else-if="listings.length === 0" class="text-center py-12">
            <i class="pi pi-inbox text-5xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-600 text-lg">No services found.  Try adjusting your filters.</p>
          </div>
          <div
            v-else-if="layout === 'grid'"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <ListingCard
              v-for="service in listings"
              :key="service.id"
              :service="service"
              layout="grid"
            />
          </div>
          <div v-else class="space-y-4">
            <ListingCard
              v-for="service in listings"
              :key="service.id"
              :service="service"
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

    <!-- Add Listing Modal -->
    <AddListingModal
      v-model:showAddModal="showAddModal"
      :categories="categories"
      :filteredCategories="filteredCategories"
      :tags="tags.map(tag => typeof tag === 'object' ? tag : { name: tag })"
      :filteredTags="filteredTags"
      @created="submitListing"
    />
  </div>
</template>

<style scoped>
/* Custom chip styling */
:deep(.p-chip) {
  background: #fef3c7;
  color: #92400e;
  font-weight: 600;
}

:deep(.p-chip .p-chip-remove-icon) {
  color: #92400e;
}

:deep(.p-chip .p-chip-remove-icon:hover) {
  color: #78350f;
}

/* AutoComplete styling */
:deep(.p-autocomplete) {
  width: 100%;
}

:deep(.p-autocomplete-input) {
  width: 100%;
}
</style>
