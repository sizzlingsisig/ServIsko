<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/composables/axios'
import { useToastStore } from '@/stores/toastStore'
import { useAuthStore } from '@/stores/AuthStore'

const toastStore = useToastStore()
const authStore = useAuthStore()
const router = useRouter()

// Reactive state
const files = ref([])
const loading = ref(false)
const uploading = ref(false)
const dragActive = ref(false)
const isAuthorized = ref(false)

// File input refs
const fileInput = ref(null)
const uploadedFile = ref(null)
const description = ref('')

// Open native file picker
const triggerFileSelect = () => {
  if (fileInput.value) fileInput.value.click()
}

// Fetch user's files
const fetchFiles = async () => {
  loading.value = true
  try {
    const response = await api.get('/files')
    files.value = response.data.files
  } catch (error) {
    toastStore.showError('Failed to load files')
  } finally {
    loading.value = false
  }
}

// Handle file selection
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    uploadedFile.value = file
  }
}

// Handle drag events
const handleDragEnter = (e) => {
  e.preventDefault()
  dragActive.value = true
}

const handleDragLeave = (e) => {
  e.preventDefault()
  dragActive.value = false
}

const handleDragOver = (e) => {
  e.preventDefault()
}

const handleDrop = (e) => {
  e.preventDefault()
  dragActive.value = false
  
  const file = e.dataTransfer.files[0]
  if (file) {
    uploadedFile.value = file
  }
}

