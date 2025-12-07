// composables/useToastHelper.js
import { useToast } from 'primevue/usetoast'

export function useToastHelper() {
  const toast = useToast()

  const success = (message, summary = 'Success', life = 3000) => {
    if (typeof message === 'object') {
      toast.add({
        severity: 'success',
        summary: message.title || message.summary || summary,
        detail: message.text || message.detail || message. message,
        life: message.timeout || message.life || life,
      })
    } else {
      toast.add({
        severity: 'success',
        summary: summary,
        detail: message,
        life: life,
      })
    }
  }

  const error = (message, summary = 'Error', life = 5000) => {
    if (typeof message === 'object') {
      toast.add({
        severity: 'error',
        summary: message.title || message. summary || summary,
        detail: message.text || message.detail || message.message,
        life: message.timeout || message.life || life,
      })
    } else {
      toast.add({
        severity: 'error',
        summary: summary,
        detail: message,
        life: life,
      })
    }
  }

  const warning = (message, summary = 'Warning', life = 4000) => {
    if (typeof message === 'object') {
      toast.add({
        severity: 'warn',
        summary: message.title || message. summary || summary,
        detail: message.text || message.detail || message.message,
        life: message.timeout || message.life || life,
      })
    } else {
      toast.add({
        severity: 'warn',
        summary: summary,
        detail: message,
        life: life,
      })
    }
  }

  const info = (message, summary = 'Info', life = 3000) => {
    if (typeof message === 'object') {
      toast.add({
        severity: 'info',
        summary: message.title || message.summary || summary,
        detail: message. text || message.detail || message. message,
        life: message. timeout || message.life || life,
      })
    } else {
      toast.add({
        severity: 'info',
        summary: summary,
        detail: message,
        life: life,
      })
    }
  }

  return {
    success,
    error,
    warning,
    info,
  }
}
