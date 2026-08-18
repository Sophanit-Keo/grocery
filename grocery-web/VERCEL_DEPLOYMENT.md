# Deploying the FreshCart web app to Vercel

This directory contains the Vue/Vite single-page application. Deploy it as a separate Vercel project from the Laravel API.

## 1. Configure the Vercel project

When importing the monorepo, set **Root Directory** to:

```text
grocery-web
```

The committed `vercel.json` selects Vite, runs `npm run build`, publishes `dist`, and rewrites browser-history routes such as `/login` and `/account/security` to `index.html`.

Expected Vercel settings:

```text
Framework Preset: Vite
Build Command: npm run build
Output Directory: dist
Install Command: npm install
Node.js: 24.x
```

## 2. Configure the public API URL

Add this variable to the web project's Production and Preview environments:

```dotenv
VITE_API_URL=https://grocery-umber-seven.vercel.app
```

`VITE_*` values are public and embedded in the browser bundle. Never place passwords, application keys, or database credentials in them.

## 3. Allow the web origin in Laravel

After Vercel assigns the final web domain, update the API project's environment variables:

```dotenv
FRONTEND_URL=https://your-web-domain.example
CORS_ALLOWED_ORIGINS=https://your-web-domain.example
SANCTUM_STATEFUL_DOMAINS=your-web-domain.example,your-api-domain.example
```

For reliable Sanctum cookie authentication, use custom domains on the same root domain, for example `app.example.com` and `api.example.com`, with:

```dotenv
SESSION_DOMAIN=.example.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

Separate `*.vercel.app` projects are cross-site. They require `SESSION_SAME_SITE=none` and a host-only API cookie, and authentication can still fail in browsers that block third-party cookies. Custom same-site domains are the production-safe setup.

Redeploy the API after changing its environment variables.

## 4. Deploy and verify

Deploy from this directory or through the Git integration:

```bash
npm ci
npm run build
npx vercel
npx vercel --prod
```

Verify the home page and direct navigation to `/login`, `/reset-password`, `/verify-email`, and `/account/security`. Then test CSRF initialization, registration, login, email verification, password reset, and logout against the production API.
