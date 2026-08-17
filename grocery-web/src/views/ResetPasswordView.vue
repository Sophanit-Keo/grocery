<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import AuthShell from '../components/AuthShell.vue'
import api, { apiErrorMessage, ensureCsrf, validationErrors } from '../services/api'

const route = useRoute()
const form = reactive({
  token: typeof route.query.token === 'string' ? route.query.token : '',
  email: typeof route.query.email === 'string' ? route.query.email : '',
  password: '',
  password_confirmation: '',
})
const errors = ref({})
const message = ref(form.token ? '' : 'This reset link is incomplete. Request a new password reset email.')
const success = ref(false)
const submitting = ref(false)

async function submit() {
  if (!form.token) return

  errors.value = {}
  message.value = ''
  submitting.value = true

  try {
    await ensureCsrf()
    const response = await api.post('/api/v1/auth/reset-password', form)
    success.value = true
    message.value = response.data.message || 'Your password has been reset. You can now sign in.'
    form.password = ''
    form.password_confirmation = ''
  } catch (error) {
    errors.value = validationErrors(error)
    message.value = apiErrorMessage(error, 'This password reset link is invalid or has expired.')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AuthShell title="Choose a new password" subtitle="Create a strong password that you do not use elsewhere.">
    <form class="auth-form" @submit.prevent="submit">
      <div v-if="message" :class="['form-alert', success ? 'success' : 'error']" role="status">{{ message }}</div>
      <label>
        Email address
        <input v-model.trim="form.email" type="email" autocomplete="email" required />
        <span v-if="errors.email" class="field-error">{{ errors.email[0] }}</span>
      </label>
      <label>
        New password
        <input v-model="form.password" type="password" autocomplete="new-password" required />
        <small>At least 12 characters with mixed case, a number, and a symbol.</small>
        <span v-if="errors.password" class="field-error">{{ errors.password[0] }}</span>
      </label>
      <label>
        Confirm new password
        <input v-model="form.password_confirmation" type="password" autocomplete="new-password" required />
      </label>
      <button class="form-button" type="submit" :disabled="submitting || !form.token">
        {{ submitting ? 'Resetting…' : 'Reset password' }}
      </button>
    </form>
    <p class="auth-switch"><RouterLink to="/login">Return to sign in</RouterLink></p>
  </AuthShell>
</template>
