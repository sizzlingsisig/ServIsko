<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/composables/axios'

import Card from 'primevue/card'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import AutoComplete from 'primevue/autocomplete'
import { useAuthStore } from '@/stores/AuthStore'
const categoryOptions = ref([])

// --- Tag logic (now only tag names are stored in serviceForm.tags) ---
const tagOptions = ref([]) // [{label, value}]
const tagSuggestions = ref([]) // [string]
const tagInput = ref('')

const user = ref(null)
const isOwner = ref(false)

const showMessageModal = ref(false)
const messageText = ref('')
const messageSending = ref(false)
const messageError = ref('')

const sendMessageToProvider = async () => {
  if (!messageText.value.trim()) return

  messageSending.value = true
  messageError.value = ''

  try {
    // First, get or create conversation with this provider
    const convResp = await api.get(`/conversations/${providerId.value}`)
    const conversation = convResp.data

    // Then send the message
    await api.post(`/conversations/${conversation.id}/messages`, {
      message_text: messageText.value
    })

    // Success - close modal and reset
    showMessageModal.value = false
    messageText.value = ''
    alert('Message sent successfully!')
  } catch (err) {
    messageError.value = err.response?.data?.message || 'Failed to send message'
  } finally {
    messageSending.value = false
  }
}

const openMessageModal = () => {
  messageText.value = ''
  messageError.value = ''
  showMessageModal.value = true
}

const fetchUserInfo = async ()=> {
  try {
    const resp = await api.get('/user/')
    const data = resp.data
    if (data.success && data.data) {
      user.value = data.data
      console.log('user info:', user.value.id)
      console.log('provider id:', providerId.value)
      if(user.value.id === parseInt(providerId.value)) {
        isOwner.value = true
      }
    }
  } catch {
    // Silently fail if user can't be loaded
  }
}



const fetchCategoryOptions = async () => {
  try {
    const resp = await api.get('/categories')
    categoryOptions.value = resp.data.data || []
  } catch {
    // Silently fail if categories can't be loaded
  }
}

const fetchTagOptions = async () => {
  try {
    const resp = await api.get('/seeker/tags')
    tagOptions.value = (resp.data.data || []).map((tag) => ({ label: tag.name, value: tag.name }))
    tagSuggestions.value = tagOptions.value.map((t) => t.label)
  } catch {
    // Silently fail if tags can't be loaded
  }
}

function filterTags(event) {
  const query = event.query?.toLowerCase() || ''
  if (!query) {
    tagSuggestions.value = tagOptions.value.map((t) => t.label)
  } else {
    tagSuggestions.value = tagOptions.value
      .map((t) => t.label)
      .filter((label) => label.toLowerCase().includes(query))
  }
}

function addTag(event) {
  let tag = ''
  if (event && event.value) {
    tag = typeof event.value === 'string' ? event.value : event.value?.label || event.value
  } else if (tagInput.value) {
    tag = tagInput.value
  }
  const trimmedTag = tag.trim()
  if (!trimmedTag) {
    tagInput.value = ''
    return
  }
  if (serviceForm.value.tags.length >= 5) {
    tagInput.value = ''
    return
  }
  // If tag is already present (by name), skip
  const already = serviceForm.value.tags.some((t) => t.toLowerCase() === trimmedTag.toLowerCase())
  if (already) {
    tagInput.value = ''
    return
  }
  // Always store tag as string (name)
  serviceForm.value.tags.push(trimmedTag)
  tagInput.value = ''
}

function handleTagKeydown(event) {
  if (event.key === 'Enter') {
    event.preventDefault()
    addTag({ value: tagInput.value })
  }
}

function removeTag(tag) {
  // Support both string and object (defensive)
  let tagStr = typeof tag === 'string' ? tag : tag?.label || tag?.name || String(tag)
  const idx = serviceForm.value.tags.findIndex((t) => {
    let tStr = typeof t === 'string' ? t : t?.label || t?.name || String(t)
    return tStr.toLowerCase() === tagStr.toLowerCase()
  })
  if (idx > -1) {
    serviceForm.value.tags.splice(idx, 1)
  }
}

onMounted(() => {
  fetchUserInfo()
  fetchCategoryOptions()
  fetchTagOptions()
})
// Share service handler
const shareService = (service) => {
  const url = window.location.origin + '/service/' + service.id
  if (navigator.share) {
    navigator.share({
      title: service.title,
      text: service.description,
      url,
    })
  } else {
    window.prompt('Copy service link:', url)
  }
}

