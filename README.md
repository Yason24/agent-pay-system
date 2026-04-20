# Website Template

Lightweight PHP MVC framework created for fast development of web applications.

---

## 🚀 Features

* MVC Architecture
* Dependency Injection Container
* Custom Router
* Environment configuration (.env)
* PDO Database connection
* Composer autoloading
* Clean project structure

---

## 📁 Project Structure

```
app/
 ├── Controllers/
 ├── Core/
 │   ├── Container.php
 │   ├── Controller.php
 │   ├── Database.php
 │   ├── Env.php
 │   └── Router.php
 └── Views/

public/
 └── index.php

routes/
 └── web.php
```

---

## ⚙️ Requirements

* PHP 8.1+
* Composer
* PostgreSQL
* PDO pgsql extension enabled (`pdo_pgsql`)
* OSPanel / XAMPP / Docker

---

## 🔧 Installation

### 1. Clone repository

```
git clone https://github.com/YOUR_USERNAME/website-template.git
```

### 2. Install dependencies

```
composer install
```

### 3. Create `.env` from `.env.example`

```powershell
Copy-Item .env.example .env
```

Then set DB values in `.env`:

```dotenv
DB_DRIVER=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agent_pay_system
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### 4. Set document root to `public/`

For OSPanel, make sure project `.osp/project.ini` has:

```ini
web_root    = {base_dir}\public
```

### 5. Create PostgreSQL database

Create DB manually (example name): `agent_pay_system`.

### 6. Run migrations

```powershell
php console migrate
```

### 7. Start server

Open browser:

```
http://agent-pay-system/
```

---

## 🧠 Architecture

This project follows MVC pattern:

* **Models** — Database logic
* **Views** — Templates
* **Controllers** — Application logic

Routing handled via custom Router class.

---

## 📦 Next Steps

* Migration System
* ORM Layer
* Auth System
* API Support
* CLI commands

---

## 📄 License

MIT

---

## 🛡 Stability Checks (OSPanel)

Run a full stability check:

```powershell
composer stability:check
```

Run only environment checks:

```powershell
composer stability:preflight
```

Run app/bootstrap smoke checks:

```powershell
composer stability:smoke
```

Detailed runbook: `OPS_STABILITY.md`

