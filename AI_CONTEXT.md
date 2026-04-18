# AI_CONTEXT — Agent Pay System

## Project Type

Internal financial workflow system (software product).

Purpose: replace direct money transfers to agents with controlled purchase payments to reduce legal and tax risks.

---

## Business Logic

Company works with insurance agents (OSAGO).

Agents receive commission monthly.

Instead of transferring cash:

* agents submit purchase links
* company dispatchers pay purchases manually
* agent debt balance decreases

System acts as **internal payment accounting platform**.

---

## Core Workflow

1. Accountant sets agent balance (company debt).
2. Agent logs in.
3. Agent sees available balance.
4. Agent submits purchase link/order.
5. Dispatcher accepts order.
6. Dispatcher pays manually using company card.
7. Dispatcher confirms payment.
8. Agent balance decreases.
9. All actions logged.

---

## User Roles

### Agent

* personal cabinet
* view balance
* submit orders
* view history

### Dispatcher

* see all agents
* accept orders
* mark paid
* add payment amount

### Accountant

* adjust balances
* create agents
* financial control

### Admin

* full access
* role management
* system settings

---

## Technical Stack

Language: PHP 8+
Architecture: Custom MVC Framework
Database: PostgreSQL
Autoload: PSR-4 (Composer)
Environment: OSPanel (local dev)

Framework Repository:
website-template

Product Repository:
agent-pay-system

---

## Architecture

Request
→ Router
→ Container (DI)
→ Controller
→ Service
→ Model
→ Database (PDO PostgreSQL)
→ View

---

## Current Status

✔ MVC implemented
✔ Dependency Injection Container
✔ ENV configuration
✔ Database connection
✔ Product repository created

Next Step:
Database migrations + Auth system.

---

## Database Concepts (Planned)

agents
users
roles
balances
orders
payments
operation_logs

All actions must be auditable.

---

## Non-Functional Requirements

* multiple legal entities
* RUB currency only
* manual payments only (initial phase)
* concurrent dispatcher work
* full history logging

---

## Notifications (Future)

Possible external bot:

* Telegram or MAX-bot
  Messages:
* Order accepted
* Order completed

---

## Development Rules

* framework code separated from product
* migrations only (no manual DB changes)
* every action logged
* role-based access

---

## AI Instruction

You are continuing development of an existing software product.

Do NOT rebuild architecture.
Extend existing system step-by-step.

Current phase:
START DATABASE DESIGN.
