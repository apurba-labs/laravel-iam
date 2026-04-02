# Laravel IAM (Identity & Access Management)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/apurba-labs/laravel-iam.svg?style=flat-square)](https://packagist.org/packages/apurba-labs/laravel-iam)
[![Total Downloads](https://img.shields.io/packagist/dt/apurba-labs/laravel-iam.svg?style=flat-square)](https://packagist.org/packages/apurba-labs/laravel-iam)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

A powerful, context-aware Identity and Access Management (IAM) system for Laravel. Inspired by AWS IAM, built for modern SaaS architectures.

## 🚀 Key Features

* **Contextual Scopes:** Assign roles to users for specific branches or tenants.
* **Wildcard Logic:** Support for `resource.*`, `*.action`, and `*.*` overrides.
* **Action Aliasing:** Built-in `manage` capability (grants all actions for a resource).
* **Developer Friendly:** Dynamic Resource & Action registration.
* **Performance First:** Built-in caching for permission resolution.

---
## ⚖️ Design Philosophy: Contextual Authority

Most RBAC (Role-Based Access Control) packages treat permissions as "Global Flags." In modern SaaS and Enterprise applications, authority is rarely that simple. 

**Laravel IAM** is built on the principle that power is **Contextual**. A user might be a `Manager` in the *Dhaka Branch* but only a `Viewer` in the *Chittagong Branch*. Our engine resolves this complexity using a prioritized hierarchy we call the **Four Levels of Truth**.

### 🧠 The "Four Levels of Truth"

When you check a permission like `invoice.approve`, the engine doesn't just look for a string match. It evaluates authority from the broadest scope to the most specific to ensure maximum flexibility and security.

#### 🔑 Permission Hierarchy: "Context-Aware RBAC"

| Level | Type | Example | Description |
| :---: | :--- | :--- | :--- |
| **1** | `Global` | `*.*` | **Full Access**: Absolute power across all resources and actions. |
| **2** | `Resource` | `invoice.*` | **Module Control**: Full authority over a specific resource. |
| **3** | `Action` | `*.approve` | **Action Specialist**: Specific action allowed system-wide. |
| **4** | `Atomic` | `invoice.approve` | **Task Specific**: One specific action on one resource. |

> **Performance Note:** The engine uses a "Fast-Pass" strategy. If Level 1 or 2 is satisfied, the resolution exits immediately, ensuring that administrative accounts experience zero latency during authorization checks.

---
## 🧱 Architectural Design Patterns

Laravel IAM is not just a collection of scripts; it is a structured engine built using industry-standard patterns to ensure scalability and maintainability.

### 🗄️ Registry Pattern
We use a **Registry Pattern** for Resource and Action management. This decouples your application's domain logic from the database, allowing you to register permissions dynamically via Service Providers without hitting the database on every boot.

### 🛡️ Singleton & Manager Pattern
The `IAMManager` acts as a **Singleton** within the Laravel Service Container. This ensures a single source of truth for authorization checks during a request lifecycle, enabling efficient memory usage and consistent caching.

### 🔌 Facade & Proxy Pattern
By providing a **Facade**, we offer a clean, expressive API (`IAM::can()`). Internally, this proxies calls to the underlying `PermissionResolver`, shielding the developer from the complexity of the hierarchical resolution logic.

### 🔍 Strategy Pattern
The `PermissionResolver` employs a **Strategy Pattern** to evaluate permissions. It switches between "Global," "Wildcard," and "Atomic" strategies to find the fastest path to an authorization decision.

---

## 📦 Installation

Install the package via composer:

```bash
composer require apurba-labs/laravel-iam
```
Publish and run the migrations:
```bash
php artisan vendor:publish --tag="iam-migrations"
php artisan migrate
```
---
## 🛠 Usage

### 1. Setup your Model
Add the trait and contract to your User.php:
```php
use ApurbaLabs\IAM\Traits\HasRoles;
use ApurbaLabs\IAM\Contracts\Authorizable;

class User extends Authenticatable implements Authorizable {
    use HasRoles;
}
```

### 2. Registration Resources

```markdown
Register your modules in `AppServiceProvider.php`:

```php
public function boot() {
    IAM::registerResources([
        'inventory' => 'Stock Management',
        'payroll'   => 'Employee Salary'
    ]);

    IAM::registerActions(['submit', 'approve']);
}
```

### 3. Syncing to Database
```bash
php artisan iam:sync

```
--- 
### 🔍 4. Checking Permissions (The Logic)

### Via Facade
```php
// Global check
IAM::can($user, 'inventory.view');

// Scoped check (e.g., for Branch ID 101)
IAM::can($user, 'inventory.view', 101);

```
### Via Middleware

The middleware automatically detects the scope from the X-Scope-ID header.

```php
// Single permission
Route::middleware('iam:inventory.view')->get('/inventory', ...);

// Multiple permissions (OR logic)
Route::middleware('iam:payroll.edit|payroll.manage')->post('/payroll', ...);

```
---
📄 License
The MIT License (MIT). Please see License File for more information.