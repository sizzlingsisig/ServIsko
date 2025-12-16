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
  if (route.meta.requiresAuth !== false) {
    await checkProfile()
  }
})

// Re-check profile when navigating to authenticated routes
watch(() => route.path, async (newPath) => {
  // Skip auth and public pages
  const publicPaths = ['/login', '/register', '/forgot-password', '/reset-password']
  if (!publicPaths.includes(newPath) && route.meta.requiresAuth !== false) {
    const skipped = localStorage.getItem('profileSetupSkipped')
    if (!skipped) {
      await checkProfile()
    }
  }
})

const handleSetupCompleted = async () => {
  console.log('✅ Profile setup completed!')
  // Optionally reload profile data across the app
  await checkProfile()
}
</script>
