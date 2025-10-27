<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const userMenu = ref(null)
const mobileMenuOpen = ref(false)

const items = [
  { label: 'Home', to: '/' },
  { label: 'Listings', to: '/listings' },
  { label: 'Service Providers', to: '/providers' },
  { label: 'Messages', to: '/messages' },
]

const userMenuItems = [
  { label: 'Profile', icon: 'pi pi-user', command: () => router.push('/profile') },
  { label: 'Settings', icon: 'pi pi-cog', command: () => router.push('/settings') },
  { separator: true },
  { label: 'Logout', icon: 'pi pi-sign-out', command: () => handleLogout() },
]

const toggleUserMenu = (event) => userMenu.value.toggle(event)
const handleLogout = () => {
  console.log('Logged out')
  router.push('/login')
}

const isActiveRoute = (path) => {
  return route.path === path
}
</script>

<template>
  <nav
    class="sticky top-0 overflow-visible z-50 backdrop-blur-lg bg-white/80 dark:bg-gray-900/80 border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Left Section: Burger / Logo -->
        <div class="flex items-center">
          <button
            class="md:hidden flex items-center text-text-600 dark:text-text-300 focus:outline-none"
            @click="mobileMenuOpen = !mobileMenuOpen"
          >
            <i :class="mobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'" class="text-2xl"></i>
          </button>

          <router-link to="/" class="flex items-center space-x-3 group ml-3">
            <span class="text-2xl md:text-3xl font-black text-primary-500">ServISKO</span>
          </router-link>
        </div>

        <!-- Center Section: Menu Items (Desktop Only) -->
        <div class="hidden lg:flex items-center">
          <ul class="flex items-center gap-6">
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

        <!-- Right Section: Icons + Avatar -->
        <div class="flex items-center gap-4">
          <!-- Help Icon -->
          <button
            class="flex items-center justify-center text-text-600 dark:text-text-300 hover:text-primary-500 dark:hover:text-primary-400 transition-colors cursor-pointer"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="currentColor"
              class="size-6"
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
              class="size-6"
            >
              <path
                fill-rule="evenodd"
                d="M5.25 9a6.75 6.75 0 0 1 13.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 0 1-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 1 1-7.48 0 24.585 24.585 0 0 1-4.831-1.244.75.75 0 0 1-.298-1.205A8.217 8.217 0 0 0 5.25 9.75V9Zm4.502 8.9a2.25 2.25 0 1 0 4.496 0 25.057 25.057 0 0 1-4.496 0Z"
                clip-rule="evenodd"
              />
            </svg>
          </button>

          <!-- User Avatar -->
          <div class="relative">
            <button @click="toggleUserMenu">
              <Avatar
                image="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png"
                shape="circle"
                size="normal"
                class="cursor-pointer ring-2 w-10 h-10 ring-text-200 dark:ring-text-700 hover:ring-primary-500 transition-all duration-200"
              />
            </button>
            <Menu
              ref="userMenu"
              :model="userMenuItems"
              popup
              appendTo="body"
              :popupOptions="{
                placement: 'bottom-end',
                flip: true,
                autoZIndex: true,
                viewportMargin: 8,
              }"
              class="bg-white shadow-lg rounded-md w-56"
            >
              <template #start>
                <div class="px-4 py-3 border-b border-text-200 dark:border-text-700">
                  <p class="text-sm font-semibold text-text-900 dark:text-white">Amy Elsner</p>
                  <p class="text-xs text-text-500 dark:text-text-400 mt-0.5">amy@example.com</p>
                </div>
              </template>
            </Menu>
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
    </Transition>

    <!-- Mobile Menu Popover (Fixed Position) -->
    <!-- Mobile Menu Popover (Fixed Position) -->
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
        class="fixed top-16 left-0 w-72 h-[calc(100vh-4rem)] bg-white dark:bg-gray-900 shadow-2xl z-50 lg:hidden overflow-y-auto"
      >
        <!-- Menu Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-primary-50 to-primary-100">
          <h3 class="text-lg font-bold text-primary-700 dark:text-primary-300">Menu</h3>
          <p class="text-xs text-text-500 dark:text-text-400 mt-1">Navigate your experience</p>
        </div>

        <!-- Menu Items -->
        <div class="px-4 py-4 space-y-1">
          <RouterLink
            v-for="item in items"
            :key="item.to"
            :to="item.to"
            @click="mobileMenuOpen = false"
            :class="[
              'flex items-center gap-3 px-4 py-3.5 rounded-xl font-semibold transition-all duration-200 group',
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

        <!-- Menu Footer (Optional) -->
        <div
          class="absolute bottom-0 left-0 right-0 px-6 py-4 bg-gradient-to-t from-text-50 to-transparent dark:from-gray-800 dark:to-transparent"
        >
          <p class="text-xs text-text-500 text-center">ServISKO © 2025</p>
        </div>
      </div>
    </Transition>
  </nav>
</template>

<style scoped>
/* Remove overflow-x from body if needed */
body {
  overflow-x: hidden;
}
</style>
