<!-- components/ProfileCardHeader.vue -->
<template>
  <div class="bg-primary-500 p-4 flex items-center justify-between rounded-md">
    <div class="flex items-center gap-4">
      <!-- Avatar with Upload -->
      <div class="relative group cursor-pointer" @click="emit('open-picture-modal')">
        <div
          class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden border-2 border-white hover:opacity-80 transition-opacity"
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

        <!-- Upload Overlay -->
        <div
          class="absolute inset-0 bg-opacity-0 group-hover:bg-opacity-40 rounded-full flex items-center justify-center transition-all"
        >
          <i
            class="pi pi-upload text-white opacity-0 group-hover:opacity-100 transition-opacity text-lg"
          ></i>
        </div>
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
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import Tag from 'primevue/tag'

defineProps({
  profileName: {
    type: String,
    required: true,
  },
  profileRoles: {
    type: Array,
    required: true,
  },
  profilePictureUrl: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['open-picture-modal'])
</script>

<style scoped>
:deep(.p-tag) {
  background-color: #000000 !important;
  color: #ffffff !important;
}

:deep(.p-tag .p-tag-value) {
  color: #ffffff !important;
}
</style>
