<template>
  <div class="p-8">
    <h1 class="text-3xl font-bold mb-6">Working Toast Test</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <button
        @click="testSuccess"
        class="bg-green-500 hover:bg-green-600 text-white font-bold px-6 py-3 rounded"
      >
        Success
      </button>

      <button
        @click="testError"
        class="bg-red-500 hover:bg-red-600 text-white font-bold px-6 py-3 rounded"
      >
        Error
      </button>

      <button
        @click="testWarning"
        class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-3 rounded"
      >
        Warning
      </button>

      <button
        @click="testInfo"
        class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-6 py-3 rounded"
      >
        Info
      </button>
    </div>

    <div class="mt-8 bg-gray-100 p-4 rounded">
      <h3 class="font-bold mb-2">Debug Info:</h3>
      <p>Toast instance: {{ !!toast }}</p>
      <p>Toast has add: {{ typeof toast?. add === 'function' }}</p>
      <p class="mt-2 text-sm text-gray-600">
        Look at the top-right corner of your screen for toasts
      </p>
    </div>
  </div>
</template>

<script setup>
import { useToast } from 'primevue/usetoast'

const toast = useToast()

const testSuccess = () => {
  console.log('Calling success toast')
  toast.add({
    severity: 'success',
    summary: 'Success',
    detail: 'This is a success message! ',
    life: 3000
  })
  console.log('Success toast called')
}

const testError = () => {
  console.log('Calling error toast')
  toast.add({
    severity: 'error',
    summary: 'Error',
    detail: 'This is an error message! ',
    life: 3000
  })
  console.log('Error toast called')
}

const testWarning = () => {
  console.log('Calling warning toast')
  toast.add({
    severity: 'warn',
    summary: 'Warning',
    detail: 'This is a warning message!',
    life: 3000
  })
  console.log('Warning toast called')
}

const testInfo = () => {
  console. log('Calling info toast')
  toast.add({
    severity: 'info',
    summary: 'Info',
    detail: 'This is an info message!',
    life: 3000
  })
  console. log('Info toast called')
}
</script>
