<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/composables/axios'
import { useAuthStore } from '@/stores/AuthStore'
import { useToastStore } from '@/stores/toastStore'

const router = useRouter()
const authStore = useAuthStore()
const toastStore = useToastStore()

// Stepper state
const activeStep = ref(1)

// Loading state
const loading = ref(false)

// Selected role
const selectedRole = ref('')

// Form data
const fullName = ref('')
const email = ref('')
const password = ref('')
const passwordConfirm = ref('')

// Select role
const selectRole = (role) => {
  selectedRole.value = role
}

// Handle register
const handleRegister = async () => {
  if (!selectedRole.value) {
    toastStore.showWarning('Please select a role')
    return
  }

  loading.value = true

  try {
    const response = await api.post('/api/register', {
      name: fullName.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirm.value,
      role: selectedRole.value,
    })

    // Store auth data using AuthStore
    authStore.setToken(response.data.token)
    authStore.setUser(response.data.user)

    // Show success toast
    toastStore.showSuccess(`Welcome to ServISKO, ${fullName.value}!`)

    // Move to success step
    activeStep.value = 3
  } catch (err) {
    // Error handled globally by axios interceptor
    // But we can add specific validation error handling here
    if (err.response?.data?.errors) {
      const validationErrors = Object.values(err.response.data.errors).flat()
      toastStore.showError(validationErrors.join(', '), 'Validation Error')
    }
  } finally {
    loading.value = false
  }
}

// Navigate to dashboard
const goToDashboard = () => {
  router.push('/dashboard')
}
</script>

