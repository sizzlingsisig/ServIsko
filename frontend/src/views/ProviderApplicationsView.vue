<template>
  <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Left Container (3/4 width) - Applications List -->
    <div class="lg:col-span-3">
      <Card class="shadow-sm">
        <template #content>
          <!-- Status Tabs and Search -->
          <div
            class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b border-gray-200"
          >
            <div class="flex gap-2 flex-wrap">
              <button
                @click="changeStatus('all')"
                :class="statusButtonClass('all')"
              >
                <i class="pi pi-list"></i>
                All
              </button>
              <button
                @click="changeStatus('pending')"
                :class="statusButtonClass('pending')"
              >
                <i class="pi pi-clock"></i>
                Pending
              </button>
              <button
                @click="changeStatus('accepted')"
                :class="statusButtonClass('accepted')"
              >
                <i class="pi pi-check-circle"></i>
                Accepted
              </button>
              <button
                @click="changeStatus('rejected')"
                :class="statusButtonClass('rejected')"
              >
                <i class="pi pi-times-circle"></i>
                Rejected
              </button>
            </div>

            <!-- Search -->
            <div class="flex gap-2 w-full md:w-auto">
              <IconField iconPosition="left" class="flex-1 md:flex-initial">
                <InputIcon class="pi pi-search" />
                <InputText
                  v-model="searchQuery"
                  placeholder="Search applications..."
                  class="w-full md:w-64"
                />
              </IconField>
            </div>
          </div>

          <!-- Applications Content -->
          <div v-if="loading" class="flex justify-center py-12">
            <ProgressSpinner />
          </div>

          <div v-else-if="applications.length === 0" class="text-center py-12">
            <i class="pi pi-inbox text-6xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-600 text-lg mb-4">No applications found</p>
            <button
              @click="router.push('/listings')"
              class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold transition-all inline-flex items-center gap-2"
            >
              <i class="pi pi-search"></i>
              Browse Listings
            </button>
          </div>

          <div v-else class="space-y-4">
            <Card
              v-for="application in applications"
              :key="application.id"
              class="hover:shadow-md transition-shadow border border-gray-200"
            >
              <template #content>
                <div class="flex flex-col md:flex-row justify-between gap-4">
                  <div class="flex-1">
                    <!-- Listing Info -->
                    <div class="flex items-start gap-3 mb-3">
                      <div
                        class="w-12 h-12 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-lg flex-shrink-0"
                      >
                        {{ application.listing.title.charAt(0) }}
                      </div>
                      <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900 mb-1">
                          {{ application.listing.title }}
                        </h3>
                        <p class="text-gray-600 text-sm mb-1">
                          by {{ application.listing.seeker.name }}
                        </p>
                        <p class="text-gray-600 text-sm line-clamp-2">
                          {{ application.listing.description }}
                        </p>
                      </div>
                    </div>

                    <!-- Application Message -->
                    <div class="bg-gray-50 p-3 rounded-lg mb-3">
                      <p class="text-sm font-semibold text-gray-700 mb-1">Your Message:</p>
                      <p class="text-sm text-gray-600">{{ application.message }}</p>
                    </div>

                    <!-- Metadata -->
                    <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                      <div class="flex items-center gap-1">
                        <i class="pi pi-calendar text-primary-500"></i>
                        <span>Applied {{ formatDate(application.created_at) }}</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <i class="pi pi-dollar text-primary-500"></i>
                        <span>₱{{ parseFloat(application.listing.budget).toLocaleString() }}</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <i class="pi pi-tag text-primary-500"></i>
                        <span>{{ application.listing.category?.name || 'No Category' }}</span>
                      </div>
                      <div class="flex items-center gap-1">
                        <i
                          :class="[
                            'pi',
                            application.listing.status === 'active'
                              ? 'pi-check-circle text-green-500'
                              : application.listing.status === 'expired'
                              ? 'pi-times-circle text-red-500'
                              : 'pi-lock text-blue-500',
                          ]"
                        ></i>
                        <span class="capitalize">Listing: {{ application.listing.status }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Status and Actions -->
                  <div class="flex md:flex-col gap-2 items-start">
                    <span :class="getApplicationStatusClass(application.status)">
                      {{ application.status }}
                    </span>
                    <button
                      @click="toggleMenu($event, application)"
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
          <div v-if="applications.length > 0" class="mt-6">
            <Paginator
              :rows="pagination.per_page"
              :totalRecords="pagination.total"
              :first="(pagination.current_page - 1) * pagination.per_page"
              :rowsPerPageOptions="[10, 15, 20, 50]"
              @page="onPageChange"
            />
          </div>
        </template>
      </Card>
    </div>

    <!-- Right Sidebar - Application Stats -->
    <div class="lg:col-span-1 space-y-6">
      <!-- Statistics Card -->
      <Card class="shadow-lg border-t-4 border-primary-500">
        <template #title>
          <div class="flex items-center gap-2 text-gray-900">
            <i class="pi pi-chart-bar text-primary-500"></i>
            <span>Application Statistics</span>
          </div>
        </template>
        <template #content>
          <div class="space-y-4">
            <div class="p-4 rounded-lg shadow-md hover:scale-105 transition-transform bg-primary-50">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-semibold text-primary-900">Total Applications</span>
                <i class="pi pi-list text-2xl text-primary-600"></i>
              </div>
              <p class="text-4xl font-bold text-primary-900">{{ stats.total }}</p>
            </div>

            <div class="p-4 rounded-lg shadow-md hover:scale-105 transition-transform bg-yellow-50">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-semibold text-yellow-900">Pending</span>
                <i class="pi pi-hourglass text-2xl text-yellow-600"></i>
              </div>
              <p class="text-4xl font-bold text-yellow-900">{{ stats.pending }}</p>
            </div>

            <div class="p-4 rounded-lg shadow-md hover:scale-105 transition-transform bg-green-50">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-semibold text-green-900">Accepted</span>
                <i class="pi pi-check-circle text-2xl text-green-600"></i>
              </div>
              <p class="text-4xl font-bold text-green-900">{{ stats.accepted }}</p>
            </div>

            <div class="p-4 rounded-lg shadow-md hover:scale-105 transition-transform bg-red-50">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-semibold text-red-900">Rejected</span>
                <i class="pi pi-times-circle text-2xl text-red-600"></i>
              </div>
              <p class="text-4xl font-bold text-red-900">{{ stats.rejected }}</p>
            </div>

            <Divider />

            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-3 rounded-lg border border-green-200">
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-700 font-semibold flex items-center gap-1">
                  <i class="pi pi-trophy text-green-600"></i>
                  Success Rate
                </span>
                <span class="font-bold text-green-700">{{ successRate }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5 shadow-inner">
                <div
                  class="bg-gradient-to-r from-green-500 to-emerald-500 h-2.5 rounded-full transition-all duration-500 shadow-sm"
                  :style="{ width: `${successRate}%` }"
                ></div>
              </div>
            </div>
          </div>
        </template>
      </Card>

      <!-- Quick Actions -->
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
              @click="router.push('/listings')"
              class="w-full bg-primary-500 hover:bg-primary-600 text-white px-4 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2"
            >
              <i class="pi pi-search"></i>
              Browse Listings
            </button>
            <button
              @click="exportApplications"
              class="w-full border-2 border-gray-300 hover:border-primary-500 text-gray-700 hover:text-primary-500 px-4 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2"
            >
              <i class="pi pi-download"></i>
              Export Applications
            </button>
          </div>
        </template>
      </Card>
    </div>

    <!-- Context Menu -->
    <Menu ref="menu" :model="menuItems" :popup="true" />

    <!-- Edit Application Dialog -->
    <Dialog v-model:visible="showEditDialog" :modal="true" :style="{ width: '600px' }">
      <template #header>
        <div class="flex items-center gap-2">
          <i class="pi pi-pencil text-primary-500"></i>
          <span class="font-bold text-xl">Edit Application</span>
        </div>
      </template>

      <div class="space-y-4 py-4">
        <div>
          <label class="block font-semibold mb-2 text-gray-700">
            Message <span class="text-red-500">*</span>
          </label>
          <Textarea
            v-model="editForm.message"
            placeholder="Update your application message..."
            rows="8"
            class="w-full"
            :class="{ 'p-invalid': editErrors.message }"
          />
          <small v-if="editErrors.message" class="text-red-500">{{ editErrors.message }}</small>
        </div>
      </div>

      <template #footer>
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
          @click="updateApplication"
          :loading="updateLoading"
        />
      </template>
    </Dialog>

    <!-- Withdraw Confirmation Dialog -->
    <Dialog v-model:visible="showWithdrawDialog" :modal="true" :style="{ width: '450px' }">
      <template #header>
        <div class="flex items-center gap-2">
          <i class="pi pi-exclamation-circle text-primary-500 text-2xl"></i>
          <span class="font-bold text-xl">Withdraw Application</span>
        </div>
      </template>

      <div class="py-4">
        <p class="text-gray-700 mb-2">
          Are you sure you want to withdraw your application to <strong>"{{ selectedApplication?.listing?.title }}"</strong>?
        </p>
        <p class="text-sm text-primary-600 font-semibold">
          ⚠️ This action cannot be undone.
        </p>
      </div>

      <template #footer>
        <Button
          label="Cancel"
          severity="secondary"
          outlined
          @click="showWithdrawDialog = false"
          :disabled="updateLoading"
        />
        <Button
  label="Withdraw Application"
  icon="pi pi-trash"
  @click="confirmWithdraw"
  :loading="updateLoading"
  class="bg-primary-500 hover:bg-primary-600 border-primary-500 hover:border-primary-600"
/>

      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
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
import Divider from 'primevue/divider'
import api from '@/composables/axios'

const router = useRouter()
const toast = useToast()

// State
const activeStatus = ref('all')
const searchQuery = ref('')
const loading = ref(false)
const showEditDialog = ref(false)
const showWithdrawDialog = ref(false)
const updateLoading = ref(false)

const applications = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
})

