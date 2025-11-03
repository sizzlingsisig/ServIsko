<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/AuthStore'
import axios from '@/composables/axios'

const authStore = useAuthStore()

// State
const skills = ref([])
const loading = ref(false)
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const editingSkill = ref(null)
const searchQuery = ref('')

// Form data
const skillForm = ref({
  name: '',
  description: '',
  category: '',
})

// Pagination
const pagination = ref({
  total: 0,
  perPage: 50,
  currentPage: 1,
  lastPage: 1,
})

// Category options
const categories = ref([
  { label: 'Programming', value: 'programming' },
  { label: 'Design', value: 'design' },
  { label: 'Writing', value: 'writing' },
  { label: 'Marketing', value: 'marketing' },
  { label: 'Business', value: 'business' },
  { label: 'Other', value: 'other' },
])

// Fetch all skills with pagination
const fetchSkills = async (page = 1, search = '') => {
  try {
    loading.value = true

    const res = await axios.get('/provider/skills', {
      params: {
        page,
        per_page: pagination.value.perPage,
        search,
      },
    })

    skills.value = res.data.data
    pagination.value = {
      total: res.data.pagination.total,
      perPage: res.data.pagination.per_page,
      currentPage: res.data.pagination.current_page,
      lastPage: res.data.pagination.last_page,
    }
  } catch (err) {
    console.error('Error fetching skills:', err)
    alert(err.response?.data?.message || 'Failed to fetch skills')
  } finally {
    loading.value = false
  }
}

// Search skills
const searchSkills = async () => {
  await fetchSkills(1, searchQuery.value)
}

// Reset search and fetch all
const resetSearch = async () => {
  searchQuery.value = ''
  await fetchSkills()
}

// Open create dialog
const openCreateDialog = () => {
  editingSkill.value = null
  skillForm.value = {
    name: '',
    description: '',
    category: '',
  }
  showCreateDialog.value = true
}

// Open edit dialog
const openEditDialog = (skill) => {
  editingSkill.value = skill
  skillForm.value = {
    name: skill.name,
    description: skill.description,
    category: skill.category,
  }
  showEditDialog.value = true
}

// Save skill (create or update)
const saveSkill = async () => {
  try {
    if (!skillForm.value.name || !skillForm.value.category) {
      alert('Name and category are required')
      return
    }

    loading.value = true

    if (editingSkill.value) {
      // Update
      await axios.put(`/provider/skills/${editingSkill.value.id}`, skillForm.value)
      alert('Skill updated successfully')
    } else {
      // Create
      await axios.post('/provider/skills', skillForm.value)
      alert('Skill created successfully')
    }

    showCreateDialog.value = false
    showEditDialog.value = false
    await fetchSkills(pagination.value.currentPage, searchQuery.value)
  } catch (err) {
    console.error('Error saving skill:', err)
    alert(err.response?.data?.message || 'Failed to save skill')
  } finally {
    loading.value = false
  }
}

// Delete skill
const deleteSkill = async (skillId) => {
  if (!confirm('Are you sure you want to delete this skill?')) return

  try {
    loading.value = true
    await axios.delete(`/provider/skills/${skillId}`)
    alert('Skill deleted successfully')
    await fetchSkills(pagination.value.currentPage, searchQuery.value)
  } catch (err) {
    console.error('Error deleting skill:', err)
    alert(err.response?.data?.message || 'Failed to delete skill')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (!authStore.token) {
    alert('No authentication token found')
    return
  }
  fetchSkills()
})
</script>

