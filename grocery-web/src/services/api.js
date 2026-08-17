import axios from 'axios'

const baseURL = (import.meta.env.VITE_API_URL || 'http://localhost:8000').replace(/\/$/, '')

const api = axios.create({
  baseURL,
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: true,
  withXSRFToken: true,
})

let csrfPromise = null

export async function ensureCsrf(force = false) {
  if (force) {
    csrfPromise = null
  }

  csrfPromise ??= api.get('/sanctum/csrf-cookie').catch((error) => {
    csrfPromise = null
    throw error
  })

  await csrfPromise
}

export function resetCsrf() {
  csrfPromise = null
}

export function validationErrors(error) {
  return error.response?.data?.errors ?? {}
}

export function apiErrorMessage(error, fallback = 'Something went wrong. Please try again.') {
  if (error.response?.status === 419) {
    return 'Your session expired. Please try again.'
  }

  if (error.response?.status === 429) {
    return 'Too many attempts. Please wait a moment and try again.'
  }

  if (error.response?.status === 401) {
    return 'Please sign in to continue.'
  }

  return error.response?.data?.message || fallback
}

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const request = error.config
    const shouldRetry =
      error.response?.status === 419 &&
      request &&
      !request._csrfRetried &&
      !String(request.url).includes('/sanctum/csrf-cookie')

    if (!shouldRetry) {
      return Promise.reject(error)
    }

    request._csrfRetried = true
    await ensureCsrf(true)

    return api(request)
  },
)

export default api
