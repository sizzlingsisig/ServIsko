// src/composables/useProfileSetup.js
import { ref, computed } from 'vue'
import axios from '@/composables/axios'

const showModal = ref(false)
const profile = ref(null)
const loading = ref(false)

export function useProfileSetup() {
  const isProfileIncomplete = computed(() => {
    if (!profile.value) return false

    const { roles, provider_profile, profile: userProfile } = profile.value
    const isProvider = roles?.includes('service-provider')

    if (!isProvider) return false

    // Check if essential fields are missing
    const hasBio = userProfile?.bio && userProfile.bio.trim().length > 0
    const hasLocation = userProfile?.location && userProfile.location.trim().length > 0
    const hasSkills = provider_profile?.skills && provider_profile.skills.length > 0

    return !hasBio || !hasLocation || !hasSkills
  })

  const checkProfile = async () => {
    try {
      loading.value = true
      const { data } = await axios.get('/user')
      profile.value = data.data

      // Auto-show modal if profile is incomplete
      if (isProfileIncomplete.value) {
        showModal.value = true
      }

      return profile.value
    } catch (error) {
      console.error('Failed to check profile:', error)
      return null
    } finally {
      loading.value = false
    }
  }

  const openModal = () => {
    showModal.value = true
  }

  const closeModal = () => {
    showModal.value = false
  }

  const skipSetup = () => {
    showModal.value = false
    // Store in localStorage to not show again for this session
    localStorage.setItem('profileSetupSkipped', 'true')
  }

  const shouldShowModal = () => {
    const skipped = localStorage.getItem('profileSetupSkipped')
    return !skipped && isProfileIncomplete.value
  }

  return {
    showModal,
    profile,
    loading,
    isProfileIncomplete,
    checkProfile,
    openModal,
    closeModal,
    skipSetup,
    shouldShowModal
  }
}
