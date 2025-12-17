<script setup>
import beautyImage from '@/assets/icons/4.png'
import designImage from '@/assets/icons/6.png'
import techImage from '@/assets/icons/5.png'
import writingImage from '@/assets/icons/3.png'
import tutoringImage from '@/assets/icons/2.png'

const cards = [
  {
    id: 1,
    title: 'Tutoring & Education',
    subtitle: 'Get peer help with courses, exams, and study skills.',
    image: tutoringImage,
    gradient: 'from-blue-500 to-indigo-600',
  },
  {
    id: 2,
    title: 'Writing',
    subtitle: 'Polish essays, reports, resumes, and creative work.',
    image: writingImage,
    gradient: 'from-purple-500 to-pink-600',
  },
  {
    id: 3,
    title: 'Beauty and Cosmetics',
    subtitle: 'Book student stylists for hair, makeup, and grooming.',
    image: beautyImage,
    gradient: 'from-pink-500 to-rose-600',
  },
  {
    id: 4,
    title: 'Programming and Tech',
    subtitle: 'Get coding help, troubleshooting, and basic tech support.',
    image: techImage,
    gradient: 'from-emerald-500 to-teal-600',
  },
  {
    id: 5,
    title: 'Design and Creatives',
    subtitle: 'Hire students for graphics, layouts, and visual content.',
    image: designImage,
    gradient: 'from-amber-500 to-orange-600',
  },
]
</script>

<template>
  <section class="relative bg-gradient-to-b from-gray-50 to-white py-20 overflow-hidden">
    <!-- Decorative background blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-100 rounded-full opacity-20 blur-3xl"></div>
      <div class="absolute top-1/2 -right-24 w-96 h-96 bg-purple-100 rounded-full opacity-20 blur-3xl"></div>
    </div>

    <!-- Header -->
    <div class="relative text-center mb-16 px-4">
      <h2 class="text-5xl font-bold bg-gradient-to-r from-gray-900 pb-2 to-gray-700 bg-clip-text text-transparent mb-4 tracking-tight">
        Popular Listing Categories
      </h2>
      <p class="text-lg text-gray-600 max-w-2xl mx-auto">
        Take a sneak peek at some of our categories
      </p>
    </div>

    <!-- Cards Grid -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
        <div
          v-for="card in cards"
          :key="card.id"
          class="group relative h-full"
        >
          <!-- Card Container with Curved Bottom -->
          <div class="relative bg-white rounded-t-2xl overflow-visible shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3 h-full flex flex-col">
            <!-- Image Container - Fixed Height -->
            <div class="relative w-full h-64 overflow-hidden bg-gray-100 flex-shrink-0">
              <img
                :src="card.image"
                :alt="card.title"
                class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700"
              />
              <!-- Gradient Overlay -->
              <div
                class="absolute inset-0 bg-gradient-to-t opacity-0 group-hover:opacity-90 transition-opacity duration-500"
              ></div>
            </div>

            <!-- Content Area - Fixed Height -->
            <div class="relative bg-white px-5 pt-5 pb-8 flex-shrink-0" style="height: 140px;">
              <h3 class="text-base font-bold text-gray-900 mb-2 leading-tight group-hover:text-[#6d0019] transition-colors duration-300 line-clamp-2">
                {{ card.title }}
              </h3>
              <p class="text-sm text-gray-600 leading-relaxed line-clamp-3">
                {{ card.subtitle }}
              </p>
            </div>

            <!-- Curved Bottom SVG -->
            <div class="absolute bottom-0 left-0 right-0 h-6 bg-white pointer-events-none">
              <svg
                viewBox="0 0 200 20"
                preserveAspectRatio="none"
                class="absolute bottom-0 w-full h-6"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M0,0 Q50,20 100,15 T200,0 L200,20 L0,20 Z"
                  fill="white"
                  class="transition-all duration-500"
                />
              </svg>
            </div>

            <!-- Border -->
            <div class="absolute inset-0 rounded-t-2xl border-2 border-gray-100 group-hover:border-gray-200 transition-colors duration-300 pointer-events-none"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Smooth animations */
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.group:hover {
  animation: float 3s ease-in-out infinite;
}

/* Line clamping for consistent text heights */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
