<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import Card from 'primevue/card'
import ProgressSpinner from 'primevue/progressspinner'
import Tag from 'primevue/tag'
import Menu from 'primevue/menu'
import Chip from 'primevue/chip'
import api from '@/composables/axios'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const applications = ref([])
const loading = ref(false)
const error = ref(null)
const menuRefs = ref({})
const actionLoading = ref({})
const activeFilter = ref('all')

const listingId = route.params.id

// Computed
const filteredApplications = computed(() => {
  if (activeFilter.value === 'all') return applications.value
  return applications.value.filter((app) => app.status === activeFilter.value)
})

const stats = computed(() => ({
  total: applications.value.length,
  pending: applications.value.filter((a) => a.status === 'pending').length,
  accepted: applications.value.filter((a) => a.status === 'accepted').length,
  rejected: applications.value.filter((a) => a.status === 'rejected').length,
}))

// Helper Methods
const statusButtonClass = (status) => {
  return [
    'px-4 py-2 rounded-lg font-medium transition-all flex items-center gap-2',
    activeFilter.value === status
      ? 'bg-primary-500 text-white shadow-sm'
      : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
  ]
}

const getStatusSeverity = (status) => {
  const severities = {
    pending: 'warn',
    accepted: 'success',
    rejected: 'danger',
  }
  return severities[status] || 'info'
}

const getUserInitials = (name) => {
  if (!name) return 'U'
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getTimeAgo = (date) => {
  const now = new Date()
  const past = new Date(date)
  const diffMs = now - past
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)

  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  return formatDate(date)
}

// API Methods
const fetchApplications = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.get(`/seeker/listings/${listingId}/applications`)
    if (response.data.success) {
      applications.value = Array.isArray(response.data.data?.data) ? response.data.data.data : []
    } else {
      error.value = response.data.message || 'Failed to fetch applications'
      applications.value = []
    }
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Failed to load applications'
    applications.value = []
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.value,
      life: 3000,
    })
  } finally {
    loading.value = false
  }
}

const handleApplicationAction = async (app, action) => {
  if (!['accept', 'reject'].includes(action)) return

  const url = `/seeker/listings/${listingId}/applications/${app.id}/${action}`
  actionLoading.value[app.id] = true

  try {
    const response = await api.post(url)
    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: `Application ${action}ed successfully!`,
        life: 3000,
      })
      app.status = action === 'accept' ? 'accepted' : 'rejected'
      fetchApplications()
    } else {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: response.data.message || `Failed to ${action} application.`,
        life: 4000,
      })
      fetchApplications()
    }
  } catch (e) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: e.response?.data?.message || `Failed to ${action} application.`,
      life: 4000,
    })
    fetchApplications()
  } finally {
    actionLoading.value[app.id] = false
  }
}

const viewUserProfile = (user) => {
  if (user && user.id) {
    router.push(`/providers/${user.id}`)
  } else {
    toast.add({
      severity: 'info',
      summary: 'No Profile',
      detail: 'No user profile available.',
      life: 3000,
    })
  }
}

const getMenuItems = (app) => {
  const items = [
    {
      label: 'View Profile',
      icon: 'pi pi-user',
      command: () => viewUserProfile(app.user),
    },
  ]

  if (app.status === 'pending') {
    items.push(
      { separator: true },
      {
        label: 'Accept Application',
        icon: 'pi pi-check',
        command: () => handleApplicationAction(app, 'accept'),
        class: 'text-green-600',
      },
      {
        label: 'Reject Application',
        icon: 'pi pi-times',
        command: () => handleApplicationAction(app, 'reject'),
        class: 'text-red-600',
      },
    )
  }

  return items
}

const toggleMenu = (event, appId) => {
  menuRefs.value[appId]?.toggle(event)
}

