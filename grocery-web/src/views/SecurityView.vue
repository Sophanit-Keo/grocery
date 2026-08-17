<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import AccountShell from '../components/AccountShell.vue'
import { useAuth } from '../composables/useAuth'
import api, { apiErrorMessage, ensureCsrf, validationErrors } from '../services/api'

const auth = useAuth()
const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })
const passwordErrors = ref({})
const passwordMessage = ref('')
const passwordSuccess = ref(false)
const passwordBusy = ref(false)

const twoFactorPassword = ref('')
const twoFactorCode = ref('')
const twoFactorQr = ref('')
const recoveryCodes = ref([])
const twoFactorMessage = ref('')
const twoFactorSuccess = ref(false)
const twoFactorBusy = ref(false)
const twoFactorQrDataUrl = computed(() => (
  twoFactorQr.value ? `data:image/svg+xml;charset=utf-8,${encodeURIComponent(twoFactorQr.value)}` : ''
))

const sessions = ref([])
const sessionPassword = ref('')
const sessionMessage = ref('')
const sessionSuccess = ref(false)
const sessionsBusy = ref(false)

async function updatePassword() {
  passwordErrors.value = {}
  passwordMessage.value = ''
  passwordBusy.value = true

  try {
    await ensureCsrf()
    const response = await api.put('/api/v1/auth/password', passwordForm)
    passwordSuccess.value = true
    passwordMessage.value = response.data.message || 'Password updated. Other browser sessions were signed out.'
    Object.assign(passwordForm, { current_password: '', password: '', password_confirmation: '' })
    await loadSessions()
  } catch (error) {
    passwordSuccess.value = false
    passwordErrors.value = validationErrors(error)
    passwordMessage.value = apiErrorMessage(error, 'Your password could not be updated.')
  } finally {
    passwordBusy.value = false
  }
}

async function confirmPassword(password) {
  await ensureCsrf()
  await api.post('/api/v1/auth/confirm-password', { password })
}

async function beginTwoFactor() {
  twoFactorMessage.value = ''
  twoFactorBusy.value = true

  try {
    await confirmPassword(twoFactorPassword.value)
    await api.post('/api/v1/auth/two-factor-authentication')
    const [qrResponse, recoveryResponse] = await Promise.all([
      api.get('/api/v1/auth/two-factor-authentication/qr-code'),
      api.get('/api/v1/auth/two-factor-authentication/recovery-codes'),
    ])
    twoFactorQr.value = qrResponse.data.svg
    recoveryCodes.value = recoveryResponse.data
    twoFactorSuccess.value = true
    twoFactorMessage.value = 'Scan this QR code, then enter the six-digit code to finish setup.'
  } catch (error) {
    twoFactorSuccess.value = false
    twoFactorMessage.value = apiErrorMessage(error, 'Two-factor authentication could not be started.')
  } finally {
    twoFactorBusy.value = false
  }
}

async function confirmTwoFactor() {
  twoFactorBusy.value = true
  twoFactorMessage.value = ''

  try {
    await api.post('/api/v1/auth/two-factor-authentication/confirm', { code: twoFactorCode.value })
    await auth.refresh()
    twoFactorQr.value = ''
    twoFactorCode.value = ''
    twoFactorPassword.value = ''
    twoFactorSuccess.value = true
    twoFactorMessage.value = 'Two-factor authentication is now enabled. Store your recovery codes safely.'
  } catch (error) {
    twoFactorSuccess.value = false
    twoFactorMessage.value = apiErrorMessage(error, 'That authentication code is not valid.')
  } finally {
    twoFactorBusy.value = false
  }
}

async function revealRecoveryCodes(regenerate = false) {
  twoFactorBusy.value = true
  twoFactorMessage.value = ''

  try {
    await confirmPassword(twoFactorPassword.value)

    if (regenerate) {
      await api.post('/api/v1/auth/two-factor-authentication/recovery-codes')
    }

    const response = await api.get('/api/v1/auth/two-factor-authentication/recovery-codes')
    recoveryCodes.value = response.data
    twoFactorSuccess.value = true
    twoFactorMessage.value = regenerate
      ? 'New recovery codes generated. Previous codes no longer work.'
      : 'Keep these one-time codes in a secure place.'
  } catch (error) {
    twoFactorSuccess.value = false
    twoFactorMessage.value = apiErrorMessage(error, 'Recovery codes could not be loaded.')
  } finally {
    twoFactorBusy.value = false
  }
}

async function disableTwoFactor() {
  twoFactorBusy.value = true
  twoFactorMessage.value = ''

  try {
    await confirmPassword(twoFactorPassword.value)
    await api.delete('/api/v1/auth/two-factor-authentication')
    await auth.refresh()
    recoveryCodes.value = []
    twoFactorPassword.value = ''
    twoFactorSuccess.value = true
    twoFactorMessage.value = 'Two-factor authentication has been disabled.'
  } catch (error) {
    twoFactorSuccess.value = false
    twoFactorMessage.value = apiErrorMessage(error, 'Two-factor authentication could not be disabled.')
  } finally {
    twoFactorBusy.value = false
  }
}

async function loadSessions() {
  sessionsBusy.value = true

  try {
    const response = await api.get('/api/v1/auth/sessions')
    sessions.value = response.data.data
  } catch (error) {
    sessionSuccess.value = false
    sessionMessage.value = apiErrorMessage(error, 'Browser sessions could not be loaded.')
  } finally {
    sessionsBusy.value = false
  }
}

