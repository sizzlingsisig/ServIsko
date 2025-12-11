<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/composables/axios'
import { useAuthStore } from '@/stores/AuthStore'
import { useToast } from 'primevue/usetoast'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

// Stepper state
const activeStep = ref(1)
const selectedRole = ref('')

// Loading state
const loading = ref(false)

// Form data
const fullName = ref('')
const username = ref('')
const email = ref('')
const password = ref('')
const passwordConfirm = ref('')

// Handle register (Step 1 - account creation with role selection)
const handleRegister = async (role) => {
  loading.value = true
  try {
    const response = await api.post('/register', {
      name: fullName.value,
      username: username.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirm.value,
      role: role,
    })
    authStore.setToken(response.data.token)
    authStore.setUser(response.data.user)
    console.log(response.data.user)
    toast.add({ severity: 'success', summary: 'Success', detail: `Welcome to ServISKO as a ${role}!`, life: 3000 })
    activeStep.value = 3
  } catch (err) {
    if (err.response?.data?.errors) {
      const validationErrors = Object.values(err.response.data.errors).flat()
      toast.add({ severity: 'error', summary: 'Validation Error', detail: validationErrors.join(', '), life: 4000 })
    }
  } finally {
    loading.value = false
  }
}

// Navigate to dashboard
const goToDashboard = () => {
  router.push('/')
}

// Prevent navigation to step 2 if step 1 is not completed
const preventNavigation = (callback) => {
  if (activeStep.value === 1) {
    // Don't allow navigation away from step 1
    return
  }
  callback()
}

// Step 1: Validate and go to role selection
const handleStep1 = () => {
  // Basic validation (can be improved)
  if (!fullName.value || !username.value || !email.value || !password.value || !passwordConfirm.value) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Please fill out all fields.', life: 3000 })
    return
  }
  if (password.value !== passwordConfirm.value) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Passwords do not match.', life: 3000 })
    return
  }
  activeStep.value = 2
}

// Step 2: Register with selected role
const handleRoleSelect = async (role) => {
  selectedRole.value = role
  await handleRegister(role)
}
</script>

