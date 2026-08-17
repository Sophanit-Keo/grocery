<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AuthShell from '../components/AuthShell.vue'
import { useAuth } from '../composables/useAuth'
import { apiErrorMessage, validationErrors } from '../services/api'

const auth = useAuth()
const router = useRouter()
const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const errors = ref({})
const message = ref('')
const submitting = ref(false)

async function submit() {
  errors.value = {}
  message.value = ''
  submitting.value = true

  try {
    await auth.register(form)
    await router.push({ name: 'verify-email' })
  } catch (error) {
    errors.value = validationErrors(error)
    message.value = apiErrorMessage(error, 'We could not create your account.')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AuthShell title="Create your account" subtitle="Get fresh groceries moving in just a minute.">
    <form class="auth-form" @submit.prevent="submit">
      <div v-if="message" class="form-alert error" role="alert">{{ message }}</div>
      <div class="field-grid">
        <label>
          First name
          <input v-model.trim="form.first_name" autocomplete="given-name" required autofocus />
          <span v-if="errors.first_name" class="field-error">{{ errors.first_name[0] }}</span>
        </label>
        <label>
          Last name
          <input v-model.trim="form.last_name" autocomplete="family-name" required />
          <span v-if="errors.last_name" class="field-error">{{ errors.last_name[0] }}</span>
        </label>
      </div>
      <label>
        Email address
        <input v-model.trim="form.email" type="email" autocomplete="email" required />
        <span v-if="errors.email" class="field-error">{{ errors.email[0] }}</span>
      </label>
      <label>
        Password
        <input v-model="form.password" type="password" autocomplete="new-password" required />
        <small>Use at least 12 characters with upper/lowercase letters, a number, and a symbol.</small>
        <span v-if="errors.password" class="field-error">{{ errors.password[0] }}</span>
      </label>
      <label>
        Confirm password
        <input v-model="form.password_confirmation" type="password" autocomplete="new-password" required />
      </label>
      <button class="form-button" type="submit" :disabled="submitting">
        {{ submitting ? 'Creating account…' : 'Create account' }}
      </button>
    </form>
    <p class="auth-switch">Already have an account? <RouterLink to="/login">Sign in</RouterLink></p>
  </AuthShell>
</template>
