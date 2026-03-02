# Notas de Arquitectura — TCG Engineering Hub

## Decisiones clave

**Domain Services > Controllers**
Las reglas de negocio viven en `ArtifactGateService`. Los controllers sólo coordinan: reciben la request, llaman al service, devuelven la response. Esto permite testear las reglas de forma aislada sin HTTP overhead.

**Policies RBAC completas**
Cada entidad (Project, Artifact, Module) tiene su `Policy`. Los métodos del controller llaman `$this->authorize()` — cero checks de rol hardcodeados. El admin tiene acceso total; viewer es read-only.

**Audit como evento, no como log**
`AuditService` recibe el before/after state y genera un diff real. Sólo se guardan los campos que realmente cambiaron (`logUpdated`). Esto hace la timeline útil y no ruidosa.

**JSON flexible + validación estricta**
`content_json` permite flexibilidad por tipo de artifact sin crear 7 tablas distintas. La validación se hace en los controllers antes de persistir (array rules en FormRequest / inline validation).

**Gate 3 configurable**
`config/tcg.php` define `min_validated_modules` con env var `TCG_MIN_VALIDATED_MODULES`. Cambiar el threshold no requiere tocar código.

---

## Qué mejoraría en la siguiente iteración

1. **Project Templates** — Al crear proyecto, elegir un preset que auto-genera los 7 artifacts en `not_started` con `content_json` pre-poblado de ejemplos. Evita que el equipo empiece desde cero.

2. **Export JSON** — `GET /api/v1/projects/{id}/export` retorna el proyecto completo (artifacts + modules + audit) como JSON descargable. Útil para handoffs o auditorías externas.

3. **Notificaciones en tiempo real** — Usar Laravel Broadcasting + Pusher para que el PM vea en tiempo real cuando un engineer marca un módulo como validado.

4. **Versionado completo de Modules** — Actualmente solo hay `version_note` (string). La próxima versión guardaría snapshots completos en una tabla `module_versions` con diff entre versiones.

5. **Domain como entidad** — Separar `domain` de módulos a una tabla `domains` para poder listar todos los dominios de un proyecto, asignarles owner, y navegar por dominio.
