<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import AutoComplete from 'primevue/autocomplete'
import Chip from 'primevue/chip'
import ListingCard from '@/components/ListingCard.vue'
import FilterSidebar from '@/components/FilterSidebar.vue'
import { useToastHelper } from '@/composables/useToastHelper'
import api from '@/composables/axios'
import { useAuthStore } from '@/stores/AuthStore'

const authStore = useAuthStore()
const toast = useToastHelper()

// Custom debounce function
const debounce = (func, delay) => {
  let timeoutId
  return function (...args) {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => func.apply(this, args), delay)
  }
}

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
const newDescription = ref('')
const hasExpiry = ref(false)
const expiryDate = ref('')
const newBudget = ref(null)
const newCategoryId = ref(null)
const categories = ref([])
const tags = ref([])
const selectedTags = ref([])
const tagInput = ref('')
const filteredTags = ref([])

const listings = ref([])

const paginatedListings = computed(() => listings.value)

// Load listings function
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
      const paginatedData = response.data. data
      listings.value = paginatedData.data || []
      totalRecords. value = paginatedData.total || 0
    } else {
      listings.value = []
      totalRecords.value = 0
    }
  } catch (error) {
    console.error('Failed to load listings:', error)
    toast.error('Failed to load listings')
    listings.value = []
    totalRecords.value = 0
  } finally {
    loading. value = false
  }
}

// Debounced search function (500ms delay)
const debouncedLoadListings = debounce(() => {
  currentPage.value = 1
  loadListings()
}, 500)

// Watch search input with debounce
watch(
  () => filters.search,
  () => {
    debouncedLoadListings()
  }
)

// Watch filters (without debounce for immediate feedback)
watch(
  () => [
    filters.category,
    filters.minBudget,
    filters. maxBudget,
    filters.minRating,
    filters.sort_by,
  ],
  () => {
    currentPage.value = 1
    loadListings()
  }
)

// Watch pagination
watch(
  () => [currentPage.value, itemsPerPage.value],
  () => {
    loadListings()
  }
)

const handleSearch = (value) => {
  filters.search = value
  // Watcher will handle the debounced call
}

const handleFilterChange = (newFilters) => {
  Object.assign(filters, newFilters)
  // Watcher will handle the call
}

const handlePageChange = (event) => {
  currentPage.value = event.page + 1
  itemsPerPage.value = event. rows
  // Watcher will handle the call
}

const handleSortChange = (sortBy) => {
  filters. sort_by = sortBy
  // Watcher will handle the call
}

const loadCategories = async () => {
  try {
    const { data } = await api.get('/categories')
    categories.value = (data. data ??  []).slice(). sort((a, b) => a.name.localeCompare(b.name))
  } catch (err) {
    console.error('Failed to load categories:', err)
    toast.error('Failed to load categories')
    categories.value = []
  }
}

const loadTags = async () => {
  try {
    let res
    try {
      res = await api. get('/admin/tags')
    } catch {
      tags.value = []
      return
    }
    const data = res.data
    const rawTags = data.data ??  data
    tags.value = rawTags.map(t => typeof t === 'string' ? t : t.name)
  } catch (err) {
    console.error('Failed to load tags:', err)
    toast.warning('Failed to load tags, you can still add custom tags')
    tags.value = []
  }
}

// AutoComplete search function with debounce
const searchTagsInternal = (query) => {
  if (! query) {
    filteredTags. value = tags.value
  } else {
    filteredTags.value = tags.value. filter(tag =>
      tag.toLowerCase().includes(query.toLowerCase())
    )
  }
}

const debouncedSearchTags = debounce(searchTagsInternal, 300)

const searchTags = (event) => {
  debouncedSearchTags(event. query)
}

