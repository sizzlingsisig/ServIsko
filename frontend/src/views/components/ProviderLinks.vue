<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/AuthStore'
import axios from '@/composables/axios'

const authStore = useAuthStore()

// State
const links = ref([])
const loading = ref(false)
const showAddLinkDialog = ref(false)
const showEditLinkDialog = ref(false)
const editingLink = ref(null)

// Form data
const linkForm = ref({
  title: '',
  url: '',
  type: '',
})

// Link type options
const linkTypes = ref([
  { label: 'Portfolio', value: 'portfolio' },
  { label: 'GitHub', value: 'github' },
  { label: 'LinkedIn', value: 'linkedin' },
  { label: 'Twitter', value: 'twitter' },
  { label: 'Personal Website', value: 'website' },
  { label: 'Other', value: 'other' },
])

// Fetch Links
const fetchLinks = async () => {
  try {
    loading.value = true
    const res = await axios.get('/provider/profile/links')
    links.value = res.data.data
  } catch (err) {
    console.error('Error fetching links:', err)
    alert(err.response?.data?.message || 'Failed to fetch links')
  } finally {
    loading.value = false
  }
}

// Open add link dialog
const openAddLinkDialog = () => {
  editingLink.value = null
  linkForm.value = {
    title: '',
    url: '',
    type: '',
  }
  showAddLinkDialog.value = true
}

// Open edit link dialog
const openEditLinkDialog = (link) => {
  editingLink.value = link
  linkForm.value = {
    title: link.title,
    url: link.url,
    type: link.type,
  }
  showEditLinkDialog.value = true
}

// Save link
const saveLink = async () => {
  try {
    if (!linkForm.value.title || !linkForm.value.url || !linkForm.value.type) {
      alert('All fields are required')
      return
    }

    loading.value = true

    if (editingLink.value) {
      // Update
      await axios.put(`/provider/profile/links/${editingLink.value.id}`, linkForm.value)
      alert('Link updated successfully')
    } else {
      // Create
      await axios.post('/provider/profile/links', linkForm.value)
      alert('Link added successfully')
    }

    showAddLinkDialog.value = false
    showEditLinkDialog.value = false
    await fetchLinks()
  } catch (err) {
    console.error('Error saving link:', err)
    alert(err.response?.data?.message || 'Failed to save link')
  } finally {
    loading.value = false
  }
}

// Delete link
const deleteLink = async (linkId) => {
  if (!confirm('Are you sure you want to delete this link?')) return

  try {
    loading.value = true
    await axios.delete(`/provider/profile/links/${linkId}`)
    alert('Link deleted successfully')
    await fetchLinks()
  } catch (err) {
    console.error('Error deleting link:', err)
    alert(err.response?.data?.message || 'Failed to delete link')
  } finally {
    loading.value = false
  }
}

// Get icon for link type
const getLinkIcon = (type) => {
  const icons = {
    portfolio: 'pi pi-briefcase',
    github: 'pi pi-github',
    linkedin: 'pi pi-linkedin',
    twitter: 'pi pi-twitter',
    website: 'pi pi-globe',
    other: 'pi pi-link',
  }
  return icons[type] || 'pi pi-link'
}

