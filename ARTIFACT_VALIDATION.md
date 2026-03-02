# Artifact Content Validation by Type

This document describes the specific validation rules for each artifact type's `content_json` field.

## Overview

All 7 artifact types have **strict validation rules** enforced at the backend level. Empty content is allowed for drafts, but once you start filling the content, it must follow the structure defined below.

---

## 1. Strategic Alignment

**Purpose:** Define the transformation and key decisions.

**Required Fields:**
- `transformation` (string, min 10 chars) - Description of the transformation
- `supported_decisions` (array, min 1 item) - Key decisions supported
- `measurable_success` (array, min 1 item) - Metrics with targets
  - Each item must have: `metric` (string) and `target` (string)

**Optional Fields:**
- `out_of_scope` (array) - Items explicitly out of scope

**Example:**
```json
{
  "transformation": "Migrate legacy monolith to cloud-native microservices",
  "supported_decisions": ["Build vs Buy", "Tech Stack Selection"],
  "measurable_success": [
    {"metric": "Uptime", "target": "99.9%"},
    {"metric": "Response Time", "target": "<200ms"}
  ],
  "out_of_scope": ["Mobile app", "Third-party integrations"]
}
```

---

## 2. Big Picture

**Purpose:** Define the ecosystem vision and success criteria.

**Required Fields:**
- `ecosystem_vision` (string, min 10 chars) - Vision of the ecosystem
- `impacted_domains` (array, min 1 item) - List of impacted domains
- `success_definition` (string, min 10 chars) - Definition of success

**Example:**
```json
{
  "ecosystem_vision": "Unified e-commerce platform with microservices architecture",
  "impacted_domains": ["Auth", "Catalog", "Cart", "Orders", "Payments"],
  "success_definition": "Platform handles 10k concurrent users with <200ms p95 latency"
}
```

---

## 3. Domain Breakdown

**Purpose:** Break down the system into logical domains.

**Required Fields:**
- `domains` (array, min 1 item) - List of domains
  - Each domain must have:
    - `name` (string) - Domain name
    - `objective` (string) - Domain objective
  - Optional: `owner_user_id` (integer, must exist in users table)

**Example:**
```json
{
  "domains": [
    {
      "name": "Authentication",
      "objective": "Handle user authentication and authorization",
      "owner_user_id": 1
    },
    {
      "name": "Catalog",
      "objective": "Manage product catalog and inventory"
    }
  ]
}
```

---

## 4. Module Matrix

**Purpose:** Overview of all modules with priorities and phases.

**Required Fields:**
- `modules_overview` (array, min 1 item) - List of modules
  - Each module must have:
    - `name` (string) - Module name
    - `domain` (string) - Domain it belongs to
  - Optional:
    - `priority` (string: high|medium|low)
    - `phase` (string)

**Example:**
```json
{
  "modules_overview": [
    {
      "name": "Authentication Module",
      "domain": "Auth",
      "priority": "high",
      "phase": "Phase 1"
    },
    {
      "name": "Catalog Module",
      "domain": "Catalog",
      "priority": "medium",
      "phase": "Phase 2"
    }
  ]
}
```

---

## 5. Module Engineering

**Purpose:** Detailed engineering approach for each module.

**Required Fields:**
- `modules` (array, min 1 item) - List of modules with engineering details
  - Each module must have:
    - `module_id` (integer, must exist in modules table)
    - `engineering_approach` (string, min 10 chars) - Engineering approach
    - `technical_decisions` (array, min 1 item) - Key technical decisions
  - Optional:
    - `implementation_notes` (string)
    - `risks` (array of strings)

**Example:**
```json
{
  "modules": [
    {
      "module_id": 1,
      "engineering_approach": "JWT-based authentication with refresh tokens",
      "technical_decisions": [
        "Use bcrypt for password hashing",
        "Implement rate limiting",
        "Store tokens in HTTP-only cookies"
      ],
      "implementation_notes": "Follow OAuth 2.0 best practices",
      "risks": ["Token theft", "Brute force attacks"]
    }
  ]
}
```

---

## 6. System Architecture

**Purpose:** Define the overall system architecture.

**Required Fields:**
- `auth_model` (string, min 5 chars) - Authentication model description
- `api_style` (string, min 3 chars) - API style (REST, GraphQL, etc.)
- `data_model_notes` (string, min 10 chars) - Data model notes

**Optional Fields:**
- `scalability_notes` (string) - Scalability considerations

**Example:**
```json
{
  "auth_model": "JWT with refresh tokens",
  "api_style": "REST",
  "data_model_notes": "Relational database with PostgreSQL, normalized to 3NF",
  "scalability_notes": "Horizontal scaling with load balancer and Redis cache"
}
```

---

## 7. Phase Scope

**Purpose:** Define what's included/excluded in a specific phase.

**Required Fields:**
- `included_modules` (array, min 1 item) - Module IDs included in this phase
  - Each item must be an integer that exists in modules table
- `acceptance_criteria` (array, min 1 item) - Acceptance criteria

**Optional Fields:**
- `excluded_items` (array of strings) - Items excluded from this phase

**Example:**
```json
{
  "included_modules": [1, 2, 3],
  "excluded_items": ["Mobile app", "Admin panel"],
  "acceptance_criteria": [
    "All tests passing",
    "Code review completed",
    "Documentation updated",
    "Performance benchmarks met"
  ]
}
```

---

## Validation Enforcement

### Backend
- All validation is enforced in `ArtifactContentValidator` service
- Validation runs on both `create` and `update` operations
- Empty content (`{}` or `null`) is allowed for drafts
- Once content is provided, it must match the structure above

### Tests
- 14 dedicated tests for content validation
- Tests cover both positive (valid content) and negative (invalid content) cases
- All 7 artifact types have validation tests

### Error Messages
When validation fails, you'll receive a 422 response with specific error messages indicating which fields are missing or invalid.

**Example error response:**
```json
{
  "success": false,
  "errors": {
    "content_json": [
      "The transformation field is required.",
      "The supported decisions must have at least 1 items."
    ]
  }
}
```

---

## Summary

✅ **All 7 artifact types have strict validation**  
✅ **25 tests passing** (14 for content validation)  
✅ **Empty content allowed for drafts**  
✅ **Clear error messages when validation fails**  
✅ **Validation enforced at backend level (not just frontend)**

This ensures data quality and consistency across all artifacts in the TCG Engineering Framework.
