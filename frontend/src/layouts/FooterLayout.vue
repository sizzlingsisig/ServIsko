<template>
  <footer role="contentinfo" class="bg-[#0f1724] text-white">
    <div class="max-w-7xl mx-auto px-6 md:px-8 py-12 lg:py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Brand / Description -->
        <div>
          <h3 class="text-3xl font-extrabold tracking-tight">ServISKO</h3>
          <p class="mt-3 text-sm text-gray-300 max-w-xs">
            Connecting service providers and service seekers through a trusted service marketplace
          </p>

          <div class="mt-6 flex items-center gap-3" aria-hidden>
            <button
              class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              aria-label="Instagram"
            >
              <i class="pi pi-camera"></i>
            </button>
            <button
              class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              aria-label="Facebook"
            >
              <i class="pi pi-facebook"></i>
            </button>
            <button
              class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              aria-label="Twitter"
            >
              <i class="pi pi-twitter"></i>
            </button>
          </div>
        </div>

        <!-- For Service Seekers -->
        <nav aria-label="For Service Seekers" class="text-sm text-gray-200">
          <h4 class="font-semibold mb-3">For Service Seekers</h4>
          <ul class="space-y-2">
            <li>
              <router-link to="/listings" class="text-gray-300 hover:text-white"
                >Browse Services</router-link
              >
            </li>
            <li>
              <router-link to="/request/create" class="text-gray-300 hover:text-white"
                >Post a Request</router-link
              >
            </li>
            <li>
              <router-link to="/how-it-works" class="text-gray-300 hover:text-white"
                >How it Works</router-link
              >
            </li>
            <li>
              <router-link to="/help" class="text-gray-300 hover:text-white"
                >Help Center</router-link
              >
            </li>
          </ul>
        </nav>

        <!-- For Service Providers -->
        <nav aria-label="For Service Providers" class="text-sm text-gray-200">
          <h4 class="font-semibold mb-3">For Service Providers</h4>
          <ul class="space-y-2">
            <li>
              <router-link to="/provider/register" class="text-gray-300 hover:text-white"
                >List your service</router-link
              >
            </li>
            <li>
              <router-link to="/tips" class="text-gray-300 hover:text-white"
                >Success tips</router-link
              >
            </li>
            <li>
              <router-link to="/support" class="text-gray-300 hover:text-white"
                >Provider Support</router-link
              >
            </li>
          </ul>
        </nav>

        <!-- Information -->
        <nav aria-label="Information" class="text-sm text-gray-200">
          <h4 class="font-semibold mb-3">Information</h4>
          <ul class="space-y-2">
            <li>
              <router-link to="/about" class="text-gray-300 hover:text-white">About us</router-link>
            </li>
            <li>
              <router-link to="/terms" class="text-gray-300 hover:text-white"
                >Terms and Conditions</router-link
              >
            </li>
            <li>
              <router-link to="/privacy" class="text-gray-300 hover:text-white"
                >Privacy Policy</router-link
              >
            </li>
            <li>
              <router-link to="/contact" class="text-gray-300 hover:text-white"
                >Contact us</router-link
              >
            </li>
          </ul>
        </nav>
      </div>

      <hr class="border-gray-700 my-8" />

      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-300">©2025 ServIsko. All Rights Reserved.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
// purely presentational
</script>

<style scoped>
/* Ensure links inside footer are clearly focusable */
.pi {
  font-size: 0.9rem;
}

a:focus {
  outline: 3px solid rgba(99, 102, 241, 0.6);
  outline-offset: 2px;
}
</style>
