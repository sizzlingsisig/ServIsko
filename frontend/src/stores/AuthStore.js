import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore(
  'auth',
  () => {
    const user = ref(null)
    const token = ref(localStorage.getItem('authToken'))

    const isAuthenticated = computed(() => !!token.value)

    function setUser(data) {
      user.value = data
    }

    function setToken(tokenValue) {
      token.value = tokenValue
      if (tokenValue) {
        localStorage.setItem('authToken', tokenValue)
      } else {
        localStorage.removeItem('authToken')
      }
    }

    function clearUser() {
      user.value = null
      token.value = null
      localStorage.removeItem('authToken')
    }

    const getToken = () => token.value
    const getUser = () => user.value

    return {
      user,
      token,
      isAuthenticated,
      setUser,
      setToken,
      clearUser,
      getToken,
      getUser,
    }
  },
  {
    persist: true,
  },
)
