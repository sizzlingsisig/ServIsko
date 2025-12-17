<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/AuthStore.js'
import axios from '@/composables/axios'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const userMenu = ref(null)
const mobileMenuOpen = ref(false)

const items = [
  { label: 'Home', to: '/' },
  { label: 'Listings', to: '/listings' },
  { label: 'Service Providers', to: '/providers' },
  { label: 'Messages', to: '/messages' },
]

const userMenuItems = ref([
  {
    label: 'Profile',
    icon: 'pi pi-user',
    command: () => {
      router.push('/profile')
    },
  },
  {
    label: 'Settings',
    icon: 'pi pi-cog',
    command: () => {
      router.push('/settings')
    },
  },
  {
    separator: true,
  },
  {
    label: 'Logout',
    icon: 'pi pi-sign-out',
    command: () => handleLogout(),
  },
])

const profilePictureUrl = ref(null)
const userProfile = ref(null)

const loadUserProfile = async () => {
  try {
    const { data } = await axios.get('/user')
    userProfile.value = data.data
    await loadProfilePicture()
  } catch (error) {
    console.error('Failed to load user profile:', error)
  }
}

const loadProfilePicture = async () => {
  try {
    const response = await axios.get('/seeker/profile-picture', { responseType: 'blob' })
    if (response.data && response.data.size > 0) {
      profilePictureUrl.value = URL.createObjectURL(response.data)
    } else {
      profilePictureUrl.value = null
    }
  } catch (error) {
    profilePictureUrl.value = null
  }
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  if (isAuthenticated()) {
    loadUserProfile()
  }
})

const handleMobileLogin = () => {
  router.push('/login')
  mobileMenuOpen.value = false
}

const handleMobileRegister = () => {
  router.push('/register')
  mobileMenuOpen.value = false
}

const toggleUserMenu = (event) => {
  userMenu.value.toggle(event)
}

const handleLogout = async () => {
  try {
    await axios.post(
      '/logout',
      {},
      {
        headers: {
          Authorization: `Bearer ${authStore.getToken()}`,
        },
      },
    )

    authStore.clearUser()
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
    authStore.clearUser()
    router.push('/login')
  }
}

const isActiveRoute = (path) => {
  return route.path === path
}

const isAuthenticated = () => {
  return authStore.isAuthenticated
}

// Hide menu on scroll
const handleScroll = () => {
  if (userMenu.value) {
    userMenu.value.hide()
  }
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  if (profilePictureUrl.value) {
    URL.revokeObjectURL(profilePictureUrl.value)
  }
})
</script>

