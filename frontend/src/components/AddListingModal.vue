<script setup>
import { ref, watch, reactive, onMounted } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import AutoComplete from 'primevue/autocomplete'
import Chip from 'primevue/chip'
import DatePicker from 'primevue/datepicker'
import useToastHelper from '@/composables/useToastHelper'

const props = defineProps({
  showAddModal: Boolean,
  categories: Array,
  tags: Array,
})
const emit = defineEmits(['update:showAddModal', 'created'])

const categorySuggestions = ref([])
const tagSuggestions = ref([])

function filterCategories(event) {
  const query = event.query?.toLowerCase() || ''
  if (!query) {
    categorySuggestions.value = props.categories || []
  } else {
    categorySuggestions.value = (props.categories || []).filter(cat =>
      cat.name.toLowerCase().includes(query)
    )
  }
}

function filterTags(event) {
  const query = event.query?.toLowerCase() || ''
  if (!query) {
    tagSuggestions.value = props.tags || []
  } else {
    tagSuggestions.value = (props.tags || []).filter(tag =>
      tag.toLowerCase().includes(query)
    )
  }
}

onMounted(() => {
  categorySuggestions.value = props.categories || []
  tagSuggestions.value = props.tags || []
})

const visible = ref(props.showAddModal)
watch(() => props.showAddModal, val => { visible.value = val })

const activeStep = ref(1)
const tagInput = ref('')
const createListingForm = reactive({
  title: '',
  description: '',
  budget: null,
  category_id: null,
  tags: [],
  expires_at: '',
})
const hasExpiry = ref(false)
const expiryDate = ref('')

const { success, error, warning, info } = useToastHelper()

function resetForm() {
  activeStep.value = 1
  createListingForm.title = ''
  createListingForm.description = ''
  createListingForm.budget = null
  createListingForm.category_id = null
  createListingForm.tags = []
  createListingForm.expires_at = ''
  hasExpiry.value = false
  expiryDate.value = ''
  tagInput.value = ''
}

function onUpdateVisible(val) {
  visible.value = val
  emit('update:showAddModal', val)
  if (!val) {
    resetForm()
  }
}

function goNext() {
  if (activeStep.value === 1) {
    if (!createListingForm.title || createListingForm.title.trim() === '') {
      warning('Please enter a title')
      return
    }
    if (createListingForm.title.length > 255) {
      warning('Title must be 255 characters or less')
      return
    }
  }
  if (activeStep.value === 2) {
    if (createListingForm.tags.length === 0) {
      warning('Please select at least one tag')
      return
    }
    if (createListingForm.tags.length > 5) {
      warning('Maximum 5 tags allowed', 'Tag Limit')
      return
    }
  }
  if (activeStep.value < 3) {
    activeStep.value++
  }
}

function goBack() {
  if (activeStep.value > 1) activeStep.value--
}

function addTag(event) {
  let tag = ''
  if (event && event.value) {
    tag = typeof event.value === 'string' ? event.value : event.value?.name || event.value
  } else if (tagInput.value) {
    tag = tagInput.value
  }
  const trimmedTag = tag.trim()
  if (!trimmedTag) {
    tagInput.value = ''
    return
  }
  if (createListingForm.tags.length >= 5) {
    warning('Maximum 5 tags allowed', 'Tag Limit')
    tagInput.value = ''
    return
  }
  if (createListingForm.tags.includes(trimmedTag)) {
    info(`Tag "${trimmedTag}" already added`)
    tagInput.value = ''
    return
  }
  createListingForm.tags.push(trimmedTag)
  tagInput.value = ''
  success(`Tag "${trimmedTag}" added`)
}

function handleTagKeydown(event) {
  if (event.key === 'Enter') {
    event.preventDefault()
    addTag({ value: tagInput.value })
  }
}

function removeTag(tag) {
  const idx = createListingForm.tags.indexOf(tag)
  if (idx > -1) {
    createListingForm.tags.splice(idx, 1)
    info(`Tag "${tag}" removed`)
  }
}

async function submitListing() {
  // Final validation
  if (!createListingForm.title || createListingForm.title.trim() === '') {
    warning('Please enter a title for your listing')
    return
  }
  if (createListingForm.title.length > 255) {
    warning('Title must be 255 characters or less')
    return
  }
  if (createListingForm.tags.length === 0) {
    warning('Please add at least one tag')
    return
  }
  if (createListingForm.tags.length > 5) {
    warning('Maximum 5 tags allowed', 'Tag Limit')
    return
  }
  if (hasExpiry.value && !expiryDate.value) {
    warning('Please select an expiry date')
    return
  }
  if (createListingForm.budget !== null && createListingForm.budget !== undefined && createListingForm.budget !== '') {
    const budgetVal = Number(createListingForm.budget)
    if (isNaN(budgetVal) || budgetVal < 0) {
      warning('Budget must be a valid positive number')
      return
    }
    createListingForm.budget = budgetVal
  } else {
    createListingForm.budget = 0
  }
  if (hasExpiry.value && expiryDate.value) {
    try {
      let dateObj = expiryDate.value
      if (typeof dateObj === 'string') {
        dateObj = new Date(dateObj)
      }
      const year = dateObj.getFullYear()
      const month = String(dateObj.getMonth() + 1).padStart(2, '0')
      const day = String(dateObj.getDate()).padStart(2, '0')
      const hours = String(dateObj.getHours()).padStart(2, '0')
      const minutes = String(dateObj.getMinutes()).padStart(2, '0')
      createListingForm.expires_at = `${year}-${month}-${day} ${hours}:${minutes}:00`
    } catch (e) {
      error('Invalid expiry date')
      return
    }
  } else {
    createListingForm.expires_at = ''
  }
  if (typeof createListingForm.category_id === 'object' && createListingForm.category_id && 'id' in createListingForm.category_id) {
    createListingForm.category_id = createListingForm.category_id.id
  }
  info('Creating listing...')
  emit('created', { ...createListingForm })
  visible.value = false
  resetForm()
}
</script>

