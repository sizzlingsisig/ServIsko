<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <!-- Main Container -->
    <div class="max-w-7xl mx-auto">
      <!-- Header Section -->
      <div
        class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 bg-white p-6 rounded-lg shadow-sm border-l-4 border-primary-500"
      >
        <div class="flex items-start gap-3">
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">My Listings</h1>
            <p class="text-lg text-gray-600">Manage your service requests and listing status</p>
          </div>

        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 mt-4 md:mt-0">
          <button
            @click="activeRole = 'seeker'"
            :class="[
              'px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center gap-2',
              activeRole === 'seeker'
                ? 'bg-primary-500 text-white shadow-md hover:bg-primary-600'
                : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
            ]"
          >
            <i class="pi pi-search"></i>
            Service Seeker
          </button>
          <button
            @click="activeRole = 'provider'"
            :class="[
              'px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center gap-2',
              activeRole === 'provider'
                ? 'bg-primary-500 text-white shadow-md hover:bg-primary-600'
                : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
            ]"
          >
            <i class="pi pi-briefcase"></i>
            Service Provider
          </button>
        </div>
      </div>

      <!-- Bottom Container -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Left Container (3/4 width) -->
        <div class="lg:col-span-3">
          <Card class="shadow-sm">
            <!-- Header with Filters -->
            <template #content>
              <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b border-gray-200"
              >
                <!-- Status Tabs -->
                <div class="flex gap-2 flex-wrap">
                  <button
                    @click="changeStatus('all')"
                    :class="[
                      'px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-2',
                      activeStatus === 'all'
                        ? 'bg-primary-500 text-white shadow-sm'
                        : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
                    ]"
                  >
                    <i class="pi pi-list"></i>
                    All
                  </button>
                  <button
                    @click="changeStatus('active')"
                    :class="[
                      'px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-2',
                      activeStatus === 'active'
                        ? 'bg-primary-500 text-white shadow-sm'
                        : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
                    ]"
                  >
                    <i class="pi pi-check-circle"></i>
                    Active
                  </button>
                  <button
                    @click="changeStatus('expired')"
                    :class="[
                      'px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-2',
                      activeStatus === 'expired'
                        ? 'bg-primary-500 text-white shadow-sm'
                        : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
                    ]"
                  >
                    <i class="pi pi-times-circle"></i>
                    Expired
                  </button>
                  <button
                    @click="changeStatus('closed')"
                    :class="[
                      'px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-2',
                      activeStatus === 'closed'
                        ? 'bg-primary-500 text-white shadow-sm'
                        : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
                    ]"
                  >
                    <i class="pi pi-check"></i>
                    Closed
                  </button>
                </div>

                <!-- Search and Filter -->
                <div class="flex gap-2 w-full md:w-auto">
                  <IconField iconPosition="left" class="flex-1 md:flex-initial">
                    <InputIcon class="pi pi-search" />
                    <InputText
                      v-model="searchQuery"
                      placeholder="Search listings..."
                      class="w-full md:w-64"
                    />
                  </IconField>
                  <button
                    @click="showFilterDialog = true"
                    class="relative px-4 py-2 bg-white border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500 text-gray-700 rounded-lg transition-all"
                  >
                    <i class="pi pi-filter"></i>
                    <span
                      v-if="appliedFiltersCount"
                      class="absolute -top-2 -right-2 bg-primary-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold"
                    >
                      {{ appliedFiltersCount }}
                    </span>
                  </button>
                </div>
              </div>

              <!-- Listings List -->
              <div v-if="loading" class="flex justify-center py-12">
                <ProgressSpinner />
              </div>

              <div v-else-if="listings.length === 0" class="text-center py-12">
                <i class="pi pi-inbox text-6xl text-gray-300 mb-4 block"></i>
                <p class="text-gray-600 text-lg mb-4">No listings found</p>
                <button
                  @click="openCreateDialog"
                  class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold transition-all inline-flex items-center gap-2"
                >
                  <i class="pi pi-plus"></i>
                  Create Your First Listing
                </button>
              </div>

              <div v-else class="space-y-4">
                <Card
                  v-for="listing in listings"
                  :key="listing.id"
                  class="hover:shadow-md transition-shadow border border-gray-200"
                >
                  <template #content>
                    <div class="flex flex-col md:flex-row justify-between gap-4">
                      <div class="flex-1">
                        <div class="flex items-start gap-3 mb-3">
                          <div
                            class="w-12 h-12 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-lg flex-shrink-0"
                          >
                            {{ listing.title.charAt(0) }}
                          </div>
                          <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 mb-1">
                              {{ listing.title }}
                            </h3>
                            <p class="text-gray-600 text-sm line-clamp-2">
                              {{ listing.description }}
                            </p>
                          </div>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-3">
                          <span
                            v-for="tag in listing.tags"
                            :key="tag.id"
                            class="px-3 py-1 border border-primary-100 text-primary-700 text-sm rounded-full font-medium"
                          >
                            {{ tag.name }}
                          </span>
                        </div>

                        <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                          <div class="flex items-center gap-1">
                            <i class="pi pi-calendar text-primary-500"></i>
                            <span>{{ formatDate(listing.created_at) }}</span>
                          </div>
                          <div class="flex items-center gap-1">
                            <i class="pi pi-dollar text-primary-500"></i>
                            <span>₱{{ parseFloat(listing.budget).toLocaleString() || 'N/A' }}</span>
                          </div>
                          <div class="flex items-center gap-1">
                            <i class="pi pi-tag text-primary-500"></i>
                            <span>{{ listing.category?.name || 'No Category' }}</span>
                          </div>
                        </div>
                      </div>

                      <div class="flex md:flex-col gap-2 items-start">
                        <span
                          :class="[
                            'px-3 py-1 rounded-full text-xs font-semibold uppercase',
                            listing.status === 'active' ? 'bg-green-100 text-green-700' : '',
                            listing.status === 'expired' ? 'bg-red-100 text-red-700' : '',
                            listing.status === 'closed' ? 'bg-blue-100 text-blue-700' : '',
                          ]"
                        >
                          {{ listing.status }}
                        </span>
                        <button
                          @click="toggleMenu($event, listing)"
                          class="p-2 hover:bg-gray-100 rounded-lg transition-all"
                        >
                          <i class="pi pi-ellipsis-v text-gray-600"></i>
                        </button>
                      </div>
                    </div>
                  </template>
                </Card>
              </div>

              <!-- Pagination -->
              <div v-if="listings.length > 0" class="mt-6">
                <Paginator
                  :rows="pagination.per_page"
                  :totalRecords="pagination.total"
                  :first="(pagination.current_page - 1) * pagination.per_page"
                  :rowsPerPageOptions="[10, 20, 50]"
                  @page="onPageChange"
                />
              </div>
            </template>
          </Card>
        </div>

        <!-- Right Container (1/4 width) -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Statistics Card -->
          <Card class="shadow-lg border-t-4 border-primary-500">
            <template #title>
              <div class="flex items-center gap-2 text-gray-900">
                <i class="pi pi-chart-bar text-primary-500"></i>
                <span>Listing Statistics</span>
              </div>
            </template>
            <template #content>
              <div class="space-y-4">
                <div
                  class="p-4 rounded-lg shadow-md transform hover:scale-105 transition-transform"
                >
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm text-black font-semibold">Active Listings</span>
                    <i class="pi pi-check-circle text-black text-2xl"></i>
                  </div>
                  <p class="text-4xl font-bold text-black">{{ stats.active }}</p>
                </div>
                <div
                  class="p-4  rounded-lg shadow-md transform hover:scale-105 transition-transform"
                >
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm text-black font-semibold">Closed</span>
                    <i class="pi pi-check text-black text-2xl"></i>
                  </div>
                  <p class="text-4xl font-bold text-black">{{ stats.closed }}</p>
                </div>
                <div
                  class="p-4  rounded-lg shadow-md transform hover:scale-105 transition-transform"
                >
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm text-black font-semibold">Expired</span>
                    <i class="pi pi-times-circle text-black text-2xl"></i>
                  </div>
                  <p class="text-4xl font-bold text-black">{{ stats.expired }}</p>
                </div>

                <Divider />

                <div class="space-y-2 bg-gray-50 p-3 rounded-lg">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-600 font-medium">Total Records</span>
                    <span class="font-bold text-gray-900">{{ stats.active + stats.closed + stats.expired }}</span>
                  </div>
                </div>
              </div>
            </template>
          </Card>

          <!-- Quick Actions Card -->
          <Card class="shadow-sm">
            <template #title>
              <div class="flex items-center gap-2 text-gray-900">
                <i class="pi pi-bolt text-primary-500"></i>
                <span>Quick Actions</span>
              </div>
            </template>
            <template #content>
              <div class="space-y-3">
                <button
                  @click="openCreateDialog"
                  class="w-full bg-primary-500 hover:bg-primary-600 text-white px-4 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2"
                >
                  <i class="pi pi-plus"></i>
                  Create New Listing
                </button>
                <button
                  @click="exportListings"
                  class="w-full border-2 border-gray-300 hover:border-primary-500 text-gray-700 hover:text-primary-500 px-4 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2"
                >
                  <i class="pi pi-download"></i>
                  Export Listings
                </button>
              </div>
            </template>
          </Card>
        </div>
      </div>
    </div>

    <!-- Context Menu -->
    <Menu ref="menu" :model="menuItems" :popup="true" />

    <!-- Filter Dialog -->
    <Dialog v-model:visible="showFilterDialog" :modal="true" :style="{ width: '500px' }">
      <template #header>
        <div class="flex items-center gap-2">
          <i class="pi pi-filter text-primary-500"></i>
          <span class="font-bold text-xl">Filter Listings</span>
        </div>
      </template>

      <div class="space-y-4 py-4">
        <div>
          <label class="block font-semibold mb-2 text-gray-700">Category</label>
          <Dropdown
            v-model="filters.categoryId"
            :options="categories"
            optionLabel="name"
            optionValue="id"
            placeholder="Select category"
            class="w-full"
            showClear
          />
        </div>

        <div>
          <label class="block font-semibold mb-2 text-gray-700">Budget Range</label>
          <div class="flex gap-2">
            <InputNumber
              v-model="filters.minBudget"
              placeholder="Min"
              :min="0"
              prefix="₱"
              class="flex-1"
            />
            <InputNumber
              v-model="filters.maxBudget"
              placeholder="Max"
              :min="0"
              prefix="₱"
              class="flex-1"
            />
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <button
            @click="clearFilters"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-semibold transition-all"
          >
            Clear
          </button>
          <button
            @click="applyFilters"
            class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-semibold transition-all"
          >
            Apply Filters
          </button>
        </div>
      </template>
    </Dialog>

    <!-- Edit Listing Dialog -->
    <Dialog
      v-model:visible="showEditDialog"
      :modal="true"
      :style="{ width: '600px' }"
      :draggable="false"
    >
      <template #header>
        <div class="flex items-center gap-2">
          <i class="pi pi-pencil text-primary-500"></i>
          <span class="font-bold text-xl">Edit Listing</span>
        </div>
      </template>

      <div class="space-y-4 py-4">
        <!-- Title -->
        <div>
          <label for="edit-title" class="block font-semibold mb-2 text-gray-700">
            Title <span class="text-red-500">*</span>
          </label>
          <InputText
            id="edit-title"
            v-model="editForm.title"
            placeholder="Enter listing title"
            class="w-full"
            :class="{ 'p-invalid': editErrors.title }"
          />
          <small v-if="editErrors.title" class="text-red-500">{{ editErrors.title }}</small>
        </div>

        <!-- Description -->
        <div>
          <label for="edit-description" class="block font-semibold mb-2 text-gray-700">
            Description <span class="text-red-500">*</span>
          </label>
          <Textarea
            id="edit-description"
            v-model="editForm.description"
            placeholder="Describe what you're looking for..."
            rows="5"
            class="w-full"
            :class="{ 'p-invalid': editErrors.description }"
          />
          <small v-if="editErrors.description" class="text-red-500">{{ editErrors.description }}</small>
        </div>

        <!-- Category -->
        <div>
          <label for="edit-category" class="block font-semibold mb-2 text-gray-700">
            Category <span class="text-red-500">*</span>
          </label>
          <Dropdown
            id="edit-category"
            v-model="editForm.category_id"
            :options="categories"
            optionLabel="name"
            optionValue="id"
            placeholder="Select a category"
            class="w-full"
            :class="{ 'p-invalid': editErrors.category_id }"
          />
          <small v-if="editErrors.category_id" class="text-red-500">{{ editErrors.category_id }}</small>
        </div>

        <!-- Budget -->
        <div>
          <label for="edit-budget" class="block font-semibold mb-2 text-gray-700">
            Budget <span class="text-red-500">*</span>
          </label>
          <InputNumber
            id="edit-budget"
            v-model="editForm.budget"
            placeholder="Enter budget amount"
            :min="0"
            prefix="₱"
            class="w-full"
            :class="{ 'p-invalid': editErrors.budget }"
          />
          <small v-if="editErrors.budget" class="text-red-500">{{ editErrors.budget }}</small>
        </div>

        <!-- Deadline -->
        <div>
          <label for="edit-deadline" class="block font-semibold mb-2 text-gray-700">
            Deadline
          </label>
          <Calendar
            id="edit-deadline"
            v-model="editForm.deadline"
            :showIcon="true"
            placeholder="Select deadline"
            dateFormat="yy-mm-dd"
            class="w-full"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <Button
            label="Cancel"
            severity="secondary"
            outlined
            @click="closeEditDialog"
            :disabled="updateLoading"
          />
          <Button
            label="Save Changes"
            icon="pi pi-check"
            @click="updateListing"
            :loading="updateLoading"
          />
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Paginator from 'primevue/paginator'
import ProgressSpinner from 'primevue/progressspinner'
import Menu from 'primevue/menu'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Dropdown from 'primevue/dropdown'
import InputNumber from 'primevue/inputnumber'
import Calendar from 'primevue/calendar'
import Divider from 'primevue/divider'
import Tooltip from 'primevue/tooltip'
import { useToast } from 'primevue/usetoast'
import { useRouter } from 'vue-router'
import api from '@/composables/axios'

