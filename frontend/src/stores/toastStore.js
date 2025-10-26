import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'

export const useToastStore = defineStore('toast', () => {
  let toastInstance = null

  // Initialize toast (call this once in App.vue)
  const initToast = () => {
    toastInstance = useToast()
  }

  // Success toast
  const showSuccess = (message, title = 'Success', life = 3000) => {
    if (!toastInstance) return
    toastInstance.add({
      severity: 'success',
      summary: title,
      detail: message,
      group: 'br',
      life,
    })
  }

  // Error toast
  const showError = (message, title = 'Error', life = 5000) => {
    if (!toastInstance) return
    toastInstance.add({
      severity: 'error',
      summary: title,
      detail: message,
      group: 'br',
      life,
    })
  }

  // Warning toast
  const showWarning = (message, title = 'Warning', life = 4000) => {
    if (!toastInstance) return
    toastInstance.add({
      severity: 'warn',
      summary: title,
      detail: message,
      group: 'br',
      life,
    })
  }

  // Info toast
  const showInfo = (message, title = 'Info', life = 3000) => {
    if (!toastInstance) return
    toastInstance.add({
      severity: 'info',
      summary: title,
      detail: message,
      group: 'br',
      life,
    })
  }

  // Custom toast with full control
  const showCustom = (options) => {
    if (!toastInstance) return
    toastInstance.add({
      group: 'br',
      life: 3000,
      ...options,
    })
  }

  // Clear all toasts
  const clearAll = () => {
    if (!toastInstance) return
    toastInstance.removeAllGroups()
  }

  return {
    initToast,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showCustom,
    clearAll,
  }
})
