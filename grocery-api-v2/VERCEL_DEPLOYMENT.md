# Deploying the FreshCart API to Vercel

This directory is configured to deploy only the Laravel API. Vercel runs it as one PHP serverless function through the recommended community `vercel-php` runtime. PHP is not an official native Vercel runtime, so review runtime updates before upgrading `vercel-php` in `vercel.json`.

References:

- [Vercel function runtimes](https://vercel.com/docs/functions/runtimes)
- [Vercel project configuration](https://vercel.com/docs/project-configuration)
- [vercel-php runtime](https://github.com/vercel-community/php)

## Architecture requirements

- Use a managed MySQL or PostgreSQL database. A local database cannot be reached from Vercel.
- Keep `SESSION_DRIVER=database` so Sanctum sessions survive between serverless invocations.
- Keep `CACHE_STORE=database` so authentication rate limits are shared across instances.
- Use `QUEUE_CONNECTION=sync` when Vercel is the only backend host. Vercel does not provide a continuously running Laravel queue worker. For asynchronous queues, deploy a separate worker elsewhere and use Redis, SQS, or another supported queue.
- Store uploads in S3 or another object-storage service. Vercel's filesystem is read-only except for temporary `/tmp` storage.
- Send logs to `stderr`; inspect them from the Vercel Runtime Logs screen.

## 1. Configure the Vercel project

Import the Git repository into Vercel and set the project Root Directory to the inner Laravel directory containing `artisan`, `composer.json`, and `vercel.json`:

```text
grocery-api-v2
```

Set **Settings → Build and Deployment → Root Directory** to `grocery-api-v2`, not the repository root. The committed configuration selects the `Other` preset, disables the backend's unused Vite build, and clears any static Output Directory such as `dist`. `vercel.json` provides the function runtime and sends every request through `api/index.php`.

In the Vercel dashboard, leave **Build Command** and **Output Directory** blank. If this project was previously configured as Vue/Vite, remove the old `dist` Output Directory before redeploying.

## 2. Configure production environment variables

Copy the names from `vercel.env.example` into the Vercel project's Production environment and replace every placeholder. Never upload the local `.env` file.

Generate a dedicated production key:

```bash
php artisan key:generate --show
```

The important authentication values are:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example.com
FRONTEND_URL=https://app.example.com
TRUSTED_PROXIES=*

CORS_ALLOWED_ORIGINS=https://app.example.com
SANCTUM_STATEFUL_DOMAINS=app.example.com,api.example.com

SESSION_DRIVER=database
SESSION_DOMAIN=.example.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_ENCRYPT=true

CACHE_STORE=database
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
```

Use custom domains on the same root domain, such as `app.example.com` and `api.example.com`. This lets the browser treat Sanctum's secure cookie as same-site. Separate `*.vercel.app` projects are different sites and may be blocked by browser third-party-cookie policies.

Set SMTP credentials in Vercel so verification and password-reset notifications can be delivered. Because the Vercel-only configuration uses the synchronous queue driver, these messages are sent during the request and do not need `queue:work`.

For Gmail on port 587, use `MAIL_SCHEME=smtp`; Symfony Mailer will negotiate TLS automatically. Use a Google app password, not the normal account password. If an app password is ever exposed, revoke it immediately and create a replacement.

## 3. Migrate the managed database

Do not run migrations automatically during every Vercel build. From a trusted machine configured with the production database credentials, run:

```bash
php artisan migrate --force
```

The database must contain the `sessions`, `cache`, `jobs`, `failed_jobs`, password-reset, users, and authentication tables from this repository's migrations.

## 4. Deploy

Deploy through the Vercel Git integration or from this directory:

```bash
npx vercel
npx vercel --prod
```

After assigning the production API domain, update `APP_URL` if necessary and redeploy. In the Vue project, configure:

```dotenv
VITE_API_URL=https://api.example.com
```

## 5. Smoke checks

Verify these URLs after deployment:

```text
GET https://api.example.com/
GET https://api.example.com/up
GET https://api.example.com/sanctum/csrf-cookie
```

The root and health endpoints should return `200`, while the CSRF endpoint should return `204` and set `XSRF-TOKEN` plus the FreshCart session cookie. Confirm registration, verification email, login, `/api/v1/auth/me`, and logout from the production Vue origin.

Preview deployments should use separate preview database credentials. Do not connect untrusted preview deployments to the production database.
