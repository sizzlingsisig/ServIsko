<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Divider from 'primevue/divider'

const PRIMARY = '#6d0019'

const listing = ref(null)
const loading = ref(true)
const error = ref('')

const route = useRoute()
const listingId = computed(() => route.params.id)

const fetchListing = async (id) => {
  listing.value = null
  loading.value = true
  error.value = ''
  if (!id) {
    error.value = 'No listing specified.'
    loading.value = false
    return
  }
  try {
    const resp = await fetch(`http://localhost:8000/api/listings/${id}`)
    const data = await resp.json()
    if (data.success && data.data) {
      listing.value = data.data
    } else {
      error.value = data.message || 'Listing not found.'
    }
  } catch (e) {
    error.value = 'Error loading listing.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchListing(listingId.value)
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col items-center">
    <div class="w-full max-w-6xl flex gap-2 py-2">
      <!-- Main column: 3/4 -->
      <div class="flex-1 min-w-0">
        <Card class="m-0 border-0 p-0 no-card-padding">
          <template #header>
            <div class="flex flex-col gap-1 mb-2">
              <h1 class="text-2xl font-semibold">{{ listing?.title }}</h1>
              <div class="flex items-center gap-1">
                <span class="text-gray-600">By {{ listing?.seeker?.name || "Unknown" }}</span>
                <Tag
                  v-if="listing?.category"
                  :value="listing.category.name"
                  class="ml-1"
                  :style="{ background: PRIMARY, color: '#fff', border: 'none' }"
                />
                <Tag
                  v-if="listing?.status"
                  :value="listing.status"
                  class="ml-1"
                  :style="{
                    background: PRIMARY,
                    color: '#fff',
                    border: 'none',
                    opacity: (listing.status === 'expired' || listing.is_expired) ? 0.55 : 1
                  }"
                />
              </div>
            </div>
            <Divider :style="{ background: PRIMARY }" />
          </template>
          <template #content>
            <div class="mb-3">
              <div class="font-medium mb-1 text-lg" :style="{ color: PRIMARY }">About this Listing</div>
              <div class="text-gray-700 text-base whitespace-pre-line">{{ listing?.description || "No description." }}</div>
            </div>
            <Divider align="left">
              <span class="font-medium text-sm" :style="{ color: PRIMARY }">Comments</span>
            </Divider>
            <div>
              <div v-if="listing?.comments?.length">
                <div v-for="(comment, i) in listing.comments" :key="i" class="mb-2">
                  <b class="text-gray-600">{{ comment.author ?? 'Anonymous' }}</b>
                  <div class="text-xs text-gray-400">{{ comment.created_at ?? '' }}</div>
                  <div class="text-gray-700 mt-2">{{ comment.body }}</div>
                </div>
              </div>
              <div v-else class="text-xs text-gray-400">No comments yet.</div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Side column: 1/4 -->
      <div class="w-72 flex flex-col gap-2 min-w-0">
        <Card class="p-1 border-0 no-card-padding">
          <template #content>
            <div class="mb-2">
              <div class="font-semibold mb-1" :style="{ color: PRIMARY }">Budget</div>
              <div v-if="listing?.budget" class="text-2xl font-bold mb-1" :style="{ color: PRIMARY }">
                ₱{{ parseFloat(listing.budget).toLocaleString() }}
              </div>
              <div v-else class="text-gray-400 text-sm">No budget info.</div>
            </div>
            <Divider />
            <div class="flex flex-col gap-1">
              <Button
                label="Report this Listing"
                text
                rounded
                class="w-full font-medium border"
                icon="pi pi-exclamation-triangle"
                :style="{ color: PRIMARY, borderColor: PRIMARY }"
              />
              <Button
                label="Apply to this Listing"
                rounded
                class="w-full font-medium border-0"
                icon="pi pi-send"
                :disabled="listing?.status === 'expired' || listing?.is_expired"
                :style="{
                  background: PRIMARY,
                  color: '#fff',
                  opacity: (listing?.status === 'expired' || listing?.is_expired) ? 0.55 : 1
                }"
              />
            </div>
          </template>
        </Card>
      </div>
    </div>
    <div v-if="loading" class="fixed w-full h-full top-0 left-0 bg-white/60 flex items-center justify-center z-10">
      <span class="animate-pulse text-gray-500 text-lg">Loading listing...</span>
    </div>
    <div v-if="error" class="fixed w-full h-full top-0 left-0 flex items-center justify-center z-10 text-red-700 font-bold text-xl">
      {{ error }}
    </div>
  </div>
</template>

<style scoped>
</style>
