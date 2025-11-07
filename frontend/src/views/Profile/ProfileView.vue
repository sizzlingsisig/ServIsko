<script setup>
import { ref, onMounted, reactive } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import AutoComplete from 'primevue/autocomplete'
import axios from '@/composables/axios'
import ProfileCardHeader from '@/views/Profile/Components/ProfileCardHeader.vue'

// State
const flags = reactive({
  isProvider: false,
  isSeeker: true,
  isEditingDetails: false,
  showCreateSkillDialog: false,
  showSkillNotFoundDialog: false,
  showDeleteConfirmDialog: false,
  showDeleteLinkConfirmDialog: false,
  showCreateLinkDialog: false,
  showEditLinkDialog: false,
  showPreviewModal: false,
  showPictureModal: false,
  loading: false,
})

const profile = reactive({
  name: '',
  roles: [],
  username: '',
  email: '',
  bio: '',
  location: '',
  pictureUrl: null,
})

const provider = reactive({
  skills: [],
  links: [],
})

const forms = reactive({
  details: { bio: '', location: '' },
  skill: { name: '', category: '', description: '' },
  skillRequest: { name: '', description: '' },
  link: { title: '', url: '' },
  errors: { bio: '', location: '', link: {} },
  preview: { tempUrl: null, file: null },
})

const indexes = reactive({ skillToDelete: null, linkToDelete: null })
const allSkills = ref([])
const filteredSkills = ref([])
const editingLinkId = ref(null)

// Constants
const CATEGORIES = [
  { label: 'Programming', value: 'programming' },
  { label: 'Design', value: 'design' },
  { label: 'Writing', value: 'writing' },
  { label: 'Marketing', value: 'marketing' },
  { label: 'Business', value: 'business' },
  { label: 'Other', value: 'other' },
]

// API Calls
const loadAvailableSkills = async () => {
  try {
    const { data } = await axios.get('/provider/skills', {
      params: {
        per_page: 100,
      },
    })
    allSkills.value = data.data
  } catch (error) {
    console.error('Failed to load skills:', error)
  }
}

const loadProfile = async () => {
  try {
    flags.loading = true
    const { data } = await axios.get('/user')
    const user = data.data

    Object.assign(profile, {
      name: user.name,
      username: user.username || '',
      email: user.email,
      roles: user.roles || [],
    })

    flags.isProvider = user.roles?.includes('service-provider') || false

    if (flags.isProvider && user.provider_profile) {
      const { profile: userProfile, provider_profile: provProfile } = user
      Object.assign(profile, {
        bio: userProfile?.bio || '',
        location: userProfile?.location || '',
      })
      forms.details = { bio: profile.bio, location: profile.location }

      provider.skills = (provProfile.skills || []).map((s) => ({
        id: s.id,
        name: s.name,
        description: s.description,
        category: s.category || 'other',
      }))
      provider.links = (provProfile.links || []).map((l) => ({
        id: l.id,
        title: l.title,
        url: l.url,
      }))
    }

    await loadProfilePicture()
  } catch (error) {
    console.error('❌ Failed to load profile:', error)
  } finally {
    flags.loading = false
  }
}

const loadProfilePicture = async () => {
  try {
    const response = await axios.get('/seeker/profile-picture', { responseType: 'blob' })
    profile.pictureUrl = response.data?.size > 0 ? URL.createObjectURL(response.data) : null
  } catch {
    profile.pictureUrl = null
  }
}

// File Handling
const handleFileSelect = (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  if (!file.type.startsWith('image/')) {
    alert('Please select a valid image file')
    return
  }
  if (file.size > 2048000) {
    alert('Image size must be less than 2MB')
    return
  }

  forms.preview.file = file
  const reader = new FileReader()
  reader.onload = (e) => {
    forms.preview.tempUrl = e.target.result
    closePictureModal()
    flags.showPreviewModal = true
  }
  reader.readAsDataURL(file)
}

const saveProfilePicture = async () => {
  if (!forms.preview.file) return
  try {
    flags.loading = true
    const formData = new FormData()
    formData.append('profile_picture', forms.preview.file)
    await axios.post('/seeker/profile-picture', formData)
    await loadProfilePicture()
    forms.preview = { tempUrl: null, file: null }
    closePreviewModal()
  } catch (error) {
    alert('Failed to upload image')
  } finally {
    flags.loading = false
  }
}

