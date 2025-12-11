<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/composables/axios'
import Dropdown from 'primevue/dropdown'
import AutoComplete from 'primevue/autocomplete'
const categoryOptions = ref([])

// --- Tag logic (now only tag names are stored in serviceForm.tags) ---
const tagOptions = ref([]) // [{label, value}]
const tagSuggestions = ref([]) // [string]
const tagInput = ref('')

const fetchCategoryOptions = async () => {
  try {
    const resp = await api.get('/categories')
    categoryOptions.value = resp.data.data || []
  } catch {}
}

const fetchTagOptions = async () => {
  try {
    const resp = await api.get('/seeker/tags')
    tagOptions.value = (resp.data.data || []).map((tag) => ({ label: tag.name, value: tag.name }))
    tagSuggestions.value = tagOptions.value.map((t) => t.label)
  } catch {}
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
  let tagStr = typeof tag === 'string' ? tag : (tag?.label || tag?.name || String(tag))
  const idx = serviceForm.value.tags.findIndex(
    (t) => {
      let tStr = typeof t === 'string' ? t : (t?.label || t?.name || String(t))
      return tStr.toLowerCase() === tagStr.toLowerCase()
    }
  )
  if (idx > -1) {
    serviceForm.value.tags.splice(idx, 1)
  }
}

// Helper to get tag label for display (now just the string)
function getTagLabel(tag) {
  return tag
}

