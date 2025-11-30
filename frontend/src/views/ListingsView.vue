<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useListingsStore } from '@/stores/ListingsStore'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Dialog from 'primevue/dialog'
import ServiceCard from '@/components/ServiceCard.vue'
import FilterSidebar from '@/components/FilterSidebar.vue'
import { useToastStore } from '@/stores/toastStore'
import api from '@/composables/axios'

const listingsStore = useListingsStore()
const toastStore = useToastStore()
const layout = ref('grid')
const searchQuery = ref('')
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(12)

const filters = reactive({
  categories: [],
  minBudget: 0,
  maxBudget: 10000,
  minRating: 5,
  sortBy: 'newest',
})

// Add near other state items
const showAddModal = ref(false)
const activeStep = ref(1)

// Form fields for new listing
const newTitle = ref('')
const newDescription = ref('')
const newBudget = ref(null)
const newCategoryIds = ref([]) // multi-select for categories
const categories = ref([])

const tags = ref([]) // fetched from backend
const selectedTags = ref([]) // multi-select for tags
const customTagInput = ref('') // allow user to type a custom tag

// Computed
const paginatedListings = computed(() => {
  return listingsStore.listings
})

// Methods
const loadListings = async () => {
  try {
    loading.value = true
    await listingsStore.fetchListings({
      page: currentPage.value,
      per_page: itemsPerPage.value,
      search: searchQuery.value,
      categories: filters.categories,
      min_budget: filters.minBudget,
      max_budget: filters.maxBudget,
      min_rating: filters.minRating,
      sort_by: filters.sortBy,
    })
    totalRecords.value = listingsStore.total
  } catch (error) {
    console.error('Failed to load listings:', error)
    toastStore.showError('Failed to load listings', 'Error')
  } finally {
    loading.value = false
  }
}

const handleSearch = (value) => {
  searchQuery.value = value
  currentPage.value = 1
  loadListings()
}

const handleFilterChange = (newFilters) => {
  Object.assign(filters, newFilters)
  currentPage.value = 1
  loadListings()
}

const handlePageChange = (event) => {
  currentPage.value = event.page + 1
  loadListings()
}

const handleSortChange = (sortBy) => {
  filters.sortBy = sortBy
  currentPage.value = 1
  loadListings()
}

// Load categories for dropdown
const loadCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = data.data ?? data
  } catch (err) {
    console.error('Failed to load categories', err)
    categories.value = []
  }
}

// Load tags (try public /tags, fallback to /admin/tags)
const loadTags = async () => {
  try {
    let res
    try {
      res = await api.get('/tags')
    } catch {
      res = await api.get('/admin/tags')
    }
    const data = res.data
    tags.value = data.data ?? data
  } catch (err) {
    console.error('Failed to load tags', err)
    tags.value = []
  }
}

// Tag helpers: add a custom tag
const addCustomTag = () => {
  const t = (customTagInput.value || '').trim()
  if (!t) return
  if (!selectedTags.value.includes(t)) {
    selectedTags.value.push(t)
  }
  customTagInput.value = ''
}

// Open modal and fetch categories + tags
const openAddModal = async () => {
  // Show modal immediately
  showAddModal.value = true
  activeStep.value = 1

  // Load categories and tags in parallel with independent error handling
  const categoriesPromise = loadCategories().catch((err) => {
    console.error('Categories load failed:', err)
    categories.value = []
  })

  const tagsPromise = loadTags().catch((err) => {
    console.error('Tags load failed:', err)
    tags.value = []
  })

  await Promise.all([categoriesPromise, tagsPromise])
}

// Navigation in stepper with validation
const goNext = () => {
  // Step 1: Title required
  // if (activeStep.value === 1 && (!newTitle.value || newTitle.value.trim() === '')) {
  //   toastStore.showError('Title is required')
  //   return
  // }
  // // Step 2: At least one category and one tag required
  // if (
  //   activeStep.value === 2 &&
  //   (newCategoryIds.value.length === 0 || selectedTags.value.length === 0)
  // ) {
  //   toastStore.showError('At least one category and one tag are required')
  //   return
  // }
  if (activeStep.value < 3) activeStep.value++
}
const goBack = () => {
  if (activeStep.value > 1) activeStep.value--
}

