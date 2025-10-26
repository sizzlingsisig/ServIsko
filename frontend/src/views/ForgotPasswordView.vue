<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/composables/axios'
import { useToastStore } from '@/stores/toastStore'

const router = useRouter()
const toastStore = useToastStore()

// Stepper state
const activeStep = ref(1)

// Loading state
const loading = ref(false)

// Form data
const email = ref('')
const otp = ref('')
const password = ref('')
const passwordConfirm = ref('')

// Step 1: Request OTP
const requestOtp = async () => {
  loading.value = true

  try {
    const response = await api.post('/api/forgot-password', {
      email: email.value,
    })

    toastStore.showSuccess(response.data.message || 'OTP sent to your email')
    sessionStorage.setItem('reset_email', email.value)

    setTimeout(() => {
      activeStep.value = 2
    }, 1500)
  } catch (err) {
    if (err.response?.data?.message) {
      toastStore.showError(err.response.data.message)
    }
  } finally {
    loading.value = false
  }
}

// Step 2: Verify OTP
const verifyOtp = async () => {
  loading.value = true

  try {
    const response = await api.post('/api/verify-reset-otp', {
      email: email.value,
      otp: otp.value,
    })

    sessionStorage.setItem('reset_token', response.data.reset_token)
    toastStore.showSuccess('Code verified successfully')
    activeStep.value = 3
  } catch (err) {
    if (err.response?.data?.message) {
      toastStore.showError(err.response.data.message)
    }
  } finally {
    loading.value = false
  }
}

// Step 3: Reset Password
const resetPassword = async () => {
  loading.value = true

  try {
    const token = sessionStorage.getItem('reset_token')
    const savedEmail = sessionStorage.getItem('reset_email')

    await api.post('/api/reset-password', {
      email: savedEmail,
      reset_token: token,
      password: password.value,
      password_confirmation: passwordConfirm.value,
    })

    sessionStorage.removeItem('reset_token')
    sessionStorage.removeItem('reset_email')

    toastStore.showSuccess('Password reset successful!')
    activeStep.value = 4
  } catch (err) {
    if (err.response?.data?.message) {
      toastStore.showError(err.response.data.message)
    }
  } finally {
    loading.value = false
  }
}

// Go to login
const goToLogin = () => {
  router.push('/login')
}
</script>

