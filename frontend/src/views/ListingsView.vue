<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useListingsStore } from '@/stores/ListingsStore'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
// Add these imports at top of ListingsView.vue (near other primevue imports)
import Dialog from 'primevue/dialog'
// InputTextarea caused import resolution errors in this environment; use native textarea instead
import Dropdown from 'primevue/dropdown'
// DataView components removed — not used and causing import resolution errors
import ServiceCard from '@/components/ServiceCard.vue'
import FilterSidebar from '@/components/FilterSidebar.vue'
import { useToastStore } from '@/stores/toastStore'
import api from '@/composables/axios' // axios instance (if not already imported)

const listingsStore = useListingsStore()
const toastStore = useToastStore()

// State
const layout = ref('grid')
const searchQuery = ref('')
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(12)

const filters = reactive({
  categories: [],
  minBudget: 0,
  maxBudget: 5000,
  minRating: 0,
  sortBy: 'newest',
})

// Add near other state items
const showAddModal = ref(false)
const activeStep = ref(1)

// Form fields for new listing
const newTitle = ref('')
const newDescription = ref('')
const newBudget = ref(null)
const newCategoryId = ref(null)
const categories = ref([])

const tags = ref([]) // fetched from backend
const selectedTag = ref(null) // currently selected tag name (string)
const customTagInput = ref('') // allow user to type a custom tag

// Computed
const paginatedListings = computed(() => {
  return listingsStore.listings
})

const displayView = computed(() => {
  return layout.value === 'grid' ? 'grid' : 'list'
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
    } catch (e) {
      res = await api.get('/admin/tags')
    }
    const data = res.data
    tags.value = data.data ?? data
  } catch (err) {
    console.error('Failed to load tags', err)
    tags.value = []
  }
}

// Tag helpers: select a single tag (radio-style) or add a custom tag
const selectTag = (tagName) => {
  selectedTag.value = tagName
}
const addCustomTag = () => {
  const t = (customTagInput.value || '').trim()
  if (!t) return
  selectedTag.value = t
  customTagInput.value = ''
}

// Open modal and fetch categories + tags
const openAddModal = async () => {
  await Promise.all([loadCategories(), loadTags()])
  activeStep.value = 1
  showAddModal.value = true
}