<template>
  <div style="min-height: 100vh; background-color: rgb(249, 250, 251); padding: 2rem 1rem">
    <div
      style="max-width: 80rem; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem"
    >
      <!-- Header -->
      <Card style="margin-bottom: 2rem">
        <template #content>
          <div style="display: flex; justify-content: space-between; align-items: center">
            <div>
              <h1 style="font-size: 2.25rem; font-weight: bold; color: rgb(17, 24, 39)">
                My Skills
              </h1>
              <p style="margin-top: 0.5rem; color: rgb(75, 85, 99)">
                Manage your professional skills
              </p>
            </div>
            <div style="display: flex; gap: 0.75rem">
              <Button
                label="Add Skill"
                icon="pi pi-plus"
                @click="openCreateDialog"
                :loading="loading"
              />
            </div>
          </div>
        </template>
      </Card>

      <!-- Search Bar -->
      <div style="display: flex; gap: 0.5rem">
        <span style="position: relative; flex: 1">
          <i
            style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%)"
            class="pi pi-search"
          ></i>
          <InputText
            v-model="searchQuery"
            placeholder="Search skills..."
            style="width: 100%; padding-left: 2.5rem"
            @keyup.enter="searchSkills"
          />
        </span>
        <Button label="Search" icon="pi pi-search" @click="searchSkills" :loading="loading" />
        <Button
          label="Reset"
          icon="pi pi-refresh"
          @click="resetSearch"
          severity="secondary"
          :loading="loading"
        />
      </div>

      <!-- Skills Tags Display -->
      <Card>
        <template #header>
          <div
            style="
              padding: 1rem;
              font-weight: 600;
              font-size: 1rem;
              color: rgb(17, 24, 39);
              border-bottom: 1px solid rgb(229, 231, 235);
            "
          >
            Skills ({{ pagination.total }} total)
          </div>
        </template>
        <template #content>
          <div v-if="skills.length > 0" style="display: flex; flex-wrap: wrap; gap: 0.75rem">
            <div
              v-for="skill in skills"
              :key="skill.id"
              style="
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background-color: rgb(243, 244, 246);
                border: 1px solid rgb(209, 213, 219);
                border-radius: 9999px;
                position: relative;
                padding-right: 3.5rem;
              "
            >
              <div style="display: flex; flex-direction: column; gap: 0.25rem; flex: 1">
                <span style="font-weight: 600; color: rgb(17, 24, 39); font-size: 0.875rem">
                  {{ skill.name }}
                </span>
                <span style="font-size: 0.75rem; color: rgb(107, 114, 128)">
                  {{ skill.category }}
                </span>
              </div>

              <Button
                icon="pi pi-times"
                rounded
                text
                severity="danger"
                size="small"
                @click="deleteSkill(skill.id)"
                :loading="loading"
                style="
                  width: 1.75rem;
                  height: 1.75rem;
                  padding: 0;
                  position: absolute;
                  right: 0.5rem;
                "
              />
            </div>
          </div>
          <div v-else style="text-align: center; padding: 3rem 1rem; color: rgb(107, 114, 128)">
            <i
              style="
                font-size: 2rem;
                color: rgb(209, 213, 219);
                display: block;
                margin-bottom: 0.5rem;
              "
              class="pi pi-inbox"
            ></i>
            <p>No skills added yet</p>
          </div>
        </template>
      </Card>

      <!-- Pagination Info -->
      <div style="text-align: center; color: rgb(107, 114, 128); font-size: 0.875rem">
        Showing {{ skills.length }} of {{ pagination.total }} skills
        <span v-if="pagination.lastPage > 1">
          (Page {{ pagination.currentPage }} of {{ pagination.lastPage }})
        </span>
      </div>

      <!-- Statistics -->
      <div
        style="
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 1rem;
        "
      >
        <Card>
          <template #content>
            <div style="text-align: center">
              <i
                style="font-size: 2rem; color: rgb(59, 130, 246); margin-bottom: 0.5rem"
                class="pi pi-list"
              ></i>
              <p style="color: rgb(75, 85, 99); font-size: 0.875rem">Total Skills</p>
              <p style="font-size: 1.875rem; font-weight: bold; color: rgb(17, 24, 39)">
                {{ pagination.total }}
              </p>
            </div>
          </template>
        </Card>

        <Card>
          <template #content>
            <div style="text-align: center">
              <i
                style="font-size: 2rem; color: rgb(34, 197, 94); margin-bottom: 0.5rem"
                class="pi pi-code"
              ></i>
              <p style="color: rgb(75, 85, 99); font-size: 0.875rem">Programming</p>
              <p style="font-size: 1.875rem; font-weight: bold; color: rgb(17, 24, 39)">
                {{ skills.filter((s) => s.category === 'programming').length }}
              </p>
            </div>
          </template>
        </Card>

        <Card>
          <template #content>
            <div style="text-align: center">
              <i
                style="font-size: 2rem; color: rgb(168, 85, 247); margin-bottom: 0.5rem"
                class="pi pi-palette"
              ></i>
              <p style="color: rgb(75, 85, 99); font-size: 0.875rem">Design</p>
              <p style="font-size: 1.875rem; font-weight: bold; color: rgb(17, 24, 39)">
                {{ skills.filter((s) => s.category === 'design').length }}
              </p>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Create Skill Dialog -->
    <Dialog
      v-model:visible="showCreateDialog"
      header="Add New Skill"
      :modal="true"
      style="width: 100%; max-width: 384px"
      :closable="!loading"
    >
      <div style="display: flex; flex-direction: column; gap: 1rem">
        <div>
          <label
            style="display: block; font-weight: 600; color: rgb(55, 65, 81); margin-bottom: 0.5rem"
          >
            Skill Name *
          </label>
          <InputText v-model="skillForm.name" placeholder="Enter skill name" style="width: 100%" />
        </div>

        <div>
          <label
            style="display: block; font-weight: 600; color: rgb(55, 65, 81); margin-bottom: 0.5rem"
          >
            Category *
          </label>
          <Dropdown
            v-model="skillForm.category"
            :options="categories"
            option-label="label"
            option-value="value"
            placeholder="Select a category"
            style="width: 100%"
          />
        </div>

        <div>
          <label
            style="display: block; font-weight: 600; color: rgb(55, 65, 81); margin-bottom: 0.5rem"
          >
            Description
          </label>
          <Textarea
            v-model="skillForm.description"
            placeholder="Enter skill description"
            rows="3"
            style="width: 100%"
          />
        </div>
      </div>

      <template #footer>
        <Button
          label="Cancel"
          icon="pi pi-times"
          @click="showCreateDialog = false"
          :disabled="loading"
          text
        />
        <Button label="Add" icon="pi pi-check" @click="saveSkill" :loading="loading" autofocus />
      </template>
    </Dialog>

    <!-- Edit Skill Dialog -->
    <Dialog
      v-model:visible="showEditDialog"
      header="Edit Skill"
      :modal="true"
      style="width: 100%; max-width: 384px"
      :closable="!loading"
    >
      <div style="display: flex; flex-direction: column; gap: 1rem">
        <div>
          <label
            style="display: block; font-weight: 600; color: rgb(55, 65, 81); margin-bottom: 0.5rem"
          >
            Skill Name *
          </label>
          <InputText v-model="skillForm.name" placeholder="Enter skill name" style="width: 100%" />
        </div>

        <div>
          <label
            style="display: block; font-weight: 600; color: rgb(55, 65, 81); margin-bottom: 0.5rem"
          >
            Category *
          </label>
          <Dropdown
            v-model="skillForm.category"
            :options="categories"
            option-label="label"
            option-value="value"
            placeholder="Select a category"
            style="width: 100%"
          />
        </div>

        <div>
          <label
            style="display: block; font-weight: 600; color: rgb(55, 65, 81); margin-bottom: 0.5rem"
          >
            Description
          </label>
          <Textarea
            v-model="skillForm.description"
            placeholder="Enter skill description"
            rows="3"
            style="width: 100%"
          />
        </div>
      </div>

      <template #footer>
        <Button
          label="Cancel"
          icon="pi pi-times"
          @click="showEditDialog = false"
          :disabled="loading"
          text
        />
        <Button
          label="Save Changes"
          icon="pi pi-check"
          @click="saveSkill"
          :loading="loading"
          autofocus
        />
      </template>
    </Dialog>
  </div>
</template>
