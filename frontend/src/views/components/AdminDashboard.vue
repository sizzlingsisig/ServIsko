<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/AuthStore'
import axios from '@/composables/axios'

const authStore = useAuthStore()

// Tabs
const activeTab = ref(0)

// Users state
const users = ref([])
const usersLoading = ref(false)
const usersPagination = ref({
  total: 0,
  perPage: 10,
  currentPage: 1,
  lastPage: 1,
})

// Skill Requests state
const skillRequests = ref([])
const requestsLoading = ref(false)
const requestsPagination = ref({
  total: 0,
  perPage: 10,
  currentPage: 1,
  lastPage: 1,
})

const requestsFilter = ref('pending')

// Fetch users
const fetchUsers = async (page = 1) => {
  try {
    usersLoading.value = true
    const res = await axios.get('/admin/users', {
      params: { page, per_page: usersPagination.value.perPage },
    })
    users.value = res.data.data
    usersPagination.value = {
      total: res.data.pagination.total,
      perPage: res.data.pagination.per_page,
      currentPage: res.data.pagination.current_page,
      lastPage: res.data.pagination.last_page,
    }
  } catch (err) {
    console.error('Error fetching users:', err)
    alert(err.response?.data?.message || 'Failed to fetch users')
  } finally {
    usersLoading.value = false
  }
}

// Fetch skill requests
const fetchSkillRequests = async (page = 1) => {
  try {
    requestsLoading.value = true
    const res = await axios.get('/admin/skill-requests', {
      params: {
        page,
        per_page: requestsPagination.value.perPage,
        status: requestsFilter.value || undefined,
      },
    })
    skillRequests.value = res.data.data
    requestsPagination.value = {
      total: res.data.pagination.total,
      perPage: res.data.pagination.per_page,
      currentPage: res.data.pagination.current_page,
      lastPage: res.data.pagination.last_page,
    }
  } catch (err) {
    console.error('Error fetching skill requests:', err)
    alert(err.response?.data?.message || 'Failed to fetch skill requests')
  } finally {
    requestsLoading.value = false
  }
}

// Approve skill request
const approveRequest = async (requestId) => {
  try {
    requestsLoading.value = true
    await axios.post(`/admin/skill-requests/${requestId}/approve`)
    alert('Skill request approved')
    await fetchSkillRequests()
  } catch (err) {
    console.error('Error approving request:', err)
    alert(err.response?.data?.message || 'Failed to approve request')
  } finally {
    requestsLoading.value = false
  }
}

// Reject skill request
const rejectRequest = async (requestId) => {
  try {
    requestsLoading.value = true
    await axios.post(`/admin/skill-requests/${requestId}/reject`)
    alert('Skill request rejected')
    await fetchSkillRequests()
  } catch (err) {
    console.error('Error rejecting request:', err)
    alert(err.response?.data?.message || 'Failed to reject request')
  } finally {
    requestsLoading.value = false
  }
}

// Status badge template
const statusBadgeTemplate = (status) => {
  const severities = {
    pending: 'warning',
    approved: 'success',
    rejected: 'danger',
  }
  return severities[status] || 'info'
}