<template>
  <Dialog
    v-model:visible="visible"
    :modal="true"
    header="Create a Listing"
    header-style="background-color: #6d0019; color: white;"
    :style="{ width: '100%', maxWidth: '800px' }"
    @update:visible="onUpdateVisible"
  >
    <div class="p-4 md:p-6">
      <!-- Stepper Header -->
      <!-- ...stepper header code... -->

      <!-- Step Content -->
      <div class="min-h-75 mb-8">
        <!-- Step 1: About -->
        <div v-if="activeStep === 1" class="space-y-4">
          <div>
            <label class="font-semibold block mb-2">
              Title <span class="text-red-500">*</span>
            </label>
            <InputText
              v-model="createListingForm.title"
              :maxlength="255"
              placeholder="e.g., Need Web Developer for E-commerce Site"
              class="w-full"
            />
            <div class="text-xs text-gray-500 mt-1">{{ createListingForm.title.length }}/255 characters</div>
          </div>
          <div>
            <label class="font-semibold block mb-2">Description</label>
            <textarea
              v-model="createListingForm.description"
              rows="6"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500"
              placeholder="Describe your project requirements, deliverables, timeline, etc..."
            ></textarea>
          </div>
        </div>

        <!-- Step 2: Category & Tags -->
        <div v-if="activeStep === 2" class="space-y-4">
          <div>
            <label class="font-semibold block mb-2">Category</label>
            <AutoComplete
              v-model="createListingForm.category_id"
              :suggestions="categorySuggestions"
              optionLabel="name"
              @complete="filterCategories"
              placeholder="Type or select a category"
              class="w-full"
              dropdown
              showClear
              aria-label="Category"
            >
              <template #option="slotProps">
                <div class="flex items-center gap-2">
                  <i class="pi pi-folder text-primary-500"></i>
                  <span>{{ slotProps.option.name }}</span>
                </div>
              </template>
            </AutoComplete>
          </div>
          <div>
            <label class="font-semibold block mb-2">
              Tags <span class="text-red-500">*</span> (Max 5)
            </label>
            <div v-if="createListingForm.tags.length > 0" class="flex flex-wrap gap-2 mb-3">
              <Chip
                v-for="(tag, idx) in createListingForm.tags"
                :key="idx"
                :label="tag"
                removable
                @remove="removeTag(tag)"
              />
            </div>
            <AutoComplete
              v-model="tagInput"
              inputId="tags-autocomplete"
              :suggestions="tagSuggestions"
              @complete="filterTags"
              @itemSelect="addTag"
              @keydown="handleTagKeydown"
              :disabled="createListingForm.tags.length >= 5"
              class="w-full"
              :forceSelection="false"
              dropdown
              showClear
              aria-label="Tags"
              placeholder="Type to search tags or press Enter to add custom"
            >
              <template #option="slotProps">
                <div class="flex items-center gap-2">
                  <i class="pi pi-tag text-primary-500"></i>
                  <span>{{ slotProps.option }}</span>
                </div>
              </template>
            </AutoComplete>
            <p class="text-xs text-gray-500 mt-2">
              {{ createListingForm.tags.length }}/5 tags selected. Type and press Enter for custom tags.
            </p>
          </div>
        </div>

        <!-- Step 3: Budget & Expiry -->
        <div v-if="activeStep === 3" class="space-y-4">
          <div>
            <label class="font-semibold block mb-2">Budget (₱)</label>
            <InputText
              v-model="createListingForm.budget"
              type="number"
              placeholder="e.g., 10000"
              class="w-full"
              min="0"
            />
            <p class="text-xs text-gray-500 mt-1">Optional: Enter your budget in Philippine Pesos</p>
          </div>
          <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
            <input
              type="checkbox"
              v-model="hasExpiry"
              class="accent-[#6d0019] w-5 h-5 cursor-pointer"
              id="hasExpiry"
            />
            <label for="hasExpiry" class="font-semibold cursor-pointer">
              Set an expiry date for this listing
            </label>
          </div>
          <div v-if="hasExpiry" class="p-4 bg-blue-50 rounded-lg">
            <label class="font-semibold block mb-2">Expiry Date & Time</label>
            <DatePicker
              v-model="expiryDate"
              showTime
              hourFormat="12"
              placeholder="Select expiry date and time"
              fluid
              showIcon
              inputId="expiry-datepicker"
              :manualInput="false"
              class="w-full"
            />
            <p class="text-xs text-gray-500 mt-2">
              Format: MM/DD/YYYY hh:mm AM/PM
            </p>
          </div>
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div class="flex justify-between items-center pt-4 border-t">
        <Button
          :label="activeStep === 1 ?  'Cancel' : '← Back'"
          severity="secondary"
          @click="activeStep === 1 ? onUpdateVisible(false) : goBack()"
        />
        <button
          type="button"
          class="px-6 py-2 rounded-lg font-semibold text-white bg-primary-500 hover:bg-[#6d0019] transition flex items-center gap-2"
          @click="activeStep === 3 ? submitListing() : goNext()"
        >
          <i :class="activeStep === 3 ? 'pi pi-check' : 'pi pi-arrow-right'"></i>
          <span>{{ activeStep === 3 ? 'Create Listing' : 'Next' }}</span>
        </button>
      </div>
    </div>
  </Dialog>
</template>