// Service CRUD state
const serviceForm = ref({
  id: null,
  title: '',
  description: '',
  price: '',
  category_id: '',
  tags: [],
})
const showServiceModal = ref(false)
const isEditingService = ref(false)
const serviceError = ref('')

// Fetch all services for provider (already in provider.value.services)
const fetchServices = async () => {
  if (!providerId.value) return
  try {
    const resp = await api.get(`/providers/${providerId.value}`)
    const data = resp.data
    if (data.success && data.data) {
      provider.value = data.data
    }
  } catch {
    // Silently fail if provider can't be loaded
  }
}

// Create service
const createService = async () => {
  serviceError.value = ''
  try {
    let payload = { ...serviceForm.value }
    if (payload.price !== undefined && payload.price !== null) {
      payload.price = parseFloat(payload.price)
    }
    if (payload.category_id) {
      payload.category_id =
        typeof payload.category_id === 'object' && payload.category_id.id
          ? payload.category_id.id
          : parseInt(payload.category_id)
    }
    payload.tags = serviceForm.value.tags
    const resp = await api.post(`/provider/services`, payload)
    const data = resp.data
    if (data.data) {
      showServiceModal.value = false
      fetchServices()
    } else {
      serviceError.value = data.errors || data.message || 'Error creating service.'
    }
  } catch (err) {
    if (err.response && err.response.data && err.response.data.errors) {
      serviceError.value = err.response.data.errors
    } else {
      serviceError.value = 'Error creating service.'
    }
  }
}

// Edit service
const editService = (service) => {
  // Convert tag objects to tag names for editing
  const tags = Array.isArray(service.tags)
    ? service.tags.map((t) => (typeof t === 'object' && t.name ? t.name : t))
    : []
  serviceForm.value = {
    id: service.id,
    title: service.title,
    description: service.description,
    price: service.price,
    category_id: service.category?.id || service.category_id || '',
    tags,
  }
  isEditingService.value = true
  showServiceModal.value = true
}

// Update service
const updateService = async () => {
  serviceError.value = ''
  try {
    let payload = { ...serviceForm.value }
    if (payload.price !== undefined && payload.price !== null) {
      payload.price = parseFloat(payload.price)
    }
    if (payload.category_id) {
      payload.category_id =
        typeof payload.category_id === 'object' && payload.category_id.id
          ? payload.category_id.id
          : parseInt(payload.category_id)
    }
    // Ensure tags are only names (strings)
    payload.tags = (serviceForm.value.tags || []).map((t) =>
      typeof t === 'object' && t.name ? t.name : t,
    )
    const resp = await api.put(`/provider/services/${payload.id}`, payload)
    const data = resp.data
    if (data.data) {
      showServiceModal.value = false
      fetchServices()
    } else {
      serviceError.value = data.errors || data.message || 'Error updating service.'
    }
  } catch (err) {
    if (err.response && err.response.data && err.response.data.errors) {
      serviceError.value = err.response.data.errors
    } else {
      serviceError.value = 'Error updating service.'
    }
  }
}

// Delete service
const deleteService = async (id) => {
  showDeleteDialog.value = true
  pendingDeleteId.value = id
}

const confirmDeleteService = async () => {
  if (!pendingDeleteId.value) return
  try {
    const resp = await api.delete(`/provider/services/${pendingDeleteId.value}`)
    const data = resp.data
    if (data.message) fetchServices()
  } catch {
    // Silently fail if delete fails
  }
  showDeleteDialog.value = false
  pendingDeleteId.value = null
}

const cancelDeleteService = () => {
  showDeleteDialog.value = false
  pendingDeleteId.value = null
}

const openCreateService = () => {
  serviceForm.value = { id: null, title: '', description: '', price: '', category_id: '', tags: [] }
  isEditingService.value = false
  showServiceModal.value = true
}

const showDeleteDialog = ref(false)
const pendingDeleteId = ref(null)

const provider = ref(null)
const loading = ref(true)
const error = ref('')

const route = useRoute()
const providerId = computed(() => route.params.id)

onMounted(() => {
  fetchServices()
  loading.value = false
})

watch(providerId, () => {
  fetchServices()
})

