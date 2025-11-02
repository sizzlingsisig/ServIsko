<template>
  <component :is="currentLayout">
    <RouterView />
  </component>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
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
