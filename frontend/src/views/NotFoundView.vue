<template>
  <main class="min-h-screen flex items-center justify-center bg-[#6d0019] p-6">
    <section class="max-w-4xl w-full bg-white rounded-lg shadow-md overflow-hidden">
      <div class="flex flex-col md:flex-row items-center">
        <!-- Illustration / Graphic -->
        <div class="w-full md:w-1/2 bg-[#e9f3f7] flex items-center justify-center p-8">
          <!-- simple inline illustration themed to ServISKO colors -->
          <svg
            width="280"
            height="200"
            viewBox="0 0 560 400"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden
          >
            <rect x="40" y="80" width="480" height="240" rx="12" fill="#f8fafc" stroke="#cbd5e1" />
            <rect x="70" y="110" width="420" height="160" rx="6" fill="#0f1724" />
            <g transform="translate(150, 40)" fill="#ffffff">
              <rect x="0" y="0" width="120" height="20" rx="4" opacity="0.06" />
              <rect x="0" y="30" width="120" height="10" rx="3" opacity="0.06" />
            </g>
            <g transform="translate(70,260)" fill="#6d0019">
              <rect x="0" y="0" width="420" height="20" rx="6" />
            </g>
            <!-- small workers -->
            <circle cx="120" cy="160" r="18" fill="#6d0019" />
            <rect x="110" y="182" width="20" height="32" rx="4" fill="#6d0019" />
            <path d="M420 100 L440 120" stroke="#6d0019" stroke-width="6" stroke-linecap="round" />
          </svg>
        </div>

        <!-- Text -->
        <div class="w-full md:w-1/2 p-8 md:p-10">
          <h1 class="text-2xl md:text-3xl font-extrabold text-[#0f1724] mb-3">
            Undergoing Renovations
          </h1>
          <p class="text-sm text-gray-600 mb-6">
            Looks like the page you're trying to reach isn't available yet. We're actively building
            new features — thanks for your patience!
          </p>

          <ul class="text-sm text-gray-700 mb-6 space-y-2">
            <li class="flex items-start gap-3">
              <span class="inline-block w-2 h-2 bg-[#6d0019] rounded-full mt-2" aria-hidden></span>
              <span
                >Find trusted student-run and local services on the
                <router-link to="/" class="text-[#6d0019] font-semibold">home page</router-link
                >.</span
              >
            </li>
            <li class="flex items-start gap-3">
              <span class="inline-block w-2 h-2 bg-[#6d0019] rounded-full mt-2" aria-hidden></span>
              <span
                >Need assistance? Contact us at
                <a href="mailto:support@servisko.local" class="text-[#6d0019] font-semibold"
                  >support@servisko.local</a
                >.</span
              >
            </li>
          </ul>

          <div class="flex gap-3">
            <router-link
              to="/"
              class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-[#6d0019] text-white font-semibold hover:bg-[#5a0013] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6d0019]"
              aria-label="Go to home"
              >Go to Home</router-link
            >
            <router-link
              to="/listings"
              class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100"
              aria-label="Browse listings"
              >Browse Listings</router-link
            >
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
// Presentational only
</script>

<style scoped>
main {
  background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
}
</style>
