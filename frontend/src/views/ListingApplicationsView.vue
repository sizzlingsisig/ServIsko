<script setup>
import { ref, onMounted, computed } from 'vue'
import Card from 'primevue/card'
import ProgressSpinner from 'primevue/progressspinner'
import Tag from 'primevue/tag'
import Menu from 'primevue/menu'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import Chip from 'primevue/chip'
import Avatar from 'primevue/avatar'
import Divider from 'primevue/divider'
import { useRoute, useRouter } from 'vue-router'
import api from '@/composables/axios'
import { useToast } from 'primevue/usetoast'

const route = useRoute()
const router = useRouter()
const toast = useToast()

const applications = ref([])
const loading = ref(false)
const error = ref(null)
const menuRefs = ref({})
const actionLoading = ref({})
const activeFilter = ref('all') // all, pending, accepted, rejected

const listingId = route.params.id

// Computed filtered applications
const filteredApplications = computed(() => {
  if (activeFilter.value === 'all') return applications.value
  return applications.value.filter(app => app.status === activeFilter.value)
})

// Computed statistics
const stats = computed(() => ({
  total: applications.value.length,
  pending: applications.value.filter(a => a.status === 'pending').length,
  accepted: applications.value.filter(a => a.status === 'accepted').length,
  rejected: applications.value.filter(a => a.status === 'rejected').length
}))

const fetchApplications = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.get(`/seeker/listings/${listingId}/applications`)
    if (response.data.success) {
      applications.value = Array.isArray(response.data.data?.data)
        ? response.data.data.data
        : []
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

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
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
      // Update the status locally with smooth transition
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
      command: () => viewUserProfile(app.user)
    }
  ]

  if (app.status === 'pending') {
    items.push(
      { separator: true },
      {
        label: 'Accept Application',
        icon: 'pi pi-check',
        command: () => handleApplicationAction(app, 'accept'),
        class: 'text-green-600'
      },
      {
        label: 'Reject Application',
        icon: 'pi pi-times',
        command: () => handleApplicationAction(app, 'reject'),
        class: 'text-red-600'
      }
    )
  }

  return items
}

const toggleMenu = (event, appId) => {
  menuRefs.value[appId]?.toggle(event)
}