// Add tag from autocomplete or Enter key
const addTag = (event) => {
  let tag = ''

  if (event && event.value) {
    tag = typeof event.value === 'string' ? event.value : event.value?. name || event.value
  } else if (tagInput.value) {
    tag = tagInput.value
  }

  const trimmedTag = tag.trim()

  if (! trimmedTag) {
    return
  }

  if (selectedTags.value.length >= 5) {
    toast.warning('Maximum 5 tags allowed', 'Tag Limit')
    tagInput.value = ''
    return
  }

  if (selectedTags.value.includes(trimmedTag)) {
    toast.info(`Tag "${trimmedTag}" already added`)
    tagInput.value = ''
    return
  }

  selectedTags.value.push(trimmedTag)
  tagInput.value = ''
  toast.success(`Tag "${trimmedTag}" added`)
}

// Handle Enter key for custom tags
const handleTagKeydown = (event) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    addTag({ value: tagInput.value })
  }
}

// Remove tag
const removeTag = (tag) => {
  const index = selectedTags.value.indexOf(tag)
  if (index > -1) {
    selectedTags.value.splice(index, 1)
    toast.info(`Tag "${tag}" removed`)
  }
}

const openAddModal = async () => {
  if (! authStore.isAuthenticated) {
    toast. error('You must be logged in to add a listing')
    return
  }

  showAddModal.value = true
  activeStep.value = 1

  // Reset form
  newTitle.value = ''
  newDescription.value = ''
  hasExpiry.value = false
  expiryDate.value = ''
  newBudget.value = null
  newCategoryId. value = null
  selectedTags.value = []
  tagInput.value = ''

  // Load categories and tags
  await Promise.all([
    loadCategories(). catch(() => {
      categories.value = []
    }),
    loadTags().catch(() => {
      tags.value = []
    }),
  ])
}

const goNext = () => {
  if (activeStep.value === 1) {
    if (! newTitle.value || newTitle.value.trim() === '') {
      toast.warning('Please enter a title')
      return
    }
    if (newTitle.value.length > 255) {
      toast.warning('Title must be 255 characters or less')
      return
    }
  }

  if (activeStep. value === 2) {
    if (selectedTags.value.length === 0) {
      toast. warning('Please select at least one tag')
      return
    }
    if (selectedTags.value.length > 5) {
      toast.warning('Maximum 5 tags allowed')
      return
    }
  }

  if (activeStep. value < 3) {
    activeStep.value++
    toast.success('Step completed!')
  }
}

const goBack = () => {
  if (activeStep.value > 1) activeStep.value--
}