<template>
  <div class="flex flex-col items-center min-h-screen bg-gray-50 p-8">
    <!-- Content Area -->
    <div class="flex-1 flex flex-col items-center justify-start w-full max-w-2xl">
      <Stepper v-model:value="activeStep" class="w-full">
        <!-- Step List at Top -->
        <StepList class="mb-12">
          <Step v-slot="{ value, a11yAttrs }" asChild :value="1">
            <div class="flex flex-row flex-auto gap-2" v-bind="a11yAttrs.root">
              <div class="bg-transparent border-0 inline-flex flex-col gap-2">
                <span
                  :class="[
                    'rounded-full border-2 w-12 h-12 inline-flex items-center justify-center',
                    {
                      'bg-primary text-primary-contrast border-primary': value <= activeStep,
                      'border-gray-300': value > activeStep,
                    },
                  ]"
                >
                  <i class="pi pi-envelope" />
                </span>
              </div>
              <Divider />
            </div>
          </Step>
          <Step v-slot="{ value, a11yAttrs }" asChild :value="2">
            <div class="flex flex-row flex-auto gap-2 pl-2" v-bind="a11yAttrs.root">
              <div class="bg-transparent border-0 inline-flex flex-col gap-2">
                <span
                  :class="[
                    'rounded-full border-2 w-12 h-12 inline-flex items-center justify-center',
                    {
                      'bg-primary text-primary-contrast border-primary': value <= activeStep,
                      'border-gray-300': value > activeStep,
                    },
                  ]"
                >
                  <i class="pi pi-lock" />
                </span>
              </div>
              <Divider />
            </div>
          </Step>
          <Step v-slot="{ value, a11yAttrs }" asChild :value="3">
            <div class="flex flex-row flex-auto gap-2 pl-2" v-bind="a11yAttrs.root">
              <div class="bg-transparent border-0 inline-flex flex-col gap-2">
                <span
                  :class="[
                    'rounded-full border-2 w-12 h-12 inline-flex items-center justify-center',
                    {
                      'bg-primary text-primary-contrast border-primary': value <= activeStep,
                      'border-gray-300': value > activeStep,
                    },
                  ]"
                >
                  <i class="pi pi-key" />
                </span>
              </div>
              <Divider />
            </div>
          </Step>
          <Step v-slot="{ value, a11yAttrs }" asChild :value="4">
            <div class="flex flex-row pl-2" v-bind="a11yAttrs.root">
              <div class="bg-transparent border-0 inline-flex flex-col gap-2">
                <span
                  :class="[
                    'rounded-full border-2 w-12 h-12 inline-flex items-center justify-center',
                    {
                      'bg-primary text-primary-contrast border-primary': value <= activeStep,
                      'border-gray-300': value > activeStep,
                    },
                  ]"
                >
                  <i class="pi pi-check" />
                </span>
              </div>
            </div>
          </Step>
        </StepList>

        <!-- Step Panels - Centered Content -->
        <StepPanels class="bg-transparent mt-30">
          <!-- Step 1: Enter Email -->
          <StepPanel :value="1">
            <div class="flex flex-col gap-2 mx-auto" style="max-width: 28rem">
              <div class="text-center mb-8">
                <h2 class="text-2xl font-semibold mb-2 text-gray-900">Reset your password</h2>
                <p class="text-gray-600">
                  Enter your email address and we'll send you a code to reset your password
                </p>
              </div>

              <FormKit
                type="form"
                @submit="requestOtp"
                :actions="false"
                :incomplete-message="false"
              >
                <div class="space-y-5">
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
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-between mt-8">
                  <Button
                    label="Back to Login"
                    severity="secondary"
                    icon="pi pi-arrow-left"
                    @click="goToLogin"
                    class="flex-1 sm:flex-initial"
                  />
                  <Button
                    type="submit"
                    label="Send Code"
                    icon="pi pi-arrow-right"
                    iconPos="right"
                    :loading="loading"
                    :disabled="loading"
                    class="flex-1 sm:flex-initial"
                  />
                </div>
              </FormKit>
            </div>
          </StepPanel>

          <!-- Step 2: Enter OTP -->
          <StepPanel v-slot="{ prevCallback }" :value="2">
            <div class="flex flex-col gap-2 mx-auto" style="max-width: 28rem">
              <div class="text-center mb-8">
                <h2 class="text-2xl font-semibold mb-2 text-gray-900">Enter verification code</h2>
                <p class="text-gray-600">
                  We sent a 6-digit code to <strong>{{ email }}</strong>
                </p>
              </div>

              <FormKit type="form" @submit="verifyOtp" :actions="false" :incomplete-message="false">
                <div class="space-y-5">
                  <FormKit
                    type="text"
                    v-model="otp"
                    name="otp"
                    label="Verification Code"
                    placeholder="Enter 6-digit code"
                    validation="required|length:6|matches:/^[0-9]+$/"
                    validation-visibility="blur"
                    :validation-messages="{
                      required: 'Code is required.',
                      length: 'Code must be 6 digits.',
                      matches: 'Code must contain only numbers.',
                    }"
                    outer-class="!max-w-full"
                  />
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-between mt-8">
                  <Button
                    label="Back"
                    severity="secondary"
                    icon="pi pi-arrow-left"
                    @click="prevCallback"
                    class="flex-1 sm:flex-initial"
                  />
                  <Button
                    type="submit"
                    label="Verify Code"
                    icon="pi pi-arrow-right"
                    iconPos="right"
                    :loading="loading"
                    :disabled="loading"
                    class="flex-1 sm:flex-initial"
                  />
                </div>
              </FormKit>
            </div>
          </StepPanel>

          <!-- Step 3: New Password -->
          <StepPanel v-slot="{ prevCallback }" :value="3">
            <div class="flex flex-col gap-2 mx-auto" style="max-width: 36rem">
              <div class="text-center mb-8">
                <h2 class="text-2xl font-semibold mb-2 text-gray-900">Create new password</h2>
                <p class="text-gray-600">Enter a strong password for your account</p>
              </div>

              <FormKit
                type="form"
                @submit="resetPassword"
                :actions="false"
                :incomplete-message="false"
              >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                  <FormKit
                    type="password"
                    v-model="password"
                    name="password"
                    label="New Password"
                    placeholder="Create a strong password"
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

                <div class="flex flex-col sm:flex-row gap-3 justify-between mt-8">
                  <Button
                    label="Back"
                    severity="secondary"
                    icon="pi pi-arrow-left"
                    @click="prevCallback"
                    class="flex-1 sm:flex-initial"
                  />
                  <Button
                    type="submit"
                    label="Reset Password"
                    icon="pi pi-check"
                    iconPos="right"
                    :loading="loading"
                    :disabled="loading"
                    class="flex-1 sm:flex-initial"
                  />
                </div>
              </FormKit>
            </div>
          </StepPanel>

          <!-- Step 4: Success -->
          <StepPanel :value="4">
            <div class="flex flex-col gap-2 mx-auto text-center" style="max-width: 32rem">
              <div class="mb-4">
                <div
                  class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6"
                >
                  <i class="pi pi-check text-5xl text-green-600"></i>
                </div>
                <h3 class="text-2xl font-semibold mb-3 text-gray-900">
                  Password reset successful!
                </h3>
                <p class="text-gray-600 mb-2">Your password has been successfully reset.</p>
                <p class="text-gray-600">You can now sign in with your new password.</p>
              </div>
              <div class="flex justify-center mt-6">
                <Button
                  label="Go to Login"
                  icon="pi pi-sign-in"
                  iconPos="right"
                  @click="goToLogin"
                  size="large"
                />
              </div>
            </div>
          </StepPanel>
        </StepPanels>
      </Stepper>
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

:deep(.p-stepper-content) {
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
</style>
