# TCG Engineering Hub — MVP SaaS

## 📋 Traducción del Challenge

El sistema es un **SaaS interno** para The Cloud Group que digitaliza el TCG Engineering Framework.
Stack: **Laravel 11 (API)** + **Vue 3 (UI)**

---

## 🚀 Setup

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Configurar DB en .env (MySQL recomendado)
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

## 👥 Usuarios demo (seed)

| Email                    | Password | Rol      |
|--------------------------|----------|----------|
| admin@tcg.com            | password | admin    |
| pm@tcg.com               | password | pm       |
| engineer@tcg.com         | password | engineer |
| viewer@tcg.com           | password | viewer   |

---

## 🧪 Ejecutar Tests

```bash
cd backend
php artisan test
# O específicamente:
php artisan test --filter=GateTest
php artisan test --filter=ModuleValidationTest
php artisan test --filter=ProjectTransitionTest
php artisan test --filter=AuthorizationTest
```

---

## 🏗️ Decisiones de Arquitectura

### Backend (Laravel)

- **RBAC con Policies/Gates**: Cada entidad tiene su Policy. Las reglas de negocio **NUNCA** van en controllers.
- **ArtifactGateService**: Servicio de dominio que encapsula todas las reglas (Gates 1-4). Inyectable y testeable.
- **AuditService**: Observer pattern — cada cambio de estado dispara log automático con before/after JSON diff.
- **Sanctum**: Autenticación stateless con tokens para SPA.
- **Soft Deletes**: Projects usan `deleted_at` para archivado.
- **Resources**: Todas las respuestas pasan por API Resources para formato consistente.

### Frontend (Vue 3)

- **Pinia**: Estado global (auth, projects).
- **Vue Router**: Guards de autenticación por ruta.
- **Composables**: `usePermissions`, `useGates` reutilizables.
- **Estilo Vuexy**: Layout con sidebar, cards con sombra, badges de estado con colores.

---

## 🔮 Qué mejoraría (Next Steps)

1. **Templates**: Al crear proyecto, ofrecer "preset" que auto-crea los 7 artifacts en `not_started`.
2. **Export JSON**: Endpoint `/api/v1/projects/{id}/export` que devuelve el proyecto completo con artifacts y modules.
3. **Notificaciones**: Cuando un artifact cambia a `done`, notificar al PM vía email/broadcast.
4. **WebSockets**: Colaboración en tiempo real en el editor de artifacts.
5. **Versionado de Modules**: Historial completo de versiones, no solo `version_note`.
6. **Domain table**: Separar dominios como entidad para mejor normalización.
