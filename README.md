# TCG Engineering Hub — MVP SaaS

## 📋 Overview

This system is an **internal SaaS** for The Cloud Group that digitalizes the TCG Engineering Framework.
Stack: **Laravel 11 (API)** + **Vue 3 (UI)**

---

## 🚀 Setup

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Configure DB in .env (MySQL recommended)
php artisan migrate --seed
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
cp .env.example .env
# VITE_API_URL=http://localhost:8000
npm run dev
```

---

## 👥 Demo Users (seeded)

| Email                    | Password | Role     |
|--------------------------|----------|----------|
| admin@tcg.com            | password | admin    |
| pm@tcg.com               | password | pm       |
| engineer@tcg.com         | password | engineer |
| viewer@tcg.com           | password | viewer   |

---

## 🧪 Running Tests

```bash
cd backend
php artisan test
# Or specifically:
php artisan test --filter=GateTest
php artisan test --filter=ModuleValidationTest
php artisan test --filter=ProjectTransitionTest
php artisan test --filter=AuthorizationTest
```

---

## 🏗️ Architecture Decisions

### Backend (Laravel)

- **RBAC with Policies/Gates**: Each entity has its Policy. Business rules **NEVER** go in controllers.
- **ArtifactGateService**: Domain service that encapsulates all rules (Gates 1-4). Injectable and testable.
- **AuditService**: Observer pattern — each state change triggers automatic log with before/after JSON diff.
- **Sanctum**: Stateless authentication with tokens for SPA.
- **Soft Deletes**: Projects use `deleted_at` for archiving.
- **Resources**: All responses go through API Resources for consistent format.

### Frontend (Vue 3)

- **Pinia**: Global state (auth, projects).
- **Vue Router**: Authentication guards per route.
- **Composables**: Reusable `usePermissions`, `useGates`.
- **Vuexy Style**: Layout with sidebar, shadowed cards, colored status badges.

---

## 🔮 What I Would Improve (Next Steps)

1. **Templates**: When creating a project, offer a "preset" that auto-creates the 7 artifacts in `not_started`.
2. **Export JSON**: Endpoint `/api/v1/projects/{id}/export` that returns the complete project with artifacts and modules.
3. **Notifications**: When an artifact changes to `done`, notify the PM via email/broadcast.
4. **WebSockets**: Real-time collaboration in the artifact editor.
5. **Module Versioning**: Complete version history, not just `version_note`.
6. **Domain table**: Separate domains as an entity for better normalization.