const stats = ref({
  total: 0,
  pending: 0,
  accepted: 0,
  rejected: 0,
})

const editForm = ref({
  id: null,
  message: '',
})

const editErrors = ref({
  message: '',
})

const menu = ref()
const selectedApplication = ref(null)

const menuItems = ref([
  {
    label: 'View Listing',
    icon: 'pi pi-eye',
    command: () =>
      router.push(`/listings/${selectedApplication.value.listing.id}`),
  },
  {
    label: 'Edit Application',
    icon: 'pi pi-pencil',
    command: () => editApplication(selectedApplication.value),
  },
  { separator: true },
  {
    label: 'Withdraw Application',
    icon: 'pi pi-trash',
    class: 'text-red-500',
    command: () => openWithdrawDialog(selectedApplication.value),
  },
])

// Computed
const successRate = computed(() => {
  const total = stats.value.accepted + stats.value.rejected
  if (total === 0) return 0
  return Math.round((stats.value.accepted / total) * 100)
})

// Helper Methods
const statusButtonClass = (status) => {
  return [
    'px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-2',
    activeStatus.value === status
      ? 'bg-primary-500 text-white shadow-sm'
      : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
  ]
}

const getApplicationStatusClass = (status) => {
  return [
    'px-3 py-1 rounded-full text-xs font-semibold uppercase',
    status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '',
    status === 'accepted' ? 'bg-green-100 text-green-700' : '',
    status === 'rejected' ? 'bg-red-100 text-red-700' : '',
  ]
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const debounce = (func, delay) => {
  let timeoutId
  return function (...args) {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => func.apply(this, args), delay)
  }
}

