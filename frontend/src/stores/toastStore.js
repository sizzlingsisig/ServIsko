// stores/toastStore.js
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'

export const useToastStore = defineStore('toast-store', () => {
  let toast = null

  const getToast = () => {
    if (!toast) {
      toast = useToast()
    }
    return toast
  }

  const success = (message, title = 'Success', life = 3000) => {
    const toastInstance = getToast()
    if (typeof message === 'object') {
      toastInstance. add({
        severity: 'success',
        summary: message.title || title,
        detail: message.text || message. message,
        life: message.timeout || life,
      })
    } else {
      toastInstance.add({
        severity: 'success',
        summary: title,
        detail: message,
        life,
      })
    }
  }

  const error = (message, title = 'Error', life = 5000) => {
    const toastInstance = getToast()
    if (typeof message === 'object') {
      toastInstance.add({
        severity: 'error',
        summary: message.title || title,
        detail: message.text || message.message,
        life: message.timeout || life,
      })
    } else {
      toastInstance.add({
        severity: 'error',
        summary: title,
        detail: message,
        life,
      })
    }
  }

  const warning = (message, title = 'Warning', life = 4000) => {
    const toastInstance = getToast()
    if (typeof message === 'object') {
      toastInstance.add({
        severity: 'warn',
        summary: message.title || title,
        detail: message.text || message.message,
        life: message.timeout || life,
      })
    } else {
      toastInstance.add({
        severity: 'warn',
        summary: title,
        detail: message,
        life,
      })
    }
  }

  const info = (message, title = 'Info', life = 3000) => {
    const toastInstance = getToast()
    if (typeof message === 'object') {
      toastInstance.add({
        severity: 'info',
        summary: message.title || title,
        detail: message.text || message.message,
        life: message.timeout || life,
      })
    } else {
      toastInstance.add({
        severity: 'info',
        summary: title,
        detail: message,
        life,
      })
    }
  }

  return {
    success,
    error,
    warning,
    info,
  }
})