// Form Validation & Management
const validateDetailsForm = () => {
  forms.errors.bio = forms.details.bio.length > 500 ? 'Bio must be less than 500 characters' : ''
  forms.errors.location =
    forms.details.location.length > 100 ? 'Location must be less than 100 characters' : ''
  return !forms.errors.bio && !forms.errors.location
}

const saveEditDetails = async () => {
  if (!validateDetailsForm()) return
  try {
    flags.loading = true
    await axios.put('/seeker/profile', forms.details)
    flags.isEditingDetails = false
    await loadProfile()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save profile')
  } finally {
    flags.loading = false
  }
}

const cancelEditDetails = () => {
  flags.isEditingDetails = false
  forms.details = { bio: profile.bio, location: profile.location }
  forms.errors = { bio: '', location: '' }
}

// Skills Management
const searchSkill = (event) => {
  const query = event.query?.toLowerCase() || ''
  filteredSkills.value = allSkills.value
    .filter((s) => s.name.toLowerCase().includes(query))
    .filter((s) => !provider.skills.some((ps) => ps.id === s.id))
    .map((s) => s.name)
}

const addSkill = async () => {
  if (!forms.skill.name.trim()) {
    alert('Please select a skill')
    return
  }

  try {
    flags.loading = true

    const selectedSkill = allSkills.value.find(
      (s) => s.name.toLowerCase() === forms.skill.name.toLowerCase(),
    )

    if (!selectedSkill) {
      forms.skillRequest.name = forms.skill.name
      forms.skillRequest.description = ''
      flags.showSkillNotFoundDialog = true
      flags.loading = false
      return
    }

    await axios.post('/provider/skills', {
      skill_id: selectedSkill.id,
    })

    provider.skills.push({
      id: selectedSkill.id,
      name: selectedSkill.name,
      description: selectedSkill.description || '',
      category: selectedSkill.category || 'other',
    })

    forms.skill = { name: '', category: '', description: '' }
    closeCreateSkillDialog()
  } catch (error) {
    console.error('Failed to add skill:', error)
    alert(error.response?.data?.message || 'Failed to add skill')
  } finally {
    flags.loading = false
  }
}

const requestSkill = async () => {
  if (!forms.skillRequest.name.trim()) {
    alert('Please enter skill name')
    return
  }

  try {
    flags.loading = true
    await axios.post('/provider/skill-requests', {
      name: forms.skillRequest.name,
      description: forms.skillRequest.description,
    })

    alert('Skill request submitted successfully! Our team will review it soon.')
    forms.skillRequest = { name: '', description: '' }
    forms.skill = { name: '', category: '', description: '' }
    flags.showSkillNotFoundDialog = false
    closeCreateSkillDialog()
  } catch (error) {
    console.error('Failed to request skill:', error)
    alert(error.response?.data?.message || 'Failed to submit skill request')
  } finally {
    flags.loading = false
  }
}

const confirmDeleteSkill = async () => {
  if (indexes.skillToDelete === null) return
  try {
    flags.loading = true
    const skill = provider.skills[indexes.skillToDelete]
    await axios.delete(`/provider/skills/${skill.id}`)
    provider.skills.splice(indexes.skillToDelete, 1)
    closeDeleteConfirmDialog()
  } catch (error) {
    console.error('Failed to delete skill:', error)
    alert('Failed to delete skill')
  } finally {
    flags.loading = false
    indexes.skillToDelete = null
  }
}

// Links Management
const isValidUrl = (url) => {
  try {
    new URL(url)
    return true
  } catch {
    return false
  }
}

const validateLink = () => {
  forms.errors.link = {}

  if (!forms.link.title.trim()) {
    forms.errors.link.title = 'Title is required'
  }

  if (!forms.link.url.trim()) {
    forms.errors.link.url = 'URL is required'
  } else if (!isValidUrl(forms.link.url)) {
    forms.errors.link.url = 'Invalid URL'
  }

  return Object.keys(forms.errors.link).length === 0
}

