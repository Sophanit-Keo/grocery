<script setup>
import { ref } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'

const isMenuOpen = ref(false)
const route = useRoute()

const navItems = [
  { label: 'Why us', href: '#features' },
  { label: 'How it works', href: '#about' },
  { label: 'Offers', href: '#offers' },
]

const stats = [
  { value: '15k+', label: 'happy households' },
  { value: '4.9/5', label: 'average rating' },
  { value: '30 min', label: 'average delivery' },
]

const products = [
  { icon: '🥬', name: 'Organic Spinach', detail: 'Farm picked today', price: '$4.99', tone: 'mint' },
  { icon: '🍊', name: 'Citrus Mix', detail: 'Sweet & vitamin rich', price: '$6.50', tone: 'orange' },
  { icon: '🥖', name: 'Artisan Bread', detail: 'Fresh from the oven', price: '$3.25', tone: 'gold' },
]

const features = [
  {
    icon: '🚚',
    eyebrow: 'Speed',
    title: 'At your door in minutes',
    description: 'Live tracking and smart routes keep every delivery quick, reliable, and right on time.',
  },
  {
    icon: '🌿',
    eyebrow: 'Quality',
    title: 'Freshness you can see',
    description: 'Our team hand-picks quality produce and packs every order with care before it leaves the store.',
  },
  {
    icon: '🛡️',
    eyebrow: 'Simple',
    title: 'Easy, secure checkout',
    description: 'Use your card, wallet, or cash on delivery with clear prices and no surprise fees at checkout.',
  },
]

function closeMenu() {
  isMenuOpen.value = false
}
</script>

<template>
  <div v-if="route.name === 'home'" class="site-shell">
    <header class="site-header">
      <div class="container nav-wrap">
        <a class="brand" href="#top" aria-label="FreshCart home" @click="closeMenu">
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path d="M3.5 4.5h2l1.8 9.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.9-1.4l1.4-5.4H7" />
              <circle cx="10" cy="19" r="1.4" />
              <circle cx="17.5" cy="19" r="1.4" />
            </svg>
          </span>
          <span>Fresh<span>Cart</span></span>
        </a>

        <button
          class="menu-toggle"
          type="button"
          :aria-expanded="isMenuOpen"
          aria-label="Toggle navigation"
          @click="isMenuOpen = !isMenuOpen"
        >
          <span></span><span></span><span></span>
        </button>

        <nav :class="['nav-links', { open: isMenuOpen }]" aria-label="Main navigation">
          <a v-for="item in navItems" :key="item.href" :href="item.href" @click="closeMenu">
            {{ item.label }}
          </a>
          <RouterLink class="nav-button" to="/register" @click="closeMenu">Get started <span>→</span></RouterLink>
        </nav>
      </div>
    </header>

    <main id="top">
      <section class="hero container">
        <div class="hero-copy">
          <div class="eyebrow-pill"><span></span> Fresh picks, every day</div>
          <h1>Your groceries,<br /><em>delivered fresh.</em></h1>
          <p class="hero-intro">
            Fill your basket with farm-fresh produce, pantry favorites, and everyday essentials—delivered when you need them.
          </p>

          <div class="hero-actions">
            <RouterLink class="button button-primary" to="/register">Start shopping <span>→</span></RouterLink>
            <a class="button button-ghost" href="#features">
              <span class="play-icon">▶</span> See how it works
            </a>
          </div>

          <div class="stats" aria-label="FreshCart statistics">
            <div v-for="stat in stats" :key="stat.label" class="stat">
              <strong>{{ stat.value }}</strong>
              <span>{{ stat.label }}</span>
            </div>
          </div>
        </div>

        <div class="hero-visual" aria-label="FreshCart grocery ordering preview">
          <div class="glow glow-one"></div>
          <div class="glow glow-two"></div>
          <div class="produce produce-leaf">🌿</div>
          <div class="produce produce-orange">🍊</div>

          <div class="app-card">
            <div class="app-toolbar">
              <div>
                <small>Good morning</small>
                <strong>Alex! <span>👋</span></strong>
              </div>
              <button type="button" aria-label="Notifications">🔔<i></i></button>
            </div>

            <div class="deal-banner">
              <div>
                <small>WEEKEND SPECIAL</small>
                <h2>Fresh deals<br />up to <span>20% off</span></h2>
                <a href="#offers">Shop the offer →</a>
              </div>
              <div class="deal-art" aria-hidden="true">🥑</div>
            </div>

            <div class="list-header">
              <h3>Popular today</h3>
              <a href="#features">View all</a>
            </div>

            <div class="product-list">
              <article v-for="product in products" :key="product.name" class="product-row">
                <div :class="['product-image', product.tone]">{{ product.icon }}</div>
                <div class="product-copy">
                  <h4>{{ product.name }}</h4>
                  <p>{{ product.detail }}</p>
                </div>
                <div class="product-price">
                  <strong>{{ product.price }}</strong>
                  <button type="button" :aria-label="`Add ${product.name}`">+</button>
                </div>
              </article>
            </div>
          </div>

          <div class="savings-card">
            <span class="savings-icon">↘</span>
            <div><small>Saved this month</small><strong>$58.40</strong></div>
            <span class="trend">+12%</span>
          </div>
        </div>
      </section>

      <section id="about" class="trust-strip">
        <div class="container trust-items">
          <p>Trusted by busy households across the city</p>
          <div><span>100%</span> quality checked</div>
          <div><span>7 days</span> fresh guarantee</div>
          <div><span>4.9 ★</span> customer favorite</div>
        </div>
      </section>

      <section id="features" class="features-section container">
        <div class="section-heading">
          <div>
            <span class="section-kicker">Why FreshCart</span>
            <h2>Good food should be<br /><em>easy to enjoy.</em></h2>
          </div>
          <p>We handle the picking, packing, and delivery so you can spend more time around the table.</p>
        </div>

        <div class="feature-grid">
          <article v-for="(feature, index) in features" :key="feature.title" class="feature-card">
            <div class="feature-top">
              <span class="feature-number">0{{ index + 1 }}</span>
              <div class="feature-icon">{{ feature.icon }}</div>
            </div>
            <span class="feature-eyebrow">{{ feature.eyebrow }}</span>
            <h3>{{ feature.title }}</h3>
            <p>{{ feature.description }}</p>
          </article>
        </div>
      </section>

      <section id="offers" class="offer-section container">
        <div class="offer-card">
          <div class="offer-copy">
            <span class="offer-label">New customer offer</span>
            <h2>Fresh groceries.<br />A lighter bill.</h2>
            <p>Enjoy 20% off your first basket and free delivery on your first three orders.</p>
            <RouterLink class="button button-light" to="/register">Claim my offer <span>→</span></RouterLink>
          </div>
          <div class="offer-visual" aria-hidden="true">
            <span class="offer-badge">20%<small>OFF</small></span>
            <div class="basket">🧺</div>
            <span class="offer-food food-one">🥕</span>
            <span class="offer-food food-two">🥦</span>
            <span class="offer-food food-three">🍎</span>
          </div>
        </div>
      </section>
    </main>

    <footer>
      <div class="container footer-inner">
        <a class="brand footer-brand" href="#top">
          <span class="brand-mark" aria-hidden="true">🛒</span>
          <span>Fresh<span>Cart</span></span>
        </a>
        <p>Fresh food. Fast delivery. Better days.</p>
        <span>© {{ new Date().getFullYear() }} FreshCart</span>
      </div>
    </footer>
  </div>
  <RouterView v-else />
</template>
