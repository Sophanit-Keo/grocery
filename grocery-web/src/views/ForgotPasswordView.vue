<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import AuthShell from '../components/AuthShell.vue'
import api, { apiErrorMessage, ensureCsrf, validationErrors } from '../services/api'

const email = ref('')
const errors = ref({})
const message = ref('')
const success = ref(false)
const submitting = ref(false)

async function submit() {
  errors.value = {}
  message.value = ''
  submitting.value = true

  try {
    await ensureCsrf()
    const response = await api.post('/api/v1/auth/forgot-password', { email: email.value })
    success.value = true
    message.value = response.data.message
  } catch (error) {
    errors.value = validationErrors(error)
    message.value = apiErrorMessage(error, 'We could not process that request.')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AuthShell title="Reset your password" subtitle="Enter your email and we’ll send reset instructions if the account exists.">
    <form class="auth-form" @submit.prevent="submit">
      <div v-if="message" :class="['form-alert', success ? 'success' : 'error']" role="status">{{ message }}</div>
      <label>
        Email address
        <input v-model.trim="email" type="email" autocomplete="email" required autofocus />
        <span v-if="errors.email" class="field-error">{{ errors.email[0] }}</span>
      </label>
      <button class="form-button" type="submit" :disabled="submitting">
        {{ submitting ? 'Sending…' : 'Send reset link' }}
      </button>
    </form>
    <p class="auth-switch"><RouterLink to="/login">Back to sign in</RouterLink></p>
  </AuthShell>
</template>
