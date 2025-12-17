<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import CurvedCards from '@/layouts/components/CurvedCards.vue'
import ListingCard from '@/components/ListingCard.vue'
import Carousel from 'primevue/carousel'
import api from '@/composables/axios'
import { useToast } from 'primevue/usetoast'

const router = useRouter()

const toast = useToast()
const featuredServices = ref([])
const loading = ref(false)

// homepage search text
const homeSearch = ref('')

const responsiveOptions = ref([
  {
    breakpoint: '1024px',
    numVisible: 3,
    numScroll: 1,
  },
  {
    breakpoint: '768px',
    numVisible: 2,
    numScroll: 1,
  },
  {
    breakpoint: '560px',
    numVisible: 1,
    numScroll: 1,
  },
])

const loadFeaturedServices = async () => {
  try {
    loading.value = true
    const response = await api.get('/listings', {
      params: {
        per_page: 10,
        sort_by: 'newest',
      },
    })

    if (response.data.success && response.data.data) {
      featuredServices.value = response.data.data.data || []
      console.log('Featured services loaded:')
    } else {
      featuredServices.value = []
      console.log('No featured services found.')
    }
  } catch (error) {
    console.error('Failed to load featured services:', error)
    console.log('Failed to load featured services')
    featuredServices.value = []
  } finally {
    loading.value = false
  }
}

// redirect to listings with ?search=
const handleHomeSearch = () => {
  router.push({
    name: 'listings', // or path: '/listings'
    query: {
      search: homeSearch.value || '',
    },
  })
}

onMounted(() => {
  loadFeaturedServices()
})
</script>

