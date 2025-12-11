# ServIsko AI Agent Team Instructions – Enhanced Edition

## Executive Summary
These guidelines optimize AI agent collaboration across Backend, Frontend, QA, and Documentation teams. Focus: **clarity, parallelization, and measurable outcomes**.

---

## Core Principles for Maximum Productivity

### 1. **Single Responsibility + Clear Ownership**
- Each agent has **one primary domain** (Backend, Frontend, QA, Docs)
- Overlapping decisions are escalated via a **decision matrix** (see below)
- No "gray zone" tasks—pre-assign before work begins

### 2. **Asynchronous-First Collaboration**
- Assume agents work in parallel, not sequentially
- Use **API contracts** as the single source of truth for integration points
- Minimize synchronous handoffs; use git commits and PRs as async communication

### 3. **Metrics-Driven Feedback Loops**
- Define success criteria for each sprint/milestone
- Track: cycle time, defect escape rate, documentation coverage, test pass rate
- Review metrics weekly; adjust processes monthly

### 4. **Modular Dependency Management**
- Break down large features into independently deployable units
- Backend delivers API contracts before Frontend starts UI work
- QA prepares test plans while development is ongoing (parallel path)

---

## Team-Specific Guidelines (Enhanced)

### BACKEND AGENT GUIDELINES

**Primary Responsibilities:**
- Laravel API structure, business logic, database design
- Service layer implementation and optimization
- Authentication, authorization, and security
- Database migrations, seeders, and performance tuning

**Key Workflows:**

**1. Pre-Development: API Contract Definition**
- Before coding, create **OpenAPI/Swagger specification** for all endpoints
- Include: request/response schemas, error codes, status codes, rate limits
- Share contract with Frontend and QA agents **before implementation**
- Update contract in PR description if changes occur

**Example Structure:**
```
/backend/docs/api-contracts/
  ├── listings.openapi.yaml
  ├── auth.openapi.yaml
  └── users.openapi.yaml
```

**2. Code Organization**
- Structure: `app/{Domain}/{Service,Controller,Model,Request,Resource}`
  ```
  app/Listings/
    ├── ListingService.php
    ├── ListingController.php
    ├── Listing.php (Model)
    ├── Requests/CreateListingRequest.php
    └── Resources/ListingResource.php
  ```
- Service classes = all business logic (validation, calculations, external calls)
- Controllers = route requests to services, format responses
- Keep controllers **under 100 lines**

**3. Database & Migrations**
- One migration file per feature
- Include rollback logic in `down()` method
- Add indexes for frequently queried columns (especially foreign keys)
- Document schema changes in `/docs/database-schema.md`

**4. Testing Strategy**
- **Unit Tests**: Service layer logic (80% coverage target)
- **Feature Tests**: API endpoints with real database transactions
- Run: `php artisan test --parallel` for CI/CD
- Test both happy path AND error scenarios

**5. Code Quality Checklist Before PR**
- [ ] Follows PSR-12 standards (run `php artisan pint`)
- [ ] Includes API contract documentation
- [ ] Unit tests pass locally
- [ ] Database migrations are reversible
- [ ] No N+1 queries (use `->with()` for eager loading)
- [ ] Proper error handling with custom exceptions
- [ ] Security: Input validation, CSRF protection, rate limiting

**6. Pull Request Template**
```markdown
## Changes
- [x] API contract updated
- [x] Database migrations included
- [x] Tests added (X% coverage)

## API Endpoints Modified
- [List all new/modified endpoints]

## Breaking Changes
- [List any changes affecting Frontend or other agents]

## Testing Instructions
$ php artisan migrate
$ php artisan test
```

---

### FRONTEND AGENT GUIDELINES

**Primary Responsibilities:**
- Vue 3 SPA user interface and user experience
- Component architecture and composition API logic
- State management via Pinia
- API integration and error handling

**Key Workflows:**

**1. Pre-Development: API Contract Consumption**
- Wait for **Backend API contract** (OpenAPI spec)
- Use contract to: mock API responses, generate TypeScript types, plan data flow
- Create Figma/wireframes aligned with contract structure

**2. Component Architecture**
```
src/
  ├── components/
  │   ├── common/           (reusable UI: Button, Input, Modal)
  │   ├── features/         (feature-specific: ListingCard, FilterPanel)
  │   └── layouts/          (page layouts: AppLayout, AuthLayout)
  ├── views/                (page-level components)
  ├── stores/               (Pinia state management)
  ├── composables/          (reusable logic: useListings, useAuth)
  ├── services/             (API calls, external integrations)
  ├── types/                (TypeScript: listing.ts, user.ts)
  └── utils/                (helpers, formatters, validators)
```

