<script setup>
import Card from 'primevue/card'
import Avatar from 'primevue/avatar'
import Panel from 'primevue/panel'
import Button from 'primevue/button'
import Accordion from 'primevue/accordion'
import AccordionTab from 'primevue/accordiontab'
import Tag from 'primevue/tag'

const props = defineProps({
  listing: {
    type: Object,
    required: true,
    default: () => ({
      title: 'Sample Listing Title',
      category: 'Category Name',
      location: 'City, Country',
      datePosted: new Date(),
      description: 'Detailed description of the listing goes here...',
      price: 100,
      image: null,
      tags: ['Remote', 'Flexible', 'Featured'],
      providerName: 'Provider Name',
      providerRole: 'Service Provider',
      providerAvatar: 'https://via.placeholder.com/48',
    }),
  },
})

function formatDate(date) {
  return new Date(date).toLocaleDateString()
}
function applyToListing() {
  /* ... */
}
function saveListing() {
  /* ... */
}
function shareListing() {
  /* ... */
}
</script>

<template>
  <div class="flex flex-col min-h-screen bg-gradient-to-br from-main-50 to-main-100 py-12 px-6">
    <div class="max-w-8xl mx-auto flex flex-col md:flex-row gap-12 px-6">
      <!-- Left: Info & Provider -->
      <Card class="flex-1 bg-white border-0 overflow-visible rounded-xl shadow-light p-8">
        <template #title>
          <div class="flex items-start gap-3 flex-wrap">
            <span class="text-3xl md:text-4xl font-bold text-main-700 leading-tight">{{
              listing.title
            }}</span>
          </div>
          <div class="flex gap-3 mt-3 flex-wrap">
            <Tag
              v-for="tag in listing.tags"
              :key="tag"
              :value="tag"
              class="bg-main-100 text-main-800 border-0 px-4 py-1.5 text-sm font-semibold rounded-full"
            />
          </div>
        </template>
        <template #subtitle>
          <div class="flex gap-4 flex-wrap items-center mt-4 text-main-600 text-lg font-medium">
            <span class="flex items-center gap-2">
              <i class="pi pi-tag text-main-500 text-xl"></i>
              {{ listing.category }}
            </span>
            <span class="text-main-300 text-2xl leading-none">•</span>
            <span class="flex items-center gap-2">
              <i class="pi pi-map-marker text-main-500 text-xl"></i>
              {{ listing.location }}
            </span>
          </div>
          <div class="text-main-400 text-base font-medium mt-3 flex items-center gap-2">
            <i class="pi pi-calendar text-main-400 text-lg"></i>
            <span>Posted on {{ formatDate(listing.datePosted) }}</span>
          </div>
        </template>
        <template #content>
          <!-- Image -->
          <div
            v-if="listing.image"
            class="mb-8 rounded-lg overflow-hidden shadow-image-sm transform transition hover:scale-[1.02]"
          >
            <img
              :src="listing.image"
              :alt="listing.title"
              class="w-full max-h-[340px] object-cover"
            />
          </div>

          <!-- Description -->
          <div class="mb-8 p-8 bg-main-50 rounded-lg shadow-light">
            <h2 class="font-bold text-main-700 mb-4 text-xl flex items-center gap-3">
              <i class="pi pi-info-circle text-main-500 text-2xl"></i>
              Description
            </h2>
            <p class="text-main-700 leading-relaxed text-lg">{{ listing.description }}</p>
          </div>

          <!-- Provider card -->
          <Panel
            header="About Provider"
            toggleable
            class="bg-main-50 rounded-lg shadow-light border-0 overflow-hidden p-6"
          >
            <div class="flex gap-6 items-center">
              <Avatar
                :image="listing.providerAvatar"
                shape="circle"
                size="xxlarge"
                class="shadow-avatar-sm"
              />
              <div>
                <div class="font-bold text-main-700 text-xl">{{ listing.providerName }}</div>
                <div class="text-main-500 text-base">{{ listing.providerRole }}</div>
              </div>
            </div>
          </Panel>
        </template>
        <template #footer>
          <div class="flex justify-end items-center gap-2 pt-6">
            <Button
              label="Report Listing"
              icon="pi pi-flag"
              text
              class="text-main-500 hover:bg-main-50 transition"
              style="font-size: 1rem"
            />
          </div>
        </template>
      </Card>

      <!-- Right: Actions / FAQ -->
      <div class="md:w-[380px] w-full flex flex-col gap-8 flex-shrink-0">
        <!-- Quick Actions Card -->
        <Card class="bg-white rounded-xl shadow-light border-0 overflow-hidden p-6">
          <template #title>
            <div class="flex items-center gap-3 text-xl font-bold text-main-700">
              <i class="pi pi-bolt text-main-600 text-2xl"></i>
              Quick Actions
            </div>
          </template>
          <template #content>
            <div class="flex flex-col gap-5">
              <!-- Price Badge -->
              <div
                class="bg-gradient-to-r from-main-500 to-main-600 text-white px-6 py-4 rounded-xl shadow-price-sm text-xl font-bold"
              >
                <div class="text-sm font-medium opacity-90 mb-1">Starting at</div>
                ₱{{ listing.price }}
              </div>

              <!-- Action Buttons -->
              <Button
                label="Quick Apply"
                icon="pi pi-send"
                class="w-full bg-gradient-to-r from-main-600 to-main-700 border-none text-white font-semibold py-4 rounded-xl shadow-sm hover:shadow transition-all"
                @click="applyToListing"
              />
              <Button
                label="Save Listing"
                icon="pi pi-bookmark"
                class="w-full bg-main-100 border border-main-300 text-main-700 font-semibold py-4 rounded-xl shadow-sm hover:shadow transition-all"
                outlined
                @click="saveListing"
              />
              <Button
                label="Share"
                icon="pi pi-share-alt"
                class="w-full bg-main-200 border-none text-main-700 font-semibold py-4 rounded-xl shadow-sm hover:shadow transition-all"
                outlined
                @click="shareListing"
              />
            </div>
          </template>
        </Card>

        <!-- FAQ Accordion -->
        <Accordion multiple class="bg-white rounded-xl shadow-light border-0 overflow-hidden">
          <AccordionTab header="Frequently Asked Questions" class="border-0">
            <div class="flex flex-col gap-6 p-4">
              <div
                class="flex items-start gap-4 p-4 bg-main-50 rounded-md hover:bg-main-100 transition cursor-pointer"
              >
                <i class="pi pi-question-circle text-main-500 mt-1 text-xl"></i>
                <div>
                  <div class="font-semibold text-main-700 text-base mb-2">Application Process</div>
                  <div class="text-main-600 text-sm">Click Quick Apply and fill out the form</div>
                </div>
              </div>
              <div
                class="flex items-start gap-4 p-4 bg-main-50 rounded-md hover:bg-main-100 transition cursor-pointer"
              >
                <i class="pi pi-envelope text-main-500 mt-1 text-xl"></i>
                <div>
                  <div class="font-semibold text-main-700 text-base mb-2">Contact Provider</div>
                  <div class="text-main-600 text-sm">Message directly after applying</div>
                </div>
              </div>
              <div
                class="flex items-start gap-4 p-4 bg-main-50 rounded-md hover:bg-main-100 transition cursor-pointer"
              >
                <i class="pi pi-clock text-main-500 mt-1 text-xl"></i>
                <div>
                  <div class="font-semibold text-main-700 text-base mb-2">Response Time</div>
                  <div class="text-main-600 text-sm">Usually within 24-48 hours</div>
                </div>
              </div>
            </div>
          </AccordionTab>
        </Accordion>
      </div>
    </div>
  </div>
</template>

<style scoped>
.shadow-light {
  box-shadow:
    0 1px 6px rgba(109, 0, 25, 0.07),
    0 8px 16px rgba(109, 0, 25, 0.04);
}
.shadow-image-sm {
  box-shadow: 0 2px 12px rgba(109, 0, 25, 0.06);
}
.shadow-avatar-sm {
  box-shadow: 0 1px 6px rgba(109, 0, 25, 0.07);
}
.shadow-price-sm {
  box-shadow: 0 2px 8px rgba(109, 0, 25, 0.07);
}
.shadow-sm {
  box-shadow: 0 1px 6px rgba(109, 0, 25, 0.05);
}
</style>
