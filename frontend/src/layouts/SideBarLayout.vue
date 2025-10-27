<script setup></script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 min-h-screen">
    <!-- Left Side - Brand/Color Section -->
    <div
      class="bg-gradient-to-br from-primary-600 via-primary-500 to-primary-700 flex flex-col items-center justify-center p-12 relative overflow-hidden"
    >
      <!-- Content with higher z-index -->
      <div class="relative z-10 text-center space-y-3">
        <!-- Brand Name -->
        <div>
          <h1
            class="text-7xl md:text-8xl lg:text-9xl font-bold text-white leading-tight [text-shadow:_0_8px_24px_rgba(0,0,0,0.4)]"
          >
            Serv
          </h1>
          <h1
            class="text-7xl md:text-8xl lg:text-9xl font-bold text-white leading-tight [text-shadow:_0_8px_24px_rgba(0,0,0,0.4)]"
          >
            ISKO
          </h1>
        </div>

        <!-- Tagline -->
        <p class="text-sm md:text-base text-white/90 font-light tracking-wide max-w-md">
          Serbisyong Maaasahan, Para Sa Lahat
        </p>
      </div>
    </div>

    <!-- Right Side - Form Section -->
    <div class="flex items-center justify-center p-8 bg-gray-50">
      <div class="w-full max-w-md">
        <!-- Slot for different auth forms -->
        <slot><router-view></router-view></slot>
      </div>
    </div>
  </div>
</template>
