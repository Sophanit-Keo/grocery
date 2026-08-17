<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AuthShell from '../components/AuthShell.vue'
import { useAuth } from '../composables/useAuth'
import api, { apiErrorMessage, ensureCsrf } from '../services/api'

const auth = useAuth()
const router = useRouter()
const message = ref('')
const success = ref(false)
const working = ref(false)

async function verifyPendingLink() {
  const verificationUrl = sessionStorage.getItem('pending_verification_url')

  if (!verificationUrl) return

  working.value = true
  message.value = ''

  try {
    await api.get(verificationUrl)
    await auth.refresh()
    sessionStorage.removeItem('pending_verification_url')
    await router.replace({ name: 'verify-email' })
    success.value = true
    message.value = 'Your email address is verified. Your account is ready.'
  } catch (error) {
    message.value = apiErrorMessage(error, 'This verification link is invalid or has expired. Request a new one below.')
  } finally {
    working.value = false
  }
}

async function resend() {
  working.value = true
  message.value = ''

  try {
    await ensureCsrf()
    const response = await api.post('/api/v1/auth/email/verification-notification')
    success.value = true
    message.value = response.data.message || 'A new verification link has been sent.'
  } catch (error) {
    success.value = false
    message.value = apiErrorMessage(error, 'We could not send another verification email.')
  } finally {
    working.value = false
  }
}

onMounted(async () => {
  if (auth.state.user?.is_verified) {
    success.value = true
    message.value = 'Your email address is already verified.'
    return
  }

  await verifyPendingLink()
})
</script>

<template>
  <AuthShell eyebrow="One last step" title="Verify your email" :subtitle="`We sent a secure verification link to ${auth.state.user?.email}.`">
    <div v-if="message" :class="['form-alert', success ? 'success' : 'error']" role="status">{{ message }}</div>
    <div v-if="working" class="loading-line">Checking your verification link…</div>
    <div v-if="auth.state.user?.is_verified" class="auth-actions-stack">
      <RouterLink class="form-button button-link" to="/account/profile">Continue to your account</RouterLink>
    </div>
    <div v-else class="auth-actions-stack">
      <p>Open the link in your inbox. If you do not see it, check spam or request a fresh email.</p>
      <button class="form-button" type="button" :disabled="working" @click="resend">
        {{ working ? 'Please wait…' : 'Resend verification email' }}
      </button>
    </div>
  </AuthShell>
</template>
