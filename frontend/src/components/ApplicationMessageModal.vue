<template>
  <Dialog :visible="visible" @update:visible="val => emits('update:visible', val)" modal header="Application Message" :closable="!submitting" :style="{ width: '400px' }">
    <div>
      <label for="application-message" class="block mb-2 font-semibold">Message</label>
      <Textarea id="application-message" v-model="message" rows="5" class="w-full" :disabled="submitting"/>
    </div>
    <template #footer>
      <Button label="Cancel" @click="onCancel" :disabled="submitting" class="p-button-text"/>
      <Button label="Apply" @click="onSubmit" :loading="submitting" :disabled="!message.trim() || submitting" class="p-button-primary"/>
    </template>
  </Dialog>
</template>

<script setup>
import { ref, watch, defineEmits, defineProps } from 'vue'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'
import Button from 'primevue/button'

const props = defineProps({
  visible: Boolean,
  submitting: Boolean
})
const emits = defineEmits(['update:visible', 'submit'])
const message = ref('')

watch(() => props.visible, (val) => {
  if (val) message.value = ''
})

function onCancel() {
  emits('update:visible', false)
}
function onSubmit() {
  emits('submit', message.value)
}
</script>
