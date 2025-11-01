<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/AuthStore'
import axios from '@/composables/axios'

const authStore = useAuthStore()

// State
const user = ref(null)
const bio = ref('')
const location = ref('')
const currentPassword = ref('')
const newPassword = ref('')
const newPasswordConfirm = ref('')
const imageUrl = ref(null)
const loading = ref(false)
const editMode = ref(false)
const error = ref('')
const success = ref('')

// Fetch Profile
const fetchProfile = async () => {
  try {
    loading.value = true
    error.value = ''
    success.value = ''

    const res = await axios.get('/seeker/profile')
    user.value = res.data.data
    bio.value = user.value.profile?.bio || ''
    location.value = user.value.profile?.location || ''

    success.value = 'Profile loaded'
    await loadImage()
  } catch (err) {
    console.error('Error fetching profile:', err)
    error.value = err.response?.data?.message || 'Failed to fetch profile'
  } finally {
    loading.value = false
  }
}

// Load Image
const loadImage = async () => {
  try {
    if (!user.value?.profile?.profile_picture) {
      imageUrl.value = null
      return
    }

    const res = await axios.get('/seeker/profile-picture', {
      responseType: 'blob',
    })

    if (res.status === 200 && res.data.size > 0) {
      imageUrl.value = URL.createObjectURL(res.data)
    } else {
      imageUrl.value = null
    }
  } catch (err) {
    if (err.response?.status === 204 || err.response?.status === 404) {
      imageUrl.value = null
      return
    }
    console.error('Error loading image:', err)
    imageUrl.value = null
  }
}

// Update Profile
const updateProfile = async () => {
  try {
    loading.value = true
    error.value = ''

    const res = await axios.put('/seeker/profile', {
      bio: bio.value,
      location: location.value,
    })

    user.value = res.data.data
    authStore.setUser(user.value)
    editMode.value = false
    success.value = 'Profile updated successfully!'
  } catch (err) {
    console.error('Error updating profile:', err)
    error.value = err.response?.data?.message || 'Failed to update profile'
  } finally {
    loading.value = false
  }
}

// Upload Picture
const uploadPicture = async (event) => {
  try {
    loading.value = true
    error.value = ''
    const file = event.target.files?.[0]

    if (!file) {
      error.value = 'Please select a file'
      return
    }

    const formData = new FormData()
    formData.append('profile_picture', file)

    const res = await axios.post('/seeker/profile-picture', formData)
    user.value = res.data.data
    authStore.setUser(user.value)

    setTimeout(() => {
      loadImage()
    }, 500)

    success.value = 'Picture uploaded successfully!'
    event.target.value = ''
  } catch (err) {
    console.error('Error uploading picture:', err)
    error.value = err.response?.data?.message || 'Failed to upload picture'
  } finally {
    loading.value = false
  }
}

// Change Password
const changePassword = async () => {
  if (!currentPassword.value || !newPassword.value || !newPasswordConfirm.value) {
    error.value = 'All fields are required'
    return
  }

  if (newPassword.value !== newPasswordConfirm.value) {
    error.value = 'Passwords do not match'
    return
  }

  try {
    loading.value = true
    error.value = ''

    await axios.post('/seeker/password', {
      current_password: currentPassword.value,
      new_password: newPassword.value,
      new_password_confirmation: newPasswordConfirm.value,
    })

    currentPassword.value = ''
    newPassword.value = ''
    newPasswordConfirm.value = ''
    success.value = 'Password changed successfully!'
  } catch (err) {
    console.error('Error changing password:', err)
    error.value = err.response?.data?.message || 'Failed to change password'
  } finally {
    loading.value = false
  }
}

// Delete Account
const deleteAccount = async () => {
  if (!confirm('Are you sure? This cannot be undone.')) return

  try {
    loading.value = true
    error.value = ''

    await axios.delete('/seeker')

    success.value = 'Account deleted successfully!'
    authStore.logout()
    setTimeout(() => {
      window.location.href = '/login'
    }, 2000)
  } catch (err) {
    console.error('Error deleting account:', err)
    error.value = err.response?.data?.message || 'Failed to delete account'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (!authStore.token) {
    error.value = 'No authentication token found'
    return
  }
  fetchProfile()
})
</script>

