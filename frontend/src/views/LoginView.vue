<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/composables/axios'
import { useAuthStore } from '@/stores/AuthStore'
import { useToastStore } from '@/stores/toastStore'

const router = useRouter()
const authStore = useAuthStore()
const toastStore = useToastStore()

// Loading state
const loading = ref(false)

// Form data
const loginData = ref({})

// Handle login
const handleLogin = async () => {
  loading.value = true

  try {
    const response = await api.post('/api/login', {
      email: loginData.value.email,
      password: loginData.value.password,
    })

    // Store auth data using AuthStore
    authStore.setToken(response.data.token)
    authStore.setUser(response.data.user)

    // Show success toast
    toastStore.showSuccess('Welcome back!')

    // Redirect based on role
    const userRole = response.data.user.role
    if (userRole === 'admin' || userRole === 'moderator') {
      router.push('/admin/dashboard')
    } else {
      router.push('/dashboard')
    }
  } catch (err) {
    // Error handled by axios interceptor, but show specific message if available
    if (err.response?.data?.message) {
      toastStore.showError(err.response.data.message)
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex items-center justify-center p-8 bg-gray-50 min-h-screen">
    <div class="w-full max-w-md">
      <!-- Login Form -->
      <div class="text-center mb-8">
        <h2 class="text-3xl font-bold mb-2 text-gray-900">Welcome Back</h2>
        <p class="text-gray-600">Sign in to continue to ServISKO</p>
      </div>

      <FormKit
        type="form"
        v-model="loginData"
        @submit="handleLogin"
        :actions="false"
        :incomplete-message="false"
      >
        <div class="space-y-5">
          <FormKit
            type="email"
            name="email"
            label="Email Address"
            placeholder="user@example.com"
            validation="required|email"
            validation-visibility="blur"
            :validation-messages="{
              required: 'Email is required.',
              email: 'Please enter a valid email address.',
            }"
            outer-class="!max-w-full"
          />

          <FormKit
            type="password"
            name="password"
            label="Password"
            placeholder="Enter your password"
            validation="required"
            validation-visibility="blur"
            :validation-messages="{
              required: 'Password is required.',
            }"
            outer-class="!max-w-full"
          />

          <!-- Forgot Password Link -->
          <div class="text-right">
            <router-link
              to="/forgotpassword"
              class="text-sm text-primary-600 hover:text-primary-700 hover:underline"
            >
              Forgot password?
            </router-link>
          </div>

          <!-- Submit Button -->
          <Button
            type="submit"
            label="Sign In"
            icon="pi pi-sign-in"
            class="w-full mt-6"
            size="large"
            severity="primary"
            :loading="loading"
            :disabled="loading"
          />
        </div>
      </FormKit>

      <!-- Divider -->
      <div class="relative my-8">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="px-4 bg-gray-50 text-gray-500">Or</span>
        </div>
      </div>

      <!-- Register Link -->
      <div class="text-center">
        <p class="text-sm text-gray-600">
          Don't have an account?
          <router-link
            to="/register"
            class="text-primary-600 hover:text-primary-700 hover:underline font-semibold"
          >
            Create an account
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* FormKit styling */
:deep(.formkit-outer) {
  margin-bottom: 0;
}

:deep(.formkit-label) {
  font-size: 0.875rem;
  margin-bottom: 0.375rem;
  color: #374151;
  font-weight: 500;
}

:deep(.formkit-messages) {
  min-height: 1.25rem;
  margin-top: 0.25rem;
}

:deep(.formkit-message) {
  color: #dc2626;
  font-size: 0.75rem;
}

:deep(input) {
  transition: all 0.2s ease;
}

:deep(input:focus) {
  transform: translateY(-1px);
}
</style>