const toast = useToast()
const router = useRouter()

// Custom debounce
const debounce = (func, delay) => {
  let timeoutId
  return function (...args) {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => func.apply(this, args), delay)
  }
}

// State
const activeRole = ref('seeker')
const activeStatus = ref('all')
const searchQuery = ref('')
const loading = ref(false)
const showFilterDialog = ref(false)
const showEditDialog = ref(false)
const updateLoading = ref(false)

const listings = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0,
})

// Statistics
const stats = ref({
  active: 0,
  closed: 0,
  expired: 0,
})

// Filters
const filters = ref({
  categoryId: null,
  minBudget: null,
  maxBudget: null,
})

const categories = ref([])

// Edit Form
const editForm = ref({
  id: null,
  title: '',
  description: '',
  category_id: null,
  budget: null,
  deadline: null,
})

const editErrors = ref({
  title: '',
  description: '',
  category_id: '',
  budget: '',
})

// Menu
const menu = ref()
const selectedListing = ref(null)

const menuItems = ref([
  {
    label: 'Edit',
    icon: 'pi pi-pencil',
    command: () => editListing(selectedListing.value),
  },
  {
    label: 'View Details',
    icon: 'pi pi-eye',
    command: () => viewListing(selectedListing.value),
  },
  {
    label: 'View Applications',
    icon: 'pi pi-users',
    command: () => viewApplications(selectedListing.value),
  },
  {
    separator: true,
  },
  {
    label: 'Deactivate',
    icon: 'pi pi-times',
    command: () => deactivateListing(selectedListing.value),
  },
  {
    separator: true,
  },
  {
    label: 'Delete',
    icon: 'pi pi-trash',
    class: 'text-red-500',
    command: () => deleteListing(selectedListing.value),
  },
])

