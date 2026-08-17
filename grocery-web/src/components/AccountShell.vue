<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const auth = useAuth()

async function signOut() {
  await auth.logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <div class="account-page">
    <header class="account-header">
      <div class="account-header-inner">
        <RouterLink class="account-brand" to="/">
          <span>F</span> FreshCart
        </RouterLink>
        <div class="account-user">
          <span>{{ auth.state.user?.first_name }} {{ auth.state.user?.last_name }}</span>
          <button class="text-button" type="button" @click="signOut">Sign out</button>
        </div>
      </div>
    </header>

    <main class="account-layout">
      <aside class="account-nav" aria-label="Account navigation">
        <p>Account</p>
        <RouterLink :to="{ name: 'profile' }">Profile</RouterLink>
        <RouterLink :to="{ name: 'security' }">Password & security</RouterLink>
        <RouterLink to="/">Back to FreshCart</RouterLink>
      </aside>
      <section class="account-content">
        <slot />
      </section>
    </main>
  </div>
</template>