const openCreateLinkDialog = () => {
  forms.link = { title: '', url: '' }
  forms.errors.link = {}
  flags.showCreateLinkDialog = true
}

const openEditLinkDialog = (link) => {
  editingLinkId.value = link.id
  forms.link = { title: link.title, url: link.url }
  forms.errors.link = {}
  flags.showEditLinkDialog = true
}

const addLink = async () => {
  if (!validateLink()) return

  try {
    flags.loading = true
    await axios.post('/provider/profile/links', forms.link)

    alert('Link added successfully')
    forms.link = { title: '', url: '' }
    flags.showCreateLinkDialog = false
    await loadProfile() // Refetch profile
  } catch (error) {
    console.error('Failed to add link:', error)
    alert(error.response?.data?.message || 'Failed to add link')
  } finally {
    flags.loading = false
  }
}

const updateLink = async () => {
  if (!validateLink()) return

  try {
    flags.loading = true
    await axios.put(`/provider/profile/links/${editingLinkId.value}`, forms.link)

    alert('Link updated successfully')
    forms.link = { title: '', url: '' }
    flags.showEditLinkDialog = false
    editingLinkId.value = null
    await loadProfile() // Refetch profile
  } catch (error) {
    console.error('Failed to update link:', error)
    alert(error.response?.data?.message || 'Failed to update link')
  } finally {
    flags.loading = false
  }
}

const confirmDeleteLink = async () => {
  if (indexes.linkToDelete === null) return
  try {
    flags.loading = true
    const link = provider.links[indexes.linkToDelete]
    await axios.delete(`/provider/profile/links/${link.id}`)

    closeDeleteLinkDialog()
    alert('Link deleted successfully')
    await loadProfile() // Refetch profile
  } catch (error) {
    console.error('Failed to delete link:', error)
    alert('Failed to delete link')
  } finally {
    flags.loading = false
    indexes.linkToDelete = null
  }
}

const closeCreateLinkDialog = () => {
  flags.showCreateLinkDialog = false
  forms.link = { title: '', url: '' }
}

const closeEditLinkDialog = () => {
  flags.showEditLinkDialog = false
  forms.link = { title: '', url: '' }
  editingLinkId.value = null
}

// Event Handlers
const openDeleteSkillDialog = (idx) => {
  indexes.skillToDelete = idx
  flags.showDeleteConfirmDialog = true
}

const openDeleteLinkDialog = (idx) => {
  indexes.linkToDelete = idx
  flags.showDeleteLinkConfirmDialog = true
}

const closeCreateSkillDialog = () => {
  flags.showCreateSkillDialog = false
}

const closeSkillNotFoundDialog = () => {
  flags.showSkillNotFoundDialog = false
}

const closePictureModal = () => {
  flags.showPictureModal = false
}

const closePreviewModal = () => {
  flags.showPreviewModal = false
}

const closeDeleteConfirmDialog = () => {
  flags.showDeleteConfirmDialog = false
}

const closeDeleteLinkDialog = () => {
  flags.showDeleteLinkConfirmDialog = false
}

onMounted(() => {
  loadProfile()
  loadAvailableSkills()
})
</script>

