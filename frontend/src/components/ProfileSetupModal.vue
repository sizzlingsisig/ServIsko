<script setup>

import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import AutoComplete from 'primevue/autocomplete'
import ProgressBar from 'primevue/progressbar'
import axios from '@/composables/axios'
import { useProfileSetup } from '@/composables/useProfileSetup'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:visible', 'completed'])

const router = useRouter()
const toast = useToast()
const { closeModal, profile } = useProfileSetup()

const currentStep = ref(0)
const loading = ref(false)
const allSkills = ref([])
const filteredSkills = ref([])

const form = reactive({
  bio: '',
  location: '',
  selectedSkills: [],
  errors: {
    bio: '',
    location: '',
    skills: ''
  }
})

// Ensure selectedSkills is always an array
onMounted(() => {
  if (!Array.isArray(form.selectedSkills)) form.selectedSkills = []
})

// Progress calculation
const progress = computed(() => {
  return ((currentStep.value + 1) / 3) * 100
})

// Load available skills
const loadSkills = async () => {
  try {
    const { data } = await axios.get('/provider/skills', { params: { per_page: 100 } })
    allSkills.value = data.data || []
  } catch (error) {
    console.error('Failed to load skills:', error)
  }
}

const searchSkill = (event) => {
  const query = event.query?.toLowerCase() || ''
  // Defensive: ensure selectedSkills is always an array
  const selected = Array.isArray(form.selectedSkills) ? form.selectedSkills : []
  if (!query) {
    filteredSkills.value = allSkills.value.filter(s => !selected.some(sel => sel.id === s.id))
  } else {
    filteredSkills.value = allSkills.value
      .filter(s => s.name.toLowerCase().includes(query))
      .filter(s => !selected.some(sel => sel.id === s.id))
  }
}

// Validation
const validateStep = (step) => {
  form.errors = { bio: '', location: '', skills: '' }

  if (step === 0) {
    if (!form.bio.trim() || form.bio.length < 20) {
      form.errors.bio = 'Bio must be at least 20 characters'
      return false
    }
    if (!form.location.trim()) {
      form.errors.location = 'Location is required'
      return false
    }
  }

  if (step === 1) {
    if (form.selectedSkills.length === 0) {
      form.errors.skills = 'Please add at least one skill'
      return false
    }
  }

  return true
}

// Navigation
const nextStep = () => {
  if (validateStep(currentStep.value)) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--
  }
}

const removeSkill = (index) => {
  form.selectedSkills.splice(index, 1)
}