**3. API Integration via Composables**
```javascript
// src/composables/useListings.js
import { useListingStore } from '@/stores/listings'
import { fetchListings, createListing } from '@/services/listingsService'

export function useListings() {
  const store = useListingStore()
  
  const getListings = async (filters) => {
    try {
      const data = await fetchListings(filters)
      store.setListings(data)
    } catch (error) {
      console.error('Failed to fetch:', error)
    }
  }
  
  return { getListings, listings: computed(() => store.listings) }
}
```

**4. State Management (Pinia)**
- Store per domain: `stores/listings.ts`, `stores/auth.ts`, `stores/ui.ts`
- Use **getters** for derived state (computed properties)
- Use **actions** for async operations (API calls)
- Minimal shared state; push data down to components

**5. Testing Strategy**
- **Unit Tests**: Composables, utilities, state mutations (Jest/Vitest)
- **Component Tests**: Component rendering, user interactions (Vitest + @vue/test-utils)
- **E2E Tests**: Critical user flows (Cypress or Playwright)
- Target: 70% coverage on composables and stores

**6. Performance & UX Checklist**
- [ ] Lighthouse score > 80 (Performance, Accessibility, Best Practices)
- [ ] API calls use composables (not direct in components)
- [ ] Images lazy-loaded, icons optimized (SVG preferred)
- [ ] Form validation is responsive (real-time feedback)
- [ ] Error states have user-friendly messages
- [ ] Loading states prevent double-submission
- [ ] Mobile responsive (tested at 375px, 768px, 1024px)

**7. Code Quality Checklist Before PR**
- [ ] Runs `npm run lint` without warnings
- [ ] Component tests pass (`npm run test`)
- [ ] No console errors/warnings in dev tools
- [ ] Follows Vue 3 Composition API best practices
- [ ] TypeScript types are strict (no `any`)
- [ ] API contract matches Backend implementation
- [ ] Accessibility: ARIA labels, keyboard navigation, contrast ratios

**8. Pull Request Template**
```markdown
## Changes
- [x] API contract implemented
- [x] Components created/modified
- [x] Tests added (X% coverage)

## Screenshots/Demo
[Attach before/after screenshots or demo link]

## API Dependencies
- [List Backend endpoints this PR depends on]

## Testing Instructions
$ npm install
$ npm run dev
$ npm run test
```

---

### QA AGENT GUIDELINES

**Primary Responsibilities:**
- Automated testing (unit, component, E2E)
- Manual testing of critical workflows
- Regression detection and bug documentation
- Code quality standards (linting, type checking)

**Key Workflows:**

**1. Pre-Development: Test Planning**
- Create test plan when feature spec is finalized
- Map test scenarios: happy path, error cases, edge cases, security
- Identify critical flows (auth, payments, data integrity)
- Share test plan with Backend and Frontend agents

**Example Test Plan:**
```
Feature: User Listing Creation
Scenarios:
1. Create listing with all required fields ✓
2. Create listing with optional fields ✓
3. Validation: title too short ✗
4. Validation: invalid image format ✗
5. Error: database timeout (retry logic) ✗
6. Security: SQL injection in title ✗
7. Performance: create listing under 2s ✓
```

**2. Backend Testing**
```bash
# Run all tests
php artisan test

# Run specific test class
php artisan test tests/Feature/ListingTest.php

# Run with coverage
php artisan test --coverage --min=80

# Specific database
php artisan test --database=testing
```

**Target Coverage:**
- Unit tests: 80% of service classes
- Feature tests: 100% of API endpoints
- Report coverage in CI/CD pipeline

**3. Frontend Testing**
```bash
# Lint code
npm run lint

# Unit + component tests
npm run test

# E2E tests (critical flows)
npm run test:e2e

# Check TypeScript types
npm run type-check
```

**Target Coverage:**
- Composables: 80%
- Store mutations: 80%
- Utilities: 80%
- Components: 50% (focus on complex/reusable)

**4. Manual Testing Checklist**
Run before each release:
```
[ ] Authentication: login, logout, remember me, token expiry
[ ] CRUD Operations: create, read, update, delete (each entity)
[ ] Search & Filters: apply, reset, pagination
[ ] Error Handling: network errors, validation, timeouts
[ ] Mobile: responsive layout, touch interactions
[ ] Accessibility: keyboard navigation, screen reader
[ ] Performance: load time < 3s, smooth scrolling
[ ] Browser Compatibility: Chrome, Firefox, Safari, Edge (latest 2 versions)
```

**5. Bug Documentation Template**
```markdown
## Bug Title
[Clear, specific title]

## Severity
[ ] Critical (app broken) [ ] High (feature blocked) [ ] Medium [ ] Low

## Reproduction Steps
1. [Step 1]
2. [Step 2]
3. [Expected result vs actual result]

## Environment
- OS: [Windows/Mac/Linux]
- Browser: [Chrome 120]
- Backend: [staging/production]

## Screenshots/Video
[Attach evidence]

## Affected Components
- Frontend: [component names]
- Backend: [API endpoints]
```

