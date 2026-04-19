# AGENTS.md - Agent Pay System

## Архитектура

- **Фреймворк**: Кастомный PHP фреймворк, вдохновленный Laravel
- **Входная точка**: `public/index.php` -> Application -> Kernel -> Middleware Pipeline -> Router -> Controller/View
- **DI Контейнер**: `Framework\Core\Application` расширяет `Container`, singleton/instance bindings
- **Сервис-провайдеры**: Регистрируются в `composer.json['extra']['providers']`, загружаются в `Application::registerConfiguredProviders()`

## Маршрутизация

- **Фасад Route**: `Framework\Core\Support\Facades\Route` -> 'router'
- **Группы**: `Route::middleware('web')->group(...)` для middleware групп
- **Диспетчеризация**: `Router::dispatch()` обрабатывает closure или [Controller, method]
- **Пример**: `Route::get('/', function () { echo 'WORKS'; });`

## Модели и БД

- **Модели**: Расширяют `Framework\Core\Model`, `$table` свойство
- **Отношения**: `hasMany()`, `belongsTo()` возвращают `Relations\HasMany`/`BelongsTo`
- **Создание**: `Model::create($data)` для INSERT
- **Ленивая загрузка**: `__get()` вызывает relation методы при доступе
- **Пример**: 
  ```php
  class User extends Model {
      protected static string $table = 'users';
      public function agents() {
          return $this->hasMany(Agent::class, 'user_id');
      }
  }
  ```

## Миграции и Сиды

- **Миграции**: Файлы в `migrations/`, closures `(PDO $db) => $db->exec("SQL")`
- **Сиды**: Классы в `seeders/`, `run(PDO $db)`
- **CLI**: `php console migrate`, `make:migration NAME`, `rollback`, `db:seed NAME`
- **Пример миграции**: 
  ```php
  return function(PDO $db) {
      $db->exec("CREATE TABLE users (id SERIAL PRIMARY KEY, name VARCHAR(255))");
  };
  ```
- **Пример сида**: 
  ```php
  class UserSeeder {
      public function run(PDO $db) {
          $db->exec("INSERT INTO users (name) VALUES ('Admin')");
      }
  }
  ```

## Представления

- **Фабрика**: `ViewFactory::make('view.name', $data)` компилирует Blade-like
- **Компилятор**: `BladeCompiler` кеширует в `storage/cache/views`
- **Лейауты**: `@extends('layout')` в view, `View::getLayout()`
- **Пример**: `@extends('layouts.app')` в view файле

## Конфигурация

- **Конфиг**: `ConfigLoader::load('config')`, доступ через `app('config')`
- **Env**: `Env::load('.env')` для переменных окружения

## Соглашения

- **Автозагрузка**: PSR-4 в `composer.json`, `App\\` -> `app/`, `Framework\\` -> `framework/`
- **Helpers**: Автозагружаются из `framework/Core/helpers.php`, `app/helpers.php`
- **Фасады**: `Facade::setFacadeApplication()`, `getFacadeAccessor()` возвращает ключ контейнера

## Рабочие процессы

- **Запуск**: `composer install`, создать `.env`, открыть `http://website-template/`
- **Миграции**: `php console migrate` после настройки БД в `.env`
- **Сиды**: `php console db:seed UserSeeder` для заполнения данными

## Ключевые файлы

- `framework/Core/Application.php`: Контейнер и бутстрап
- `framework/Core/Router.php`: Регистрация и диспетч маршрутов
- `framework/Core/Http/Kernel.php`: Обработка запросов через pipeline
- `framework/Core/Model.php`: ORM с relations и CRUD
- `framework/Core/View/ViewFactory.php`: Рендеринг представлений
- `console`: CLI команды для миграций и сидов
