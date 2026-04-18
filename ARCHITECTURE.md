# ARCHITECTURE — Agent Pay System

## Architecture Style

Layered MVC Architecture with Dependency Injection.

Framework separated from Business Logic.

---

## High Level Flow

HTTP Request
→ public/index.php
→ Router
→ Container (Dependency Injection)
→ Controller
→ Service / Model
→ Database (PDO PostgreSQL)
→ View Response

---

## Application Layers

### 1. Public Layer

Location: `/public`

Entry point of application.

Responsibilities:

* bootstrap system
* load Composer
* load ENV
* start Router

Rules:

* no business logic allowed

---

### 2. Routing Layer

Location: `/routes`

Defines URL → Controller mapping.

Rules:

* routing only
* no database logic

---

### 3. Controller Layer

Location: `/app/Controllers`

Responsibilities:

* receive request
* validate input
* call services
* return response

Rules:

* controllers must stay thin
* no SQL queries inside controllers

---

### 4. Service Layer (planned)

Location: `/app/Services`

Responsibilities:

* business logic
* workflows
* calculations

---

### 5. Model Layer (planned)

Location: `/app/Models`

Responsibilities:

* database interaction
* entity representation

Rules:

* models do not render views

---

### 6. Core Layer

Location: `/app/Core`

Contains framework components:

* Router
* Container
* Controller base class
* Database connection
* Env loader

Rules:

* product logic MUST NOT be placed here

---

### 7. Database Layer

Engine: PostgreSQL
Access: PDO

Rules:

* migrations only
* no manual schema editing

---

## Dependency Injection

Container automatically resolves class dependencies.

Controllers must receive services via constructor.

Example:

```php
class AgentController {
    public function __construct(Database $db) {}
}
```

---

## Configuration System

Environment variables stored in `.env`.

Never commit `.env`.

---

## Logging Principle

Every financial action must be logged.

Nothing happens without audit trail.

---

## Scaling Strategy (Future)

* Service classes
* Queue workers
* API layer
* External integrations
* Multi-company isolation

---

## Development Rules

DO:

* keep layers separated
* use migrations
* write reusable services

DO NOT:

* write SQL in views
* place logic in routes
* modify Core for business logic

---

## Project Philosophy

Framework = Engine
Product = Business Logic

Never mix them.