**6. Regression Testing**
- Maintain regression test suite for critical features
- Run before each release: `npm run test && php artisan test`
- Document any breaking changes in CHANGELOG

**7. QA Metrics (Report Weekly)**
- Defect escape rate (bugs found in production ÷ total bugs)
- Test coverage (%) by component
- Average cycle time (dev → QA → release)
- Lint warnings/errors count

---

### DOCUMENTATION AGENT GUIDELINES

**Primary Responsibilities:**
- Maintain README files (project, setup, contribution)
- API documentation (OpenAPI contracts)
- Architecture and design decisions
- Onboarding and troubleshooting guides

**Key Workflows:**

**1. Documentation Structure**
```
/
├── README.md                          (Project overview)
├── docs/
│   ├── SETUP.md                      (Dev environment setup)
│   ├── API.md                        (API reference, linked to OpenAPI)
│   ├── ARCHITECTURE.md               (Tech stack, decisions, diagrams)
│   ├── TROUBLESHOOTING.md            (Common issues & fixes)
│   ├── CONTRIBUTING.md               (How to contribute)
│   ├── api-contracts/                (OpenAPI YAML files)
│   └── adr/                          (Architecture Decision Records)
├── /backend/README.md                (Backend-specific setup)
└── /frontend/README.md               (Frontend-specific setup)
```

**2. README Best Practices**
Each README should include:
```markdown
# Project Name

## Overview
[1-2 sentence description]

## Quick Start
$ [Copy-paste commands to get running]

## Project Structure
[Directory tree with descriptions]

## Key Technologies
- [Tech]: [Version]

## Common Commands
| Task | Command |
|------|---------|
| Setup | $ npm install |
| Run | $ npm run dev |
| Test | $ npm run test |

## Troubleshooting
[Common issues and solutions]

## Contributing
[Link to CONTRIBUTING.md]
```

**3. API Documentation**
- Use **OpenAPI 3.0** format (YAML)
- Include: request/response examples, error codes, authentication
- Auto-generate docs via Swagger UI (link in README)
- Update whenever Backend agent modifies API

**Example OpenAPI Structure:**
```yaml
openapi: 3.0.0
info:
  title: ServIsko API
  version: 1.0.0
paths:
  /api/listings:
    get:
      summary: Get all listings
      parameters:
        - name: status
          in: query
          schema: { type: string }
      responses:
        200:
          description: Success
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Listing'
```

**4. Architecture Decision Record (ADR)**
Record significant technical decisions:
```
# ADR-001: Use Pinia for State Management

## Context
Need a state management solution for Vue 3 SPA.

## Decision
Use Pinia over Vuex because: simpler API, better TypeScript support, smaller bundle.

## Consequences
- Pro: Easier to test, faster development
- Con: Less ecosystem maturity than Vuex

## Date
[YYYY-MM-DD]
```

**5. Onboarding Guide**
```
# Developer Onboarding

## Week 1: Setup & Navigation
1. Clone repo, follow SETUP.md
2. Read ARCHITECTURE.md, understand tech stack
3. Complete Hello World PR (add yourself to contributors list)

## Week 2: First Task
1. Pick "good-first-issue" from GitHub
2. Follow CONTRIBUTING.md
3. Submit PR, get reviewed

## Resources
- [Team Wiki]
- [Design System Figma Link]
- [Slack Channel]
```

**6. Documentation Maintenance**
- Update docs within 24 hours of merged PR
- Link new code to relevant docs
- Review docs monthly for accuracy
- Flag outdated content in issues

---

## Cross-Team Collaboration & Decision Matrix

### API Contract Ownership
| Scenario | Owner | Approval | Deadline |
|----------|-------|----------|----------|
| New API endpoint | Backend Agent | Frontend QA | Before Frontend starts UI |
| Breaking API change | Backend Agent | Frontend + QA | 2 weeks lead time |
| New data model | Backend Agent | Documentation | In same PR as model |

### Feature Release Workflow (Parallel Path)

```
[Feature Spec Finalized]
    ↓
[Backend: API Design] ← [QA: Test Plan] ← [Frontend: Wireframes]
    ↓
[Backend: Implement] ← [Frontend: Consume API]
    ↓
[QA: Test] ← [Documentation: Update Docs]
    ↓
[Code Review] ← [PR Discussion]
    ↓
[Merge to Main] → [Deploy Staging] → [Manual Testing]
    ↓
[Release Notes] ← [Documentation: Publish]
```

### Communication Channels

