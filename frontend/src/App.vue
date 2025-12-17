<template>
  <div>
    <Toast position="top-right" />
    <component :is="currentLayout">
      <RouterView />
    </component>

    <!-- Global Profile Setup Modal -->
    <ProfileSetupModal
      v-model:visible="showModal"
      @completed="handleSetupCompleted"
    />
  </div>
</template>

<script setup>

import { useAuthStore } from '@/stores/AuthStore'
const userStore = useAuthStore()
import { computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import Toast from 'primevue/toast'
import SideBarLayout from '@/layouts/SideBarLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import BlankLayout from '@/layouts/BlankLayout.vue'
import ProfileLayout from './layouts/ProfileLayout.vue'
import ProfileSetupModal from '@/components/ProfileSetupModal.vue'
import { useProfileSetup } from '@/composables/useProfileSetup'

const route = useRoute()
const { showModal, checkProfile, isProfileIncomplete } = useProfileSetup()

const layoutComponents = {
  SideBarLayout,
  DefaultLayout,
  BlankLayout,
  ProfileLayout,
}

const currentLayout = computed(() => {
  return layoutComponents[route.meta.layout] || BlankLayout
})

// Check profile on mount and route changes

onMounted(async () => {
  // Only check if user is authenticated
  try {
    if (route.meta.requiresAuth !== false && userStore.isAuthenticated) {
      await checkProfile()
    }
  } catch (err) {
    console.error('Profile check failed on mount:', err)
  }
})

// Re-check profile when navigating to authenticated routes
watch(
  () => route.fullPath,
  async (newPath, oldPath) => {
    // Skip auth and public pages
    const publicPaths = ['/login', '/register', '/forgotpassword', '/resetpassword']
    try {
      if (!publicPaths.includes(newPath) && route.meta.requiresAuth !== false) {
        const skipped = localStorage.getItem('profileSetupSkipped')
        if (!skipped) {
          await checkProfile()
        }
      }
    } catch (err) {
      console.error('Profile check failed on route change:', err)
    }
  }
)

const handleSetupCompleted = async () => {
  console.log('✅ Profile setup completed!')
  try {
    await checkProfile()
  } catch (err) {
    console.error('Profile check failed after setup:', err)
  }
}
</script>
