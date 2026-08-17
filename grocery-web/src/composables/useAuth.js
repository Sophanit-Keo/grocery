import { readonly, reactive } from 'vue'
import api, { ensureCsrf, resetCsrf } from '../services/api'

const state = reactive({
  user: null,
  initialized: false,
  loading: false,
})

export async function refreshUser() {
  try {
    const { data } = await api.get('/api/v1/auth/me')
    state.user = data.data
  } catch (error) {
    if (error.response?.status === 401) {
      state.user = null
    } else {
      throw error
    }
  }

  return state.user
}

export async function initializeAuth() {
  if (state.initialized) {
    return state.user
  }

  state.loading = true

  try {
    await refreshUser()
  } finally {
    state.loading = false
    state.initialized = true
  }

  return state.user
}

export async function register(credentials) {
  await ensureCsrf()
  const { data } = await api.post('/api/v1/auth/register', credentials)
  state.user = data.data.user
  state.initialized = true

  return data
}

export async function login(credentials) {
  await ensureCsrf()
  const { data } = await api.post('/api/v1/auth/login', credentials)

  if (!data.two_factor) {
    state.user = data.data.user
    state.initialized = true
  }

  return data
}

export async function completeTwoFactor(payload) {
  await ensureCsrf()
  await api.post('/api/v1/auth/two-factor-challenge', payload)
  state.initialized = true

  return refreshUser()
}

export async function logout() {
  try {
    await ensureCsrf()
    await api.post('/api/v1/auth/logout')
  } finally {
    state.user = null
    state.initialized = true
    resetCsrf()
  }
}

export const authState = readonly(state)

export function useAuth() {
  return {
    state: authState,
    initialize: initializeAuth,
    refresh: refreshUser,
    register,
    login,
    completeTwoFactor,
    logout,
  }
}
