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
```markdown
Implement the Authorizable contract and add the HasRoles trait to your User model. This unlocks the relationship and authority checks.
```
```php
use ApurbaLabs\IAM\Traits\HasRoles;
use ApurbaLabs\IAM\Contracts\Authorizable;

class User extends Authenticatable implements Authorizable {
    use HasRoles;
}
```
### 2. Registering Resources & Actions
```markdown
Define your application's domain in AppServiceProvider.php. This populates the internal registry used for the "Four Levels of Truth" engine.
Register your modules in `AppServiceProvider.php`:
```
```php
public function boot() {
    // Register high-level modules
    IAM::registerResources([
        'inventory' => 'Stock Management',
        'payroll'   => 'Employee Salary'
    ]);

    // Register global actions
    IAM::registerActions(['submit', 'approve']);
}
```
### 3. Synchronize to Database
```bash
php artisan iam:sync

```
### 4. Authorization Logic
```markdown
#### Via Facade
The Facade is the most flexible way to check authority, especially for multi-tenant or scoped applications.
```
```php
// Global Check (Is this user an Admin anywhere?)
IAM::can($user, 'inventory.view');

// Contextual Check (Is this user a Manager specifically for Branch 101?)
IAM::can($user, 'inventory.approve', 101);

```
```markdown
#### Via Middleware

Perfect for protecting API routes. The middleware automatically looks for an X-Scope-ID header to evaluate contextual permissions.
```
```php
// Single permission check
Route::middleware('iam:inventory.view')->get('/inventory', ...);

// Multiple permissions (OR logic)
Route::middleware('iam:payroll.edit|payroll.manage')->post('/payroll', ...);

```
### 5. UI Integration (Blade Magic)
```markdown
We’ve provided expressive Blade directives to keep your templates clean. No more messy @if blocks.
#### Permission Checks
{{-- Checks if the user can approve in the current branch context --}}
@iam('invoice.approve', $branchId)
    <button class="btn-success">Approve Invoice</button>
@else
    <span class="text-muted">Read-only Access</span>
@endiam
```
#### Role Checks
```markdown
{{-- Checks if the user has the 'admin' role globally or in a scope --}}
@role('admin')
    <a href="/settings">System Settings</a>
@endrole
```
### 6. Workflow Resolution
```markdown
Need to find who to notify? Use the resolver to find users with specific authority within a specific scope.
```PHP
// Returns a collection of Users who can approve invoices for Branch 101
$approvers = IAM::usersWithPermission('invoice.approve', 101);
```
---
📄 License
The MIT License (MIT). Please see License File for more information.