const getStatusSeverity = (status) => {
  const severities = {
    'pending': 'warn',
    'accepted': 'success',
    'rejected': 'danger'
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

onMounted(() => {
  fetchApplications()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header Section -->
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 bg-white p-4 md:p-6 rounded-xl shadow-sm border-l-4 border-primary-500">
        <div class="flex items-start gap-3 mb-4 md:mb-0">

          <div>
            <h1 class="text-2xl md:text-4xl font-bold text-gray-900 mb-1">Listing Applications</h1>
            <p class="text-sm md:text-base text-gray-600">Review and manage applications for this listing</p>
          </div>
        </div>

        <!-- Quick Stats Chips -->
        <div class="flex flex-wrap gap-2">
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

      <!-- Filter Tabs -->
      <div class="mb-6 bg-white p-2 rounded-xl shadow-sm">
        <div class="flex gap-2 overflow-x-auto">
          <Button
            label="All"
            :outlined="activeFilter !== 'all'"
            :severity="activeFilter === 'all' ? 'primary' : 'secondary'"
            @click="activeFilter = 'all'"
            :badge="stats.total.toString()"
            size="small"
          />
          <Button
            label="Pending"
            :outlined="activeFilter !== 'pending'"
            severity="warn"
            @click="activeFilter = 'pending'"
            :badge="stats.pending > 0 ? stats.pending.toString() : null"
            size="small"
          />
          <Button
            label="Accepted"
            :outlined="activeFilter !== 'accepted'"
            severity="success"
            @click="activeFilter = 'accepted'"
            :badge="stats.accepted > 0 ? stats.accepted.toString() : null"
            size="small"
          />
          <Button
            label="Rejected"
            :outlined="activeFilter !== 'rejected'"
            severity="danger"
            @click="activeFilter = 'rejected'"
            :badge="stats.rejected > 0 ? stats.rejected.toString() : null"
            size="small"
          />
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Applications List (3/4 width) -->
        <div class="lg:col-span-3">
          <!-- Loading Skeleton -->
          <div v-if="loading" class="space-y-4">
            <Card v-for="i in 3" :key="i" class="shadow-sm">
              <template #content>
                <div class="flex gap-4">
                  <Skeleton shape="circle" size="3rem" />
                  <div class="flex-1">
                    <Skeleton width="60%" class="mb-2" />
                    <Skeleton width="40%" class="mb-4" />
                    <Skeleton width="100%" height="4rem" />
                  </div>
                </div>
              </template>
            </Card>
          </div>

          <!-- Error State -->
          <Card v-else-if="error" class="shadow-sm border-l-4 border-red-500">
            <template #content>
              <div class="text-center py-12">
                <i class="pi pi-exclamation-triangle text-6xl text-red-500 mb-4 block"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Something went wrong</h3>
                <p class="text-gray-600 mb-4">{{ error }}</p>
                <Button
                  label="Try Again"
                  icon="pi pi-refresh"
                  @click="fetchApplications"
                  outlined
                />
              </div>
            </template>
          </Card>

          <!-- Empty State -->
          <Card v-else-if="filteredApplications.length === 0" class="shadow-sm">
            <template #content>
              <div class="text-center py-12">
                <i class="pi pi-inbox text-8xl text-gray-300 mb-4 block"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                  {{ activeFilter === 'all' ? 'No applications yet' : `No ${activeFilter} applications` }}
                </h3>
                <p class="text-gray-600 mb-4">
                  {{ activeFilter === 'all'
                    ? 'Applications will appear here once providers apply to your listing.'
                    : `Try selecting a different filter to view other applications.`
                  }}
                </p>
                <Button
                  v-if="activeFilter !== 'all'"
                  label="View All Applications"
                  icon="pi pi-list"
                  @click="activeFilter = 'all'"
                  outlined
                />
              </div>
            </template>
          </Card>

          <!-- Applications List -->
          <div v-else class="space-y-4">
            <Card
              v-for="app in filteredApplications"
              :key="app.id"
              class="hover:shadow-lg transition-all duration-200 border border-gray-200 hover:border-primary-300"
              :class="{ 'opacity-60': actionLoading[app.id] }"
            >
              <template #content>
                <div class="relative">
                  <!-- Three-dot menu button -->
                  <div class="absolute top-0 right-0 z-10">
                    <Button
                      icon="pi pi-ellipsis-v"
                      text
                      rounded
                      @click="toggleMenu($event, app.id)"
                      aria-haspopup="true"
                      :aria-controls="`menu_${app.id}`"
                      class="text-gray-500 hover:text-gray-700 hover:bg-gray-100"
                      size="small"
                      :disabled="actionLoading[app.id]"
                    />
                    <Menu
                      :ref="el => menuRefs[app.id] = el"
                      :id="`menu_${app.id}`"
                      :model="getMenuItems(app)"
                      popup
                    />
                  </div>

                  <div class="flex flex-col sm:flex-row gap-4 pr-8">
                    <!-- User Avatar & Info -->
                    <div class="flex gap-3 flex-1">
                      <Avatar
                        :label="getUserInitials(app.user?.name)"
                        size="large"
                        shape="circle"
                        class="bg-gradient-to-br from-primary-400 to-primary-600 text-white flex-shrink-0"
                      />

                      <div class="flex-1 min-w-0">
                        <!-- User Name & Status -->
                        <div class="flex items-start gap-2 mb-1 flex-wrap">
                          <h3 class="font-semibold text-gray-900 text-lg">
                            {{ app.user?.name || 'Unknown User' }}
                          </h3>
                          <Tag
                            :value="app.status"
                            :severity="getStatusSeverity(app.status)"
                            class="uppercase text-xs font-semibold"
                          />
                        </div>

                        <!-- User Metadata -->
                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mb-3">
                          <span v-if="app.user?.username" class="flex items-center gap-1">
                            <i class="pi pi-at text-xs"></i>
                            {{ app.user.username }}
                          </span>
                          <span v-if="app.user?.email" class="flex items-center gap-1">
                            <i class="pi pi-envelope text-xs"></i>
                            {{ app.user.email }}
                          </span>
                          <span class="flex items-center gap-1">
                            <i class="pi pi-clock text-xs"></i>
                            {{ getTimeAgo(app.created_at) }}
                          </span>
                        </div>

                        <Divider class="my-3" />

                        <!-- Application Message -->
                        <div>
                          <div class="flex items-center gap-2 mb-2">
                            <i class="pi pi-comment text-gray-500"></i>
                            <span class="font-semibold text-gray-700 text-sm">Application Message</span>
                          </div>
                          <p class="text-gray-800 text-sm leading-relaxed whitespace-pre-line bg-gray-50 p-3 rounded-lg">
                            {{ app.message || 'No message provided.' }}
                          </p>
                        </div>

                        <!-- Quick Actions (Mobile/Pending Only) -->
                        <div v-if="app.status === 'pending'" class="flex gap-2 mt-4 sm:hidden">
                          <Button
                            label="Accept"
                            icon="pi pi-check"
                            severity="success"
                            size="small"
                            @click="handleApplicationAction(app, 'accept')"
                            :loading="actionLoading[app.id]"
                            class="flex-1"
                          />
                          <Button
                            label="Reject"
                            icon="pi pi-times"
                            severity="danger"
                            outlined
                            size="small"
                            @click="handleApplicationAction(app, 'reject')"
                            :loading="actionLoading[app.id]"
                            class="flex-1"
                          />
                        </div>
                      </div>
                    </div>

                    <!-- Desktop Quick Actions -->
                    <div v-if="app.status === 'pending'" class="hidden sm:flex flex-col gap-2 min-w-[140px]">
                      <Button
                        label="Accept"
                        icon="pi pi-check"
                        severity="success"
                        size="small"
                        @click="handleApplicationAction(app, 'accept')"
                        :loading="actionLoading[app.id]"
                      />
                      <Button
                        label="Reject"
                        icon="pi pi-times"
                        severity="danger"
                        outlined
                        size="small"
                        @click="handleApplicationAction(app, 'reject')"
                        :loading="actionLoading[app.id]"
                      />
                    </div>
                  </div>

                  <!-- Loading Overlay -->
                  <div v-if="actionLoading[app.id]" class="absolute inset-0 bg-white/50 flex items-center justify-center rounded-lg">
                    <ProgressSpinner style="width: 40px; height: 40px" />
                  </div>
                </div>
              </template>
            </Card>
          </div>
        </div>

        <!-- Right Sidebar (1/4 width) -->
        <div class="lg:col-span-1 space-y-4">
          <!-- Stats Card -->
          <Card class="shadow-lg border-t-4 border-primary-500 sticky top-6">
            <template #title>
              <div class="flex items-center gap-2 text-gray-900 text-lg">
                <i class="pi pi-chart-bar text-primary-500"></i>
                <span>Statistics</span>
              </div>
            </template>
            <template #content>
              <div class="space-y-3">
                <div
                  class="p-4 rounded-lg shadow-md bg-gradient-to-br from-primary-50 to-primary-100 cursor-pointer hover:shadow-lg transition-shadow"
                  @click="activeFilter = 'all'"
                >
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-primary-900 font-semibold flex items-center gap-2">
                      <i class="pi pi-users"></i>
                      Total
                    </span>
                  </div>
                  <p class="text-3xl font-bold text-primary-900">{{ stats.total }}</p>
                </div>

                <div
                  class="p-4 rounded-lg shadow-md bg-gradient-to-br from-orange-50 to-orange-100 cursor-pointer hover:shadow-lg transition-shadow"
                  @click="activeFilter = 'pending'"
                >
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-orange-900 font-semibold flex items-center gap-2">
                      <i class="pi pi-clock"></i>
                      Pending
                    </span>
                  </div>
                  <p class="text-3xl font-bold text-orange-900">{{ stats.pending }}</p>
                </div>

                <div
                  class="p-4 rounded-lg shadow-md bg-gradient-to-br from-green-50 to-green-100 cursor-pointer hover:shadow-lg transition-shadow"
                  @click="activeFilter = 'accepted'"
                >
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-green-900 font-semibold flex items-center gap-2">
                      <i class="pi pi-check-circle"></i>
                      Accepted
                    </span>
                  </div>
                  <p class="text-3xl font-bold text-green-900">{{ stats.accepted }}</p>
                </div>

                <div
                  class="p-4 rounded-lg shadow-md bg-gradient-to-br from-red-50 to-red-100 cursor-pointer hover:shadow-lg transition-shadow"
                  @click="activeFilter = 'rejected'"
                >
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-red-900 font-semibold flex items-center gap-2">
                      <i class="pi pi-times-circle"></i>
                      Rejected
                    </span>
                  </div>
                  <p class="text-3xl font-bold text-red-900">{{ stats.rejected }}</p>
                </div>
              </div>
            </template>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>