onMounted(() => {
  fetchApplications()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header Section -->
      <div
        class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 bg-white p-6 rounded-lg shadow-sm border-l-4 border-primary-500"
      >
        <div class="flex items-start gap-3">
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Listing Applications</h1>
            <p class="text-lg text-gray-600">Review and manage applications for this listing</p>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="flex flex-wrap gap-2 mt-4 md:mt-0">
          <Chip
            :label="`${stats.total} Total`"
            icon="pi pi-users"
            class="bg-primary-50 text-primary-700"
          />
          <Chip
            v-if="stats.pending > 0"
            :label="`${stats.pending} Pending`"
            icon="pi pi-clock"
            class="bg-orange-50 text-orange-700"
          />
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Applications List (3/4 width) -->
        <div class="lg:col-span-3">
          <Card class="shadow-sm">
            <template #content>
              <!-- Status Filter Tabs -->
              <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b border-gray-200"
              >
                <div class="flex gap-2 flex-wrap">
                  <button @click="activeFilter = 'all'" :class="statusButtonClass('all')">
                    <i class="pi pi-list"></i>
                    All
                  </button>
                  <button @click="activeFilter = 'pending'" :class="statusButtonClass('pending')">
                    <i class="pi pi-clock"></i>
                    Pending
                  </button>
                  <button @click="activeFilter = 'accepted'" :class="statusButtonClass('accepted')">
                    <i class="pi pi-check-circle"></i>
                    Accepted
                  </button>
                  <button @click="activeFilter = 'rejected'" :class="statusButtonClass('rejected')">
                    <i class="pi pi-times-circle"></i>
                    Rejected
                  </button>
                </div>
              </div>

              <!-- Loading State -->
              <div v-if="loading" class="flex justify-center py-12">
                <ProgressSpinner />
              </div>

              <!-- Error State -->
              <div v-else-if="error" class="text-center py-12">
                <i class="pi pi-exclamation-triangle text-6xl text-red-500 mb-4 block"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Something went wrong</h3>
                <p class="text-gray-600 mb-4">{{ error }}</p>
                <button
                  @click="fetchApplications"
                  class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold transition-all inline-flex items-center gap-2"
                >
                  <i class="pi pi-refresh"></i>
                  Try Again
                </button>
              </div>

              <!-- Empty State -->
              <div v-else-if="filteredApplications.length === 0" class="text-center py-12">
                <i class="pi pi-inbox text-6xl text-gray-300 mb-4 block"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                  {{
                    activeFilter === 'all'
                      ? 'No applications yet'
                      : `No ${activeFilter} applications`
                  }}
                </h3>
                <p class="text-gray-600 mb-4">
                  {{
                    activeFilter === 'all'
                      ? 'Applications will appear here once providers apply to your listing.'
                      : 'Try selecting a different filter to view other applications.'
                  }}
                </p>
                <button
                  v-if="activeFilter !== 'all'"
                  @click="activeFilter = 'all'"
                  class="bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-primary-500 px-6 py-3 rounded-lg font-semibold transition-all inline-flex items-center gap-2"
                >
                  <i class="pi pi-list"></i>
                  View All Applications
                </button>
              </div>

              <!-- Applications List -->
              <div v-else class="space-y-4">
                <Card
                  v-for="app in filteredApplications"
                  :key="app.id"
                  class="hover:shadow-md transition-shadow border border-gray-200"
                  :class="{ 'opacity-60': actionLoading[app.id] }"
                >
                  <template #content>
                    <div class="relative">
                      <!-- Header Row: User Info + Status + Actions -->
                      <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                          <div
                            class="w-12 h-12 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-lg flex-shrink-0"
                          >
                            {{ getUserInitials(app.user?.name) }}
                          </div>
                          <div class="flex-1 min-w-0">
                            <h3 class="text-xl font-semibold text-gray-900 mb-1">
                              {{ app.user?.name || 'Unknown User' }}
                            </h3>
                            <p class="text-sm text-gray-500">
                              @{{ app.user?.username || 'unknown' }}
                            </p>
                          </div>
                        </div>

                        <!-- Status Tag + Action Buttons (Right Side) -->
                        <div class="flex items-start gap-2 flex-shrink-0">
                          <Tag
                            :value="app.status"
                            :severity="getStatusSeverity(app.status)"
                            class="uppercase text-xs"
                          />

                          <!-- Action Buttons Group -->
                          <div v-if="app.status === 'pending'" class="flex items-center gap-2">
                            <button
                              @click="handleApplicationAction(app, 'reject')"
                              :disabled="actionLoading[app.id]"
                              class="p-2.5 bg-red-50 hover:bg-red-100 active:bg-red-200 text-red-600 border border-red-200 hover:border-red-400 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                              title="Reject Application"
                            >
                              <i class="pi pi-times text-base"></i>
                            </button>
                            <button
                              @click="handleApplicationAction(app, 'accept')"
                              :disabled="actionLoading[app.id]"
                              class="p-2.5 bg-green-500 hover:bg-green-600 active:bg-green-700 text-white rounded-lg transition-all shadow-sm hover:shadow disabled:opacity-50 disabled:cursor-not-allowed"
                              title="Accept Application"
                            >
                              <i class="pi pi-check text-base"></i>
                            </button>
                          </div>

                          <button
                            @click="toggleMenu($event, app.id)"
                            :disabled="actionLoading[app.id]"
                            class="p-2 hover:bg-gray-100 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            title="More options"
                          >
                            <i class="pi pi-ellipsis-v text-gray-600"></i>
                          </button>
                        </div>
                      </div>

                      <!-- Application Message -->
                      <div class="bg-gray-50 p-3 rounded-lg mb-3">
                        <p class="text-sm font-semibold text-gray-700 mb-1">Message:</p>
                        <p class="text-sm text-gray-600">
                          {{ app.message || 'No message provided.' }}
                        </p>
                      </div>

                      <!-- Metadata -->
                      <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                        <div class="flex items-center gap-1">
                          <i class="pi pi-calendar text-primary-500"></i>
                          <span>{{ getTimeAgo(app.created_at) }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                          <i class="pi pi-envelope text-primary-500"></i>
                          <span>{{ app.user?.email }}</span>
                        </div>
                      </div>

                      <!-- Loading Overlay -->
                      <div
                        v-if="actionLoading[app.id]"
                        class="absolute inset-0 bg-white/70 flex items-center justify-center rounded-lg"
                      >
                        <ProgressSpinner style="width: 40px; height: 40px" />
                      </div>
                    </div>
                  </template>
                </Card>
              </div>
            </template>
          </Card>
        </div>

        <!-- Right Sidebar (1/4 width) -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Statistics Card -->
          <Card class="shadow-lg border-t-4 border-primary-500">
            <template #title>
              <div class="flex items-center gap-2 text-gray-900">
                <i class="pi pi-chart-bar text-primary-500"></i>
                <span>Statistics</span>
              </div>
            </template>
            <template #content>
              <div class="space-y-4">
                <div
                  class="p-4 rounded-lg shadow-md hover:scale-105 transition-transform bg-primary-50"
                >
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-semibold text-primary-900">Total Applications</span>
                    <i class="pi pi-list text-2xl text-primary-600"></i>
                  </div>
                  <p class="text-4xl font-bold text-primary-900">{{ stats.total }}</p>
                </div>

                <div
                  class="p-4 rounded-lg shadow-md hover:scale-105 transition-transform bg-yellow-50"
                >
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-semibold text-yellow-900">Pending</span>
                    <i class="pi pi-hourglass text-2xl text-yellow-600"></i>
                  </div>
                  <p class="text-4xl font-bold text-yellow-900">{{ stats.pending }}</p>
                </div>

                <div
                  class="p-4 rounded-lg shadow-md hover:scale-105 transition-transform bg-green-50"
                >
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-semibold text-green-900">Accepted</span>
                    <i class="pi pi-check-circle text-2xl text-green-600"></i>
                  </div>
                  <p class="text-4xl font-bold text-green-900">{{ stats.accepted }}</p>
                </div>

                <div
                  class="p-4 rounded-lg shadow-md hover:scale-105 transition-transform bg-red-50"
                >
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-semibold text-red-900">Rejected</span>
                    <i class="pi pi-times-circle text-2xl text-red-600"></i>
                  </div>
                  <p class="text-4xl font-bold text-red-900">{{ stats.rejected }}</p>
                </div>
              </div>
            </template>
          </Card>
        </div>
      </div>
    </div>

    <!-- Context Menu -->
    <Menu
      v-for="app in applications"
      :key="`menu-${app.id}`"
      :ref="(el) => (menuRefs[app.id] = el)"
      :model="getMenuItems(app)"
      :popup="true"
    />
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