// API Methods
const loadApplications = async () => {
  try {
    loading.value = true

    const params = {
      per_page: pagination.value.per_page,
      page: pagination.value.current_page,
    }

    const response = await api.get('/provider/applications', { params })

    if (response.data.success) {
      let allApplications = response.data.data.data

      // Client-side filtering by status and search
      if (activeStatus.value !== 'all') {
        allApplications = allApplications.filter(
          (app) => app.status === activeStatus.value
        )
      }

      if (searchQuery.value?.trim()) {
        const query = searchQuery.value.toLowerCase()
        allApplications = allApplications.filter(
          (app) =>
            app.listing.title.toLowerCase().includes(query) ||
            app.message.toLowerCase().includes(query) ||
            app.listing.seeker.name.toLowerCase().includes(query)
        )
      }

      applications.value = allApplications
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total,
      }
    }
  } catch (error) {
    console.error('Failed to load applications:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Failed to load applications',
      life: 3000,
    })
  } finally {
    loading.value = false
  }
}

const loadStatistics = async () => {
  try {
    const response = await api.get('/provider/applications', {
      params: { per_page: 1000 }, // Get all to count
    })

    if (response.data.success) {
      const allApps = response.data.data.data

      stats.value.total = allApps.length
      stats.value.pending = allApps.filter((app) => app.status === 'pending').length
      stats.value.accepted = allApps.filter((app) => app.status === 'accepted').length
      stats.value.rejected = allApps.filter((app) => app.status === 'rejected').length
    }
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const validateEditForm = () => {
  editErrors.value = { message: '' }
  let isValid = true

  if (!editForm.value.message?.trim()) {
    editErrors.value.message = 'Message is required'
    isValid = false
  } else if (editForm.value.message.length < 10) {
    editErrors.value.message = 'Message must be at least 10 characters'
    isValid = false
  }

  return isValid
}

const updateApplication = async () => {
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
      message: editForm.value.message,
    }

    const response = await api.patch(
      `/provider/applications/${editForm.value.id}`,
      payload
    )

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Application updated successfully',
        life: 3000,
      })
      closeEditDialog()
      loadApplications()
      loadStatistics()
    }
  } catch (error) {
    console.error('Failed to update application:', error)
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Failed to update application',
      life: 3000,
    })
  } finally {
    updateLoading.value = false
  }
}