// Save profile
const saveProfile = async () => {
  if (!validateStep(1)) return

  loading.value = true

  try {
    // Update bio and location
    await axios.put('/seeker/profile', {
      bio: form.bio,
      location: form.location
    })

    // Add skills
    for (const skill of form.selectedSkills) {
      try {
        await axios.post('/provider/skills', { skill_id: skill.id })
      } catch (error) {
        console.error(`Failed to add skill ${skill.name}:`, error)
      }
    }

    toast.add({
      severity: 'success',
      summary: 'Profile Setup Complete!',
      detail: 'Your profile has been updated successfully',
      life: 3000
    })

    emit('completed')
    handleClose()

    // Redirect to profile page
    setTimeout(() => {
      router.push('/profile')
    }, 1000)

  } catch (error) {
    console.error('Failed to save profile:', error)
    toast.add({
      severity: 'error',
      summary: 'Save Failed',
      detail: error.response?.data?.message || 'Failed to update profile',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}

const handleClose = () => {
  emit('update:visible', false)
  closeModal()
}

const handleSkip = () => {
  toast.add({
    severity: 'info',
    summary: 'Setup Skipped',
    detail: 'You can complete your profile anytime from the Profile page',
    life: 4000
  })
  handleClose()
  localStorage.setItem('profileSetupSkipped', 'true')
}

onMounted(() => {
  loadSkills()
})
</script>

<template>
  <Dialog
    :visible="visible"
    @update:visible="$emit('update:visible', $event)"
    modal
    :closable="false"
    :draggable="false"
    class="w-full max-w-2xl"
  >
    <template #header>
      <div class="w-full">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Complete Your Profile</h2>
            <p class="text-sm text-gray-600 mt-1">
              Help others learn more about you and your expertise
            </p>
          </div>
          <Button
            icon="pi pi-times"
            text
            rounded
            severity="secondary"
            @click="handleSkip"
            class="text-gray-500 hover:text-gray-700"
          />
        </div>
        <ProgressBar :value="progress" :showValue="false" class="h-2" />
      </div>
    </template>

    <div class="py-6">
      <!-- Step 0: Basic Info -->
      <div v-if="currentStep === 0" class="space-y-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <i class="pi pi-info-circle text-blue-600 text-xl mt-0.5"></i>
            <div>
              <h4 class="font-semibold text-blue-900 mb-1">Why complete your profile?</h4>
              <p class="text-sm text-blue-800">
                A complete profile helps clients understand your background and increases your chances of getting hired.
              </p>
            </div>
          </div>
        </div>

        <div>
          <label for="setup-bio" class="block font-semibold text-gray-700 mb-2">
            Bio <span class="text-red-500">*</span>
          </label>
          <Textarea
            id="setup-bio"
            v-model="form.bio"
            rows="4"
            placeholder="Tell clients about yourself, your experience, and what makes you unique..."
            class="w-full"
            :class="{ 'p-invalid': form.errors.bio }"
          />
          <div class="flex justify-between items-center mt-1">
            <small v-if="form.errors.bio" class="text-red-500">{{ form.errors.bio }}</small>
            <small class="text-gray-500 ml-auto">{{ form.bio.length }}/500</small>
          </div>
        </div>

        <div>
          <label for="setup-location" class="block font-semibold text-gray-700 mb-2">
            Location <span class="text-red-500">*</span>
          </label>
          <InputText
            id="setup-location"
            v-model="form.location"
            placeholder="e.g., Miagao, Iloilo"
            class="w-full"
            :class="{ 'p-invalid': form.errors.location }"
          />
          <small v-if="form.errors.location" class="text-red-500">{{ form.errors.location }}</small>
        </div>
      </div>

      <!-- Step 1: Skills -->
      <div v-else-if="currentStep === 1" class="space-y-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <i class="pi pi-lightbulb text-green-600 text-xl mt-0.5"></i>
            <div>
              <h4 class="font-semibold text-green-900 mb-1">Showcase Your Skills</h4>
              <p class="text-sm text-green-800">
                Add skills that represent your expertise. This helps clients find you for relevant projects.
              </p>
            </div>
          </div>
        </div>

        <div>
          <label class="block font-semibold text-gray-700 mb-2">
            Add Skills <span class="text-red-500">*</span>
          </label>
          <AutoComplete
            v-model="form.selectedSkills"
            :suggestions="filteredSkills"
            @complete="searchSkill"
            optionLabel="name"
            placeholder="Search and select skills..."
            class="w-full"
            :class="{ 'p-invalid': form.errors.skills }"
            multiple
            dropdown
            showClear
            forceSelection
            :virtualScrollerOptions="{ itemSize: 38 }"
            @remove="removeSkill"
          />
          <small v-if="form.errors.skills" class="text-red-500 block mt-1">{{ form.errors.skills }}</small>
        </div>

        <div v-if="form.selectedSkills.length > 0" class="space-y-2">
          <label class="block font-semibold text-gray-700">Selected Skills ({{ form.selectedSkills.length }})</label>
          <div class="flex flex-wrap gap-2">
            <div
              v-for="(skill, idx) in form.selectedSkills"
              :key="idx"
              class="inline-flex items-center gap-2 px-3 py-2 bg-primary-50 border border-primary-200 rounded-lg"
            >
              <span class="text-sm font-medium text-primary-900">{{ skill.name }}</span>
              <button
                @click="removeSkill(idx)"
                class="text-primary-600 hover:text-primary-800"
              >
                <i class="pi pi-times text-xs"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Review -->
      <div v-else-if="currentStep === 2" class="space-y-6">
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
          <div class="flex items-start gap-3">
            <i class="pi pi-check-circle text-purple-600 text-xl mt-0.5"></i>
            <div>
              <h4 class="font-semibold text-purple-900 mb-1">Review Your Profile</h4>
              <p class="text-sm text-purple-800">
                Please review your information before saving. You can always edit this later.
              </p>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <h4 class="font-semibold text-gray-700 mb-2">Bio</h4>
            <p class="text-gray-800 bg-gray-50 p-3 rounded-lg">{{ form.bio }}</p>
          </div>

          <div>
            <h4 class="font-semibold text-gray-700 mb-2">Location</h4>
            <p class="text-gray-800 bg-gray-50 p-3 rounded-lg">{{ form.location }}</p>
          </div>

          <div>
            <h4 class="font-semibold text-gray-700 mb-2">Skills ({{ form.selectedSkills.length }})</h4>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="skill in form.selectedSkills"
                :key="skill.id"
                class="px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm font-medium"
              >
                {{ skill.name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-between items-center w-full">
        <div>
          <Button
            v-if="currentStep > 0"
            label="Back"
            icon="pi pi-arrow-left"
            text
            @click="prevStep"
          />
        </div>
        <div class="flex gap-2">
          <Button
            label="Skip for Now"
            text
            severity="secondary"
            @click="handleSkip"
          />
          <Button
            v-if="currentStep < 2"
            label="Next"
            icon="pi pi-arrow-right"
            iconPos="right"
            @click="nextStep"
          />
          <Button
            v-else
            label="Complete Setup"
            icon="pi pi-check"
            @click="saveProfile"
            :loading="loading"
          />
        </div>
      </div>
    </template>
  </Dialog>
</template>