onMounted(() => {
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
import Fieldset from 'primevue/fieldset'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
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
  } catch {}
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
      typeof t === 'object' && t.name ? t.name : t
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
  } catch {}
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
  <div class="min-h-screen bg-gray-50 flex flex-col items-center py-5">
    <div class="w-full max-w-6xl">
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
      <div v-else-if="provider && provider.name" class="w-full flex gap-6 p-5">
        <!-- Left column -->
        <div class="w-1/4 flex flex-col gap-8 px-4">
          <!-- Profile Pic -->
          <div class="flex items-center justify-center">
            <img
              :src="avatar"
              alt="Avatar"
              class="w-32 h-32 rounded-full object-cover border shadow"
            />
          </div>
          <!-- Bio and Links -->
          <div>
            <div class="font-bold text-lg mb-2">Bio</div>
            <div class="text-gray-700 text-sm mb-2">{{ bio || 'No bio provided.' }}</div>
            <div v-if="links.length" class="flex flex-col gap-2 mt-2">
              <div class="font-bold text-xs text-gray-600 mb-1">Links</div>
              <span v-for="link in links" :key="link.id" class="inline-flex items-center gap-2">
                <a :href="link.url" target="_blank" class="text-sm text-primary-600 underline">{{
                  link.title
                }}</a>
              </span>
            </div>
          </div>
          <!-- Skills -->
          <div>
            <div class="font-bold text-lg mb-2">Skills</div>
            <div v-if="skills.length" class="flex flex-wrap gap-2">
              <span
                v-for="skill in skills"
                :key="skill.id"
                class="bg-gray-100 text-xs text-gray-700 px-2 py-1 rounded"
                >{{ skill.name }}</span
              >
            </div>
            <div v-else class="text-xs text-gray-400">No skills listed.</div>
          </div>
        </div>
        <!-- Right column -->
        <div class="flex-1 flex flex-col gap-6 px-4">
          <!-- Name, Location, Bookmark -->
          <div class="flex justify-between items-center">
            <div>
              <div class="font-extrabold text-2xl">{{ name }}</div>
              <div class="text-sm text-gray-500">{{ location || 'No location provided' }}</div>
            </div>
            <button
              @click="toggleBookmark"
              class="bg-gray-100 border border-gray-300 rounded-full px-5 py-2 font-bold text-gray-700 hover:bg-primary-100 transition flex items-center"
            >
              <span v-if="bookmarked" class="pi pi-bookmark text-[#6d0019] mr-2"></span>
              <span v-else class="pi pi-bookmark mr-2"></span>
              <span>{{ bookmarked ? 'Bookmarked' : 'Bookmark' }}</span>
            </button>
          </div>
          <!-- Action Buttons -->
          <div class="flex gap-4">
            <button
              class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded transition"
            >
              Send Message
            </button>
            <button
              class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded border transition"
            >
              Report User
            </button>
          </div>
          <!-- Services Offered -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <div class="font-bold text-lg">Services Offered</div>
              <Button
                label="Add Service"
                icon="pi pi-plus"
                class="px-3 py-1 bg-primary-600 text-white rounded"
                @click="openCreateService"
              />
            </div>
            <div v-if="services.length">
              <div class="flex flex-col gap-4">
                <div
                  v-for="(service, i) in services"
                  :key="service.id || i"
                  class="bg-white rounded-lg shadow p-4 flex items-center justify-between"
                >
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-4 mb-2">
                      <div class="font-bold text-lg truncate">{{ service.title }}</div>
                      <div
                        v-if="service.category"
                        class="text-xs text-gray-600 bg-gray-100 px-2 py-1 rounded"
                      >
                        {{ service.category.name }}
                      </div>
                      <div v-if="service.price" class="text-xs text-gray-600 ml-2">
                        ₱{{ service.price }}
                      </div>
                    </div>
                    <div class="text-sm text-gray-700 mb-2 truncate">{{ service.description }}</div>
                    <div
                      v-if="service.tags && service.tags.length"
                      class="flex flex-wrap gap-1 mb-2"
                    >
                      <span
                        v-for="tag in service.tags"
                        :key="tag.id"
                        class="bg-gray-100 text-xs px-2 py-1 rounded"
                        >{{ tag.name }}</span
                      >
                    </div>
                  </div>
                  <div class="flex flex-col gap-1 items-end ml-4">
                    <Button
                      label=""
                      icon="pi pi-pencil"
                      class="p-1 text-xs bg-primary-100 text-primary-600 rounded"
                      style="min-width: 28px; min-height: 28px"
                      @click="editService(service)"
                    />
                    <Button
                      label=""
                      icon="pi pi-trash"
                      class="p-1 text-xs bg-red-100 text-red-700 rounded"
                      style="min-width: 28px; min-height: 28px"
                      @click="deleteService(service.id)"
                    />
                    <Button
                      label=""
                      icon="pi pi-share-alt"
                      class="p-1 text-xs bg-gray-100 text-gray-600 rounded"
                      style="min-width: 28px; min-height: 28px"
                      @click="shareService(service)"
                    />
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-xs text-gray-400">No services listed.</div>
          </div>

          <!-- Service Modal -->
          <Dialog v-model:visible="showServiceModal" :modal="true" :style="{ width: '400px' }">
            <template #header>
              <span class="font-bold text-lg">{{
                isEditingService ? 'Edit Service' : 'Add Service'
              }}</span>
            </template>
            <form @submit.prevent="isEditingService ? updateService() : createService()">
              <div class="mb-2">
                <label class="block text-sm font-semibold mb-1">Title</label>
                <input
                  v-model="serviceForm.title"
                  type="text"
                  class="w-full border px-2 py-1 rounded"
                  required
                />
              </div>
              <div class="mb-2">
                <label class="block text-sm font-semibold mb-1">Description</label>
                <textarea
                  v-model="serviceForm.description"
                  class="w-full border px-2 py-1 rounded"
                  required
                ></textarea>
              </div>
              <div class="mb-2">
                <label class="block text-sm font-semibold mb-1">Price</label>
                <input
                  v-model="serviceForm.price"
                  type="text"
                  inputmode="decimal"
                  pattern="^[0-9]*\.?[0-9]+$"
                  class="w-full border px-2 py-1 rounded"
                  min="0"
                  step="any"
                  placeholder="e.g. 149.99"
                />
              </div>
              <div class="mb-2">
                <label class="block text-sm font-semibold mb-1">Category</label>
                <Select
                  v-model="serviceForm.category_id"
                  :options="categoryOptions"
                  optionLabel="name"
                  optionValue="id"
                  placeholder="Select Category"
                  class="w-full"
                  required
                />
              </div>
              <div class="mb-2">
                <label class="block text-sm font-semibold mb-1">Tags</label>
                <div v-if="serviceForm.tags && serviceForm.tags.length" class="flex flex-wrap gap-2 mb-2">
                  <span
                    v-for="(tag, idx) in serviceForm.tags"
                    :key="idx"
                    class="bg-gray-100 text-xs px-2 py-1 rounded flex items-center gap-1"
                  >
                    {{ getTagLabel(tag) }}
                    <button
                      type="button"
                      class="ml-1 text-red-500 hover:text-red-700"
                      @click="removeTag(tag)"
                    >
                      <i class="pi pi-times"></i>
                    </button>
                  </span>
                </div>
                <AutoComplete
                  v-model="tagInput"
                  :suggestions="tagSuggestions"
                  @complete="filterTags"
                  @itemSelect="addTag"
                  @keydown="handleTagKeydown"
                  :disabled="serviceForm.tags.length >= 5"
                  class="w-full"
                  :forceSelection="false"
                  dropdown
                  showClear
                  aria-label="Tags"
                  placeholder="Type to search tags or press Enter to add custom"
                >
                  <template #option="slotProps">
                    <span>{{ slotProps.option }}</span>
                  </template>
                </AutoComplete>
                <p class="text-xs text-gray-500 mt-2">
                  {{ serviceForm.tags.length }}/5 tags selected. Type and press Enter for custom tags.
                </p>
              </div>
              <div v-if="serviceError" class="text-xs text-red-600 mb-2">
                <span v-if="typeof serviceError === 'string'">{{ serviceError }}</span>
                <ul v-else-if="typeof serviceError === 'object' && serviceError">
                  <li v-for="(errs, field) in serviceError" :key="field">
                    <span v-for="err in errs" :key="err">{{ err }}</span>
                  </li>
                </ul>
              </div>
              <div class="flex gap-2 mt-4">
                <Button
                  type="submit"
                  :label="isEditingService ? 'Update' : 'Create'"
                  class="px-4 py-2 bg-primary-600 text-white rounded"
                />
                <Button
                  type="button"
                  label="Cancel"
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded"
                  @click="showServiceModal = false"
                />
              </div>
            </form>
          </Dialog>
          <!-- Delete Confirmation Dialog -->
          <Dialog v-model:visible="showDeleteDialog" :modal="true" :style="{ width: '350px' }">
            <template #header>
              <span class="font-bold text-lg">Confirm Delete</span>
            </template>
            <div class="mb-4">Are you sure you want to delete this service?</div>
            <div class="flex gap-2 justify-end">
              <Button label="Delete" class="bg-red-600 text-white" @click="confirmDeleteService" />
              <Button
                label="Cancel"
                class="bg-gray-200 text-gray-700"
                @click="cancelDeleteService"
              />
            </div>
          </Dialog>
        </div>
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
