# AI_CONTEXT.md

## Project

Website Template — базовый шаблон backend-приложения.

## Goal

Используется как foundation для будущих software products.

## Stack

* PHP 8+
* Custom MVC
* OSPanel (local dev)
* PostgreSQL (planned)
* No framework (framework-like architecture)

## Architecture

* Front Controller → public/index.php
* Router → app/Core/Router.php
* Controllers → app/Controllers
* Views → app/Views
* Layout system enabled
* MVC implemented

## Current State

✅ Router
✅ Controller system
✅ View + Layout system
✅ GitHub connected

## Next Planned Steps

* Composer
* PSR-4 Autoload
* Database Layer
* Auth system
* Roles & Permissions

## Rules

* No direct PHP pages
* Only Controllers render views
* All requests go through index.php