// Navigation in stepper with validation
const goNext = () => {
  // Step 1: Title required
  if (activeStep.value === 1 && (!newTitle.value || newTitle.value.trim() === '')) {
    toastStore.showError('Title is required')
    return
  }
  // Step 2: Category and Tag required
  if (activeStep.value === 2 && (!newCategoryId.value || !selectedTag.value)) {
    toastStore.showError('Category and Tag are required')
    return
  }
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
    category_id: newCategoryId.value ?? null,
    tags: selectedTag.value ? [selectedTag.value] : null,
  }

  try {
    // Use store action (preferred)
    const created = await listingsStore.createListing(payload)
    toastStore.showSuccess('Listing created successfully')
    showAddModal.value = false

    // Reset form
    newTitle.value = ''
    newDescription.value = ''
    newBudget.value = null
    newCategoryId.value = null
    newTags.value = []
    tagInput.value = ''

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
        <div class="w-56 flex-shrink-0">
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
                <!-- Add Listing button -->
                <Button
                  class="ml-4 bg-[#10b981] hover:bg-[#0ea77a] text-white"
                  icon="pi pi-plus"
                  label="+ Add Listing"
                  @click="openAddModal"
                />
              </div>

              <!-- View Toggle Buttons -->
              <div class="flex gap-2 ml-4">
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
      :style="{ width: '720px' }"
      draggable
    >
      <div class="p-4">
        <Stepper v-model:value="activeStep" class="w-full" linear>
          <StepList>
            <Step v-slot="{ activateCallback, value, a11yAttrs }" asChild :value="1">
              <div class="flex items-center gap-2" v-bind="a11yAttrs.root">
                <button
                  class="bg-transparent border-0 inline-flex flex-col items-center gap-1"
                  @click="activateCallback"
                  v-bind="a11yAttrs.header"
                >
                  <span
                    :class="[
                      'rounded-full border-2 w-10 h-10 inline-flex items-center justify-center',
                      { 'bg-[#6d0019] text-white': value <= activeStep },
                    ]"
                  >
                    <i class="pi pi-info"></i>
                  </span>
                  <span class="text-xs text-gray-600 mt-1">About</span>
                </button>
                <Divider />
              </div>
            </Step>
            <Step v-slot="{ activateCallback, value, a11yAttrs }" asChild :value="2">
              <div class="flex items-center gap-2" v-bind="a11yAttrs.root">
                <button
                  class="bg-transparent border-0 inline-flex flex-col items-center gap-1"
                  @click="activateCallback"
                  v-bind="a11yAttrs.header"
                >
                  <span
                    :class="[
                      'rounded-full border-2 w-10 h-10 inline-flex items-center justify-center',
                      { 'bg-[#6d0019] text-white': value <= activeStep },
                    ]"
                  >
                    <i class="pi pi-tags"></i>
                  </span>
                  <span class="text-xs text-gray-600 mt-1">Category & Tags</span>
                </button>
                <Divider />
              </div>
            </Step>
            <Step v-slot="{ activateCallback, value, a11yAttrs }" asChild :value="3">
              <div class="flex items-center gap-2" v-bind="a11yAttrs.root">
                <button
                  class="bg-transparent border-0 inline-flex flex-col items-center gap-1"
                  @click="activateCallback"
                  v-bind="a11yAttrs.header"
                >
                  <span
                    :class="[
                      'rounded-full border-2 w-10 h-10 inline-flex items-center justify-center',
                      { 'bg-[#6d0019] text-white': value <= activeStep },
                    ]"
                  >
                    <i class="pi pi-wallet"></i>
                  </span>
                  <span class="text-xs text-gray-600 mt-1">Pricing</span>
                </button>
              </div>
            </Step>
          </StepList>

          <StepPanels class="bg-transparent">
            <!-- Step 1: About -->
            <StepPanel :value="1">
              <div class="space-y-4">
                <label class="font-semibold">Title <span class="text-red-500">*</span></label>
                <InputText
                  v-model="newTitle"
                  placeholder="Service title e.g. 'English Tutor - Academic Support'"
                  class="w-full"
                />

                <label class="font-semibold">Description</label>
                <textarea
                  v-model="newDescription"
                  rows="4"
                  class="w-full border rounded px-3 py-2"
                  placeholder="Describe your service..."
                ></textarea>

                <div class="flex justify-end gap-2 mt-4">
                  <Button label="Next" class="bg-[#6d0019] text-white" @click="goNext" />
                </div>
              </div>
            </StepPanel>
            <!-- Step 2: Category & Tags -->
            <StepPanel :value="2">
              <div class="space-y-4">
                <div>
                  <label class="font-semibold">Category <span class="text-red-500">*</span></label>
                  <Dropdown
                    v-model="newCategoryId"
                    :options="categories"
                    option-label="name"
                    option-value="id"
                    placeholder="Select category"
                    class="w-full"
                  />
                </div>

                <div>
                  <label class="font-semibold"
                    >Choose a tag <span class="text-red-500">*</span></label
                  >
                  <div class="flex flex-wrap gap-2 mt-2">
                    <button
                      v-for="(t, idx) in tags"
                      :key="t.id ?? idx"
                      :class="[
                        'px-3 py-1 rounded-full border',
                        selectedTag === (t.name ?? t)
                          ? 'bg-[#6d0019] text-white border-[#6d0019]'
                          : 'bg-gray-100 text-gray-700',
                      ]"
                      type="button"
                      @click="selectTag(t.name ?? t)"
                    >
                      {{ t.name ?? t }}
                    </button>
                  </div>

                  <div class="mt-3 flex gap-2 items-center">
                    <InputText
                      v-model="customTagInput"
                      placeholder="Type a custom tag"
                      class="flex-1"
                    />
                    <Button label="Use" icon="pi pi-check" @click="addCustomTag" />
                  </div>

                  <p class="text-sm text-gray-500 mt-2">
                    Tip: selecting a tag will set it as the primary tag for this listing.
                  </p>
                </div>

                <div class="flex justify-between mt-4">
                  <Button label="Back" class="bg-gray-200" @click="goBack" />
                  <Button label="Next" class="bg-[#6d0019] text-white" @click="goNext" />
                </div>
              </div>
            </StepPanel>

            <!-- Step 3: Pricing -->
            <StepPanel :value="3">
              <div class="space-y-4">
                <label class="font-semibold">Budget (₱)</label>
                <InputText
                  v-model="newBudget"
                  placeholder="Enter budget (numbers only)"
                  class="w-40"
                />
                <label class="font-semibold">Service Frequency</label>
                <div class="flex gap-2 mt-2">
                  <button class="px-3 py-1 rounded-full bg-gray-100">One-time</button>
                  <button class="px-3 py-1 rounded-full bg-gray-100">Weekly</button>
                  <button class="px-3 py-1 rounded-full bg-gray-100">Bi-weekly</button>
                </div>

                <div class="flex justify-between mt-4">
                  <Button label="Back" class="bg-gray-200" @click="goBack" />
                  <Button
                    label="Create Listing"
                    class="bg-[#6d0019] text-white"
                    @click="submitListing"
                    newTitle.value=""
                    newDescription.value=""
                    newBudget.value="null"
                    newCategoryId.value="null"
                    selectedTag.value="null"
                    customTagInput.value=""
                  />
                </div>
              </div>
            </StepPanel>
          </StepPanels>
        </Stepper>
      </div>
    </Dialog>
  </div>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