// Withdraw Application Methods
const openWithdrawDialog = (application) => {
  selectedApplication.value = application
  showWithdrawDialog.value = true
}

const confirmWithdraw = async () => {
  if (!selectedApplication.value?.id) return

  updateLoading.value = true

  try {
    const response = await api.delete(`/provider/applications/${selectedApplication.value.id}`)

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Application Withdrawn',
        detail: 'Your application has been withdrawn successfully',
        life: 3000,
      })
      showWithdrawDialog.value = false
      loadApplications()
      loadStatistics()
    }
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Failed to withdraw application',
      life: 3000,
    })
  } finally {
    updateLoading.value = false
  }
}

const changeStatus = (status) => {
  activeStatus.value = status
  pagination.value.current_page = 1
  loadApplications()
}

const toggleMenu = (event, application) => {
  selectedApplication.value = application
  menu.value.toggle(event)
}

const onPageChange = (event) => {
  pagination.value.current_page = event.page + 1
  pagination.value.per_page = event.rows
  loadApplications()
}

const editApplication = (application) => {
  editForm.value = {
    id: application.id,
    message: application.message,
  }
  editErrors.value = { message: '' }
  showEditDialog.value = true
}

const closeEditDialog = () => {
  showEditDialog.value = false
  editForm.value = { id: null, message: '' }
  editErrors.value = { message: '' }
}

const exportApplications = () => {
  toast.add({
    severity: 'success',
    summary: 'Export Started',
    detail: 'Your applications are being exported...',
    life: 3000,
  })
}

const debouncedSearch = debounce(() => {
  pagination.value.current_page = 1
  loadApplications()
}, 500)

watch(searchQuery, () => {
  debouncedSearch()
})

onMounted(() => {
  loadApplications()
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
