<!-- layouts/AppLayout.vue -->
<script setup>
import MenuBar from './components/MenuBar.vue'
</script>

<template>
  <div>
    <!-- Your Navbar Component -->
    <MenuBar />
    <nav class="flex items-center bg-primary-500 px-6 py-4 shadow-lg">
      <ul class="flex gap-2 sm:ml-4 md:ml-5 lg:ml-6 xl:ml-35">
        <li>
          <RouterLink
            to="/profile"
            class="group px-4 py-2 text-white font-bold transition-all duration-300 relative inline-block"
            active-class="border-b-2 border-white"
          >
            My Profile
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-white rounded-full origin-left transition-all duration-300 ease-out"
              :class="[
                $route.path === '/profile' ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100',
              ]"
            />
          </RouterLink>
        </li>
        <li>
          <RouterLink
            to="/profile/listings"
            class="group px-4 py-2 text-white font-medium transition-all duration-300 relative inline-block"
            active-class="border-b-2 border-white"
          >
            Listings
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-white rounded-full origin-left transition-all duration-300 ease-out"
              :class="[
                $route.path === '/profile/listings' ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100',
              ]"
            />
          </RouterLink>
        </li>
        <li>
          <RouterLink
            to="/security"
            class="group px-4 py-2 text-white font-medium transition-all duration-300 relative inline-block"
            active-class="border-b-2 border-white"
          >
            Security Settings
            <span
              class="absolute bottom-0 left-0 w-full h-0.5 bg-white rounded-full origin-left transition-all duration-300 ease-out"
              :class="[
                $route.path === '/security' ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100',
              ]"
            />
          </RouterLink>
        </li>
      </ul>
    </nav>

    <!-- Page Content -->
    <main class="p-6">
      <RouterView />
    </main>
  </div>
</template>
