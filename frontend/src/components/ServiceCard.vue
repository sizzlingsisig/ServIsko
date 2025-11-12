<script setup>
import { computed } from 'vue'

const props = defineProps({
  service: {
    type: Object,
    required: true,
  },
  layout: {
    type: String,
    default: 'grid',
    validator: (value) => ['grid', 'list'].includes(value),
  },
})

const emit = defineEmits(['click', 'contact'])

const displayRating = computed(() => {
  const rating = props.service.rating || 0
  return (Math.round(rating * 10) / 10).toFixed(1)
})

const reviewCount = computed(() => {
  return props.service.reviews_count || 0
})

const isGridLayout = computed(() => props.layout === 'grid')
</script>

<template>
  <!-- Grid Layout -->
  <div
    v-if="isGridLayout"
    class="bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden cursor-pointer"
    @click="emit('click')"
  >
    <!-- Service Image -->
    <div class="relative h-48 bg-gray-200 overflow-hidden">
      <img
        v-if="service.image"
        :src="service.image"
        :alt="service.title"
        class="w-full h-full object-cover hover:scale-105 transition"
      />
      <div
        v-else
        class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#6d0019] to-[#8b0028]"
      >
        <i class="pi pi-image text-gray-300 text-4xl"></i>
      </div>

      <!-- Rating Badge -->
      <div
        class="absolute top-3 right-3 bg-white px-3 py-1 rounded-full text-sm font-semibold flex items-center gap-1"
      >
        <span class="text-[#6d0019]">{{ displayRating }}</span>
        <i class="pi pi-star-fill text-yellow-400 text-xs"></i>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Service Title -->
      <h3 class="font-bold text-lg mb-2 line-clamp-2 text-gray-800">{{ service.title }}</h3>

      <!-- Description -->
      <p class="text-gray-600 text-sm line-clamp-2 mb-3">{{ service.description }}</p>

      <!-- Provider Info -->
      <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200">
        <img
          v-if="service.provider_profile?.user?.profile_picture"
          :src="service.provider_profile.user.profile_picture"
          :alt="service.provider_profile.user.name"
          class="w-8 h-8 rounded-full object-cover"
        />
        <div
          v-else
          class="w-8 h-8 rounded-full bg-[#6d0019] flex items-center justify-center text-white text-xs"
        >
          {{ service.provider_profile?.user?.name?.charAt(0) || 'P' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-sm text-gray-800 truncate">
            {{ service.provider_profile?.user?.name || 'Unknown Provider' }}
          </p>
          <p class="text-xs text-gray-500">
            <i class="pi pi-map-marker text-xs"></i>
            {{ service.provider_profile?.user?.profile?.location || 'Location not set' }}
          </p>
        </div>
      </div>

      <!-- Price and Category -->
      <div class="flex items-center justify-between mb-4">
        <div>
          <p class="text-2xl font-bold text-[#6d0019]">
            ₱{{ service.price_per_hour || 'TBD' }}<span class="text-sm">/hr</span>
          </p>
        </div>
        <span
          v-if="service.category"
          class="text-xs bg-gray-100 text-gray-700 px-3 py-1 rounded-full"
        >
          {{ service.category }}
        </span>
      </div>

      <!-- Rating and Reviews -->
      <div class="flex items-center justify-between text-sm mb-4">
        <div class="flex items-center gap-1 text-gray-600">
          <i class="pi pi-star-fill text-yellow-400 text-xs"></i>
          <span>{{ reviewCount }} reviews</span>
        </div>
      </div>

      <!-- CTA Button -->
      <button
        class="w-full bg-[#6d0019] text-white py-2 rounded-lg font-semibold hover:bg-[#8b0028] transition"
        @click.stop="emit('contact')"
      >
        View Details
      </button>
    </div>
  </div>

  <!-- List Layout -->
  <div
    v-else
    class="bg-white rounded-lg shadow hover:shadow-lg transition p-4 flex gap-4 cursor-pointer"
    @click="emit('click')"
  >
    <!-- Service Image -->
    <div class="h-32 w-32 flex-shrink-0 bg-gray-200 rounded-lg overflow-hidden">
      <img
        v-if="service.image"
        :src="service.image"
        :alt="service.title"
        class="w-full h-full object-cover"
      />
      <div
        v-else
        class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#6d0019] to-[#8b0028]"
      >
        <i class="pi pi-image text-gray-300 text-3xl"></i>
      </div>
    </div>

    <!-- Content -->
    <div class="flex-1 flex flex-col justify-between">
      <div>
        <!-- Title and Rating -->
        <div class="flex items-start justify-between mb-2">
          <div class="flex-1">
            <h3 class="font-bold text-lg text-gray-800 mb-1">{{ service.title }}</h3>
            <p class="text-gray-600 text-sm line-clamp-2">{{ service.description }}</p>
          </div>
          <div class="flex items-center gap-1 ml-4 flex-shrink-0">
            <i class="pi pi-star-fill text-yellow-400 text-sm"></i>
            <span class="font-semibold text-gray-800">{{ displayRating }}</span>
            <span class="text-gray-500 text-sm">({{ reviewCount }})</span>
          </div>
        </div>

        <!-- Provider Info and Tags -->
        <div class="flex items-center gap-4 mb-3 text-sm">
          <div class="flex items-center gap-2">
            <img
              v-if="service.provider_profile?.user?.profile_picture"
              :src="service.provider_profile.user.profile_picture"
              :alt="service.provider_profile.user.name"
              class="w-6 h-6 rounded-full object-cover"
            />
            <div
              v-else
              class="w-6 h-6 rounded-full bg-[#6d0019] flex items-center justify-center text-white text-xs"
            >
              {{ service.provider_profile?.user?.name?.charAt(0) || 'P' }}
            </div>
            <span class="text-gray-700 font-semibold">
              {{ service.provider_profile?.user?.name || 'Unknown Provider' }}
            </span>
          </div>

          <div class="flex items-center gap-1 text-gray-500">
            <i class="pi pi-map-marker text-xs"></i>
            {{ service.provider_profile?.user?.profile?.location || 'Location not set' }}
          </div>

          <div v-if="service.category" class="text-gray-600 bg-gray-100 px-2 py-1 rounded">
            {{ service.category }}
          </div>
        </div>
      </div>

      <!-- Bottom section with price and button -->
      <div class="flex items-center justify-between pt-3 border-t border-gray-100">
        <div>
          <p class="text-xl font-bold text-[#6d0019]">
            ₱{{ service.price_per_hour || 'TBD' }}<span class="text-sm">/hr</span>
          </p>
        </div>
        <button
          class="bg-[#6d0019] text-white px-6 py-2 rounded-lg font-semibold hover:bg-[#8b0028] transition"
          @click.stop="emit('contact')"
        >
          Contact
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
