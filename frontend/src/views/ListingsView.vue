<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import ListingCard from '@/components/ListingCard.vue'
import FilterSidebar from '@/components/FilterSidebar.vue'
import { useToastStore } from '@/stores/toastStore'
import api from '@/composables/axios'
import { useAuthStore } from '@/stores/AuthStore' // Adjust path as needed

const authStore = useAuthStore()

const toastStore = useToastStore()
const layout = ref('grid')
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(12)

const filters = reactive({
  category: null,
  search: '',
  minBudget: 0,
  maxBudget: 10000,
  minRating: 5,
  sort_by: 'newest',
})

const showAddModal = ref(false)
const activeStep = ref(1)
const newTitle = ref('')
const newBriefDescription = ref('')
const newFullDescription = ref('')
const hasExpiry = ref(false)
const expiryDate = ref('')
const newBudget = ref(null)
const newCategoryId = ref(null)
const categories = ref([])
const tags = ref([])
const selectedTags = ref([])
const customTagInput = ref('')

const listings = ref([])

const paginatedListings = computed(() => listings.value)

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
    // Show query params each call
    console.log('API Query Params:', params)

    const response = await api.get('/listings', { params })
    if (response.data.success && response.data.data) {
      const paginatedData = response.data.data
      listings.value = paginatedData.data || []
      totalRecords.value = paginatedData.total || 0
      // Show listings result each call
      console.log('Listings response:', listings.value)
    } else {
      listings.value = []
      totalRecords.value = 0
      toastStore.showError('No listings found', 'Error')
    }
  } catch (error) {
    console.error('Failed to load listings:', error)
    toastStore.showError('Failed to load listings', 'Error')
    listings.value = []
    totalRecords.value = 0
  } finally {
    loading.value = false
  }
}

const handleSearch = (value) => {
  filters.search = value
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
  filters.sort_by = sortBy
  currentPage.value = 1
  loadListings()
}

const loadCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = (data.data ?? []).slice().sort((a, b) => a.name.localeCompare(b.name))
  } catch (err) {
    categories.value = []
  }
}

onMounted(() => {
  loadCategories()
  loadListings()
})

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
    tags.value = []
  }
}

const addCustomTag = () => {
  const t = (customTagInput.value || '').trim()
  if (!t) return
  if (!selectedTags.value.includes(t)) {
    selectedTags.value.push(t)
  }
  customTagInput.value = ''
}

const openAddModal = async () => {
  if (!authStore.isAuthenticated) {
    toastStore.showError('You must be logged in to add a listing')
    return
  }
  showAddModal.value = true
  activeStep.value = 1
  await Promise.all([
    loadCategories().catch(() => {
      categories.value = []
    }),
    loadTags().catch(() => {
      tags.value = []
    }),
  ])
}

const goNext = () => {
  if (activeStep.value < 3) activeStep.value++
}
const goBack = () => {
  if (activeStep.value > 1) activeStep.value--
}