| Topic | Channel | Frequency | Owner |
|-------|---------|-----------|-------|
| Daily status | Slack #dev-standup | Daily (async) | Each agent |
| Weekly metrics | Slack #metrics | Weekly | QA Agent |
| Breaking changes | GitHub issues + Slack | As needed | Affected agent |
| Design decisions | ADR docs + PR comments | Per decision | All agents |
| API changes | OpenAPI YAML + PR | As needed | Backend agent |

### Escalation Path
1. **Blocker**: Slack mention → resolve within 2 hours
2. **Decision needed**: GitHub issue discussion → consensus within 24 hours
3. **Conflict**: Team lead review → final decision within 48 hours

---

## Sprint Structure (Recommended)

### Weekly Cycle
- **Monday**: Sprint planning, define user stories with acceptance criteria
- **Tue–Thu**: Development in parallel (Backend API, Frontend UI, QA test automation)
- **Friday**: Code review, testing, documentation updates

### Definition of Done (All Teams)
- [ ] Code written and tested locally
- [ ] Tests pass in CI/CD pipeline
- [ ] Code review approved (≥1 reviewer)
- [ ] Documentation updated
- [ ] No linting errors
- [ ] Feature works on staging environment

---

## Tools & Commands Reference

### Backend
```bash
# Setup
composer install
php artisan key:generate
php artisan migrate

# Development
php artisan serve
php artisan tinker

# Testing
php artisan test
php artisan test --coverage
php artisan pint              # Fix code style
php artisan phpstan analyse   # Static analysis

# Deployment
php artisan migrate --force
php artisan config:cache
```

### Frontend
```bash
# Setup
npm install
npm run build

# Development
npm run dev                   # Hot reload on http://localhost:5173
npm run lint                  # ESLint + Prettier
npm run type-check            # TypeScript validation

# Testing
npm run test                  # Unit + Component tests
npm run test:e2e             # End-to-end tests
npm run coverage             # Coverage report

# Deployment
npm run build
```

### QA
```bash
# Backend
php artisan test --parallel
php artisan test --coverage --min=80

# Frontend
npm run lint
npm run test
npm run test:e2e

# Manual testing checklist
[Run browser dev tools for console errors]
[Test on mobile viewport]
[Check accessibility with axe DevTools]
```

---

## Success Metrics (Measure Weekly)

| Metric | Target | Owner |
|--------|--------|-------|
| **Cycle Time** | < 5 days (feature → release) | QA |
| **Defect Escape Rate** | < 5% (bugs in production) | QA |
| **Test Coverage** (Backend) | ≥ 80% | QA |
| **Test Coverage** (Frontend) | ≥ 70% | QA |
| **Linting Pass Rate** | 100% | QA |
| **PR Review Time** | < 24 hours | All |
| **Documentation Currency** | 100% of merged PRs documented | Documentation |
| **Performance (Lighthouse)** | ≥ 80 on mobile | Frontend |
| **API Uptime** | ≥ 99.5% | Backend |

---

## Onboarding New Agents

1. **Day 1**: Clone repo, run setup commands (SETUP.md), familiarize with code structure
2. **Day 2**: Review ARCHITECTURE.md, understand current features and roadmap
3. **Day 3**: Create "Hello World" PR (small docs update or linting fix)
4. **Week 2**: Pick "good-first-issue" from backlog, submit PR with guidance
5. **Week 3**: Independently pick and complete a real issue
6. **Week 4**: Review and mentor another agent on code quality

---

## Continuous Improvement

### Monthly Retrospective
- What worked well this month?
- What could be improved?
- What should we stop doing?
- Action items for next month?

### Quarterly Review
- Revisit success metrics—adjust targets if needed
- Update documentation based on lessons learned
- Plan next quarter's priorities with stakeholder input

---

## Quick Reference Checklists

### Before Starting Development
- [ ] Feature spec finalized and shared
- [ ] User stories written with acceptance criteria
- [ ] Database schema (if applicable) designed
- [ ] API contract drafted (if Backend)
- [ ] Wireframes/mockups created (if Frontend)
- [ ] Test plan defined (QA)
- [ ] Time estimate made and agreed upon

### Before Submitting PR
- [ ] Code works locally
- [ ] Tests pass and coverage is sufficient
- [ ] Linting passes
- [ ] No console errors/warnings
- [ ] PR description includes what changed and why
- [ ] Related issue/user story linked
- [ ] Documentation updated (if applicable)

### Before Merging to Main
- [ ] ≥1 approval from code review
- [ ] All CI/CD checks pass
- [ ] No merge conflicts
- [ ] Staging deployment tested manually
- [ ] Release notes prepared (if release)

---

_Last Updated: December 2025_
_Version: 2.0 (Productivity-Optimized)_