<template>
  <nav
    class="sticky top-0 overflow-visible z-50 backdrop-blur-lg bg-white/80 dark:bg-gray-900/80 border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm"
  >
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Left Section: Burger / Logo -->
        <div class="flex items-center">
          <button
            class="md:hidden flex items-center text-text-600 dark:text-text-300 focus:outline-none"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <i :class="mobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'" class="text-2xl"></i>
          </button>

          <router-link to="/" class="flex items-center space-x-2 sm:space-x-3 group ml-2 sm:ml-3">
            <span class="text-xl sm:text-2xl md:text-3xl font-black text-primary-500">ServISKO</span>
          </router-link>
        </div>

        <!-- Center Section: Menu Items (Desktop Only) -->
        <div class="hidden lg:flex items-center">
          <ul class="flex items-center gap-4 sm:gap-6">
            <RouterLink v-for="item in items" :key="item.to" :to="item.to" class="relative group">
              <li
                :class="[
                  'font-semibold transition-colors cursor-pointer',
                  isActiveRoute(item.to)
                    ? 'text-primary-500'
                    : 'text-text-600 hover:text-primary-500',
                ]"
              >
                {{ item.label }}
                <!-- Active underline -->
                <span
                  v-if="isActiveRoute(item.to)"
                  class="absolute -bottom-[21px] left-0 right-0 h-0.5 bg-primary-500"
                ></span>
                <!-- Hover underline -->
                <span
                  v-else
                  class="absolute -bottom-[21px] left-0 right-0 h-0.5 bg-primary-500 scale-x-0 group-hover:scale-x-100 transition-transform duration-200"
                ></span>
              </li>
            </RouterLink>
          </ul>
        </div>

        <!-- Right Section: Auth Buttons or User Menu -->
        <div class="flex items-center gap-2 sm:gap-4">
          <!-- Auth CTA Buttons (Not Authenticated) -->
          <div v-if="!isAuthenticated()" class="flex items-center gap-2 sm:gap-3">
            <Button
              label="Login"
              severity="secondary"
              text
              @click="router.push('/login')"
              class="hidden sm:inline-flex text-primary-600 hover:text-primary-700"
            />
            <Button
              label="Sign Up"
              @click="router.push('/register')"
              class="hidden sm:inline-flex"
            />
          </div>

          <!-- User Menu (Authenticated) -->
          <div v-else class="flex items-center gap-2 sm:gap-4">
            <!-- Help Icon -->
            <button
              class="flex items-center justify-center text-text-600 dark:text-text-300 hover:text-primary-500 dark:hover:text-primary-400 transition-colors cursor-pointer"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
                class="size-5 sm:size-6"
              >
                <path
                  fill-rule="evenodd"
                  d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm11.378-3.917c-.89-.777-2.366-.777-3.255 0a.75.75 0 0 1-.988-1.129c1.454-1.272 3.776-1.272 5.23 0 1.513 1.324 1.513 3.518 0 4.842a3.75 3.75 0 0 1-.837.552c-.676.328-1.028.774-1.028 1.152v.75a.75.75 0 0 1-1.5 0v-.75c0-1.279 1.06-2.107 1.875-2.502.182-.088.351-.199.503-.331.83-.727.83-1.857 0-2.584ZM12 18a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <!-- Notification Bell -->
            <button
              class="flex items-center justify-center text-text-600 dark:text-text-300 hover:text-primary-500 dark:hover:text-primary-400 transition-colors cursor-pointer"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
                class="size-5 sm:size-6"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.25 9a6.75 6.75 0 0 1 13.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 0 1-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 1 1-7.48 0 24.585 24.585 0 0 1-4.831-1.244.75.75 0 0 1-.298-1.205A8.217 8.217 0 0 0 5.25 9.75V9Zm4.502 8.9a2.25 2.25 0 1 0 4.496 0 25.057 25.057 0 0 1-4.496 0Z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <!-- User Avatar with Menu -->
            <button
              type="button"
              @click="toggleUserMenu"
              aria-haspopup="true"
              aria-controls="overlay_menu"
            >
              <Avatar
                v-if="profilePictureUrl"
                :image="profilePictureUrl"
                shape="circle"
                size="normal"
                class="cursor-pointer ring-2 w-8 h-8 sm:w-10 sm:h-10 ring-text-200 dark:ring-text-700 hover:ring-primary-500 transition-all duration-200"
              />
              <Avatar
                v-else
                :label="userProfile && userProfile.name ? userProfile.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2) : '?'"
                shape="circle"
                size="normal"
                class="cursor-pointer ring-2 w-8 h-8 sm:w-10 sm:h-10 ring-text-200 dark:ring-text-700 hover:ring-primary-500 transition-all duration-200 bg-[#6d0019] text-white font-bold"
              />
            </button>

            <Menu ref="userMenu" id="overlay_menu" :model="userMenuItems" :popup="true" />
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Menu Overlay (Fixed Position) -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="mobileMenuOpen"
        class="fixed inset-0 bg-black/50 z-40"
        @click="mobileMenuOpen = false"
      ></div>
    </Transition>

    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 -translate-x-full"
      enter-to-class="opacity-100 translate-x-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 translate-x-0"
      leave-to-class="opacity-0 -translate-x-full"
    >
      <div
        v-if="mobileMenuOpen"
        class="fixed top-16 left-0 w-64 sm:w-72 h-[calc(100vh-4rem)] bg-white dark:bg-gray-900 shadow-2xl z-50 lg:hidden overflow-y-auto"
      >
        <!-- Menu Header -->
        <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-primary-50 to-primary-100">
          <h3 class="text-base sm:text-lg font-bold text-primary-700 dark:text-primary-300">Menu</h3>
          <p class="text-xs text-text-500 dark:text-text-400 mt-1">Navigate your experience</p>
        </div>

        <!-- Menu Items -->
        <div class="px-2 sm:px-4 py-4 space-y-1">
          <RouterLink
            v-for="item in items"
            :key="item.to"
            :to="item.to"
            @click="mobileMenuOpen = false"
            :class="[
              'flex items-center gap-2 sm:gap-3 px-3 sm:px-4 py-3 rounded-xl font-semibold transition-all duration-200 group',
              isActiveRoute(item.to)
                ? 'bg-primary-500 text-white shadow-md shadow-primary-200'
                : 'text-text-700 dark:text-text-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 hover:pl-5',
            ]"
          >
            <!-- Icon indicator -->
            <span
              :class="[
                'w-1.5 h-1.5 rounded-full transition-all duration-200',
                isActiveRoute(item.to) ? 'bg-white' : 'bg-text-300 group-hover:bg-primary-500',
              ]"
            ></span>
            <span>{{ item.label }}</span>
          </RouterLink>
        </div>

        <!-- Auth CTA Buttons for Mobile (Not Authenticated) -->
        <div
          v-if="!isAuthenticated()"
          class="px-2 sm:px-4 py-4 border-t border-text-200 dark:border-text-700"
        >
          <div class="flex flex-col gap-2">
            <Button label="Login" severity="secondary" class="w-full" @click="handleMobileLogin" />
            <Button label="Sign Up" class="w-full" @click="handleMobileRegister" />
          </div>
        </div>

        <!-- Menu Footer -->
        <div
          class="absolute bottom-0 left-0 right-0 px-4 sm:px-6 py-4 bg-gradient-to-t from-text-50 to-transparent dark:from-gray-800 dark:to-transparent"
        >
          <p class="text-xs text-text-500 text-center">ServISKO © 2025</p>
        </div>
      </div>
    </Transition>
  </nav>
</template>

<style scoped>
body {
  overflow-x: hidden;
}
</style>