<template>
  <div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-8">
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-4xl font-bold text-gray-900">Seeker Profile</h1>
        <p class="mt-2 text-gray-600">Manage your profile, picture, and account settings</p>
      </div>

      <!-- Success Messages -->
      <div
        v-if="success"
        class="p-4 bg-green-100 text-green-700 border border-green-400 rounded-lg flex items-center justify-between"
      >
        <span>{{ success }}</span>
        <button @click="success = ''" class="text-green-700 hover:text-green-900">✕</button>
      </div>

      <!-- Error Messages -->
      <div
        v-if="error"
        class="p-4 bg-red-100 text-red-700 border border-red-400 rounded-lg flex items-center justify-between"
      >
        <span>{{ error }}</span>
        <button @click="error = ''" class="text-red-700 hover:text-red-900">✕</button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-4 bg-blue-100 text-blue-700 border border-blue-400 rounded-lg">
        ⏳ Loading...
      </div>

      <!-- Profile Picture Section -->
      <div v-if="user" class="bg-white shadow-lg rounded-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Profile Picture</h2>
        <div class="flex items-center gap-8">
          <!-- Avatar -->
          <div class="relative group">
            <div
              class="relative w-40 h-40 rounded-full border-4 border-blue-500 overflow-hidden bg-gray-200 flex items-center justify-center"
            >
              <img
                v-if="imageUrl"
                :src="imageUrl"
                alt="avatar"
                class="w-full h-full object-cover"
              />
              <div v-else class="flex flex-col items-center justify-center text-center">
                <span class="text-6xl font-bold text-gray-400">
                  {{ user.name.charAt(0).toUpperCase() }}
                </span>
                <span class="text-sm text-gray-400 mt-2">No Photo</span>
              </div>
            </div>

            <!-- Upload Button -->
            <input
              type="file"
              accept="image/*"
              class="hidden"
              id="pictureInput"
              @change="uploadPicture"
              :disabled="loading"
            />
            <label
              for="pictureInput"
              class="absolute bottom-0 right-0 bg-blue-500 text-white p-4 rounded-full cursor-pointer hover:bg-blue-600 transition shadow-lg text-2xl"
              :class="{ 'opacity-50 cursor-not-allowed': loading }"
            >
              📷
            </label>
          </div>

          <!-- User Info -->
          <div class="flex-1">
            <h3 class="text-2xl font-bold text-gray-900">{{ user.name }}</h3>
            <p class="text-gray-600 mt-1">{{ user.email }}</p>
            <p class="text-sm text-gray-500 mt-1">ID: {{ user.id }}</p>
            <p v-if="imageUrl" class="text-sm text-green-600 mt-3 font-semibold">
              ✓ Profile picture set
            </p>
            <p v-else class="text-sm text-gray-500 mt-3">Click camera icon to add photo</p>
          </div>
        </div>
      </div>

      <!-- Profile Edit Section -->
      <div v-if="user" class="bg-white shadow-lg rounded-lg p-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Profile Details</h2>
          <button
            @click="editMode = !editMode"
            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
          >
            {{ editMode ? 'Cancel' : 'Edit' }}
          </button>
        </div>

        <div v-if="!editMode" class="space-y-6">
          <div>
            <label class="block font-semibold text-gray-700 mb-2">Bio</label>
            <p class="text-gray-600 p-4 bg-gray-50 rounded-lg min-h-20">
              {{ user.profile?.bio || '(No bio yet)' }}
            </p>
          </div>
          <div>
            <label class="block font-semibold text-gray-700 mb-2">Location</label>
            <p class="text-gray-600 p-4 bg-gray-50 rounded-lg">
              {{ user.profile?.location || '(No location set)' }}
            </p>
          </div>
        </div>

        <div v-else class="space-y-6">
          <div>
            <label class="block font-semibold text-gray-700 mb-2">Bio</label>
            <textarea
              v-model="bio"
              class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="4"
              placeholder="Tell us about yourself..."
            ></textarea>
          </div>
          <div>
            <label class="block font-semibold text-gray-700 mb-2">Location</label>
            <input
              v-model="location"
              type="text"
              class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="e.g., Miagao, Iloilo"
            />
          </div>
          <button
            @click="updateProfile"
            :disabled="loading"
            class="w-full px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition disabled:opacity-50"
          >
            {{ loading ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>

      <!-- Change Password Section -->
      <div class="bg-white shadow-lg rounded-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Change Password</h2>
        <div class="space-y-4">
          <div>
            <label class="block font-semibold text-gray-700 mb-2">Current Password</label>
            <input
              v-model="currentPassword"
              type="password"
              class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter current password"
            />
          </div>
          <div>
            <label class="block font-semibold text-gray-700 mb-2">New Password</label>
            <input
              v-model="newPassword"
              type="password"
              class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter new password"
            />
          </div>
          <div>
            <label class="block font-semibold text-gray-700 mb-2">Confirm Password</label>
            <input
              v-model="newPasswordConfirm"
              type="password"
              class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Confirm new password"
            />
          </div>
          <button
            @click="changePassword"
            :disabled="loading"
            class="w-full px-6 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition disabled:opacity-50"
          >
            {{ loading ? 'Updating...' : 'Change Password' }}
          </button>
        </div>
      </div>

      <!-- Delete Account Section -->
      <div class="bg-white shadow-lg rounded-lg p-8 border-2 border-red-200">
        <h2 class="text-2xl font-bold text-red-600 mb-4">Danger Zone</h2>
        <p class="text-gray-700 mb-6">
          Once you delete your account, there is no going back. Please be certain.
        </p>
        <button
          @click="deleteAccount"
          :disabled="loading"
          class="w-full px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition disabled:opacity-50 font-semibold"
        >
          {{ loading ? 'Deleting...' : 'Delete Account' }}
        </button>
      </div>

      <!-- Refresh Button -->
      <div class="flex justify-center">
        <button
          @click="fetchProfile"
          :disabled="loading"
          class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition"
        >
          🔄 Refresh Profile
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
input[type='file'] {
  cursor: pointer;
}
</style>