// View Applications navigation
const viewApplications = (listing) => {
  router.push(`/listings/${listing.id}/applications`)
}

// Computed
const appliedFiltersCount = computed(() => {
  let count = 0
  if (filters.value.categoryId) count++
  if (filters.value.minBudget) count++
  if (filters.value.maxBudget) count++
  return count || null
})

// API Methods
const loadListings = async () => {
  try {
    loading.value = true

    // Build params, only include filters if set
    const params = {
      perPage: pagination.value.per_page,
      page: pagination.value.current_page,
      sortBy: 'created_at',
      sortOrder: 'desc',
    }
    if (activeStatus.value && activeStatus.value !== 'all') params.status = activeStatus.value
    if (filters.value.categoryId) params.categoryId = filters.value.categoryId
    if (filters.value.minBudget !== null && filters.value.minBudget !== undefined && filters.value.minBudget !== '') params.minBudget = filters.value.minBudget
    if (filters.value.maxBudget !== null && filters.value.maxBudget !== undefined && filters.value.maxBudget !== '') params.maxBudget = filters.value.maxBudget
    if (searchQuery.value && searchQuery.value.trim() !== '') params.search = searchQuery.value.trim()

    const response = await api.get('/seeker/listings', { params })

    if (response.data.success) {
      listings.value = response.data.data
      pagination.value = response.data.pagination
    }
  } catch (error) {
    console.error('Failed to load listings:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load listings',
      life: 3000,
    })
  } finally {
    loading.value = false
  }
}