<template>
  <div class="flex items-center justify-center p-8 bg-gray-50 min-h-screen">
    <div class="w-full max-w-4xl">
      <div class="w-full">
        <Stepper v-model:value="activeStep" class="w-full" linear>
          <StepList>
            <Step v-slot="{ activateCallback, value, a11yAttrs }" asChild :value="1">
              <div class="flex flex-row flex-auto gap-2" v-bind="a11yAttrs.root">
                <button
                  class="bg-transparent border-0 inline-flex flex-col gap-2"
                  @click="preventNavigation(activateCallback)"
                  v-bind="a11yAttrs.header"
                >
                  <span
                    :class="[ 'rounded-full border-2 w-12 h-12 inline-flex items-center justify-center', { 'bg-primary text-primary-contrast border-primary': value <= activeStep, 'border-surface-200 dark:border-surface-700': value > activeStep, }, ]"
                  >
                    <i class="pi pi-user" />
                  </span>
                </button>
                <Divider />
              </div>
            </Step>
            <Step v-slot="{ activateCallback, value, a11yAttrs }" asChild :value="2">
              <div class="flex flex-row pl-2" v-bind="a11yAttrs.root">
                <button
                  class="bg-transparent border-0 inline-flex flex-col gap-2"
                  @click="preventNavigation(activateCallback)"
                  v-bind="a11yAttrs.header"
                  :disabled="activeStep < 2"
                >
                  <span
                    :class="[ 'rounded-full border-2 w-12 h-12 inline-flex items-center justify-center', { 'bg-primary text-primary-contrast border-primary': value <= activeStep, 'border-surface-200 dark:border-surface-700': value > activeStep, }, ]"
                  >
                    <i class="pi pi-briefcase" />
                  </span>
                </button>
              </div>
            </Step>
          </StepList>
          <StepPanels class="bg-transparent">
            <!-- Step 1: Account Info -->
            <StepPanel v-slot="{ activateCallback }" :value="1">
              <div class="flex flex-col gap-2 mx-auto" style="min-height: 16rem; max-width: 36rem">
                <div class="text-center mt-4 mb-4 text-2xl font-semibold">Create your account</div>
                <div class="text-center text-gray-600 mb-6">Get started with ServISKO in just a few steps</div>
                <FormKit type="form" @submit="handleStep1" :actions="false" :incomplete-message="false">
                  <div class="flex flex-col gap-4">
                    <FormKit type="text" v-model="fullName" name="fullName" label="Full Name" placeholder="John Doe" validation="required|length:3" validation-visibility="blur" :validation-messages="{ required: 'Name is required.', length: 'Name must be at least 3 characters long.', }" outer-class="!max-w-full" />
                    <FormKit type="text" v-model="username" name="username" label="Username" placeholder="johndoe123" validation="required|length:3|matches:/^[a-zA-Z0-9_-]+$/" validation-visibility="blur" :validation-messages="{ required: 'Username is required.', length: 'Username must be at least 3 characters long.', matches: 'Username can only contain letters, numbers, underscores, and hyphens.', }" outer-class="!max-w-full" />
                    <FormKit type="email" v-model="email" name="email" label="Email Address" placeholder="user@example.com" validation="required|email" validation-visibility="blur" :validation-messages="{ required: 'Email is required.', email: 'Please enter a valid email address.', }" outer-class="!max-w-full" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <FormKit type="password" v-model="password" name="password" label="Password" placeholder="Create a password" validation="required|length:8" validation-visibility="live" :validation-messages="{ required: 'Password is required.', length: 'Password must be at least 8 characters long.', }" outer-class="!max-w-full" />
                      <FormKit type="password" v-model="passwordConfirm" name="password_confirm" label="Confirm Password" placeholder="Re-enter your password" validation="required|confirm" validation-visibility="blur" :validation-messages="{ required: 'Please confirm your password.', confirm: 'Passwords do not match.', }" outer-class="!max-w-full" />
                    </div>
                    <div class="mt-4 text-center">
                      <Button type="submit" label="Next: Choose Role" class="px-6 py-2 bg-primary-600 text-white rounded" :loading="loading" :disabled="loading" />
                    </div>
                  </div>
                </FormKit>
              </div>
            </StepPanel>
            <!-- Step 2: Role Selection -->
            <StepPanel :value="2">
              <div class="flex flex-col gap-2 mx-auto" style="min-height: 16rem; max-width: 36rem">
                <div class="text-center mt-4 mb-4 text-2xl font-semibold">Choose your role</div>
                <div class="text-center text-gray-600 mb-6">How would you like to use ServISKO?</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <button type="button" @click="handleRoleSelect('service-seeker')" class="relative p-6 border-2 rounded-lg transition-all cursor-pointer group bg-white border-gray-200 hover:border-primary-400 hover:shadow-md" :disabled="loading">
                    <div class="flex flex-col items-center text-center">
                      <div class="w-14 h-14 bg-primary-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary-200 transition-colors">
                        <i class="pi pi-search text-2xl text-primary-600"></i>
                      </div>
                      <h4 class="text-base font-semibold mb-1 text-gray-900">I'm looking for services</h4>
                      <p class="text-xs text-gray-600">Find skilled professionals</p>
                    </div>
                  </button>
                  <button type="button" @click="handleRoleSelect('service-provider')" class="relative p-6 border-2 rounded-lg transition-all cursor-pointer group bg-white border-gray-200 hover:border-primary-400 hover:shadow-md" :disabled="loading">
                    <div class="flex flex-col items-center text-center">
                      <div class="w-14 h-14 bg-primary-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-primary-200 transition-colors">
                        <i class="pi pi-briefcase text-2xl text-primary-600"></i>
                      </div>
                      <h4 class="text-base font-semibold mb-1 text-gray-900">I want to offer services</h4>
                      <p class="text-xs text-gray-600">Share your skills with others</p>
                    </div>
                  </button>
                </div>
              </div>
            </StepPanel>
            <!-- Step 3: Success -->
            <StepPanel :value="3">
              <div class="flex flex-col gap-2 mx-auto text-center" style="min-height: 16rem; max-width: 32rem">
                <div class="mt-8 mb-4">
                  <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="pi pi-check text-5xl text-green-600"></i>
                  </div>
                  <h3 class="text-2xl font-semibold mb-3 text-gray-900">You're all set!</h3>
                  <p class="text-gray-600 mb-2">Your account has been created successfully.</p>
                  <p class="text-gray-600">You're ready to explore ServISKO.</p>
                </div>
              </div>
              <div class="flex pt-6 justify-center">
                <Button label="Go to Dashboard" icon="pi pi-arrow-right" iconPos="right" @click="goToDashboard" size="large" />
              </div>
            </StepPanel>
          </StepPanels>
        </Stepper>
        <!-- Footer Links -->
        <div class="mt-6 text-center pt-6 border-t border-gray-200">
          <div class="text-sm text-gray-600">
            Already have an account?
            <router-link to="/login" class="text-primary-600 hover:text-primary-700 hover:underline font-medium">Sign in</router-link>
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

button[type='button']:hover:not(:disabled) {
  transform: translateY(-2px);
}

button[type='button']:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
