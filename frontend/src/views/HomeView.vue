<script setup>
import CurvedCards from '@/layouts/components/CurvedCards.vue'
</script>

<template>
  <section
    class="relative bg-[#6d0019] text-left px-8 pt-32 pb-32 overflow-hidden h-[65vh] flex items-center justify-start"
  >
    <div class="max-w-[800px]">
      <h1 class="text-6xl text-white font-extrabold leading-tight mb-4 -mt-[120px]">
        Services by Students,<br />
        for Students
      </h1>

      <p class="text-[#f3e9e9] text-lg leading-relaxed max-w-[600px] mb-8">
        ServIsko connects Miagao's students and locals, making it easy to find, offer, and review
        trusted services in one convenient platform.
      </p>

      <div class="flex gap-4">
        <Button label="Find Services" />
        <Button label="Offer Services" outlined />
      </div>
    </div>

    <svg
      class="absolute bottom-0 left-0 w-full h-[250px] block pointer-events-none z-0"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 1440 320"
      preserveAspectRatio="none"
    >
      <path fill="#ffffff" d="M0,320 C480,160 960,160 1440,320 L1440,320 L0,320 Z" />
    </svg>
  </section>

  <section
    class="bg-white text-center px-8 py-20 -mt-[100px] min-h-[35vh] flex items-center justify-center z-[3]"
  >
    <div class="max-w-7xl mx-auto w-full">
      <h2 class="text-3xl font-bold mb-1 text-[#2a2a2a] mt-3 text-center">
        Search & Browse Services
      </h2>
      <p class="text-[#555] mb-6 text-center">
        Lorem ipsum dolor sit amet, consectetur adipiscing elitiiiii
      </p>

      <div class="bg-gray-100 p-8 rounded-2xl">
        <div class="flex gap-4 w-full">
          <input
            type="text"
            placeholder="Search for services or providers..."
            class="flex-1 px-6 py-4 border-2 border-gray-300 rounded text-base focus:outline-none focus:border-[#6d0019] bg-white"
          />
          <Button label="Search" />
        </div>
      </div>
    </div>
  </section>

  <section class="bg-[#f9f9f9] text-center px-4 md:px-8 py-12 md:py-20 w-full">
    <div class="max-w-[700px] mx-auto mb-8 md:mb-12 text-center">
      <h2 class="text-2xl md:text-3xl font-bold mb-2 text-[#2a2a2a]">Why Choose ServIsko?</h2>
      <p class="text-sm md:text-base text-[#555]">
        Connecting students and locals for trusted services.
      </p>
    </div>
    <div class="why-cards pb-8 md:pb-0">
      <div class="flex flex-wrap justify-center gap-6 md:gap-8">
        <div
          v-animateonscroll="{
            enterClass: 'animate-enter fade-in-10 zoom-in-50 animate-duration-1000',
          }"
          class="flex flex-col bg-white text-[#680723] border-[#680723] border-2 shadow-lg justify-center items-center w-full sm:max-w-80 rounded-2xl p-6 md:p-8 gap-4 transition-all duration-300 hover:scale-110 hover:bg-[#680723] hover:text-white"
        >
          <div
            class="rounded-full border-2 border-[#680723] w-12 h-12 flex items-center justify-center"
          >
            <i class="pi pi-wifi !text-2xl"></i>
          </div>
          <span class="text-xl md:text-2xl font-bold">Bandwidth</span>
          <span class="text-center text-sm md:text-base"
            >Lorem ipsum dolor sit amet consectetur adipisicing elit.</span
          >
        </div>
        <div
          v-animateonscroll="{
            enterClass: 'animate-enter fade-in-10 zoom-in-75 animate-duration-1000',
          }"
          class="flex flex-col bg-white text-[#680723] border-[#680723] border-2 shadow-lg justify-center items-center w-full sm:max-w-80 rounded-2xl p-6 md:p-8 gap-4 transition-all duration-300 hover:scale-110 hover:bg-[#680723] hover:text-white"
        >
          <div
            class="rounded-full border-2 border-[#680723] w-12 h-12 flex items-center justify-center"
          >
            <i class="pi pi-database !text-2xl"></i>
          </div>
          <span class="text-xl md:text-2xl font-bold">Storage</span>
          <span class="text-center text-sm md:text-base"
            >Lorem ipsum dolor sit amet consectetur adipisicing elit.</span
          >
        </div>
        <div
          v-animateonscroll="{
            enterClass: 'animate-enter fade-in-10 zoom-in-50 animate-duration-1000',
          }"
          class="flex flex-col bg-white text-[#680723] border-[#680723] border-2 shadow-lg justify-center items-center w-full sm:max-w-80 rounded-2xl p-6 md:p-8 gap-4 transition-all duration-300 hover:scale-110 hover:bg-[#680723] hover:text-white hover:border-white"
        >
          <div
            class="rounded-full border-2 border-[#680723] w-12 h-12 flex items-center justify-center"
          >
            <i class="pi pi-arrows-v !text-2xl"></i>
          </div>
          <span class="text-xl md:text-2xl font-bold">Requests</span>
          <span class="text-center text-sm md:text-base"
            >Lorem ipsum dolor sit amet consectetur adipisicing elit.</span
          >
        </div>
      </div>
    </div>
  </section>

  <section class="w-full py-8 md:py-16 bg-white">
    <div
      class="text-center w-[95%] md:w-[85%] lg:w-[80%] mx-auto my-8 md:my-12 shadow-[0_0_10px_rgba(0,0,0,0.4)] rounded-lg pb-4 md:pb-5"
    >
      <h2 class="font-bold text-2xl md:text-3xl pt-4 md:pt-12 lg:pt-10 px-4">How ServIsko Works</h2>

      <div
        class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 max-w-6xl mx-auto px-4 md:px-8 py-8 md:py-12"
      >
        <!-- left column: For Service Seekers -->
        <div class="ml-0 lg:ml-15">
          <h2 class="text-2xl md:text-2xl font-bold mb-6 md:mb-8 text-left">For Service Seekers</h2>

          <div class="space-y-6 md:space-y-8 text-left">
            <div class="flex gap-3 md:gap-4">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#8B1538] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg"
              >
                1
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold">Browse & Search</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Explore thousands of services or use our advanced filters to find exactly what you
                  need
                </p>
              </div>
            </div>

            <div class="flex gap-3 md:gap-4">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#8B1538] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg"
              >
                2
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold">Connect & Communicate</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Message service providers directly to discuss your requirements and negotiate
                  terms
                </p>
              </div>
            </div>

            <div class="flex gap-3 md:gap-4">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#8B1538] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg"
              >
                3
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold">Get Service & Review</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Receive quality service and leave feedback to help our community grow
                </p>
              </div>
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-2xl md:text-2xl font-bold mb-6 md:mb-8 text-left">
            For Service Providers
          </h2>

          <div class="space-y-6 md:space-y-8 text-left">
            <div class="flex gap-3 md:gap-4">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#1e5128] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg"
              >
                1
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold">Create Your Profile</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Showcase your skills, experience, and portfolio to attract potential clients
                </p>
              </div>
            </div>

            <div class="flex gap-3 md:gap-4">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#1e5128] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg"
              >
                2
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold">List Your Services</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Post detailed service offerings with competitive pricing and clear deliverables
                </p>
              </div>
            </div>

            <div class="flex gap-3 md:gap-4">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#1e5128] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg"
              >
                3
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold">Deliver & Earn</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Complete projects successfully and build your reputation while earning money
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-[#f0f0f0] w-full h-screen">
    <h2>Featured Services</h2>
    <p>Lorem ipsum dolor sit amet, consectetur adipisicing</p>
    <div class="services-carousel">
      <Carousel
        :value="products"
        :numVisible="3"
        :numScroll="1"
        :responsiveOptions="responsiveOptions"
        circular
        :autoplayInterval="3000"
      >
        <template #item="slotProps">
          <div class="border border-surface-200 dark:border-surface-700 rounded m-2 p-4">
            <div class="mb-4">
              <div class="relative mx-auto">
                <img
                  :src="
                    'https://primefaces.org/cdn/primevue/images/product/' + slotProps.data.image
                  "
                  :alt="slotProps.data.name"
                  class="w-full rounded"
                />
                <Tag
                  :value="slotProps.data.inventoryStatus"
                  :severity="getSeverity(slotProps.data.inventoryStatus)"
                  class="absolute"
                  style="left: 5px; top: 5px"
                />
              </div>
            </div>
            <div class="mb-4 font-medium">{{ slotProps.data.name }}</div>
            <div class="flex justify-between items-center">
              <div class="mt-0 font-semibold text-xl">${{ slotProps.data.price }}</div>
              <span>
                <Button icon="pi pi-heart" severity="secondary" variant="outlined" />
                <Button icon="pi pi-shopping-cart" class="ml-2" />
              </span>
            </div>
          </div>
        </template>
      </Carousel>
    </div>
  </section>

  <section class="pb-0 bg-white text-center">
    <h2 class="text-3xl font-bold mb-2 mt-15">Popular Service Categories</h2>
    <p class="text-gray-600 mb-1">Lorem ipsum dolor sit amet, consectetur adipiscing elit</p>
    <CurvedCards class="mb-[-80px] mt-[-20px]" />
  </section>

  <section class="relative bg-[#6d0019] text-white text-center pt-25 pb-20 overflow-hidden">
    <svg
      class="absolute top-0 left-0 w-full h-[250px] block pointer-events-none z-0"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 1440 320"
      preserveAspectRatio="none"
    >
      <path fill="#ffffff" d="M0,0 L0,160 C480,320 960,320 1440,160 L1440,0 Z" />
    </svg>

    <div class="relative z-10 mt-50">
      <h2 class="text-5xl font-bold mb-28">Putting the service in Iskolars ng Bayan</h2>
      <div class="flex flex-col md:flex-row justify-center gap-24 md:gap-28 stats">
        <div>
          <h3 class="text-6xl font-extrabold mb-5">300+</h3>
          <p class="text-xl">services offered</p>
        </div>
        <div>
          <h3 class="text-6xl font-extrabold mb-5">95%</h3>
          <p class="text-xl">satisfaction rate from successful users</p>
        </div>
        <div>
          <h3 class="text-6xl font-extrabold mb-5">50+</h3>
          <p class="text-xl">verified service providers</p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.stats {
  position: relative;
}
</style>
