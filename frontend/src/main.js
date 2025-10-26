// main.js

import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// PrimeVue Setup
import PrimeVue from 'primevue/config'
import servisko from '@/composables/servisko.js'

// FormKit Setup
import { plugin as formKitPlugin, defaultConfig } from '@formkit/vue'
import formKitConfig from '../../frontend/formkit.config.js' // <-- Import the file you just created

const app = createApp(App)

app.use(createPinia())
app.use(router)

// Use PrimeVue
app.use(PrimeVue, {
  theme: {
    preset: servisko,
    options: {
      prefix: 'p',
      darkmodeSelector: 'system',
      cssLayer: false,
    },
  },
})

// Use FormKit with your custom Tailwind config
app.use(formKitPlugin, defaultConfig(formKitConfig))

app.mount('#app')
