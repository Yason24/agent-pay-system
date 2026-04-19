# Agent Pay System — Framework Architecture

---

## Overview

Agent Pay System is a custom PHP framework inspired by Laravel architecture.

The project is NOT a simple website -- it is a full application framework.

---

## Request Lifecycle

Browser Request

-> public/index.php

-> Application Container

-> HTTP Kernel

-> Global Middleware Pipeline

-> Router

-> Route Middleware

-> Controller

-> View Engine

-> Response

-> Browser

---

## Core Components

### Application

-  Service Container

-  Dependency Injection

-  Base bindings

-  Framework bootstrap

### HTTP Kernel

-  Main entry point

-  Runs global middleware

-  Loads routes

-  Dispatches requests

### Router

-  Route registration

-  Route groups

-  Middleware support

-  Controller resolution

### Middleware System

-  Global middleware

-  Middleware aliases

-  Middleware groups (Laravel-style)

-  Pipeline execution

### Container

-  Singleton bindings

-  Instance bindings

-  Auto dependency resolution

-  Reflection-based injection

### View Engine

-  PHP view rendering

-  Layout support (planned)

-  Blade-like features (planned)

---

## Current Implemented Features

✅ Service Container

✅ Dependency Injection

✅ HTTP Kernel

✅ Request Lifecycle

✅ Global Middleware

✅ Middleware Registry

✅ Route Groups

✅ Middleware Aliases

✅ Router Dispatching

✅ Response System

✅ View Factory

---

## Planned Features

### Phase 2 -- Application Layer

-  Layout system

-  Blade-like templates

-  Controllers expansion

-  Validation

-  Session handling

### Phase 3 -- Platform Features

-  Database layer (ORM)

-  Authentication

-  User dashboard

-  Payment system modules

### Phase 4 -- Production

-  CLI tooling

-  Queue system

-  Events & Listeners

-  Caching

-  Deployment pipeline

---

## Philosophy

Build a stable framework FIRST.

Build business logic SECOND.

Architecture > Speed.

---

## Bootstrap Layer

bootstrap/app.php

Responsible for:
- creating Application instance
- registering providers
- loading configuration
- preparing runtime environment

---
