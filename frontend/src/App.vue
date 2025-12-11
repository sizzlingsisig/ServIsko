<template>
  <div>
    <Toast position="top-right" />
    <component :is="currentLayout">
      <RouterView />
    </component>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Toast from 'primevue/toast'
import SideBarLayout from '@/layouts/SideBarLayout.vue'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import BlankLayout from '@/layouts/BlankLayout.vue'
import ProfileLayout from './layouts/ProfileLayout.vue'

const route = useRoute()

const layoutComponents = {
  SideBarLayout,
  DefaultLayout,
  BlankLayout,
  ProfileLayout,
}

const currentLayout = computed(() => {
  return layoutComponents[route.meta.layout] || BlankLayout
})
</script>
