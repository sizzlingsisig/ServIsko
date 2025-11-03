// composables/useAppLoading.js
import { ref } from 'vue'

const isLoading = ref(false)

export const useAppLoading = () => {
  const setLoading = (value) => {
    isLoading.value = value
  }

  return {
    isLoading,
    setLoading,
  }
}