onMounted(() => {
  if (!authStore.token) {
    alert('No authentication token found')
    return
  }
  fetchLinks()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <Card class="mb-8">
        <template #content>
          <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900">Portfolio Links</h1>
            <p class="mt-2 text-gray-600">Manage your personal and professional links</p>
          </div>
        </template>
      </Card>

      <!-- Links Section -->
      <Card>
        <template #title>
          <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-2">
              <i class="pi pi-link text-blue-500"></i>
              Your Links
            </div>
            <Button
              label="Add Link"
              icon="pi pi-plus"
              size="small"
              @click="openAddLinkDialog"
              :loading="loading"
            />
          </div>
        </template>
        <template #content>
          <div v-if="links.length > 0" class="space-y-2">
            <div
              v-for="link in links"
              :key="link.id"
              class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition"
            >
              <div class="flex items-center gap-3 flex-1">
                <i :class="[getLinkIcon(link.type), 'text-blue-500 text-xl']"></i>
                <div>
                  <p class="font-semibold text-gray-900">{{ link.title }}</p>
                  <a
                    :href="link.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm text-blue-600 hover:text-blue-800 truncate"
                  >
                    {{ link.url }}
                  </a>
                </div>
              </div>
              <div class="flex gap-2">
                <Button
                  icon="pi pi-pencil"
                  rounded
                  text
                  severity="info"
                  size="small"
                  @click="openEditLinkDialog(link)"
                  :loading="loading"
                />
                <Button
                  icon="pi pi-trash"
                  rounded
                  text
                  severity="danger"
                  size="small"
                  @click="deleteLink(link.id)"
                  :loading="loading"
                />
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8">
            <i class="pi pi-inbox text-4xl text-gray-400 mb-2"></i>
            <p class="text-gray-600">No links added yet</p>
            <p class="text-sm text-gray-500 mt-1">Add your first link to showcase your work</p>
          </div>
        </template>
      </Card>

      <!-- Refresh Button -->
      <div class="flex justify-center pt-4">
        <Button
          label="Refresh Links"
          icon="pi pi-refresh"
          @click="fetchLinks"
          :loading="loading"
          outlined
        />
      </div>
    </div>

    <!-- Add Link Dialog -->
    <Dialog
      v-model:visible="showAddLinkDialog"
      header="Add Link"
      :modal="true"
      class="w-full md:w-96"
      :closable="!loading"
    >
      <div class="space-y-4">
        <div>
          <label class="block font-semibold text-gray-700 mb-2">Title *</label>
          <InputText v-model="linkForm.title" placeholder="e.g., My Portfolio" class="w-full" />
        </div>

        <div>
          <label class="block font-semibold text-gray-700 mb-2">URL *</label>
          <InputText
            v-model="linkForm.url"
            type="url"
            placeholder="e.g., https://example.com"
            class="w-full"
          />
        </div>

        <div>
          <label class="block font-semibold text-gray-700 mb-2">Type *</label>
          <Dropdown
            v-model="linkForm.type"
            :options="linkTypes"
            option-label="label"
            option-value="value"
            placeholder="Select type"
            class="w-full"
          />
        </div>
      </div>

      <template #footer>
        <Button
          label="Cancel"
          icon="pi pi-times"
          @click="showAddLinkDialog = false"
          :disabled="loading"
          text
        />
        <Button
          label="Add Link"
          icon="pi pi-check"
          @click="saveLink"
          :loading="loading"
          autofocus
        />
      </template>
    </Dialog>

    <!-- Edit Link Dialog -->
    <Dialog
      v-model:visible="showEditLinkDialog"
      header="Edit Link"
      :modal="true"
      class="w-full md:w-96"
      :closable="!loading"
    >
      <div class="space-y-4">
        <div>
          <label class="block font-semibold text-gray-700 mb-2">Title *</label>
          <InputText v-model="linkForm.title" placeholder="e.g., My Portfolio" class="w-full" />
        </div>

        <div>
          <label class="block font-semibold text-gray-700 mb-2">URL *</label>
          <InputText
            v-model="linkForm.url"
            type="url"
            placeholder="e.g., https://example.com"
            class="w-full"
          />
        </div>

        <div>
          <label class="block font-semibold text-gray-700 mb-2">Type *</label>
          <Dropdown
            v-model="linkForm.type"
            :options="linkTypes"
            option-label="label"
            option-value="value"
            placeholder="Select type"
            class="w-full"
          />
        </div>
      </div>

      <template #footer>
        <Button
          label="Cancel"
          icon="pi pi-times"
          @click="showEditLinkDialog = false"
          :disabled="loading"
          text
        />
        <Button
          label="Save Changes"
          icon="pi pi-check"
          @click="saveLink"
          :loading="loading"
          autofocus
        />
      </template>
    </Dialog>
  </div>
</template>

<style scoped>
:deep(.p-card-content) {
  padding: 1.5rem;
}
</style>