// Upload file
const uploadFile = async () => {
  if (!uploadedFile.value) {
    toastStore.showError('Please select a file to upload')
    return
  }

  uploading.value = true
  
  try {
    const formData = new FormData()
    formData.append('file', uploadedFile.value)
    if (description.value) {
      formData.append('description', description.value)
    }

  const response = await api.post('/files', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    toastStore.showSuccess(response.data.message)
    
    // Reset form
    uploadedFile.value = null
    description.value = ''
    if (fileInput.value) {
      fileInput.value.value = ''
    }
    
    // Refresh files list
    await fetchFiles()
    
  } catch (error) {
    toastStore.showError(error.response?.data?.message || 'Upload failed')
  } finally {
    uploading.value = false
  }
}

// Download file
const downloadFile = async (fileId, fileName) => {
  try {
  const response = await api.get(`/files/${fileId}/download`, {
      responseType: 'blob',
    })
    
    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    
  } catch (error) {
    toastStore.showError('Failed to download file')
  }
}

// Delete file
const deleteFile = async (fileId) => {
  if (!confirm('Are you sure you want to delete this file?')) {
    return
  }
  
  try {
  const response = await api.delete(`/files/${fileId}`)
    toastStore.showSuccess(response.data.message)
    await fetchFiles()
  } catch (error) {
    toastStore.showError('Failed to delete file')
  }
}

// Clear selected file
const clearFile = () => {
  uploadedFile.value = null
  description.value = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Format file size
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Get file icon based on mime type
const getFileIcon = (mimeType) => {
  if (mimeType.startsWith('image/')) return 'pi pi-image'
  if (mimeType.startsWith('video/')) return 'pi pi-video'
  if (mimeType.startsWith('audio/')) return 'pi pi-volume-up'
  if (mimeType.includes('pdf')) return 'pi pi-file-pdf'
  if (mimeType.includes('word') || mimeType.includes('document')) return 'pi pi-file-word'
  if (mimeType.includes('excel') || mimeType.includes('sheet')) return 'pi pi-file-excel'
  return 'pi pi-file'
}

// Ensure only providers can access; fetch files after check
onMounted(async () => {
  try {
    let user = authStore.getUser?.() || authStore.user
    if (!user || !user.roles) {
      const { data } = await api.get('/user')
      user = data.data
      if (authStore.setUser) authStore.setUser(user)
    }
    const roles = Array.isArray(user?.roles) ? user.roles : []
    if (roles.includes('service-provider')) {
      isAuthorized.value = true
      await fetchFiles()
    } else {
      toastStore.showError('This page is for service providers only.')
      router.replace({ name: 'profile' })
    }
  } catch (e) {
    router.replace({ name: 'login' })
  }
})
</script>

<template>
  <div v-if="isAuthorized" class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-4 text-center">
        <h1 class="text-3xl font-bold text-gray-900">User Verification</h1>
        <p class="mt-2 text-gray-600">Upload file to verify your identity</p>
      </div>

      <!-- Upload Section -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        
        <!-- Drag & Drop Area -->
        <div
          @dragenter="handleDragEnter"
          @dragleave="handleDragLeave"
          @dragover="handleDragOver"
          @drop="handleDrop"
          @click="!uploadedFile && triggerFileSelect()"
          role="button"
          tabindex="0"
          @keydown.enter.prevent="!uploadedFile && triggerFileSelect()"
          @keydown.space.prevent="!uploadedFile && triggerFileSelect()"
          :class="[
            'border-2 border-dashed rounded-lg p-8 text-center transition-colors duration-200 cursor-pointer',
            dragActive 
              ? 'border-primary-500 bg-primary-50' 
              : 'border-gray-300 hover:border-gray-400'
          ]"
        >
          <div class="space-y-4">
            <i class="pi pi-cloud-upload text-4xl text-gray-400"></i>
            
            <div v-if="!uploadedFile">
              <p class="text-lg text-gray-600">
                <span class="font-medium">Click to upload</span> or drag and drop
              </p>
              <p class="text-sm text-gray-500">Maximum file size: 10MB</p>
            </div>
            
            <div v-else class="space-y-2">
              <div class="flex items-center justify-center space-x-2">
                <i :class="getFileIcon(uploadedFile.type)" class="text-primary-600"></i>
                <span class="font-medium text-gray-900">{{ uploadedFile.name }}</span>
                <span class="text-sm text-gray-500">({{ formatFileSize(uploadedFile.size) }})</span>
              </div>
              <Button
                @click="clearFile"
                label="Remove"
                icon="pi pi-times"
                severity="secondary"
                size="large"
                outlined
                class="mt-2 w-full sm:w-auto"
              />
            </div>
            
            <input
              ref="fileInput"
              type="file"
              @change="handleFileSelect"
              class="hidden"
              accept="*/*"
            />
            
            <Button
              v-if="!uploadedFile"
              @click="triggerFileSelect"
              label="Choose File"
              icon="pi pi-upload"
              size="large"
              class="mt-4 whitespace-nowrap w-full sm:w-auto"
              aria-label="Choose file to upload"
            />
          </div>
        </div>

        <!-- Description Input -->
        <div class="mt-4">
          <FormKit
            type="text"
            v-model="description"
            label="Description (Optional)"
            placeholder="Add a description for your file..."
            outer-class="!max-w-full"
          />
        </div>

        <!-- Upload Button -->
        <div class="flex justify-end mt-6" :aria-busy="uploading">
          <Button
            @click="uploadFile"
            :loading="uploading"
            :disabled="!uploadedFile || uploading"
            icon="pi pi-upload"
            severity="primary"
            :label="uploading ? 'Uploading...' : 'Upload File'"
            size="large"
            class="w-full sm:w-auto"
          />
        </div>
      </div>

      <!-- Files List -->
      <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Your Files</h2>
        </div>
        
        <div v-if="loading" class="p-8 text-center">
          <ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
          <p class="mt-2 text-gray-600">Loading files...</p>
        </div>
        
        <div v-else-if="files.length === 0" class="p-8 text-center">
          <i class="pi pi-folder-open text-4xl text-gray-400 mb-4"></i>
          <p class="text-gray-600">No files uploaded yet</p>
        </div>
        
        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="file in files"
            :key="file.id"
            class="p-6 hover:bg-gray-50 transition-colors duration-150"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <i :class="getFileIcon(file.mime_type)" class="text-2xl text-primary-600"></i>
                <div>
                  <h3 class="font-medium text-gray-900">{{ file.original_name }}</h3>
                  <div class="flex items-center space-x-4 mt-1 text-sm text-gray-500">
                    <span>{{ file.formatted_size }}</span>
                    <span>{{ file.created_at }}</span>
                  </div>
                  <p v-if="file.description" class="text-sm text-gray-600 mt-1">
                    {{ file.description }}
                  </p>
                </div>
              </div>
              
              <div class="flex space-x-2">
                <Button
                  @click="downloadFile(file.id, file.original_name)"
                  icon="pi pi-download"
                  severity="secondary"
                  size="small"
                  outlined
                  v-tooltip="'Download'"
                />
                <Button
                  @click="deleteFile(file.id)"
                  icon="pi pi-trash"
                  severity="danger"
                  size="small"
                  outlined
                  v-tooltip="'Delete'"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.formkit-outer) {
  margin-bottom: 0;
}

:deep(.formkit-label) {
  font-size: 0.875rem;
  margin-bottom: 0.375rem;
  color: #374151;
  font-weight: 500;
}

:deep(.formkit-messages) {
  min-height: 1.25rem;
  margin-top: 0.25rem;
}

:deep(.formkit-message) {
  color: #dc2626;
  font-size: 0.75rem;
}
</style>