const submitListing = async () => {
  // Final validation
  if (!newTitle. value || newTitle.value.trim() === '') {
    toast. warning('Please enter a title for your listing')
    return
  }
  if (newTitle.value.length > 255) {
    toast.warning('Title must be 255 characters or less')
    return
  }
  if (selectedTags.value.length === 0) {
    toast.warning('Please add at least one tag')
    return
  }
  if (selectedTags.value.length > 5) {
    toast.warning('Maximum 5 tags allowed')
    return
  }

  if (hasExpiry.value && ! expiryDate.value) {
    toast.warning('Please select an expiry date')
    return
  }

  // Validate budget
  let budgetVal = newBudget.value
  if (budgetVal !== null && budgetVal !== undefined && budgetVal !== '') {
    budgetVal = Number(budgetVal)
    if (isNaN(budgetVal) || budgetVal < 0) {
      toast. warning('Budget must be a valid positive number')
      return
    }
  } else {
    budgetVal = null
  }

  // Format expires_at
  let expiresAtVal = null
  if (hasExpiry.value && expiryDate.value) {
    try {
      const date = new Date(expiryDate.value)
      const year = date.getFullYear()
      const month = String(date. getMonth() + 1).padStart(2, '0')
      const day = String(date. getDate()).padStart(2, '0')
      const hours = String(date.getHours()). padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')
      expiresAtVal = `${year}-${month}-${day} ${hours}:${minutes}:00`
    } catch (e) {
      console.error('Date formatting error:', e)
      toast.error('Invalid expiry date')
      return
    }
  }

  // Build payload
  const payload = {
    title: newTitle.value. trim(),
    tags: [... selectedTags.value],
  }

  if (newDescription.value && newDescription.value.trim()) {
    payload.description = newDescription.value.trim()
  }

  if (budgetVal !== null) {
    payload. budget = budgetVal
  }

  if (newCategoryId.value) {
    payload.category_id = newCategoryId.value
  }

  if (expiresAtVal) {
    payload.expires_at = expiresAtVal
  }

  console.log('Submitting payload:', JSON.stringify(payload, null, 2))

  try {
    toast.info('Creating listing...')
    const response = await api.post('/seeker/listings', payload)

    if (response.data.success) {
      toast.success('Listing created successfully! ', 'Success')
      showAddModal.value = false

      // Reset form
      newTitle.value = ''
      newDescription.value = ''
      hasExpiry.value = false
      expiryDate.value = ''
      newBudget.value = null
      newCategoryId. value = null
      selectedTags.value = []
      tagInput.value = ''
      activeStep.value = 1

      // Reload listings
      await loadListings()
    } else {
      const msg = response.data.message || 'Failed to create listing'
      toast.error(msg)
    }
  } catch (err) {
    console.error('API error:', err)

    if (err.response?.status === 422) {
      if (err.response.data?.errors) {
        const errors = err.response.data.errors
        const errorMessages = Object.entries(errors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n')
        toast.error(errorMessages, 'Validation Error')
      } else if (err.response.data?.suggestions) {
        const msg = err.response.data.message
        const suggestions = err.response.data. suggestions
        toast.info(`${msg}\nSuggestions: ${suggestions. join(', ')}`, 'Tag Suggestion')
      } else {
        toast.error(err.response. data?.message || 'Validation failed')
      }
    } else if (err.response?.status === 500) {
      const serverMsg = err.response.data?.message || 'Server error occurred'
      toast.error(`Server Error: ${serverMsg}`, 'Server Error')
    } else {
      const msg = err.response?.data?.message || err.message || 'Failed to create listing'
      toast. error(msg)
    }
  }
}

// Load initial data
onMounted(() => {
  loadCategories()
  loadListings()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Main Search Bar Section -->
    <section class="bg-primary-500 text-white px-4 py-8">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl font-bold mb-6">Find Services</h2>
        <div class="flex gap-4">
          <IconField icon-position="left" class="flex-1">
            <InputIcon class="pi pi-search"></InputIcon>
            <InputText
              v-model="filters. search"
              placeholder="Search for services..."
              class="w-full"
              @input="handleSearch($event.target.value)"
            />
          </IconField>
          <button
            class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 transition"
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
                  @change="handleSortChange($event. target.value)"
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
                      ? 'bg-primary-500 text-white'
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
                      ? 'bg-primary-500 text-white'
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
            <p class="text-gray-600 text-lg">No services found.  Try adjusting your filters.</p>
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

          <!-- Pagination -->
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
                  'w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold border-2 mb-2 transition',
                  activeStep >= 1 ? 'bg-primary-500 border-[#6d0019]' : 'bg-gray-300 border-gray-300',
                ]"
              >
                1
              </div>
              <span class="text-sm font-semibold text-gray-800">About</span>
            </div>
            <div
              :class="['flex-1 h-1 mb-8 transition', activeStep > 1 ? 'bg-primary-500' : 'bg-gray-300']"
            ></div>
            <div class="flex flex-col items-center flex-1">
              <div
                :class="[
                  'w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold border-2 mb-2 transition',
                  activeStep >= 2 ? 'bg-primary-500 border-[#6d0019]' : 'bg-gray-300 border-gray-300',
                ]"
              >
                2
              </div>
              <span class="text-sm font-semibold text-gray-800">Category & Tags</span>
            </div>
            <div
              :class="['flex-1 h-1 mb-8 transition', activeStep > 2 ?  'bg-primary-500' : 'bg-gray-300']"
            ></div>
            <div class="flex flex-col items-center flex-1">
              <div
                :class="[
                  'w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold border-2 mb-2 transition',
                  activeStep >= 3 ? 'bg-primary-500 border-[#6d0019]' : 'bg-gray-300 border-gray-300',
                ]"
              >
                3
              </div>
              <span class="text-sm font-semibold text-gray-800">Budget & Expiry</span>
            </div>
          </div>
        </div>

        <!-- Step Content -->
        <div class="min-h-75 mb-8">
          <!-- Step 1: About -->
          <div v-if="activeStep === 1" class="space-y-4">
            <div>
              <label class="font-semibold block mb-2">
                Title <span class="text-red-500">*</span>
              </label>
              <InputText
                v-model="newTitle"
                :maxlength="255"
                placeholder="e.g., Need Web Developer for E-commerce Site"
                class="w-full"
              />
              <div class="text-xs text-gray-500 mt-1">{{ newTitle.length }}/255 characters</div>
            </div>

            <div>
              <label class="font-semibold block mb-2">Description</label>
              <textarea
                v-model="newDescription"
                rows="6"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Describe your project requirements, deliverables, timeline, etc..."
              ></textarea>
            </div>
          </div>

          <!-- Step 2: Category & Tags -->
          <div v-if="activeStep === 2" class="space-y-4">
            <div>
              <label class="font-semibold block mb-2">Category</label>
              <Dropdown
                v-model="newCategoryId"
                :options="categories"
                option-label="name"
                option-value="id"
                placeholder="Select a category (optional)"
                class="w-full"
                :filter="true"
                show-clear
              />
            </div>

            <div>
              <label class="font-semibold block mb-2">
                Tags <span class="text-red-500">*</span> (Max 5)
              </label>

              <!-- Selected Tags Display -->
              <div v-if="selectedTags.length > 0" class="flex flex-wrap gap-2 mb-3">
                <Chip
                  v-for="(tag, idx) in selectedTags"
                  :key="idx"
                  :label="tag"
                  removable
                  @remove="removeTag(tag)"
                />
              </div>

              <!-- AutoComplete for Tags -->
              <AutoComplete
                v-model="tagInput"
                :suggestions="filteredTags"
                @complete="searchTags"
                @item-select="addTag"
                @keydown="handleTagKeydown"
                placeholder="Type to search tags or press Enter to add custom"
                :disabled="selectedTags.length >= 5"
                class="w-full"
                :force-selection="false"
                dropdown
              >
                <template #option="slotProps">
                  <div class="flex items-center gap-2">
                    <i class="pi pi-tag text-primary-500"></i>
                    <span>{{ slotProps. option }}</span>
                  </div>
                </template>
              </AutoComplete>
              <p class="text-xs text-gray-500 mt-2">
                {{ selectedTags. length }}/5 tags selected.  Type and press Enter for custom tags.
              </p>
            </div>
          </div>

          <!-- Step 3: Budget & Expiry -->
          <div v-if="activeStep === 3" class="space-y-4">
            <div>
              <label class="font-semibold block mb-2">Budget (₱)</label>
              <InputText
                v-model="newBudget"
                type="number"
                placeholder="e.g., 10000"
                class="w-full"
                min="0"
              />
              <p class="text-xs text-gray-500 mt-1">Optional: Enter your budget in Philippine Pesos</p>
            </div>

            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
              <input
                type="checkbox"
                v-model="hasExpiry"
                class="accent-[#6d0019] w-5 h-5 cursor-pointer"
                id="hasExpiry"
              />
              <label for="hasExpiry" class="font-semibold cursor-pointer">
                Set an expiry date for this listing
              </label>
            </div>

            <div v-if="hasExpiry" class="p-4 bg-blue-50 rounded-lg">
              <label class="font-semibold block mb-2">Expiry Date & Time</label>
              <input
                type="datetime-local"
                v-model="expiryDate"
                class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <p class="text-xs text-gray-500 mt-2">
                Format: YYYY-MM-DD HH:MM:SS
              </p>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center pt-4 border-t">
          <Button
            :label="activeStep === 1 ?  'Cancel' : '← Back'"
            severity="secondary"
            @click="activeStep === 1 ? (showAddModal = false) : goBack()"
          />
          <Button
            :label="activeStep === 3 ? 'Create Listing' : 'Next →'"
            severity="success"
            :icon="activeStep === 3 ?  'pi pi-check' : 'pi pi-arrow-right'"
            iconPos="right"
            @click="activeStep === 3 ?  submitListing() : goNext()"
          />
        </div>
      </div>
    </Dialog>
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