const loadStatistics = async () => {
  try {
    // Load active listings count
    const activeResponse = await api.get('/seeker/listings', {
      params: { status: 'active', perPage: 1 },
    })
    stats.value.active = activeResponse.data.pagination.total

    // Load closed listings count
    const closedResponse = await api.get('/seeker/listings', {
      params: { status: 'closed', perPage: 1 },
    })
    stats.value.closed = closedResponse.data.pagination.total

    // Load expired listings count
    const expiredResponse = await api.get('/seeker/listings', {
      params: { status: 'expired', perPage: 1 },
    })
    stats.value.expired = expiredResponse.data.pagination.total
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data.data || []
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}

// Validation
const validateEditForm = () => {
  editErrors.value = {
    title: '',
    description: '',
    category_id: '',
    budget: '',
  }

  let isValid = true

  if (!editForm.value.title || editForm.value.title.trim() === '') {
    editErrors.value.title = 'Title is required'
    isValid = false
  } else if (editForm.value.title.length < 5) {
    editErrors.value.title = 'Title must be at least 5 characters'
    isValid = false
  }

  if (!editForm.value.description || editForm.value.description.trim() === '') {
    editErrors.value.description = 'Description is required'
    isValid = false
  } else if (editForm.value.description.length < 20) {
    editErrors.value.description = 'Description must be at least 20 characters'
    isValid = false
  }

  if (!editForm.value.category_id) {
    editErrors.value.category_id = 'Category is required'
    isValid = false
  }

  if (!editForm.value.budget || editForm.value.budget <= 0) {
    editErrors.value.budget = 'Budget must be greater than 0'
    isValid = false
  }

  return isValid
}

// Update Listing
const updateListing = async () => {
  if (!validateEditForm()) {
    toast.add({
      severity: 'warn',
      summary: 'Validation Error',
      detail: 'Please fix the errors in the form',
      life: 3000,
    })
    return
  }

  updateLoading.value = true

  try {
    const payload = {
      title: editForm.value.title,
      description: editForm.value.description,
      category_id: editForm.value.category_id,
      budget: editForm.value.budget,
      deadline: editForm.value.deadline
        ? new Date(editForm.value.deadline).toISOString().split('T')[0]
        : null,
    }

    const response = await api.put(`/seeker/listings/${editForm.value.id}`, payload)

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Listing updated successfully',
        life: 3000,
      })
      closeEditDialog()
      loadListings()
      loadStatistics()
    }
  } catch (error) {
    console.error('Failed to update listing:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Failed to update listing',
      life: 3000,
    })
  } finally {
    updateLoading.value = false
  }
}

