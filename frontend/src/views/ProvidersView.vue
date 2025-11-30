<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import InputText from 'primevue/inputtext'
import Paginator from 'primevue/paginator'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import FilterSidebar from '@/components/FilterSidebar.vue'
import ProviderCard from '@/components/ProviderCard.vue'
import { useToastStore } from '@/stores/toastStore'
import api from '@/composables/axios'

const toastStore = useToastStore()
const layout = ref('grid')
const loading = ref(false)
const totalRecords = ref(0)
const currentPage = ref(1)
const itemsPerPage = ref(12)

const filters = reactive({
  search: '',
  location: '',
  skill: [],
  category_id: null,
  min_price: null,
  max_price: null,
  sort_by: 'newest',
})

const providers = ref([])
const paginatedProviders = computed(() => providers.value)

const loadProviders = async () => {
  try {
    loading.value = true
    // Clean up params
    const params = {
      search: filters.search,
      location: filters.location,
      skill: filters.skill,
      category_id: filters.category_id,
      min_price: filters.min_price,
      max_price: filters.max_price,
      sort_by: filters.sort_by,
      page: currentPage.value,
      per_page: itemsPerPage.value,
    }
    Object.keys(params).forEach(key => {
      if (
        params[key] === null ||
        params[key] === '' ||
        typeof params[key] === 'undefined' ||
        (Array.isArray(params[key]) && params[key].length === 0)
      ) {
        delete params[key]
      }
    })
    const response = await api.get('/providers', { params })
    // Use snake_case field access as returned by the API
    if (response.data.success && response.data.data) {
      const paginatedData = response.data.data
      providers.value = paginatedData.data || []
      totalRecords.value = paginatedData.total || 0
    } else {
      providers.value = []
      totalRecords.value = 0
      toastStore.showError('No providers found', 'Error')
    }
  } catch (error) {
    toastStore.showError('Failed to load providers', 'Error')
    providers.value = []
    totalRecords.value = 0
  } finally {
    loading.value = false
  }
}

const handleSearch = (value) => {
  filters.search = value
  currentPage.value = 1
  loadProviders()
}

const handleFilterChange = (newFilters) => {
  Object.assign(filters, newFilters)
  currentPage.value = 1
  loadProviders()
}

const handlePageChange = (event) => {
  currentPage.value = event.page + 1
  loadProviders()
}

const handleSortChange = (sortBy) => {
  filters.sort_by = sortBy
  currentPage.value = 1
  loadProviders()
}

onMounted(() => {
  loadProviders()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <section class="bg-[#6d0019] text-white px-4 py-8">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl font-bold mb-6">Find Service Providers</h2>
        <div class="flex gap-4">
          <IconField icon-position="left" class="flex-1">
            <InputIcon class="pi pi-search"></InputIcon>
            <InputText
              v-model="filters.search"
              placeholder="Search for providers..."
              class="w-full"
              @input="handleSearch($event.target.value)"
            />
          </IconField>
          <button class="bg-black text-white px-4 py-2 rounded" @click="handleSearch(filters.search)">Search</button>
        </div>
      </div>
    </section>
    <div class="max-w-7xl mx-auto px-4 py-8">
      <div class="flex gap-6">
        <div class="w-75 flex-shrink-0">
          <FilterSidebar
            :filters="filters"
            @update="handleFilterChange"
          />
        </div>
        <div class="flex-1">
          <div class="flex items-center justify-between mb-6">
            <div class="text-gray-600">
              Showing
              {{ totalRecords === 0 ? 0 : ((currentPage - 1) * itemsPerPage + 1) }}
              -
              {{ Math.min(currentPage * itemsPerPage, totalRecords) }}
              of {{ totalRecords }} providers
            </div>
            <div class="flex items-center gap-4">
              <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Sort by:</label>
                <select
                  :value="filters.sort_by"
                  @change="handleSortChange($event.target.value)"
                  class="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                >
                  <option value="newest">Newest</option>
                  <option value="oldest">Oldest</option>
                  <option value="name">Name</option>
                </select>
              </div>
              <div class="flex gap-1">
                <button
                  @click="layout = 'grid'"
                  :class="[
                    'px-3 py-2 rounded-lg transition',
                    layout === 'grid'
                      ? 'bg-[#6d0019] text-white'
                      : 'bg-gray-200 text-gray-600 hover:bg-gray-300',
                  ]"
                >
                  <i class="pi pi-th-large"></i>
                </button>
                <button
                  @click="layout = 'list'"
                  :class="[
                    'px-3 py-2 rounded-lg transition',
                    layout === 'list'
                      ? 'bg-[#6d0019] text-white'
                      : 'bg-gray-200 text-gray-600 hover:bg-gray-300',
                  ]"
                >
                  <i class="pi pi-list"></i>
                </button>
              </div>
            </div>
          </div>
          <div v-if="loading" class="flex items-center justify-center py-12">
            <i class="pi pi-spin pi-spinner text-4xl text-[#6d0019]"></i>
          </div>
          <div v-else-if="paginatedProviders.length === 0" class="text-center py-12">
            <i class="pi pi-inbox text-5xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-600 text-lg">No providers found. Try adjusting your filters.</p>
          </div>
          <div
            v-else-if="layout === 'grid'"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <ProviderCard
              v-for="provider in paginatedProviders"
              :key="provider.id"
              :provider="provider"
              layout="grid"
            />
          </div>
          <div v-else class="space-y-4">
            <ProviderCard
              v-for="provider in paginatedProviders"
              :key="provider.id"
              :provider="provider"
              layout="list"
            />
          </div>
          <div class="mt-8 flex justify-center">
            <Paginator
              :rows="itemsPerPage"
              :total-records="totalRecords"
              :first="(currentPage - 1) * itemsPerPage"
              :rows-per-page-options="[12, 24, 36]"
              @page="handlePageChange"
              class="flex justify-center"
            ></Paginator>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
