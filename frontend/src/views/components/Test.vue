<script setup>
import { ref, onMounted } from 'vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import Dropdown from 'primevue/dropdown'
import AutoComplete from 'primevue/autocomplete'
import axios from '@/composables/axios'

// ... (all previous state and functions remain the same until loadProfilePicture)

const loadProfilePicture = async () => {
  try {
    const response = await axios.get('/seeker/profile-picture', {
      responseType: 'blob',
    })

    // Check if blob has data
    if (response.data && response.data.size > 0) {
      const url = URL.createObjectURL(response.data)
      profilePictureUrl.value = url
    } else {
      profilePictureUrl.value = null
    }
  } catch (error) {
    // 404 or no image error - set to null
    profilePictureUrl.value = null
    console.log('No profile picture found')
  }
}

// ... (rest of the code remains the same)
</script>

<template>
  <div class="px-6 sm:px-4 md:px-5 lg:px-6 xl:px-35 py-6 mx-auto">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Profile</h1>
      <p class="text-sm text-gray-600 mt-1">Manage your profile information</p>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-12 gap-4">
      <!-- Left Column (3/4 width for provider, full width for regular user) -->
      <div :class="isProvider ? 'col-span-12 lg:col-span-9' : 'col-span-12'" class="space-y-4">
        <!-- Profile Card with Picture Section -->
        <Card class="shadow-lg">
          <template #header>
            <div class="bg-primary-500 p-4 flex items-center justify-between rounded-md">
              <div class="flex items-center gap-4">
                <!-- Avatar with Upload -->
                <div class="relative group">
                  <div
                    class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden border-2 border-white"
                  >
                    <img
                      v-if="profilePictureUrl"
                      :src="profilePictureUrl"
                      alt="Profile Picture"
                      class="w-full h-full object-cover"
                    />
                    <span v-else class="text-2xl font-bold text-gray-400">
                      {{ profileName.charAt(0).toUpperCase() }}
                    </span>
                  </div>

                  <!-- Upload Button -->
                  <input
                    type="file"
                    accept="image/*"
                    class="hidden"
                    id="pictureInput"
                    @change="openPreviewModal"
                    :disabled="loading"
                  />
                  <label
                    for="pictureInput"
                    class="absolute bottom-0 right-0 bg-white text-primary-500 p-2 rounded-full cursor-pointer hover:bg-blue-100 transition shadow-lg group-hover:block hidden"
                    :class="{ 'opacity-50 cursor-not-allowed': loading }"
                  >
                    <i class="pi pi-camera text-lg"></i>
                  </label>
                </div>

                <div>
                  <h2 class="text-white font-bold text-base">{{ profileName }}</h2>
                  <div class="flex gap-2 mt-1">
                    <Tag
                      v-for="role in profileRoles"
                      :key="role"
                      :value="role"
                      class="!bg-black !text-white text-xs"
                    />
                  </div>
                  <div v-if="profilePictureUrl" class="mt-2">
                    <Tag
                      severity="success"
                      value="✓ Photo Set"
                      icon="pi pi-check"
                      class="text-xs"
                    />
                  </div>
                </div>
              </div>

              <!-- Delete Picture Button -->
              <Button
                v-if="profilePictureUrl"
                icon="pi pi-trash"
                text
                rounded
                severity="danger"
                size="small"
                @click="deleteProfilePicture"
                :loading="loading"
                class="text-white hover:text-red-200"
              />
            </div>
          </template>

          <template #content>
            <div class="space-y-4">
              <!-- Username and Email (Read-only) -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
                  <InputText v-model="username" class="w-full text-sm" readonly />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                  <InputText v-model="email" type="email" class="w-full text-sm" readonly />
                </div>
              </div>

              <!-- Edit Details Button -->
              <div v-if="!isEditingDetails && isProvider" class="flex justify-end mb-0">
                <Button
                  icon="pi pi-pencil"
                  label="Edit Info"
                  text
                  size="small"
                  @click="isEditingDetails = true"
                  class="text-primary-500 hover:bg-primary-50 !p-0"
                />
              </div>

              <!-- Bio -->
              <div v-if="isProvider">
                <label class="block text-xs font-medium text-gray-700 mb-1">Bio</label>
                <Textarea
                  v-model="bio"
                  rows="2"
                  class="w-full text-sm"
                  :readonly="!isEditingDetails"
                />
              </div>

              <!-- Location -->
              <div v-if="isProvider">
                <label class="block text-xs font-medium text-gray-700 mb-1">Location</label>
                <InputText
                  v-model="location"
                  class="w-full text-sm"
                  :readonly="!isEditingDetails"
                />
              </div>

              <!-- Save and Cancel Buttons -->
              <div v-if="isEditingDetails && isProvider" class="flex gap-2 justify-end">
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
                  :loading="loading"
                  class="bg-primary-500 text-white"
                />
              </div>
            </div>
          </template>
        </Card>

        <!-- Skills Card - Only for providers -->
        <Card v-if="isProvider" class="shadow-lg">
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
                @click="openCreateSkillDialog"
                class="bg-white text-primary-500 hover:bg-blue-50"
              />
            </div>
          </template>

          <template #content>
            <div class="space-y-3">
              <!-- Skills Display - Two Column Grid -->
              <div v-if="skills.length > 0" class="grid grid-cols-2 gap-3">
                <div
                  v-for="(skill, index) in skills"
                  :key="skill.id || index"
                  class="flex flex-col gap-2 p-3 bg-blue-50 border border-blue-200 rounded-lg relative"
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
                      @click="removeSkill(index)"
                      class="w-7 h-7 p-0 flex-shrink-0"
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

      <!-- Right Column (1/4 width) - Only for providers -->
      <div v-if="isProvider" class="col-span-12 lg:col-span-3">
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
                class="bg-white text-primary-500 hover:bg-blue-50"
              />
            </div>
          </template>

          <template #content>
            <div class="space-y-3">
              <div
                v-for="(link, index) in portfolioLinks"
                :key="link.id || index"
                class="border border-blue-200 rounded-md p-3 bg-blue-50"
              >
                <div class="flex items-center justify-between mb-2">
                  <h4 class="font-medium text-sm text-gray-900">{{ link.title }}</h4>
                  <Button
                    icon="pi pi-pencil"
                    text
                    rounded
                    size="small"
                    class="text-gray-500 hover:text-primary-500"
                  />
                </div>
                <p class="text-blue-600 text-xs break-all mb-2">{{ link.url }}</p>
                <Button
                  icon="pi pi-trash"
                  text
                  size="small"
                  class="text-red-500 hover:bg-red-50 w-full justify-start"
                  @click="removeLink(index)"
                  label="Delete"
                />
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Create Skill Dialog -->
    <Dialog
      v-model:visible="showCreateSkillDialog"
      header="Add New Skill"
      :modal="true"
      class="w-full max-w-sm"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Skill Name *</label>
          <AutoComplete
            v-model="skillForm.name"
            :suggestions="filteredSkills"
            @complete="searchSkill"
            placeholder="Search or enter skill name"
            class="w-full text-sm"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Category *</label>
          <Dropdown
            v-model="skillForm.category"
            :options="categories"
            option-label="label"
            option-value="value"
            placeholder="Select a category"
            class="w-full"
          />
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
          <Textarea
            v-model="skillForm.description"
            placeholder="Enter skill description"
            rows="3"
            class="w-full text-sm"
          />
        </div>
      </div>

      <template #footer>
        <Button
          label="Cancel"
          icon="pi pi-times"
          @click="showCreateSkillDialog = false"
          :disabled="loading"
          text
        />
        <Button label="Add" icon="pi pi-check" @click="addSkill" :loading="loading" />
      </template>
    </Dialog>

    <!-- Delete Skill Confirmation Dialog -->
    <Dialog
      v-model:visible="showDeleteConfirmDialog"
      header="Confirm Delete"
      :modal="true"
      class="w-full max-w-sm"
    >
      <p class="text-gray-700 mb-4">Are you sure you want to delete this skill?</p>

      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="cancelDeleteSkill" text />
        <Button label="Delete" icon="pi pi-check" severity="danger" @click="confirmDeleteSkill" />
      </template>
    </Dialog>

    <!-- Delete Link Confirmation Dialog -->
    <Dialog
      v-model:visible="showDeleteLinkConfirmDialog"
      header="Confirm Delete"
      :modal="true"
      class="w-full max-w-sm"
    >
      <p class="text-gray-700 mb-4">Are you sure you want to delete this link?</p>

      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="cancelDeleteLink" text />
        <Button label="Delete" icon="pi pi-check" severity="danger" @click="confirmDeleteLink" />
      </template>
    </Dialog>

    <!-- Preview Modal -->
    <Dialog
      v-model:visible="showPreviewModal"
      header="Preview Profile Picture"
      :modal="true"
      class="w-full md:w-96"
      :closable="!loading"
    >
      <div class="space-y-4">
        <!-- Preview Image -->
        <div class="flex justify-center">
          <img
            v-if="tempImageUrl"
            :src="tempImageUrl"
            alt="preview"
            class="w-64 h-64 object-cover rounded-lg border-2 border-gray-200"
          />
        </div>

        <!-- File Info -->
        <div v-if="pendingFile" class="bg-blue-50 border border-blue-200 p-3 rounded-lg">
          <p class="text-sm text-gray-700">
            <span class="font-semibold">Filename:</span> {{ pendingFile.name }}
          </p>
          <p class="text-sm text-gray-700">
            <span class="font-semibold">Size:</span> {{ (pendingFile.size / 1024).toFixed(2) }} KB
          </p>
        </div>

        <!-- Message -->
        <p class="text-center text-gray-600">Does this look good? You can save or cancel.</p>
      </div>

      <template #footer>
        <Button label="Cancel" icon="pi pi-times" @click="cancelPreview" :disabled="loading" text />
        <Button
          label="Save Changes"
          icon="pi pi-check"
          @click="savePreviewImage"
          :loading="loading"
          autofocus
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
</style>
