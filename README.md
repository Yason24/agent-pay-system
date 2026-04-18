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
* MySQL
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

### 3. Create .env file

```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=test
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Start server

Open browser:

```
http://website-template/
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
