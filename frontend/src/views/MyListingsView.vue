<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <!-- Header Section -->
      <div
        class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 bg-white p-6 rounded-lg shadow-sm border-l-4 border-primary-500"
      >
        <div class="flex items-start gap-3">
          <div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
              {{ activeRole === 'seeker' ? 'My Listings' : 'My Applications' }}
            </h1>
            <p class="text-lg text-gray-600">
              {{
                activeRole === 'seeker'
                  ? 'Manage your service requests and listing status'
                  : 'Track your job applications and opportunities'
              }}
            </p>
          </div>
        </div>

        <!-- Role Toggle Buttons -->
        <div class="flex gap-3 mt-4 md:mt-0">
          <button
            @click="activeRole = 'seeker'"
            :class="[
              'px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center gap-2',
              activeRole === 'seeker'
                ? 'bg-primary-500 text-white shadow-md hover:bg-primary-600'
                : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
            ]"
          >
            <i class="pi pi-search"></i>
            Service Seeker
          </button>
          <button
            @click="activeRole = 'provider'"
            :class="[
              'px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center gap-2',
              activeRole === 'provider'
                ? 'bg-primary-500 text-white shadow-md hover:bg-primary-600'
                : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500',
            ]"
          >
            <i class="pi pi-briefcase"></i>
            Service Provider
          </button>
        </div>
      </div>

      <!-- Dynamic Content Based on Role -->
      <SeekerListingsView v-if="activeRole === 'seeker'" />
      <ProviderApplicationsView v-else />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import SeekerListingsView from './SeekerListingsView.vue'
import ProviderApplicationsView from './ProviderApplicationsView.vue'

const activeRole = ref('seeker')
</script>