async function logoutOtherSessions() {
  sessionsBusy.value = true
  sessionMessage.value = ''

  try {
    await ensureCsrf()
    await api.delete('/api/v1/auth/sessions/other', { data: { password: sessionPassword.value } })
    sessionPassword.value = ''
    sessionSuccess.value = true
    sessionMessage.value = 'Other browser sessions have been signed out.'
    await loadSessions()
  } catch (error) {
    sessionSuccess.value = false
    sessionMessage.value = apiErrorMessage(error, 'Other sessions could not be signed out.')
  } finally {
    sessionsBusy.value = false
  }
}

function describeAgent(agent) {
  if (!agent) return 'Unknown browser'
  return agent.length > 90 ? `${agent.slice(0, 90)}…` : agent
}

function formatDate(date) {
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date))
}

onMounted(loadSessions)
</script>

<template>
  <AccountShell>
    <div class="page-heading">
      <p>Account protection</p>
      <h1>Password & security</h1>
      <span>Use a unique password, add an authenticator, and review active sessions.</span>
    </div>

    <section class="settings-card">
      <div class="settings-title">
        <div><h2>Change password</h2><p>This signs out every other browser session.</p></div>
      </div>
      <form class="settings-form" @submit.prevent="updatePassword">
        <div v-if="passwordMessage" :class="['form-alert', passwordSuccess ? 'success' : 'error']">{{ passwordMessage }}</div>
        <label>
          Current password
          <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" required />
          <span v-if="passwordErrors.current_password" class="field-error">{{ passwordErrors.current_password[0] }}</span>
        </label>
        <div class="field-grid">
          <label>
            New password
            <input v-model="passwordForm.password" type="password" autocomplete="new-password" required />
            <span v-if="passwordErrors.password" class="field-error">{{ passwordErrors.password[0] }}</span>
          </label>
          <label>
            Confirm new password
            <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" required />
          </label>
        </div>
        <button class="form-button compact" type="submit" :disabled="passwordBusy">{{ passwordBusy ? 'Updating…' : 'Update password' }}</button>
      </form>
    </section>

    <section class="settings-card">
      <div class="settings-title split-title">
        <div><h2>Authenticator app</h2><p>Require a time-based code when you sign in.</p></div>
        <span :class="['status-pill', auth.state.user?.two_factor_enabled ? 'enabled' : '']">
          {{ auth.state.user?.two_factor_enabled ? 'Enabled' : 'Not enabled' }}
        </span>
      </div>
      <div class="settings-form">
        <div v-if="twoFactorMessage" :class="['form-alert', twoFactorSuccess ? 'success' : 'error']">{{ twoFactorMessage }}</div>
        <label>
          Confirm your password to manage 2FA
          <input v-model="twoFactorPassword" type="password" autocomplete="current-password" />
        </label>
        <template v-if="twoFactorQr">
          <div class="qr-code"><img :src="twoFactorQrDataUrl" alt="Scan this QR code with your authenticator app" /></div>
          <label>
            Six-digit authentication code
            <input v-model.trim="twoFactorCode" inputmode="numeric" autocomplete="one-time-code" />
          </label>
          <button class="form-button compact" type="button" :disabled="twoFactorBusy" @click="confirmTwoFactor">Confirm and enable</button>
        </template>
        <div v-else class="button-row">
          <button v-if="!auth.state.user?.two_factor_enabled" class="form-button compact" type="button" :disabled="twoFactorBusy || !twoFactorPassword" @click="beginTwoFactor">Enable 2FA</button>
          <template v-else>
            <button class="secondary-button" type="button" :disabled="twoFactorBusy || !twoFactorPassword" @click="revealRecoveryCodes(false)">Show recovery codes</button>
            <button class="secondary-button" type="button" :disabled="twoFactorBusy || !twoFactorPassword" @click="revealRecoveryCodes(true)">Generate new codes</button>
            <button class="danger-button" type="button" :disabled="twoFactorBusy || !twoFactorPassword" @click="disableTwoFactor">Disable 2FA</button>
          </template>
        </div>
        <div v-if="recoveryCodes.length" class="recovery-box">
          <strong>Recovery codes</strong>
          <p>Each code works once. Store them outside this browser.</p>
          <code v-for="code in recoveryCodes" :key="code">{{ code }}</code>
        </div>
      </div>
    </section>

    <section class="settings-card">
      <div class="settings-title"><div><h2>Browser sessions</h2><p>These are the browser sessions currently signed in to your account.</p></div></div>
      <div class="settings-form">
        <div v-if="sessionMessage" :class="['form-alert', sessionSuccess ? 'success' : 'error']">{{ sessionMessage }}</div>
        <p v-if="sessionsBusy && !sessions.length" class="muted-text">Loading sessions…</p>
        <div v-for="session in sessions" :key="`${session.ip_address}-${session.last_active_at}`" class="session-row">
          <div class="session-icon">◉</div>
          <div>
            <strong>{{ describeAgent(session.user_agent) }}</strong>
            <span>{{ session.ip_address || 'Unknown IP' }} · {{ formatDate(session.last_active_at) }}</span>
          </div>
          <span v-if="session.is_current_device" class="status-pill enabled">This device</span>
        </div>
        <div class="session-revoke">
          <label>
            Password
            <input v-model="sessionPassword" type="password" autocomplete="current-password" placeholder="Required to sign out other devices" />
          </label>
          <button class="secondary-button" type="button" :disabled="sessionsBusy || !sessionPassword" @click="logoutOtherSessions">Sign out other devices</button>
        </div>
      </div>
    </section>
  </AccountShell>
</template>