// finish and submit
const submitListing = async () => {
  // Validate required field(s)
  if (!newTitle.value || newTitle.value.trim() === '') {
    toastStore.showError('Please enter a title for your listing')
    return
  }

  const payload = {
    title: newTitle.value,
    description: newDescription.value || null,
    budget: newBudget.value ?? null,
    category_ids: newCategoryIds.value.length > 0 ? newCategoryIds.value : null,
    tags: selectedTags.value.length > 0 ? selectedTags.value : null,
  }

  try {
    // Use store action (preferred)
    await listingsStore.createListing(payload)
    toastStore.showSuccess('Listing created successfully')
    showAddModal.value = false

    // Reset form
    newTitle.value = ''
    newDescription.value = ''
    newBudget.value = null
    newCategoryIds.value = []
    selectedTags.value = []
    customTagInput.value = ''

    // Refresh listing list (if you prefer to refetch instead of relying on store append)
    await loadListings()
  } catch (err) {
    const msg = err.response?.data?.message || err.message || 'Failed to create listing'
    toastStore.showError(msg, 'Error')
  }
}

// Lifecycle
onMounted(() => {
  loadListings()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Main Search Bar Section -->
    <section class="bg-[#6d0019] text-white px-4 py-8">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl font-bold mb-6">Find Services</h2>
        <div class="flex gap-4">
          <IconField icon-position="left" class="flex-1">
            <InputIcon class="pi pi-search"></InputIcon>
            <InputText
              v-model="searchQuery"
              placeholder="Search for services..."
              class="w-full"
              @input="handleSearch($event.target.value)"
            />
          </IconField>
          <button class="bg-black hover:bg-[#] text-white px-4 py-2 rounded">Search</button>
        </div>
      </div>
    </section>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 py-8">
      <div class="flex gap-6">
        <!-- Left Sidebar - Filters -->
        <div class="w-75 flex-shrink-0">
          <FilterSidebar :filters="filters" @update="handleFilterChange" />
        </div>

        <!-- Right Content Area -->
        <div class="flex-1">
          <!-- View Controls and Pagination Info -->
          <div class="flex items-center justify-between mb-6">
            <div class="text-gray-600">
              Showing 1-{{ itemsPerPage }} of {{ totalRecords }} services
            </div>

            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Sort by:</label>
                <select
                  :value="filters.sortBy"
                  @change="handleSortChange($event.target.value)"
                  class="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                >
                  <option value="newest">Newest</option>
                  <option value="oldest">Oldest</option>
                  <option value="price_low">Price: Low to High</option>
                  <option value="price_high">Price: High to Low</option>
                  <option value="rating">Top Rated</option>
                </select>
              </div>

              <!-- View Toggle Buttons -->
              <div class="flex gap-1">
                <button
                  @click="layout = 'grid'"
                  :class="[
                    'px-3 py-2 rounded-lg transition',
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
                    'px-3 py-2 rounded-lg transition',
                    layout === 'list'
                      ? 'bg-[#6d0019] text-white'
                      : 'bg-gray-200 text-gray-600 hover:bg-gray-300',
                  ]"
                >
                  <i class="pi pi-list"></i>
                </button>
              </div>
              <!-- Add Listing button -->
              <Button
                class="bg-[#10b981] hover:bg-[#0ea77a] text-white"
                icon="pi pi-plus"
                label="Add Listing"
                @click="openAddModal"
              />
            </div>
          </div>

          <!-- Services Grid/List -->
          <div v-if="loading" class="flex items-center justify-center py-12">
            <i class="pi pi-spin pi-spinner text-4xl text-[#6d0019]"></i>
          </div>

          <div v-else-if="paginatedListings.length === 0" class="text-center py-12">
            <i class="pi pi-inbox text-5xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-600 text-lg">No services found. Try adjusting your filters.</p>
          </div>

          <!-- Grid Layout -->
          <div
            v-else-if="layout === 'grid'"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <ServiceCard
              v-for="service in paginatedListings"
              :key="service.id"
              :service="service"
              layout="grid"
            />
          </div>

          <!-- List Layout -->
          <div v-else class="space-y-4">
            <ServiceCard
              v-for="service in paginatedListings"
              :key="service.id"
              :service="service"
              layout="list"
            />
          </div>

          <!-- Pagination -->
          <div class="mt-8 flex justify-center">
            <Paginator
              :rows="itemsPerPage"
              :total-records="totalRecords"
              :rows-per-page-options="[12, 24, 36]"
              @page="handlePageChange"
              class="flex justify-center"
            ></Paginator>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Listing Modal -->
    <Dialog
      v-model:visible="showAddModal"
      :modal="true"
      header="Create a Listing"
      header-style="background-color: #6d0019; color: white;"
      :style="{ width: '800px' }"
    >
      <div class="p-6">
        <!-- Custom Stepper Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between gap-4">
            <!-- Step 1 -->
            <div class="flex flex-col items-center flex-1">
              <div
                :class="[
                  'w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold border-2 mb-2',
                  activeStep >= 1 ? 'bg-[#6d0019] border-[#6d0019]' : 'bg-gray-300 border-gray-300',
                ]"
              >
                1
              </div>
              <span class="text-sm font-semibold text-gray-800">About</span>
            </div>

            <!-- Line 1 -->
            <div
              :class="['flex-1 h-1 mb-8', activeStep > 1 ? 'bg-[#6d0019]' : 'bg-gray-300']"
            ></div>

            <!-- Step 2 -->
            <div class="flex flex-col items-center flex-1">
              <div
                :class="[
                  'w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold border-2 mb-2',
                  activeStep >= 2 ? 'bg-[#6d0019] border-[#6d0019]' : 'bg-gray-300 border-gray-300',
                ]"
              >
                2
              </div>
              <span class="text-sm font-semibold text-gray-800">Category & Tags</span>
            </div>

            <!-- Line 2 -->
            <div
              :class="['flex-1 h-1 mb-8', activeStep > 2 ? 'bg-[#6d0019]' : 'bg-gray-300']"
            ></div>

            <!-- Step 3 -->
            <div class="flex flex-col items-center flex-1">
              <div
                :class="[
                  'w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold border-2 mb-2',
                  activeStep >= 3 ? 'bg-[#6d0019] border-[#6d0019]' : 'bg-gray-300 border-gray-300',
                ]"
              >
                3
              </div>
              <span class="text-sm font-semibold text-gray-800">Pricing</span>
            </div>
          </div>
        </div>

        <!-- Content Area -->
        <div class="min-h-75 mb-8">
          <!-- Step 1: About -->
          <div v-if="activeStep === 1" class="space-y-4">
            <label class="font-semibold">Title <span class="text-red-500">*</span></label>
            <InputText
              v-model="newTitle"
              placeholder="Service title e.g. 'English Tutor - Academic Support'"
              class="w-full"
            />

            <label class="font-semibold">Description</label>
            <textarea
              v-model="newDescription"
              rows="8"
              class="w-full border rounded px-3 py-2"
              placeholder="Describe your service..."
            ></textarea>
          </div>

          <!-- Step 2: Category & Tags -->
          <div v-if="activeStep === 2" class="space-y-4">
            <div>
              <label class="font-semibold">Categories <span class="text-red-500">*</span></label>
              <div class="flex flex-wrap gap-2 mt-2">
                <label
                  v-for="cat in categories"
                  :key="cat.id"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="cat.id"
                    v-model="newCategoryIds"
                    class="accent-[#6d0019] w-4 h-4"
                  />
                  <span class="text-gray-800">{{ cat.name }}</span>
                </label>
              </div>
            </div>

            <div>
              <label class="font-semibold">Tags <span class="text-red-500">*</span></label>
              <div class="flex flex-wrap gap-2 mt-2">
                <label
                  v-for="(t, idx) in tags"
                  :key="t.id ?? idx"
                  class="flex items-center gap-2 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="t.name ?? t"
                    v-model="selectedTags"
                    class="accent-[#6d0019] w-4 h-4"
                  />
                  <span class="text-gray-800">{{ t.name ?? t }}</span>
                </label>
              </div>

              <div class="mt-3 flex gap-2 items-center">
                <InputText
                  v-model="customTagInput"
                  placeholder="Type a custom tag"
                  class="flex-1"
                />
                <Button label="Add" icon="pi pi-check" @click="addCustomTag" />
              </div>

              <p class="text-sm text-gray-500 mt-2">
                Tip: You can select multiple tags or add your own.
              </p>
            </div>
          </div>

          <!-- Step 3: Pricing -->
          <div v-if="activeStep === 3" class="space-y-4">
            <label class="font-semibold">Budget (₱)</label>
            <InputText v-model="newBudget" placeholder="Enter budget (numbers only)" class="w-40" />
            <InputText v-model="newFrequency" placeholder="Frequency" class="w-40" />
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center">
          <Button
            :label="activeStep === 1 ? 'Cancel' : '← Back'"
            :class="activeStep === 1 ? 'bg-gray-300 text-gray-700' : 'bg-gray-200 text-gray-700'"
            @click="activeStep === 1 ? (showAddModal = false) : goBack()"
          />
          <Button
            :label="activeStep === 3 ? 'Create Listing →' : 'Next →'"
            class="bg-[#6d0019] text-white"
            @click="activeStep === 3 ? submitListing() : goNext()"
          />
        </div>
      </div>
    </Dialog>
  </div>
</template>

<style scoped></style>
