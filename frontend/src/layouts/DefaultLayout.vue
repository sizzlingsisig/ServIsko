<script setup>
import AppMenu from './components/MenuBar.vue'
import FooterLayout from './FooterLayout.vue'
</script>

<template>
  <AppMenu />

  <RouterView />

  <FooterLayout />
</template>

<style scoped></style>