onMounted(() => {
  if (!authStore.token) {
    alert('No authentication token found')
    return
  }
  fetchUsers()
  fetchSkillRequests()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <Card class="mb-8">
        <template #content>
          <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="mt-2 text-gray-600">Manage users and skill requests</p>
          </div>
        </template>
      </Card>

      <!-- Tabs -->
      <TabView v-model:activeIndex="activeTab">
        <!-- Users Tab -->
        <TabPanel header="Users" headerIcon="pi pi-user">
          <Card>
            <template #content>
              <DataTable
                :value="users"
                :loading="usersLoading"
                responsiveLayout="scroll"
                striped-rows
              >
                <Column field="id" header="ID" style="width: 10%"></Column>
                <Column field="name" header="Name" style="width: 20%"></Column>
                <Column field="email" header="Email" style="width: 25%"></Column>
                <Column header="Roles" style="width: 25%">
                  <template #body="slotProps">
                    <div class="flex gap-1">
                      <Tag
                        v-for="role in slotProps.data.roles"
                        :key="role.id"
                        :value="role.name"
                        :severity="
                          role.name === 'admin'
                            ? 'danger'
                            : role.name === 'service-provider'
                              ? 'info'
                              : 'success'
                        "
                      />
                    </div>
                  </template>
                </Column>
                <Column header="Created" style="width: 20%">
                  <template #body="slotProps">
                    <span>{{ new Date(slotProps.data.created_at).toLocaleDateString() }}</span>
                  </template>
                </Column>

                <template #empty>
                  <div class="text-center py-8">
                    <i class="pi pi-inbox text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">No users found</p>
                  </div>
                </template>

                <template #loading>
                  <div class="text-center py-8">
                    <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                  </div>
                </template>
              </DataTable>

              <!-- Pagination -->
              <div class="mt-4 flex justify-center">
                <Paginator
                  :rows="usersPagination.perPage"
                  :totalRecords="usersPagination.total"
                  :rowsPerPageOptions="[10, 20, 50]"
                  @page="(e) => fetchUsers(e.page + 1)"
                />
              </div>
            </template>
          </Card>
        </TabPanel>

        <!-- Skill Requests Tab -->
        <TabPanel header="Skill Requests" headerIcon="pi pi-list">
          <Card>
            <template #content>
              <!-- Filter -->
              <div class="mb-4">
                <label class="block font-semibold text-gray-700 mb-2">Filter by Status</label>
                <Dropdown
                  v-model="requestsFilter"
                  :options="[
                    { label: 'All', value: '' },
                    { label: 'Pending', value: 'pending' },
                    { label: 'Approved', value: 'approved' },
                    { label: 'Rejected', value: 'rejected' },
                  ]"
                  option-label="label"
                  option-value="value"
                  placeholder="Filter by status"
                  @change="fetchSkillRequests(1)"
                  class="w-full md:w-56"
                />
              </div>

              <DataTable
                :value="skillRequests"
                :loading="requestsLoading"
                responsiveLayout="scroll"
                striped-rows
              >
                <Column field="id" header="ID" style="width: 10%"></Column>
                <Column field="skill_name" header="Skill Name" style="width: 20%"></Column>
                <Column field="user.name" header="Requested By" style="width: 20%"></Column>
                <Column field="status" header="Status" style="width: 15%">
                  <template #body="slotProps">
                    <Tag
                      :value="slotProps.data.status"
                      :severity="statusBadgeTemplate(slotProps.data.status)"
                    />
                  </template>
                </Column>
                <Column header="Actions" style="width: 35%">
                  <template #body="slotProps">
                    <div class="flex gap-2">
                      <Button
                        v-if="slotProps.data.status === 'pending'"
                        label="Approve"
                        icon="pi pi-check"
                        size="small"
                        severity="success"
                        @click="approveRequest(slotProps.data.id)"
                        :loading="requestsLoading"
                      />
                      <Button
                        v-if="slotProps.data.status === 'pending'"
                        label="Reject"
                        icon="pi pi-times"
                        size="small"
                        severity="danger"
                        @click="rejectRequest(slotProps.data.id)"
                        :loading="requestsLoading"
                      />
                      <span v-else class="text-sm text-gray-500">
                        {{ slotProps.data.status === 'approved' ? 'Approved' : 'Rejected' }}
                      </span>
                    </div>
                  </template>
                </Column>

                <template #empty>
                  <div class="text-center py-8">
                    <i class="pi pi-inbox text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-600">No skill requests found</p>
                  </div>
                </template>

                <template #loading>
                  <div class="text-center py-8">
                    <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
                  </div>
                </template>
              </DataTable>

              <!-- Pagination -->
              <div class="mt-4 flex justify-center">
                <Paginator
                  :rows="requestsPagination.perPage"
                  :totalRecords="requestsPagination.total"
                  :rowsPerPageOptions="[10, 20, 50]"
                  @page="(e) => fetchSkillRequests(e.page + 1)"
                />
              </div>
            </template>
          </Card>
        </TabPanel>
      </TabView>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-card-content) {
  padding: 1.5rem;
}

:deep(.p-tabview .p-tabview-panels) {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>
