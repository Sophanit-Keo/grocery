<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import AuthShell from '../components/AuthShell.vue'
import { useAuth } from '../composables/useAuth'
import { apiErrorMessage, validationErrors } from '../services/api'

const auth = useAuth()
const route = useRoute()
const router = useRouter()
const form = reactive({ email: '', password: '', remember: false })
const errors = ref({})
const message = ref('')
const submitting = ref(false)

function intendedRoute() {
  return typeof route.query.redirect === 'string' && route.query.redirect.startsWith('/')
    ? route.query.redirect
    : '/account/profile'
}

async function submit() {
  errors.value = {}
  message.value = ''
  submitting.value = true

  try {
    const data = await auth.login(form)

    if (data.two_factor) {
      await router.push({ name: 'two-factor-challenge', query: { redirect: intendedRoute() } })
      return
    }

    if (sessionStorage.getItem('pending_verification_url') || !auth.state.user?.is_verified) {
      await router.push({ name: 'verify-email' })
      return
    }

    await router.push(intendedRoute())
  } catch (error) {
    errors.value = validationErrors(error)
    message.value = apiErrorMessage(error, 'The provided credentials are incorrect.')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AuthShell title="Welcome back" subtitle="Sign in to continue to your FreshCart account.">
    <form class="auth-form" @submit.prevent="submit">
      <div v-if="message" class="form-alert error" role="alert">{{ message }}</div>
      <label>
        Email address
        <input v-model.trim="form.email" type="email" autocomplete="email" required autofocus />
        <span v-if="errors.email" class="field-error">{{ errors.email[0] }}</span>
      </label>
      <label>
        <span class="label-row"><span>Password</span><RouterLink to="/forgot-password">Forgot password?</RouterLink></span>
        <input v-model="form.password" type="password" autocomplete="current-password" required />
        <span v-if="errors.password" class="field-error">{{ errors.password[0] }}</span>
      </label>
      <label class="check-row">
        <input v-model="form.remember" type="checkbox" />
        <span>Keep me signed in on this device</span>
      </label>
      <button class="form-button" type="submit" :disabled="submitting">
        {{ submitting ? 'Signing in…' : 'Sign in' }}
      </button>
    </form>
    <p class="auth-switch">New to FreshCart? <RouterLink to="/register">Create an account</RouterLink></p>
  </AuthShell>
</template>