const submitListing = async () => {
  console.log('submitListing called')
  if (!newTitle.value || newTitle.value.trim() === '') {
    toastStore.showError('Please enter a title for your listing')
    return
  }
  if (newTitle.value.length > 50) {
    toastStore.showError('Title must be 50 characters or less')
    return
  }
  if (!newBriefDescription.value || newBriefDescription.value.length > 150) {
    toastStore.showError('Brief description is required and must be 150 characters or less')
    return
  }
  // Ensure tags is a plain array
  const tagsArr =
    Array.isArray(selectedTags.value) && selectedTags.value.length > 0
      ? [...selectedTags.value]
      : null
  // Ensure budget is a number or null
  let budgetVal = newBudget.value
  if (typeof budgetVal === 'string') {
    budgetVal = budgetVal.trim() === '' ? null : Number(budgetVal)
    if (isNaN(budgetVal)) budgetVal = null
  }
  // Build payload and remove null fields
  const payload = {
    title: newTitle.value,
    brief_description: newBriefDescription.value,
    description: newFullDescription.value || null,
    budget: budgetVal,
    category_id: newCategoryId.value,
    tags: tagsArr,
    expires_at: hasExpiry.value && expiryDate.value ? expiryDate.value : null,
  }
  // Remove null fields
  Object.keys(payload).forEach((key) => (payload[key] === null ? delete payload[key] : undefined))
  console.log('Submitting payload:', payload)
  try {
    const response = await api.post('/listings', payload)
    console.log('API response:', response)
    if (response.data.success) {
      toastStore.showSuccess('Listing created successfully')
      showAddModal.value = false
      newTitle.value = ''
      newBriefDescription.value = ''
      newFullDescription.value = ''
      hasExpiry.value = false
      expiryDate.value = ''
      newBudget.value = null
      newCategoryId.value = null
      selectedTags.value = []
      customTagInput.value = ''
      await loadListings()
    } else {
      const msg = response.data.message || 'Failed to create listing'
      toastStore.showError(msg, 'Error')
    }
  } catch (err) {
    console.error('API error:', err)
    const msg = err.response?.data?.message || err.message || 'Failed to create listing'
    toastStore.showError(msg, 'Error')
  }
}
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
              v-model="filters.search"
              placeholder="Search for services..."
              class="w-full"
              @input="handleSearch($event.target.value)"
            />
          </IconField>
          <button
            class="bg-black text-white px-4 py-2 rounded"
            @click="handleSearch(filters.search)"
          >
            Search
          </button>
        </div>
      </div>
    </section>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 py-8">
      <div class="flex gap-6">
        <!-- Left Sidebar - Filters -->
        <div class="w-75 flex-shrink-0">
          <FilterSidebar :filters="filters" :categories="categories" @update="handleFilterChange" />
        </div>

        <!-- Right Content Area -->
        <div class="flex-1">
          <!-- View Controls and Pagination Info -->
          <div class="flex items-center justify-between mb-6">
            <div class="text-gray-600">
              Showing
              {{ totalRecords === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1 }}
              -
              {{ Math.min(currentPage * itemsPerPage, totalRecords) }}
              of {{ totalRecords }} services
            </div>
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Sort by:</label>
                <select
                  :value="filters.sort_by"
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
              <Button
                v-if="authStore.isAuthenticated"
                class="bg-[#10b981] hover:bg-[#0ea77a] text-white"
                icon="pi pi-plus"
                label="Add Listing"
                @click="openAddModal"
              />
              <span
                v-else
                class="text-gray-400 text-sm font-semibold select-none flex items-center gap-2"
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
          <div v-else-if="paginatedListings.length === 0" class="text-center py-12">
            <i class="pi pi-inbox text-5xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-600 text-lg">No services found. Try adjusting your filters.</p>
          </div>
          <div
            v-else-if="layout === 'grid'"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <ListingCard
              v-for="service in paginatedListings"
              :key="service.id"
              :service="service"
              layout="grid"
            />
          </div>
          <div v-else class="space-y-4">
            <ListingCard
              v-for="service in paginatedListings"
              :key="service.id"
              :service="service"
              layout="list"
            />
          </div>
          <div class="mt-8 flex justify-center">
            <Paginator
              :rows="itemsPerPage"
              :total-records="totalRecords"
              :first="(currentPage - 1) * itemsPerPage"
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
            <div
              :class="['flex-1 h-1 mb-8', activeStep > 1 ? 'bg-[#6d0019]' : 'bg-gray-300']"
            ></div>
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
            <div
              :class="['flex-1 h-1 mb-8', activeStep > 2 ? 'bg-[#6d0019]' : 'bg-gray-300']"
            ></div>
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
        <div class="min-h-75 mb-8">
          <div v-if="activeStep === 1" class="space-y-4">
            <label class="font-semibold">Title <span class="text-red-500">*</span></label>
            <InputText
              v-model="newTitle"
              :maxlength="50"
              placeholder="Service title (max 50 chars)"
              class="w-full"
            />
            <div class="text-xs text-gray-500">{{ newTitle.length }}/50</div>
            <label class="font-semibold"
              >Brief Description <span class="text-red-500">*</span></label
            >
            <InputText
              v-model="newBriefDescription"
              :maxlength="150"
              placeholder="Short summary (max 150 chars, shown in card)"
              class="w-full"
            />
            <div class="text-xs text-gray-500">{{ newBriefDescription.length }}/150</div>
            <label class="font-semibold">Full Description</label>
            <textarea
              v-model="newFullDescription"
              rows="6"
              class="w-full border rounded px-3 py-2"
              placeholder="Full details (shown in details view)"
            ></textarea>
            <div class="flex items-center gap-4 mt-2">
              <label class="font-semibold">Does this listing have an expiry?</label>
              <input type="checkbox" v-model="hasExpiry" class="accent-[#6d0019] w-5 h-5" />
              <span class="text-sm text-gray-600">Yes</span>
            </div>
            <div v-if="hasExpiry" class="mt-2">
              <label class="font-semibold">Expiry Date</label>
              <input type="date" v-model="expiryDate" class="border rounded px-3 py-2" />
            </div>
          </div>
          <div v-if="activeStep === 2" class="space-y-4">
            <div>
              <label class="font-semibold">Category <span class="text-red-500">*</span></label>
              <Dropdown
                v-model="newCategoryId"
                :options="categories"
                option-label="name"
                option-value="id"
                placeholder="Select a category"
                class="w-full mt-2"
                :filter="true"
              />
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
          <div v-if="activeStep === 3" class="space-y-4">
            <label class="font-semibold">Budget (₱)</label>
            <InputText v-model="newBudget" placeholder="Enter budget (numbers only)" class="w-40" />
          </div>
        </div>
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

<style scoped>
/* Add any global styles here if needed */
</style>
