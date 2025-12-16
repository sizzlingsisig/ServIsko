<template>
  <Toast />
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar: Conversations List -->
    <div class="w-1/3 bg-white border-r border-gray-200 flex flex-col">
      <div class="p-4 border-b border-gray-200 bg-primary-600">
        <h2 class="text-xl font-semibold text-white">Messages</h2>
      </div>

      <div class="overflow-y-auto flex-1 relative">
        <div v-if="loadingConversations" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-70 z-10">
          <i class="pi pi-spin pi-spinner text-3xl text-primary-600"></i>
        </div>
        <template v-else>
          <div
            v-for="conv in conversations"
            :key="conv.id"
            @click="selectConversation(conv)"
            :class="['p-4 border-b border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors',
                     selectedConversation?.id === conv.id ? 'bg-primary-100 border-l-4 border-l-primary-600' : '']"
          >
            <div class="flex items-center space-x-3">
              <Avatar
                :label="getOtherParticipant(conv)?.name?.charAt(0).toUpperCase()"
                class="bg-primary-600 text-white"
                shape="circle"
                size="large"
              />
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 truncate">{{ getOtherParticipant(conv)?.name }}</p>
                <p class="text-sm text-gray-600 truncate">{{ conv.latest_message?.message_text || 'No messages yet' }}</p>
              </div>
            </div>
          </div>

          <div v-if="conversations.length === 0" class="p-8 text-center text-gray-500">
            <i class="pi pi-comments text-5xl mb-3 text-gray-300"></i>
            <p class="text-sm">No conversations yet</p>
          </div>
        </template>
      </div>
    </div>

    <!-- Chat Area -->

    <div class="flex-1 flex flex-col">
      <div v-if="selectedConversation" class="flex flex-col h-full">
        <!-- Chat Header -->
        <div class="bg-white border-b border-gray-300 p-4 flex items-center gap-3 shadow-sm">
          <Avatar
            :label="getOtherParticipant(selectedConversation)?.name?.charAt(0).toUpperCase()"
            class="bg-primary-600 text-white"
            shape="circle"
            size="large"
          />
          <h3 class="text-lg font-semibold text-gray-900">
            {{ getOtherParticipant(selectedConversation)?.name }}
          </h3>
        </div>

        <!-- Messages -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 relative">
          <div v-if="loadingMessages" class="absolute inset-0 flex items-center justify-center bg-gray-50 bg-opacity-80 z-10">
            <i class="pi pi-spin pi-spinner text-3xl text-primary-600"></i>
          </div>
          <template v-else>
            <div
              v-for="message in messages"
              :key="message.id"
              :class="['flex w-full', message.user_id === currentUser.id ? 'justify-end' : 'justify-start']"
            >
              <div
                :class="[
                  'max-w-[70%] px-4 py-3 rounded-2xl shadow-sm',
                  message.user_id === currentUser.id
                    ? 'bg-primary-600 text-white rounded-br-sm'
                    : 'bg-white text-gray-900 border border-gray-200 rounded-bl-sm'
                ]"
              >
                <p class="text-sm leading-relaxed break-words">{{ message.message_text }}</p>
                <p
                  :class="[
                    'text-xs mt-1.5 font-medium',
                    message.user_id === currentUser.id ? 'text-primary-100' : 'text-gray-500'
                  ]"
                >
                  {{ formatTime(message.created_at) }}
                </p>
              </div>
            </div>
          </template>
        </div>

        <!-- Message Input -->
        <div class="bg-white border-t border-gray-300 p-4 shadow-sm">
          <form @submit.prevent="sendMessage" class="flex gap-3">
            <InputText
              v-model="newMessage"
              placeholder="Type a message..."
              class="flex-1"
              :disabled="!selectedConversation || sendingMessage"
            />
            <Button
              type="submit"
              icon="pi pi-send"
              :disabled="!newMessage.trim() || sendingMessage"
              :loading="sendingMessage"
              class="bg-primary-600 border-primary-600 hover:bg-primary-700 disabled:opacity-50"
              severity="primary"
            />
          </form>
        </div>
      </div>

      <!-- No Conversation Selected -->
      <div v-else class="flex-1 flex flex-col items-center justify-center bg-gray-50">
        <i class="pi pi-comment text-7xl text-gray-300 mb-4"></i>
        <p class="text-gray-600 text-lg font-medium">Select a conversation to start messaging</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import api from '@/composables/axios'
import { useRouter } from 'vue-router'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'

const router = useRouter()

const conversations = ref([])
const selectedConversation = ref(null)
const messages = ref([])
const newMessage = ref('')
const messagesContainer = ref(null)
const currentUser = ref(null)
const loadingConversations = ref(false)
const loadingMessages = ref(false)
const sendingMessage = ref(false)

const toast = useToast()

onMounted(async () => {
  await fetchCurrentUser()
  await fetchConversations()
})

// Watch messages to auto-scroll when they change
watch(messages, async () => {
  await nextTick()
  scrollToBottom()
}, { deep: true })

const fetchCurrentUser = async () => {
  try {
    const resp = await api.get('/user/')
    if (resp.data && resp.data.success && resp.data.data) {
      currentUser.value = resp.data.data
    }
  } catch (error) {
      router.push('/')

    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load user info, Going back to home', life: 3000 })
  }

}

const fetchConversations = async () => {
  loadingConversations.value = true
  try {
    const response = await api.get('/conversations')
    if (Array.isArray(response.data)) {
      conversations.value = response.data
    } else if (response.data.data && Array.isArray(response.data.data)) {
      conversations.value = response.data.data
    } else {
      conversations.value = []
    }
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load conversations', life: 3000 })
    conversations.value = []
  } finally {
    loadingConversations.value = false
  }
}

const selectConversation = async (conversation) => {
  selectedConversation.value = conversation
  messages.value = [] // Clear messages first
  await fetchMessages(conversation.id)
}

const fetchMessages = async (conversationId) => {
  loadingMessages.value = true
  try {
    const response = await api.get(`/conversations/${conversationId}/messages`)
    if (Array.isArray(response.data)) {
      messages.value = response.data
    } else if (response.data.data && Array.isArray(response.data.data)) {
      messages.value = response.data.data
    } else {
      messages.value = []
    }
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load messages', life: 3000 })
    messages.value = []
  } finally {
    loadingMessages.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedConversation.value) return

  const messageToSend = newMessage.value
  newMessage.value = '' // Clear input immediately
  sendingMessage.value = true
  try {
    const response = await api.post(
      `/conversations/${selectedConversation.value.id}/messages`,
      { message_text: messageToSend }
    )
    const messageData = response.data.data || response.data
    messages.value.push(messageData)
    toast.add({ severity: 'success', summary: 'Message Sent', detail: 'Your message was sent successfully.', life: 2000 })
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to send message', life: 3000 })
    newMessage.value = messageToSend // Restore message on error
  } finally {
    sendingMessage.value = false
  }
}

const getOtherParticipant = (conversation) => {
  if (!conversation.participants || !currentUser.value) return { name: 'Unknown' }
  return conversation.participants.find(p => p.id !== currentUser.value.id) || { name: 'Unknown' }
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.bg-primary-100 {
  background-color: #f3e6eb;
}
.bg-primary-600 {
  background-color: #6d0019;
}
.bg-primary-700 {
  background-color: #5a0013;
}
.text-primary-100 {
  color: #fce7ed;
}
.border-primary-600 {
  border-color: #6d0019;
}
.border-l-primary-600 {
  border-left-color: #6d0019;
}
.hover\:bg-primary-700:hover {
  background-color: #5a0013 !important;
}
</style>
