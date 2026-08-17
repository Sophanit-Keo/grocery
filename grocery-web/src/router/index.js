import { createRouter, createWebHistory } from 'vue-router'
import { authState, initializeAuth } from '../composables/useAuth'

const routes = [
  { path: '/', name: 'home', component: () => import('../views/HomeView.vue') },
  { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), meta: { guest: true } },
  { path: '/register', name: 'register', component: () => import('../views/RegisterView.vue'), meta: { guest: true } },
  { path: '/forgot-password', name: 'forgot-password', component: () => import('../views/ForgotPasswordView.vue'), meta: { guest: true } },
  { path: '/reset-password', name: 'reset-password', component: () => import('../views/ResetPasswordView.vue'), meta: { guest: true } },
  { path: '/two-factor-challenge', name: 'two-factor-challenge', component: () => import('../views/TwoFactorChallengeView.vue'), meta: { guest: true } },
  { path: '/verify-email', name: 'verify-email', component: () => import('../views/VerifyEmailView.vue'), meta: { auth: true, allowUnverified: true } },
  { path: '/account/profile', name: 'profile', component: () => import('../views/ProfileView.vue'), meta: { auth: true } },
  { path: '/account/security', name: 'security', component: () => import('../views/SecurityView.vue'), meta: { auth: true } },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach(async (to) => {
  const verificationUrl = typeof to.query.verification_url === 'string' ? to.query.verification_url : null

  if (verificationUrl) {
    sessionStorage.setItem('pending_verification_url', verificationUrl)
  }

  try {
    await initializeAuth()
  } catch {
    if (to.meta.auth) {
      return { name: 'login', query: { redirect: to.fullPath } }
    }
  }

  if (to.meta.auth && !authState.user) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guest && authState.user) {
    return authState.user.is_verified ? { name: 'profile' } : { name: 'verify-email' }
  }

  if (to.meta.auth && !to.meta.allowUnverified && !authState.user?.is_verified) {
    return { name: 'verify-email' }
  }

  return true
})

export default router
