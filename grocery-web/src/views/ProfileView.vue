<script setup>
import { reactive, ref } from 'vue'
import AccountShell from '../components/AccountShell.vue'
import { useAuth } from '../composables/useAuth'
import api, { apiErrorMessage, ensureCsrf, validationErrors } from '../services/api'

const auth = useAuth()
const form = reactive({
  first_name: auth.state.user?.first_name ?? '',
  last_name: auth.state.user?.last_name ?? '',
  email: auth.state.user?.email ?? '',
})
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
    const response = await api.put('/api/v1/auth/profile', form)
    await auth.refresh()
    success.value = true
    message.value = response.data.message
  } catch (error) {
    success.value = false
    errors.value = validationErrors(error)
    message.value = apiErrorMessage(error, 'Your profile could not be updated.')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <AccountShell>
    <div class="page-heading">
      <p>Personal details</p>
      <h1>Your profile</h1>
      <span>Keep your contact information accurate and up to date.</span>
    </div>
    <div v-if="!auth.state.user?.is_verified" class="form-alert warning">
      Your new email address needs verification before you can use protected grocery features.
    </div>
    <section class="settings-card">
      <form class="settings-form" @submit.prevent="submit">
        <div v-if="message" :class="['form-alert', success ? 'success' : 'error']" role="status">{{ message }}</div>
        <div class="field-grid">
          <label>
            First name
            <input v-model.trim="form.first_name" autocomplete="given-name" required />
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
        <button class="form-button compact" type="submit" :disabled="submitting">
          {{ submitting ? 'Saving…' : 'Save profile' }}
        </button>
      </form>
    </section>
  </AccountShell>
</template>