// Debounced search
const debouncedSearch = debounce(() => {
  pagination.value.current_page = 1
  loadListings()
}, 500)

// Watchers
watch(searchQuery, () => {
  debouncedSearch()
})
watch(
  () => [filters.value.categoryId, filters.value.minBudget, filters.value.maxBudget],
  () => {
    pagination.value.current_page = 1
    loadListings()
  }
)

// Methods
const changeStatus = (status) => {
  activeStatus.value = status
  pagination.value.current_page = 1
  loadListings()
}

const toggleMenu = (event, listing) => {
  selectedListing.value = listing
  menu.value.toggle(event)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const onPageChange = (event) => {
  pagination.value.current_page = event.page + 1
  pagination.value.per_page = event.rows
  loadListings()
}

const openCreateDialog = () => {
  router.push('/listings')
}

const viewListing = (listing) => {
  router.push(`/listings/${listing.id}`)
}

const editListing = (listing) => {
  editForm.value = {
    id: listing.id,
    title: listing.title,
    description: listing.description,
    category_id: listing.category?.id || null,
    budget: parseFloat(listing.budget),
    deadline: listing.deadline ? new Date(listing.deadline) : null,
  }
  editErrors.value = {
    title: '',
    description: '',
    category_id: '',
    budget: '',
  }
  showEditDialog.value = true
}

const closeEditDialog = () => {
  showEditDialog.value = false
  editForm.value = {
    id: null,
    title: '',
    description: '',
    category_id: null,
    budget: null,
    deadline: null,
  }
  editErrors.value = {
    title: '',
    description: '',
    category_id: '',
    budget: '',
  }
}

const markClosed = (listing) => {
  toast.add({
    severity: 'success',
    summary: 'Listing Closed',
    detail: `${listing.title} marked as closed`,
    life: 3000,
  })
  loadListings()
  loadStatistics()
}


const deactivateListing = async (listing) => {
  if (!listing || !listing.id) return
  if (!confirm(`Are you sure you want to deactivate "${listing.title}"? This will reject all applications for this listing.`)) return
  try {
    updateLoading.value = true
    const response = await api.post(`/seeker/listings/${listing.id}/deactivate`)
    if (response.data.success) {
      toast.add({
        severity: 'warn',
        summary: 'Listing Deactivated',
        detail: `${listing.title} has been deactivated`,
        life: 3000,
      })
      loadListings()
      loadStatistics()
    } else {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: response.data.message || 'Failed to deactivate listing',
        life: 3000,
      })
    }
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Failed to deactivate listing',
      life: 3000,
    })
  } finally {
    updateLoading.value = false
  }
}