// --- ProviderDetailsCard logic ---
const providerProfile = computed(() => provider.value?.provider_profile ?? {})
const name = computed(() => provider.value?.name ?? '')
const profile = computed(() => provider.value?.profile ?? {})
const avatar = computed(() => {
  if (profile.value?.profile_picture) {
    // Use the image if present, add domain prefix if needed
    return profile.value.profile_picture.startsWith('/')
      ? `http://localhost:8000${profile.value.profile_picture}`
      : profile.value.profile_picture
  }
  // Otherwise, fallback to generated initials avatar
  const initials =
    name.value
      .split(' ')
      .map((n) => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2) || 'NA'
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&background=6d0019&color=fff`
})
const bio = computed(() => profile.value?.bio ?? '')
const location = computed(() => profile.value?.location ?? '')
const links = computed(() => providerProfile.value.links ?? [])
const skills = computed(() => providerProfile.value.skills ?? [])
const services = computed(() => provider.value?.services ?? [])
const bookmarked = ref(false)
const toggleBookmark = () => (bookmarked.value = !bookmarked.value)
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-b from-gray-50 to-white flex flex-col items-center py-8 px-4"
  >
    <div class="w-full max-w-7xl">
      <!-- Breadcrumbs -->
      <nav class="flex items-center text-sm text-gray-500 mb-6">
        <router-link to="/" class="hover:text-[#6d0019] transition">
          <i class="pi pi-home"></i>
        </router-link>
        <i class="pi pi-angle-right text-gray-400 text-xs mx-2"></i>
        <router-link to="/providers" class="hover:text-[#6d0019] transition">
          Providers
        </router-link>
        <i class="pi pi-angle-right text-gray-400 text-xs mx-2"></i>
        <span class="text-gray-400 truncate max-w-xs">{{ name }}</span>
      </nav>

      <!-- Loading skeleton -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-8">
        <div class="animate-pulse w-44 h-44 rounded-full bg-gray-200 mb-6"></div>
        <div class="h-6 w-64 bg-gray-200 mb-2 rounded animate-pulse"></div>
        <div class="h-4 w-48 bg-gray-200 mb-2 rounded animate-pulse"></div>
        <div class="h-4 w-60 bg-gray-200 mb-2 rounded animate-pulse"></div>
        <div class="h-4 w-40 bg-gray-200 rounded animate-pulse"></div>
        <span class="mt-6 text-sm text-gray-400">Loading provider details...</span>
      </div>
      <!-- Error -->
      <div v-else-if="error" class="text-center text-red-500 py-8">
        {{ error }}
      </div>
      <!-- Details Card -->
      <div v-else-if="provider && provider.name" class="w-full">
        <!-- Header Card with Avatar, Name, Actions -->
        <Card class="mb-6 shadow-md border-0">
          <template #content>
            <div class="flex items-start gap-6">
              <!-- Avatar -->
              <img
                :src="avatar"
                alt="Avatar"
                class="w-32 h-32 rounded-full object-cover border-4 border-[#6d0019] shadow-lg flex-shrink-0"
              />
              <!-- Name, Location, and Actions -->
              <div class="flex-1 flex flex-col justify-between">
                <div>
                  <h1 class="text-3xl font-extrabold text-[#0f1724]">
                    {{ name }}
                  </h1>
                  <p class="text-gray-600">
                    {{ location || 'No location provided' }}
                  </p>
                </div>
                <!-- Actions Buttons -->
                <div class="flex gap-3 mt-4">
                    <Button
                      label="Send Message"
                      class="bg-[#6d0019] text-white px-6 py-2 rounded-md hover:bg-[#5a0013] transition"
                      @click="openMessageModal"
                      v-if="!isOwner && useAuthStore.isAuthenticated && providerProfile && Object.keys(providerProfile).length > 0"
                    />
                  <Button
                    v-if="!isOwner && useAuthStore.isAuthenticated"
                    label="Report User"
                    style="background-color: #e5e7eb; color: #374151; border: 2px solid #d1d5db; padding: 0.5rem 1.5rem; border-radius: 0.375rem;"
                  />
                  <button
                  v-if="!isOwner && useAuthStore.isAuthenticated"
                    @click="toggleBookmark"
                    :class="[
                      'p-3 border-2 rounded-md transition flex items-center gap-2',
                      bookmarked ? 'text-yellow-500' : 'border-gray-300 hover:bg-gray-100',
                    ]"
                  >
                    <span
                      class="pi"
                      :class="bookmarked ? 'pi-bookmark-fill' : 'pi-bookmark'"
                    ></span>
                  </button>
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left column: Bio & Skills -->
          <div class="lg:col-span-1 flex flex-col gap-6">
            <!-- Bio Card -->
            <Card class="shadow-md border-0">
              <template #header>
                <div
                  class="px-6 py-3 bg-gradient-to-r from-[#6d0019] to-[#8B1538] text-white rounded-t-lg"
                >
                  <h3 class="font-bold text-lg">Bio</h3>
                </div>
              </template>
              <template #content>
                <p class="text-gray-700 text-sm leading-relaxed">{{ bio || 'No bio provided.' }}</p>
                <div v-if="links.length" class="mt-4 pt-4 border-t">
                  <h4 class="font-semibold text-sm text-gray-800 mb-3">Links</h4>
                  <div class="flex flex-col gap-2">
                    <a
                      v-for="link in links"
                      :key="link.id"
                      :href="link.url"
                      target="_blank"
                      class="text-[#6d0019] font-semibold hover:underline flex items-center gap-2 text-sm"
                    >
                      <i class="pi pi-external-link text-xs"></i>
                      {{ link.title }}
                    </a>
                  </div>
                </div>
              </template>
            </Card>

            <!-- Skills Card -->
            <Card class="shadow-md border-0">
              <template #header>
                <div
                  class="px-6 py-3 bg-gradient-to-r from-[#6d0019] to-[#8B1538] text-white rounded-t-lg"
                >
                  <h3 class="font-bold text-lg">Skills</h3>
                </div>
              </template>
              <template #content>
                <div v-if="skills.length" class="flex flex-wrap gap-2">
                  <span
                    v-for="skill in skills"
                    :key="skill.id"
                    class="bg-[#f0e5e8] text-[#6d0019] text-xs font-semibold px-3 py-1.5 rounded-full"
                    >{{ skill.name }}</span
                  >
                </div>
                <div v-else class="text-xs text-gray-500">No skills listed.</div>
              </template>
            </Card>
          </div>

          <!-- Right column: Services -->
          <div class="lg:col-span-2">
            <Card class="shadow-md border-0">
              <template #header>
                <div
                  class="px-6 py-3 bg-gradient-to-r from-[#6d0019] to-[#8B1538] text-white rounded-t-lg flex items-center justify-between"
                >
                  <h3 class="font-bold text-lg">Services Offered</h3>
                  <Button
                    v-if="isOwner"
                    label="Add Service"
                    icon="pi pi-plus"
                    size="small"
                    class="bg-white text-[#6d0019]"
                    @click="openCreateService"
                  />
                </div>
              </template>
              <template #content>
                <div v-if="services.length" class="space-y-4">
                  <div
                    v-for="(service, i) in services"
                    :key="service.id || i"
                    class="bg-gray-50 rounded-lg border border-gray-200 p-4 hover:shadow-md transition"
                  >
                    <div class="flex items-start justify-between gap-4">
                      <div class="flex-1">
                        <h4 class="font-bold text-lg text-[#0f1724] mb-2">{{ service.title }}</h4>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                          {{ service.description }}
                        </p>
                        <div class="flex gap-2 mb-3 flex-wrap items-center">
                          <span
                            v-if="service.category"
                            class="text-xs font-semibold bg-[#f0e5e8] text-[#6d0019] px-2 py-1 rounded-full"
                          >
                            {{ service.category.name }}
                          </span>
                          <span
                            v-for="(tag, idx) in service.tags"
                            :key="idx"
                            class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded"
                            >{{ tag.name }}</span
                          >
                        </div>
                      </div>
                      <div
                        v-if="service.price"
                        class="font-bold text-lg text-[#6d0019] whitespace-nowrap"
                      >
                        ₱{{ parseFloat(service.price).toLocaleString() }}/hr
                      </div>
                    </div>
                    <div class="flex gap-2 mt-4 justify-end">
                      <Button
                        v-if="isOwner"
                        icon="pi pi-pencil"
                        class="p-button-rounded p-button-sm bg-[#6d0019] text-white"
                        @click="editService(service)"
                      />
                      <Button
                        v-if="isOwner"
                        icon="pi pi-trash"
                        class="p-button-rounded p-button-sm bg-red-500 text-white"
                        @click="deleteService(service.id)"
                      />
                      <Button
                        icon="pi pi-share-alt"
                        class="p-button-rounded p-button-sm bg-gray-600 text-white"
                        @click="shareService(service)"
                      />
                    </div>
                  </div>
                </div>
                <div v-else class="text-center text-gray-500 py-8">
                  <p class="text-sm">No services listed yet.</p>
                </div>
              </template>
            </Card>
          </div>
        </div>

        <!-- Service Modal -->
        <Dialog
          v-if="isOwner"
          v-model:visible="showServiceModal"
          :modal="true"
          :header="isEditingService ? 'Edit Service' : 'Add Service'"
          class="w-full md:w-1/2"
        >
          <form
            @submit.prevent="isEditingService ? updateService() : createService()"
            class="space-y-4"
          >
            <div>
              <label class="block text-sm font-semibold mb-2">Title *</label>
              <InputText v-model="serviceForm.title" class="w-full" required />
            </div>
            <div>
              <label class="block text-sm font-semibold mb-2">Description *</label>
              <Textarea v-model="serviceForm.description" class="w-full" rows="4" required />
            </div>
            <div>
              <label class="block text-sm font-semibold mb-2">Price (₱/hour)</label>
              <InputText
                v-model="serviceForm.price"
                type="number"
                step="0.01"
                min="0"
                class="w-full"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold mb-2">Category *</label>
              <Dropdown
                v-model="serviceForm.category_id"
                :options="categoryOptions"
                option-label="name"
                option-value="id"
                placeholder="Select a category"
                class="w-full"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-semibold mb-2">Tags (up to 5)</label>
              <div class="flex gap-2 mb-2 flex-wrap">
                <span
                  v-for="(tag, idx) in serviceForm.tags"
                  :key="idx"
                  class="bg-[#f0e5e8] text-[#6d0019] text-xs font-semibold px-3 py-1.5 rounded-full flex items-center gap-2"
                >
                  {{ tag }}
                  <button
                    type="button"
                    @click="removeTag(tag)"
                    class="hover:text-red-600 font-bold"
                  >
                    ×
                  </button>
                </span>
              </div>
              <div class="flex gap-2">
                <AutoComplete
                  v-model="tagInput"
                  :suggestions="tagSuggestions"
                  @complete="filterTags"
                  @itemSelect="addTag"
                  @keydown="handleTagKeydown"
                  placeholder="Add tags..."
                  class="flex-1"
                />

              </div>
            </div>
            <div
              v-if="serviceError"
              class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-sm"
            >
              {{ serviceError }}
            </div>
          </form>
          <template #footer>
            <Button label="Cancel" @click="showServiceModal = false" class="p-button-secondary" />
            <Button
              :label="isEditingService ? 'Update' : 'Create'"
              type="submit"
              @click="isEditingService ? updateService() : createService()"
              class="bg-[#6d0019] text-white"
            />
          </template>
        </Dialog>

        <!-- Send Message Modal -->
<Dialog
  v-model:visible="showMessageModal"
  :modal="true"
  header="Send Message"
  class="w-full md:w-1/2"
>
  <div class="space-y-4">
    <p class="text-sm text-gray-600">Send a message to {{ name }}</p>
    <div>
      <label class="block text-sm font-semibold mb-2">Message *</label>
      <Textarea
        v-model="messageText"
        class="w-full"
        rows="6"
        placeholder="Type your message here..."
        :disabled="messageSending"
        required
      />
    </div>
    <div
      v-if="messageError"
      class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded text-sm"
    >
      {{ messageError }}
    </div>
  </div>
  <template #footer>
    <Button
      label="Cancel"
      @click="showMessageModal = false"
      class="p-button-secondary"
      :disabled="messageSending"
    />
    <Button
      label="Send"
      @click="sendMessageToProvider"
      class="bg-[#6d0019] text-white"
      :disabled="!messageText.trim() || messageSending"
      :loading="messageSending"
    />
  </template>
</Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog
          v-if="isOwner"
          v-model:visible="showDeleteDialog"
          :modal="true"
          header="Confirm Delete"
          class="w-full md:w-1/3"
        >
          <p class="text-gray-700 mb-4">Are you sure you want to delete this service?</p>
          <template #footer>
            <Button label="Cancel" @click="cancelDeleteService" class="p-button-secondary" />
            <Button
              label="Delete"
              @click="confirmDeleteService"
              class=" bg-primary-600 text-white"
            />
          </template>
        </Dialog>
      </div>
      <!-- Fallback -->
      <div v-else class="max-w-2xl mx-auto py-20 text-center text-gray-400">Loading profile...</div>
    </div>
  </div>
</template>

<style scoped>
.bg-primary-100 {
  background-color: #f3e6eb;
}
.text-primary-600 {
  color: #6d0019;
}
.bg-primary-600 {
  background-color: #6d0019;
}
.hover\:bg-primary-700:hover {
  background-color: #490010 !important;
}
</style>
