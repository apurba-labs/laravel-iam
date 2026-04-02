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
```markdown
## Checking Permissions

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