const deleteListing = async (listing) => {
  if (!listing || !listing.id) return
  if (!confirm(`Are you sure you want to delete "${listing.title}"? This action cannot be undone.`)) return
  try {
    updateLoading.value = true
    const response = await api.delete(`/seeker/listings/${listing.id}`)
    if (response.data.success) {
      toast.add({
        severity: 'error',
        summary: 'Listing Deleted',
        detail: `${listing.title} has been deleted`,
        life: 3000,
      })
      loadListings()
      loadStatistics()
    } else {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: response.data.message || 'Failed to delete listing',
        life: 3000,
      })
    }
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Failed to delete listing',
      life: 3000,
    })
  } finally {
    updateLoading.value = false
  }
}

const exportListings = () => {
  toast.add({
    severity: 'success',
    summary: 'Export Started',
    detail: 'Your listings are being exported...',
    life: 3000,
  })
}

const clearFilters = () => {
  filters.value = {
    categoryId: null,
    minBudget: null,
    maxBudget: null,
  }
  pagination.value.current_page = 1
  loadListings()
  showFilterDialog.value = false
  toast.add({
    severity: 'info',
    summary: 'Filters Cleared',
    detail: 'All filters have been reset',
    life: 3000,
  })
}

const applyFilters = () => {
  showFilterDialog.value = false
  pagination.value.current_page = 1
  loadListings()
  // No need to toast here, as watcher will trigger loadListings
}

// Lifecycle
onMounted(() => {
  loadCategories()
  loadListings()
  loadStatistics()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
