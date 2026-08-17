<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AuthShell from '../components/AuthShell.vue'
import { useAuth } from '../composables/useAuth'
import { apiErrorMessage, validationErrors } from '../services/api'

const auth = useAuth()
const route = useRoute()
const router = useRouter()
const recoveryMode = ref(false)
const value = ref('')
const errors = ref({})
const message = ref('')
const submitting = ref(false)
const fieldName = computed(() => (recoveryMode.value ? 'recovery_code' : 'code'))

async function submit() {
  errors.value = {}
  message.value = ''
  submitting.value = true

  try {
    await auth.completeTwoFactor({ [fieldName.value]: value.value })
    const redirect = typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
      ? route.query.redirect
      : '/account/profile'
    await router.push(auth.state.user?.is_verified ? redirect : { name: 'verify-email' })
  } catch (error) {
    errors.value = validationErrors(error)
    message.value = apiErrorMessage(error, 'That authentication code is not valid.')
  } finally {
    submitting.value = false
  }
}

function toggleMode() {
  recoveryMode.value = !recoveryMode.value
  value.value = ''
  errors.value = {}
  message.value = ''
}
</script>

<template>
  <AuthShell
    eyebrow="Extra security"
    title="Two-factor authentication"
    :subtitle="recoveryMode ? 'Enter one of your unused recovery codes.' : 'Enter the six-digit code from your authenticator app.'"
  >
    <form class="auth-form" @submit.prevent="submit">
      <div v-if="message" class="form-alert error" role="alert">{{ message }}</div>
      <label>
        {{ recoveryMode ? 'Recovery code' : 'Authentication code' }}
        <input
          v-model.trim="value"
          :autocomplete="recoveryMode ? 'off' : 'one-time-code'"
          :inputmode="recoveryMode ? 'text' : 'numeric'"
          required
          autofocus
        />
        <span v-if="errors[fieldName]" class="field-error">{{ errors[fieldName][0] }}</span>
      </label>
      <button class="form-button" type="submit" :disabled="submitting">
        {{ submitting ? 'Verifying…' : 'Verify and sign in' }}
      </button>
      <button class="link-button" type="button" @click="toggleMode">
        {{ recoveryMode ? 'Use an authenticator code' : 'Use a recovery code instead' }}
      </button>
    </form>
  </AuthShell>
</template>