<template>
  <div class="flex items-center justify-center p-8 bg-gray-50 min-h-screen">
    <div class="w-full max-w-4xl">
      <!-- Stepper -->
      <div class="w-full">
        <Stepper v-model:value="activeStep" class="w-full">
          <!-- Step List -->
          <StepList>
            <Step v-slot="{ activateCallback, value, a11yAttrs }" asChild :value="1">
              <div class="flex flex-row flex-auto gap-2" v-bind="a11yAttrs.root">
                <button
                  class="bg-transparent border-0 inline-flex flex-col gap-2"
                  @click="activateCallback"
                  v-bind="a11yAttrs.header"
                >
                  <span
                    :class="[
                      'rounded-full border-2 w-12 h-12 inline-flex items-center justify-center',
                      {
                        'bg-primary text-primary-contrast border-primary': value <= activeStep,
                        'border-surface-200 dark:border-surface-700': value > activeStep,
                      },
                    ]"
                  >
                    <i class="pi pi-users" />
                  </span>
                </button>
                <Divider />
              </div>
            </Step>
            <Step v-slot="{ activateCallback, value, a11yAttrs }" asChild :value="2">
              <div class="flex flex-row flex-auto gap-2 pl-2" v-bind="a11yAttrs.root">
                <button
                  class="bg-transparent border-0 inline-flex flex-col gap-2"
                  @click="activateCallback"
                  v-bind="a11yAttrs.header"
                >
                  <span
                    :class="[
                      'rounded-full border-2 w-12 h-12 inline-flex items-center justify-center',
                      {
                        'bg-primary text-primary-contrast border-primary': value <= activeStep,
                        'border-surface-200 dark:border-surface-700': value > activeStep,
                      },
                    ]"
                  >
                    <i class="pi pi-user" />
                  </span>
                </button>
                <Divider />
              </div>
            </Step>
            <Step v-slot="{ activateCallback, value, a11yAttrs }" asChild :value="3">
              <div class="flex flex-row pl-2" v-bind="a11yAttrs.root">
                <button
                  class="bg-transparent border-0 inline-flex flex-col gap-2"
                  @click="activateCallback"
                  v-bind="a11yAttrs.header"
                >
                  <span
                    :class="[
                      'rounded-full border-2 w-12 h-12 inline-flex items-center justify-center',
                      {
                        'bg-primary text-primary-contrast border-primary': value <= activeStep,
                        'border-surface-200 dark:border-surface-700': value > activeStep,
                      },
                    ]"
                  >
                    <i class="pi pi-check" />
                  </span>
                </button>
              </div>
            </Step>
          </StepList>

          <!-- Step Panels -->
          <StepPanels class="bg-transparent">
            <!-- Step 1: Choose Role -->
            <StepPanel v-slot="{ activateCallback }" :value="1">
              <div class="flex flex-col gap-2 mx-auto" style="min-height: 16rem; max-width: 40rem">
                <div class="text-center mt-4 mb-4 text-2xl font-semibold">
                  Join as a service seeker or provider
                </div>
                <div class="text-center text-gray-600 mb-6">
                  Choose how you want to get started with ServISKO
                </div>

                <!-- Role Selection Buttons -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Service Seeker -->
                  <button
                    type="button"
                    @click="selectRole('service seeker')"
                    :class="[
                      'relative p-8 border-2 rounded-lg transition-all cursor-pointer group bg-white',
                      selectedRole === 'service seeker'
                        ? 'border-primary-600 bg-primary-50'
                        : 'border-gray-200 hover:border-primary-400 hover:shadow-md',
                    ]"
                  >
                    <div
                      v-if="selectedRole === 'service seeker'"
                      class="absolute top-4 right-4 w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center"
                    >
                      <i class="pi pi-check text-white text-xs"></i>
                    </div>
                    <div class="flex flex-col items-center text-center">
                      <div
                        class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-4 group-hover:bg-primary-200 transition-colors"
                      >
                        <i class="pi pi-search text-3xl text-primary-600"></i>
                      </div>
                      <h4 class="text-lg font-semibold mb-2 text-gray-900">I'm a service seeker</h4>
                      <p class="text-sm text-gray-600">Looking for skilled service providers</p>
                    </div>
                  </button>

                  <!-- Service Provider -->
                  <button
                    type="button"
                    @click="selectRole('service provider')"
                    :class="[
                      'relative p-8 border-2 rounded-lg transition-all cursor-pointer group bg-white',
                      selectedRole === 'service provider'
                        ? 'border-primary-600 bg-primary-50'
                        : 'border-gray-200 hover:border-primary-400 hover:shadow-md',
                    ]"
                  >
                    <div
                      v-if="selectedRole === 'service provider'"
                      class="absolute top-4 right-4 w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center"
                    >
                      <i class="pi pi-check text-white text-xs"></i>
                    </div>
                    <div class="flex flex-col items-center text-center">
                      <div
                        class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mb-4 group-hover:bg-primary-200 transition-colors"
                      >
                        <i class="pi pi-briefcase text-3xl text-primary-600"></i>
                      </div>
                      <h4 class="text-lg font-semibold mb-2 text-gray-900">
                        I'm a service provider
                      </h4>
                      <p class="text-sm text-gray-600">Ready to offer my skills and services</p>
                    </div>
                  </button>
                </div>
              </div>
              <div class="flex pt-6 justify-end">
                <Button
                  label="Next"
                  icon="pi pi-arrow-right"
                  iconPos="right"
                  @click="activateCallback(2)"
                  :disabled="!selectedRole"
                />
              </div>
            </StepPanel>

            <!-- Step 2: User Information -->
            <StepPanel v-slot="{ activateCallback }" :value="2">
              <div class="flex flex-col gap-2 mx-auto" style="min-height: 16rem; max-width: 36rem">
                <div class="text-center mt-4 mb-4 text-2xl font-semibold">
                  Tell us about yourself
                </div>
                <div class="text-center text-gray-600 mb-6">
                  You're joining as a <strong class="text-primary-600">{{ selectedRole }}</strong>
                </div>

                <FormKit
                  type="form"
                  @submit="handleRegister"
                  :actions="false"
                  :incomplete-message="false"
                >
                  <div class="flex flex-col gap-4">
                    <!-- Full Name -->
                    <FormKit
                      type="text"
                      v-model="fullName"
                      name="fullName"
                      label="Full Name"
                      placeholder="John Doe"
                      validation="required|length:3"
                      validation-visibility="blur"
                      :validation-messages="{
                        required: 'Name is required.',
                        length: 'Name must be at least 3 characters long.',
                      }"
                      outer-class="!max-w-full"
                    />

                    <!-- Email -->
                    <FormKit
                      type="email"
                      v-model="email"
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

                    <!-- Passwords - Side by Side -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <FormKit
                        type="password"
                        v-model="password"
                        name="password"
                        label="Password"
                        placeholder="Create a password"
                        validation="required|length:8"
                        validation-visibility="live"
                        :validation-messages="{
                          required: 'Password is required.',
                          length: 'Password must be at least 8 characters long.',
                        }"
                        outer-class="!max-w-full"
                      />

                      <FormKit
                        type="password"
                        v-model="passwordConfirm"
                        name="password_confirm"
                        label="Confirm Password"
                        placeholder="Re-enter your password"
                        validation="required|confirm"
                        validation-visibility="blur"
                        :validation-messages="{
                          required: 'Please confirm your password.',
                          confirm: 'Passwords do not match.',
                        }"
                        outer-class="!max-w-full"
                      />
                    </div>
                  </div>

                  <div class="flex pt-6 justify-between">
                    <Button
                      label="Back"
                      severity="secondary"
                      icon="pi pi-arrow-left"
                      @click="activateCallback(1)"
                    />
                    <Button
                      type="submit"
                      label="Create Account"
                      icon="pi pi-user-plus"
                      :loading="loading"
                      :disabled="loading"
                    />
                  </div>
                </FormKit>
              </div>
            </StepPanel>

            <!-- Step 3: Success -->
            <StepPanel :value="3">
              <div
                class="flex flex-col gap-2 mx-auto text-center"
                style="min-height: 16rem; max-width: 32rem"
              >
                <div class="mt-8 mb-4">
                  <div
                    class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6"
                  >
                    <i class="pi pi-check text-5xl text-green-600"></i>
                  </div>
                  <h3 class="text-2xl font-semibold mb-3 text-gray-900">
                    Account created successfully!
                  </h3>
                  <p class="text-gray-600 mb-2">
                    Welcome to ServISKO, <strong>{{ fullName }}</strong
                    >!
                  </p>
                  <p class="text-gray-600">
                    You're all set as a <strong class="text-primary-600">{{ selectedRole }}</strong
                    >.
                  </p>
                </div>
              </div>
              <div class="flex pt-6 justify-center">
                <Button
                  label="Go to Dashboard"
                  icon="pi pi-arrow-right"
                  iconPos="right"
                  @click="goToDashboard"
                  size="large"
                />
              </div>
            </StepPanel>
          </StepPanels>
        </Stepper>

        <!-- Footer Links -->
        <div class="mt-6 text-center pt-6 border-t border-gray-200">
          <div class="text-sm text-gray-600">
            Already have an account?
            <router-link
              to="/login"
              class="text-primary-600 hover:text-primary-700 hover:underline font-medium"
            >
              Sign in
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Remove default stepper borders */
:deep(.p-stepper) {
  border: none !important;
  box-shadow: none !important;
  background: transparent !important;
}

/* Remove step panel background color */
:deep(.p-steppanel) {
  background: transparent !important;
}

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

button[type='button']:hover {
  transform: translateY(-2px);
}
</style>
