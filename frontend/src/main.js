// main.js
import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// PrimeVue
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'
import servisko from '@/composables/servisko.js'

// FormKit
import { plugin as formKitPlugin, defaultConfig } from '@formkit/vue'
import formKitConfig from '../../frontend/formkit.config.js'

const app = createApp(App)

app.use(createPinia())
app.use(router)

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

app.use(ToastService)

app.use(formKitPlugin, defaultConfig(formKitConfig))

app.mount('#app')
console.log('App mounted')
