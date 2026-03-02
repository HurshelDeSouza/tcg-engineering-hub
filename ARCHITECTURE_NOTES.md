# Architecture Notes — TCG Engineering Hub

## Key Decisions

**Domain Services > Controllers**
Business rules live in `ArtifactGateService`. Controllers only coordinate: receive the request, call the service, return the response. This allows testing rules in isolation without HTTP overhead.

**Complete RBAC Policies**
Each entity (Project, Artifact, Module) has its `Policy`. Controller methods call `$this->authorize()` — zero hardcoded role checks. Admin has full access; viewer is read-only.

**Audit as event, not as log**
`AuditService` receives the before/after state and generates a real diff. Only fields that actually changed are saved (`logUpdated`). This makes the timeline useful and not noisy.

**Flexible JSON + strict validation**
`content_json` allows flexibility per artifact type without creating 7 different tables. Validation is done in controllers before persisting (array rules in FormRequest / inline validation).

**Configurable Gate 3**
`config/tcg.php` defines `min_validated_modules` with env var `TCG_MIN_VALIDATED_MODULES`. Changing the threshold doesn't require touching code.

---

## What I Would Improve in the Next Iteration

1. **Project Templates** — When creating a project, choose a preset that auto-generates the 7 artifacts in `not_started` with `content_json` pre-populated with examples. Prevents the team from starting from scratch.

2. **Export JSON** — `GET /api/v1/projects/{id}/export` returns the complete project (artifacts + modules + audit) as downloadable JSON. Useful for handoffs or external audits.

3. **Real-time Notifications** — Use Laravel Broadcasting + Pusher so the PM sees in real-time when an engineer marks a module as validated.

4. **Complete Module Versioning** — Currently there's only `version_note` (string). The next version would save complete snapshots in a `module_versions` table with diff between versions.

5. **Domain as entity** — Separate `domain` from modules to a `domains` table to be able to list all domains of a project, assign them owners, and navigate by domain.
