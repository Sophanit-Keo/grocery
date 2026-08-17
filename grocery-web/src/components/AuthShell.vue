<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  eyebrow: { type: String, default: 'FreshCart account' },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
})
</script>

<template>
  <main class="auth-layout">
    <section class="auth-story" aria-label="FreshCart benefits">
      <RouterLink class="auth-brand" to="/" aria-label="FreshCart home">
        <span class="auth-brand-mark" aria-hidden="true">F</span>
        <span>Fresh<span>Cart</span></span>
      </RouterLink>
      <div>
        <p class="auth-kicker">Fresh groceries, less friction</p>
        <h2>Everything you need, safely delivered.</h2>
        <p>Manage your weekly essentials, account details, and security from one simple place.</p>
      </div>
      <ul class="auth-benefits">
        <li><span>✓</span> Freshness selected by hand</li>
        <li><span>✓</span> Fast, trackable delivery</li>
        <li><span>✓</span> Secure account protection</li>
      </ul>
    </section>

    <section class="auth-panel">
      <div class="auth-card">
        <p class="auth-eyebrow">{{ eyebrow }}</p>
        <h1>{{ title }}</h1>
        <p v-if="subtitle" class="auth-subtitle">{{ subtitle }}</p>
        <slot />
      </div>
    </section>
  </main>
</template>