<template>
  <section
    class="relative bg-[#670723] text-left px-4 sm:px-8 pt-24 sm:pt-32 pb-20 sm:pb-32 overflow-hidden min-h-[50vh] sm:h-[65vh] flex items-center justify-start z-[0]"
  >
    <div class="max-w-[800px] w-full">
      <h1
        class="text-3xl sm:text-6xl text-white font-extrabold leading-tight mb-4 -mt-12 sm:-mt-[120px]"
      >
        Services by Students,<br />
        for Students
      </h1>

      <p class="text-[#f3e9e9] text-base sm:text-lg leading-relaxed max-w-[600px] mb-8">
        ServISKO connects UPV's students, making it easy to find, offer, and review trusted services
        in one convenient platform.
      </p>

      <div class="flex flex-col sm:flex-row gap-4 w-full max-w-xs sm:max-w-none">
        <RouterLink to="/providers" class="w-full sm:w-auto">
          <button
            class="px-8 py-3 bg-white text-[#6d0019] font-semibold rounded hover:bg-gray-100 transition-colors w-full sm:w-auto"
          >
            Find Services
          </button>
        </RouterLink>
        <RouterLink to="/listings" class="w-full sm:w-auto">
          <button
            class="px-8 py-3 bg-transparent border-2 border-white text-white font-semibold rounded hover:bg-white hover:text-[#6d0019] transition-colors w-full sm:w-auto"
          >
            Offer Services
          </button>
        </RouterLink>
      </div>
    </div>

    <svg
      class="absolute bottom-0 left-0 w-full h-auto block pointer-events-none z-0"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 1440 320"
      preserveAspectRatio="none"
      width="100%"
      height="auto"
    >
      <path fill="#ffffff" d="M0,320 C480,240 960,240 1440,320 L1440,321 L0,321 Z" />
    </svg>
  </section>

  <section
    class="bg-white text-center px-2 sm:px-8 mt py-10 sm:py-20 -mt-[60px] sm:-mt-[100px] min-h-[35vh] flex items-center justify-center z-[3]"
  >
    <div class="max-w-7xl mx-auto w-full">
      <h2 class="text-2xl sm:text-3xl font-bold mb-1 text-[#2a2a2a] mt-3 text-center">
        Search & Browse Services
      </h2>
      <p class="text-[#555] mb-6 text-center text-sm sm:text-base">
        Find the right listings for your capabilities or needs from our diverse categories
      </p>

      <div class="rounded-2xl">
        <div class="flex flex-col sm:flex-row gap-4 w-full">
          <input
            v-model="homeSearch"
            type="text"
            placeholder="Search for services listings..."
            class="flex-1 px-4 sm:px-6 py-3 sm:py-4 border-2 border-gray-300 rounded text-base focus:outline-none focus:border-[#6d0019] bg-white"
            @keyup.enter="handleHomeSearch"
          />
          <Button label="Search" class="w-full sm:w-auto" @click="handleHomeSearch" />
        </div>
      </div>
    </div>
  </section>

  <section class="bg-white text-center px-2 sm:px-4 md:px-8 py-8 md:py-20 w-full">
    <div class="max-w-[700px] mx-auto mb-8 md:mb-12 text-center">
      <h2 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2 text-[#2a2a2a]">
        Why Choose servISKO?
      </h2>
      <p class="text-xs sm:text-sm md:text-base text-[#555]">
        Find trusted student-run services quickly — search, compare, and hire with confidence.
      </p>
    </div>
    <div class="why-cards pb-8 md:pb-0">
      <div class="flex flex-wrap justify-center gap-4 md:gap-6 lg:gap-8">
        <div
          v-animateonscroll="{
            enterClass: 'animate-enter fade-in-10 zoom-in-50 animate-duration-1000',
          }"
          class="flex flex-col bg-white text-[#680723] border-[#680723] border-2 shadow-lg justify-center items-center w-full sm:max-w-80 rounded-2xl p-6 md:p-8 gap-4 transition-all duration-300 hover:scale-105 hover:-translate-y-2 hover:shadow-xl"
        >
          <div
            class="rounded-full border-2 border-[#680723] w-12 h-12 flex items-center justify-center"
          >
            <i class="pi pi-wifi !text-2xl"></i>
          </div>
          <span class="text-xl md:text-2xl font-bold">Trusted Service Providers</span>
          <span class="text-center text-sm md:text-base"
            >Verified students providing reliable services with ratings and reviews to help you
            choose confidently.</span
          >
        </div>
        <div
          v-animateonscroll="{
            enterClass: 'animate-enter fade-in-10 zoom-in-75 animate-duration-1000',
          }"
          class="flex flex-col bg-white text-[#680723] border-[#680723] border-2 shadow-lg justify-center items-center w-full sm:max-w-80 rounded-2xl p-6 md:p-8 gap-4 transition-all duration-300 hover:scale-105 hover:-translate-y-2 hover:shadow-xl"
        >
          <div
            class="rounded-full border-2 border-[#680723] w-12 h-12 flex items-center justify-center"
          >
            <i class="pi pi-database !text-2xl"></i>
          </div>
          <span class="text-xl md:text-2xl font-bold">Transparent Pricing</span>
          <span class="text-center text-sm md:text-base"
            >Clear service listings and upfront prices— compare offers and pick the best fit for
            your budget.</span
          >
        </div>
        <div
          v-animateonscroll="{
            enterClass: 'animate-enter fade-in-10 zoom-in-50 animate-duration-1000',
          }"
          class="flex flex-col bg-white text-[#680723] border-[#680723] border-2 shadow-lg justify-center items-center w-full sm:max-w-80 rounded-2xl p-6 md:p-8 gap-4 transition-all duration-300 hover:scale-105 hover:-translate-y-2 hover:shadow-xl"
        >
          <div
            class="rounded-full border-2 border-[#680723] w-12 h-12 flex items-center justify-center"
          >
            <i class="pi pi-arrows-v !text-2xl"></i>
          </div>
          <span class="text-xl md:text-2xl font-bold">Easy Booking & Communication</span>
          <span class="text-center text-sm md:text-base"
            >Message providers directly, schedule jobs, and manage requests all in one place for a
            smooth booking experience.</span
          >
        </div>
      </div>
    </div>
  </section>

   <section
    class="w-full py-8 md:py-16 bg-white"
  >
    <div
      class="text-center w-[98%] sm:w-[95%] md:w-[85%] lg:w-[80%] mx-auto my-8 md:my-12 shadow-[0_0_10px_rgba(109,0,25,0.2)] rounded-lg pb-4 md:pb-5 border-t-4 border-[#6d0019]"
    >
      <h2 class="font-bold text-xl sm:text-2xl md:text-3xl pt-4 md:pt-12 lg:pt-10 px-2 sm:px-4">
        How <span class="text-[#6d0019]">ServISKO</span> Works
      </h2>

      <div
        class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 max-w-6xl mx-auto px-2 sm:px-4 md:px-8 py-8 md:py-12"
      >
        <div
          v-animateonscroll="{
            enterClass: 'animate-enter slide-right animate-duration-800',
          }"
          class="ml-0 lg:ml-15"
        >
          <h2 class="text-2xl md:text-2xl font-bold mb-6 md:mb-8 text-left text-[#8B1538]">For Service Seekers</h2>

          <div class="space-y-6 md:space-y-8 text-left">
            <div class="flex gap-3 md:gap-4 group">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#8B1538] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
              >
                1
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold group-hover:text-[#8B1538] transition-colors">Browse & Search</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Explore thousands of services or use our advanced filters to find exactly what you
                  need
                </p>
              </div>
            </div>

            <div class="flex gap-3 md:gap-4 group">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#8B1538] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
              >
                2
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold group-hover:text-[#8B1538] transition-colors">Connect with Providers</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Review profiles, compare options, and message providers to discuss your needs
                </p>
              </div>
            </div>

            <div class="flex gap-3 md:gap-4 group">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#8B1538] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
              >
                3
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold group-hover:text-[#8B1538] transition-colors">Get Service & Review</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Receive quality service and leave feedback to help our community grow
                </p>
              </div>
            </div>
          </div>
        </div>

        <div
          v-animateonscroll="{
            enterClass: 'animate-enter slide-left animate-duration-800',
          }"
        >
          <h2 class="text-2xl md:text-2xl font-bold mb-6 md:mb-8 text-left text-[#1e5128]">
            For Service Providers
          </h2>

          <div class="space-y-6 md:space-y-8 text-left">
            <div class="flex gap-3 md:gap-4 group">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#1e5128] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
              >
                1
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold group-hover:text-[#1e5128] transition-colors">Create Your Profile</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Showcase your skills, experience, and portfolio to attract potential clients
                </p>
              </div>
            </div>

            <div class="flex gap-3 md:gap-4 group">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#1e5128] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
              >
                2
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold group-hover:text-[#1e5128] transition-colors">List Your Services</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Post detailed service listings with pricing and clear deliverables
                </p>
              </div>
            </div>

            <div class="flex gap-3 md:gap-4 group">
              <div
                class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-[#1e5128] text-white rounded-full flex items-center justify-center font-bold text-base md:text-lg transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
              >
                3
              </div>
              <div>
                <h3 class="text-lg md:text-l font-bold group-hover:text-[#1e5128] transition-colors">Deliver & Earn</h3>
                <p class="text-sm md:text-base text-gray-600">
                  Complete listings successfully and build your reputation while earning money
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <section class="w-full py-8 sm:py-16">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 md:px-8">
      <div class="text-center mb-8 sm:mb-12">
        <div class="inline-flex items-center gap-2 mb-4">
          <div class="w-8 h-1 bg-[#6d0019] rounded-full"></div>
          <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">
            Featured <span class="text-[#6d0019]">Listings</span>
          </h2>
          <div class="w-8 h-1 bg-[#6d0019] rounded-full"></div>
        </div>
        <p class="text-gray-600 text-base sm:text-lg">
          Discover trending listings from our community
        </p>
      </div>

      <div v-if="loading" class="flex justify-center items-center h-48 sm:h-96">
        <div class="text-gray-500">Loading listings...</div>
      </div>

      <div v-else-if="featuredServices.length > 0" class="services-carousel">
        <Carousel
          :value="featuredServices"
          :numVisible="1"
          :numScroll="1"
          :responsiveOptions="responsiveOptions"
          circular
          :autoplayInterval="5000"
        >
          <template #item="slotProps">
            <div class="px-1 sm:px-2">
              <ListingCard :service="slotProps.data" layout="grid" />
            </div>
          </template>
        </Carousel>
      </div>

      <div v-else class="text-center py-8 sm:py-12">
        <p class="text-gray-500 text-base sm:text-lg">No listings available at the moment</p>
      </div>
    </div>
  </section>

  <section class="pb-0 bg-white text-center">
    <CurvedCards class="mb-[-50px] sm:mb-[-100px] mt-[-40px] sm:mt-[-20px]" />
  </section>

  <section
    class="relative bg-[#670723] text-white text-center sm:pt-25 pb-10 sm:pb-20 overflow-hidden"
  >
    <svg
      class="absolute top-0 left-0 w-full h-auto block pointer-events-none z-0"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 1440 320"
      preserveAspectRatio="none"
      width="100%"
      height="auto"
    >
      <path fill="#ffffff" d="M0,0 L0,100 C480,200 960,200 1440,100 L1440,0 Z" />
    </svg>

    <div class="relative z-10 mt-20 sm:mt-50">
      <h2 class="text-2xl sm:text-6xl font-bold mb-10 sm:mb-28">
        Putting the service in Iskolars ng Bayan
      </h2>
      <div class="flex flex-col md:flex-row justify-center gap-12 md:gap-24 lg:gap-28 stats">
        <div>
          <h3 class="text-4xl sm:text-6xl font-extrabold mb-2 sm:mb-5">300+</h3>
          <p class="text-base sm:text-xl">services offered</p>
        </div>
        <div>
          <h3 class="text-4xl sm:text-6xl font-extrabold mb-2 sm:mb-5">95%</h3>
          <p class="text-base sm:text-xl">satisfaction rate from successful users</p>
        </div>
        <div>
          <h3 class="text-4xl sm:text-6xl font-extrabold mb-2 sm:mb-5">50+</h3>
          <p class="text-base sm:text-xl">verified service providers</p>
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
