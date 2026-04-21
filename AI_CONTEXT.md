# AI Context — Agent Pay System

## Project

Agent Pay System — веб-система учета задолженности компании перед агентами, заявок на оплату и фактических оплат.

Это не шаблон сайта и не абстрактный framework demo. Это бизнес-приложение со строгими ролями, финансовой логикой и ограничениями доступа.

## Core Business Rule

Агент = пользователь с ролью `agent`.

Это обязательное архитектурное правило проекта.

- агент входит в систему под своим логином и паролем
- агент видит только свои данные
- все агентские сущности должны быть привязаны к `users.id`
- не использовать `agents` как отдельную основную бизнес-сущность

## Roles

- `admin`
- `accountant`
- `dispatcher`
- `agent`

## Main Business Domains

- Users and roles
- Legal entities
- Agent balances by legal entity
- Payment requests
- Payments
- Debt operations
- Audit log
- Notifications

## Financial Rules

- Баланс агента ведётся по каждому юрлицу
- Доступный баланс = total - reserved
- Баланс не должен изменяться вручную без финансовой операции
- Для конкурентных действий использовать транзакции
- Нельзя допускать двойную обработку заявки

## Access Rules

- `agent` sees only own balances, requests, payments, history
- `dispatcher` works with requests in operational flow
- `accountant` manages accruals and corrections
- `admin` manages users, roles, legal entities, logs and settings

## Technical Context

- Custom Laravel-inspired PHP MVC framework
- PostgreSQL
- Entry point: `public/index.php`
- Bootstrap: `bootstrap/app.php`
- Routes: `routes/web.php`
- App code: `app/`
- Views: `resources/views/`

## Documentation Rules

- `README.md` is the source of truth for setup
- `ARCHITECTURE.md` is the source of truth for architecture decisions
- Keep docs aligned with current code
- Do not reintroduce `agent` as a separate primary business entity