<template>
  <div class="px-6 sm:px-4 md:px-5 lg:px-6 xl:px-35 mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Profile</h1>
      <p class="text-sm text-gray-600 mt-1">Manage your profile information</p>
    </div>

    <div class="grid grid-cols-12 gap-4">
      <div
        :class="flags.isProvider ? 'col-span-12 lg:col-span-9' : 'col-span-12'"
        class="space-y-4"
      >
        <!-- Profile Card -->
        <Card class="shadow-lg">
          <template #header>
            <ProfileCardHeader
              :profile-name="profile.name"
              :profile-roles="profile.roles"
              :profile-picture-url="profile.pictureUrl"
              @open-picture-modal="flags.showPictureModal = true"
            />
          </template>

          <template #content>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
                  <InputText v-model="profile.username" class="w-full text-sm" readonly />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                  <InputText v-model="profile.email" type="email" class="w-full text-sm" readonly />
                </div>
              </div>

              <div v-if="!flags.isEditingDetails && flags.isSeeker" class="flex justify-end">
                <Button
                  icon="pi pi-pencil"
                  label="Edit Info"
                  text
                  size="small"
                  @click="flags.isEditingDetails = true"
                  class="text-primary-500 !p-0"
                />
              </div>

              <!-- Edit Form -->
              <div v-if="flags.isEditingDetails && flags.isSeeker" class="space-y-4">
                <div class="field">
                  <label for="bio" class="block text-xs font-medium text-gray-700 mb-2">Bio</label>
                  <Textarea
                    id="bio"
                    v-model="forms.details.bio"
                    rows="3"
                    class="w-full"
                    placeholder="Tell us about yourself..."
                  />
                  <div class="flex justify-between items-start mt-1">
                    <small v-if="forms.errors.bio" class="text-red-600 text-xs">{{
                      forms.errors.bio
                    }}</small>
                    <small class="text-gray-500 text-xs ml-auto"
                      >{{ forms.details.bio.length }}/500</small
                    >
                  </div>
                </div>

                <div class="field">
                  <label for="location" class="block text-xs font-medium text-gray-700 mb-2"
                    >Location</label
                  >
                  <InputText
                    id="location"
                    v-model="forms.details.location"
                    class="w-full"
                    placeholder="e.g., Miagao, Iloilo"
                  />
                  <div class="flex justify-between items-start mt-1">
                    <small v-if="forms.errors.location" class="text-red-600 text-xs">{{
                      forms.errors.location
                    }}</small>
                    <small class="text-gray-500 text-xs ml-auto"
                      >{{ forms.details.location.length }}/100</small
                    >
                  </div>
                </div>

                <div class="flex gap-2 justify-end pt-2">
                  <Button
                    label="Cancel"
                    severity="secondary"
                    size="small"
                    @click="cancelEditDetails"
                    text
                  />
                  <Button
                    label="Save"
                    size="small"
                    @click="saveEditDetails"
                    :loading="flags.loading"
                    class="bg-primary-500 text-white"
                  />
                </div>
              </div>

              <!-- Display Mode -->
              <div v-else-if="flags.isSeeker" class="space-y-4">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Bio</label>
                  <Textarea v-model="profile.bio" rows="2" class="w-full text-sm" readonly />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Location</label>
                  <InputText v-model="profile.location" class="w-full text-sm" readonly />
                </div>
              </div>
            </div>
          </template>
        </Card>

        <!-- Skills Card -->
        <Card v-if="flags.isProvider" class="shadow-lg">
          <template #header>
            <div class="bg-primary-500 p-3 rounded-md flex items-center justify-between">
              <div>
                <h3 class="font-bold text-base text-white">Skills and Expertise</h3>
                <p class="text-xs text-blue-100 mt-1">Showcase your professional skills</p>
              </div>
              <Button
                icon="pi pi-plus"
                label="Add Skill"
                size="small"
                @click="flags.showCreateSkillDialog = true"
                class="bg-white text-primary-500"
              />
            </div>
          </template>

          <template #content>
            <div class="space-y-3">
              <div v-if="provider.skills.length > 0" class="grid grid-cols-2 gap-3">
                <div
                  v-for="(skill, idx) in provider.skills"
                  :key="skill.id"
                  class="flex flex-col gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg"
                >
                  <div class="flex items-start justify-between gap-2">
                    <div class="flex-1">
                      <span class="font-semibold text-gray-900 text-sm block">{{
                        skill.name
                      }}</span>
                      <span class="text-xs text-gray-600 mt-1 block">{{ skill.description }}</span>
                    </div>
                    <Button
                      icon="pi pi-times"
                      rounded
                      text
                      severity="danger"
                      size="small"
                      @click="openDeleteSkillDialog(idx)"
                      class="w-7 h-7 p-0"
                    />
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <i class="pi pi-inbox text-2xl text-gray-300 block mb-2"></i>
                <p class="text-sm">No skills added yet</p>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Links Card -->
      <div v-if="flags.isProvider" class="col-span-12 lg:col-span-3">
        <Card class="shadow-lg">
          <template #header>
            <div class="bg-primary-500 p-3 rounded-md flex items-center justify-between">
              <div>
                <h3 class="font-bold text-base text-white">Portfolio & Links</h3>
                <p class="text-xs text-blue-100 mt-1">Showcase your work</p>
              </div>
              <Button
                icon="pi pi-plus"
                label="Add Link"
                size="small"
                @click="openCreateLinkDialog"
                class="bg-white text-primary-500"
              />
            </div>
          </template>

          <template #content>
            <div class="space-y-3">
              <div
                v-for="(link, idx) in provider.links"
                :key="link.id"
                class="border border-blue-200 rounded-md p-3 bg-blue-50"
              >
                <div class="flex items-center justify-between mb-2">
                  <h4 class="font-medium text-sm text-gray-900">{{ link.title }}</h4>
                  <div class="flex gap-1">
                    <Button
                      icon="pi pi-pencil"
                      text
                      rounded
                      size="small"
                      @click="openEditLinkDialog(link)"
                      class="text-gray-500"
                    />
                    <Button
                      icon="pi pi-trash"
                      text
                      rounded
                      size="small"
                      severity="danger"
                      @click="openDeleteLinkDialog(idx)"
                    />
                  </div>
                </div>
                <a
                  :href="link.url"
                  target="_blank"
                  class="text-blue-600 text-xs break-all hover:underline"
                >
                  {{ link.url }}
                </a>
              </div>
              <div v-if="provider.links.length === 0" class="text-center py-4 text-gray-500">
                <p class="text-sm">No links added</p>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Create Skill Dialog -->
    <Dialog
      v-model:visible="flags.showCreateSkillDialog"
      header="Add New Skill"
      :modal="true"
      class="w-full max-w-sm"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Skill Name *</label>
          <AutoComplete
            v-model="forms.skill.name"
            :suggestions="filteredSkills"
            @complete="searchSkill"
            placeholder="Search or enter skill name"
            class="w-full text-sm"
          />
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="closeCreateSkillDialog" text />
        <Button label="Add" icon="pi pi-check" @click="addSkill" :loading="flags.loading" />
      </template>
    </Dialog>

    <!-- Skill Not Found Dialog -->
    <Dialog
      v-model:visible="flags.showSkillNotFoundDialog"
      header="Skill Not Found"
      :modal="true"
      class="w-full max-w-sm"
    >
      <div class="space-y-4">
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
          <p class="text-sm text-yellow-800">
            <strong>{{ forms.skillRequest.name }}</strong> is not in our database.
          </p>
          <p class="text-sm text-yellow-700 mt-2">
            Would you like to request this skill to be added? Our team will review your request.
          </p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Skill Name</label>
          <InputText
            v-model="forms.skillRequest.name"
            class="w-full text-sm"
            placeholder="Enter skill name"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
          <Textarea
            v-model="forms.skillRequest.description"
            rows="3"
            class="w-full text-sm"
            placeholder="Describe this skill (optional)"
          />
        </div>
      </div>

      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="closeSkillNotFoundDialog" text />
        <Button
          label="Request Skill"
          icon="pi pi-check"
          @click="requestSkill"
          :loading="flags.loading"
          severity="info"
        />
      </template>
    </Dialog>

    <!-- Create Link Dialog -->
    <Dialog
      v-model:visible="flags.showCreateLinkDialog"
      header="Add Link"
      :modal="true"
      class="w-full max-w-sm"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Title</label>
          <InputText v-model="forms.link.title" placeholder="e.g., My Portfolio" class="w-full" />
          <small v-if="forms.errors.link.title" class="text-red-600">{{
            forms.errors.link.title
          }}</small>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">URL</label>
          <InputText v-model="forms.link.url" placeholder="https://example.com" class="w-full" />
          <small v-if="forms.errors.link.url" class="text-red-600">{{
            forms.errors.link.url
          }}</small>
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="closeCreateLinkDialog" text />
        <Button label="Add" icon="pi pi-check" @click="addLink" :loading="flags.loading" />
      </template>
    </Dialog>

    <!-- Edit Link Dialog -->
    <Dialog
      v-model:visible="flags.showEditLinkDialog"
      header="Edit Link"
      :modal="true"
      class="w-full max-w-sm"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Title</label>
          <InputText v-model="forms.link.title" placeholder="e.g., My Portfolio" class="w-full" />
          <small v-if="forms.errors.link.title" class="text-red-600">{{
            forms.errors.link.title
          }}</small>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">URL</label>
          <InputText v-model="forms.link.url" placeholder="https://example.com" class="w-full" />
          <small v-if="forms.errors.link.url" class="text-red-600">{{
            forms.errors.link.url
          }}</small>
        </div>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="closeEditLinkDialog" text />
        <Button label="Update" icon="pi pi-check" @click="updateLink" :loading="flags.loading" />
      </template>
    </Dialog>

    <!-- Delete Skill Dialog -->
    <Dialog
      v-model:visible="flags.showDeleteConfirmDialog"
      header="Confirm Delete"
      :modal="true"
      class="w-full max-w-sm"
    >
      <p class="text-gray-700 mb-4">Are you sure you want to delete this skill?</p>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="closeDeleteConfirmDialog" text />
        <Button label="Delete" icon="pi pi-check" severity="danger" @click="confirmDeleteSkill" />
      </template>
    </Dialog>

    <!-- Delete Link Dialog -->
    <Dialog
      v-model:visible="flags.showDeleteLinkConfirmDialog"
      header="Confirm Delete"
      :modal="true"
      class="w-full max-w-sm"
    >
      <p class="text-gray-700 mb-4">Are you sure you want to delete this link?</p>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="closeDeleteLinkDialog" text />
        <Button label="Delete" icon="pi pi-check" severity="danger" @click="confirmDeleteLink" />
      </template>
    </Dialog>

    <!-- Picture Upload Dialog -->
    <Dialog
      v-model:visible="flags.showPictureModal"
      header="Upload Profile Picture"
      :modal="true"
      class="w-full md:w-96"
    >
      <div class="space-y-4">
        <p class="text-gray-700">Choose how you'd like to upload your profile picture:</p>
        <div>
          <input
            type="file"
            accept="image/*"
            class="hidden"
            id="fileInput"
            @change="handleFileSelect"
            :disabled="flags.loading"
          />
          <label
            for="fileInput"
            class="flex items-center justify-center gap-2 p-4 border-2 border-dashed border-primary-500 rounded-lg cursor-pointer hover:bg-blue-50"
            :class="{ 'opacity-50 cursor-not-allowed': flags.loading }"
          >
            <i class="pi pi-upload text-primary-500"></i>
            <span class="text-primary-500 font-semibold">Upload from Device</span>
          </label>
        </div>
        <p class="text-xs text-gray-500 text-center">
          Max file size: 2MB. Formats: JPG, PNG, GIF, WEBP
        </p>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="closePictureModal" text />
      </template>
    </Dialog>

    <!-- Preview Dialog -->
    <Dialog
      v-model:visible="flags.showPreviewModal"
      header="Preview Profile Picture"
      :modal="true"
      class="w-full md:w-96"
    >
      <div class="space-y-4">
        <div class="flex justify-center">
          <img
            v-if="forms.preview.tempUrl"
            :src="forms.preview.tempUrl"
            alt="preview"
            class="w-64 h-64 object-cover rounded-lg border-2 border-gray-200"
          />
        </div>
        <div v-if="forms.preview.file" class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-semibold">Filename:</span> {{ forms.preview.file.name }}
          </p>
          <p class="text-sm text-gray-700">
            <span class="font-semibold">Size:</span>
            {{ (forms.preview.file.size / 1024).toFixed(2) }} KB
          </p>
        </div>
        <p class="text-center text-gray-600">Does this look good? You can save or cancel.</p>
      </div>
      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="closePreviewModal" text />
        <Button
          label="Save Changes"
          icon="pi pi-check"
          @click="saveProfilePicture"
          :loading="flags.loading"
        />
      </template>
    </Dialog>
  </div>
</template>

<style scoped>
:deep(.p-tag) {
  background-color: #000000 !important;
  color: #ffffff !important;
}

:deep(.p-tag .p-tag-value) {
  color: #ffffff !important;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
</style>
