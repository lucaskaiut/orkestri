# Orkestri

**Opinionated architectural scaffold generator for Laravel.**

Orkestri generates a consistent, module-based structure for Laravel applications. Each module contains Domain (Models, Services), HTTP (Controllers, Requests, Resources), Database (Migrations), and API routes. Generated controllers and services use built-in traits and contracts so you get a working CRUD API skeleton without writing boilerplate.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [How It Works](#how-it-works)
- [Commands](#commands)
- [Generated Structure](#generated-structure)
- [Architecture](#architecture)
- [Stubs](#stubs)
- [Testing](#testing)
- [License](#license)

---

## Requirements

- **PHP** ^8.1
- **Laravel** 10.x or 11.x (auto-discovered via Composer)

---

## Installation

Install the package via Composer:

```bash
composer require lucaskaiut/orkestri
```

The package registers its service provider automatically (Laravel package discovery). No need to add it to `config/app.php`.

---

## Configuration

Publish the config file to customize where modules live and which folders each module has:

```bash
php artisan vendor:publish --tag=orkestri-config
```

This creates `config/orkestri.php`:

| Key | Default | Description |
|-----|---------|-------------|
| `base_path` | `'Modules'` | Directory under `app/` where modules are created (e.g. `app/Modules/Customer` or `app/Domains/Customer`). |
| `structure` | See below | List of relative paths created inside each module. |

**Default `structure`:**

```php
'structure' => [
    'Domain/Models',
    'Domain/Services',
    'Database/Migrations',
    'Http/Controllers',
    'Http/Requests',
    'Http/Resources',
],
```

All commands respect `base_path` and `structure`. Changing `base_path` to e.g. `'Domains'` makes modules appear under `app/Domains/{ModuleName}` and updates namespaces accordingly.

---

## How It Works

- **Routes:** The service provider scans `app/{base_path}/{Module}/Http/Routes/` and loads `api.php` for each module. No manual `RouteServiceProvider` wiring per module.
- **Migrations:** It also loads migrations from `app/{base_path}/{Module}/Database/Migrations/` so module migrations run with `php artisan migrate`.

Generated controllers use a **ControllerTrait** (index, show, store, update, destroy) and delegate to a **Service**; services implement **ServiceContract** via **ServiceTrait** and are bound to a **Model**. So a new module is a ready-to-use CRUD API scaffold.

---

## Commands

All commands are namespaced under `orkestri:`.

### Create a full module

Creates the folder structure and all default files (service, model, migration, controller, resource, request, API routes).

```bash
php artisan orkestri:make-module {name}
```

**Examples:**

```bash
php artisan orkestri:make-module Customer
php artisan orkestri:make-module Product
```

- **`name`** — Module name (StudlyCase). Used for folder name, class names, and plural route segment (e.g. `customers`).
- Fails with exit code `1` and message *"Module already exists."* if the module directory already exists (no overwrite).

---

### Create individual components

Use these when you want to add another model, service, controller, etc. inside an existing module.

| Command | Signature | Description |
|---------|-----------|-------------|
| **Model** | `orkestri:make-model {module} {name?}` | Creates an Eloquent model in `Domain/Models`. Default `name` = module name. Table name is pluralized (e.g. `Customer` → `customers`). |
| **Service** | `orkestri:make-service {module} {name?}` | Creates a service implementing `ServiceContract` in `Domain/Services`. Default `name` = `{Module}Service`. |
| **Controller** | `orkestri:make-controller {module} {name?}` | Creates a controller using `ControllerTrait` in `Http/Controllers`. Default `name` = `{Module}Controller`. |
| **Resource** | `orkestri:make-resource {module} {name?}` | Creates a JSON API resource in `Http/Resources`. Default `name` = `{Module}Resource`. |
| **Request** | `orkestri:make-request {module} {name?}` | Creates a FormRequest in `Http/Requests`. Default `name` = `{Module}Request`. |
| **Routes** | `orkestri:make-routes {module}` | Creates `Http/Routes/api.php` with `Route::apiResource()` for the default controller and plural resource name. |
| **Migration** | `orkestri:make-migration {module} {name?}` | Creates a migration in `Database/Migrations`. Without `name`, creates `create_{plural_table}_table` (e.g. `create_customers_table`). |

**Examples:**

```bash
# Default names (Customer → Customer, CustomerService, CustomerController, etc.)
php artisan orkestri:make-model Customer
php artisan orkestri:make-service Customer
php artisan orkestri:make-controller Customer

# Custom names within a module
php artisan orkestri:make-model Customer Order
php artisan orkestri:make-migration Customer add_status_to_customers_table
```

All of these refuse to overwrite existing files and print an error message instead.

---

## Generated Structure

After running:

```bash
php artisan orkestri:make-module Customer
```

you get (with default `base_path` = `Modules`):

```
app/Modules/Customer/
├── Domain/
│   ├── Models/
│   │   └── Customer.php
│   └── Services/
│       └── CustomerService.php
├── Database/
│   └── Migrations/
│       └── {timestamp}_create_customers_table.php
├── Http/
│   ├── Controllers/
│   │   └── CustomerController.php
│   ├── Requests/
│   │   └── CustomerRequest.php
│   ├── Resources/
│   │   └── CustomerResource.php
│   └── Routes/
│       └── api.php
```

- **Namespaces** follow `App\{base_path}\{Module}\...` (e.g. `App\Modules\Customer\Domain\Models`).
- **API routes** in `api.php` use `Route::apiResource('customers', CustomerController::class)` (pluralized from the module name).
- **Migration** creates/drops the `customers` table (pluralized from the module name).
- **Model** uses table `customers`; **Controller** uses **CustomerService**, **CustomerResource**, and **CustomerRequest**; **Service** uses **Customer** model and **ServiceTrait**/**ServiceContract**.

---

## Architecture

### ControllerTrait

Controllers generated by Orkestri use `LucasKaiut\Orkestri\Traits\ControllerTrait`. You must define:

- `protected string $service` — FQCN of the service (e.g. `CustomerService::class`)
- `protected string $resource` — FQCN of the JSON resource (e.g. `CustomerResource::class`)
- `protected string $request` — FQCN of the FormRequest for store/update (e.g. `CustomerRequest::class`)

The trait provides:

- `index(Request $request)` — Paginated list (query params: `filters`, `orderBy`, `per_page`)
- `show($id)` — Single resource
- `store()` — Create (validates with `$request`, runs in DB transaction)
- `update($id)` — Update (validates with `$request`, runs in DB transaction)
- `destroy($id)` — Delete (in transaction)

Responses use your Resource class and appropriate HTTP status codes.

### ServiceContract & ServiceTrait

Services implement `LucasKaiut\Orkestri\Contracts\ServiceContract` and use `LucasKaiut\Orkestri\Traits\ServiceTrait`. You must define:

- `protected string $model` — FQCN of the Eloquent model (e.g. `Customer::class`)

The trait implements: `create`, `all`, `find`, `findOrFail`, `findBy`, `getBy`, `paginate`, `update`, `delete`, with support for conditions, ordering, relations, and pagination. Filtering in the controller uses the `filters` and `orderBy` query parameters passed into `paginate`/`getBy`.

---

## Stubs

Templates live in the package under `stubs/`:

| Stub | Used by | Main placeholders |
|------|---------|-------------------|
| `model.stub` | make-model | `{{ namespace }}`, `{{ model }}`, `{{ table }}` |
| `service.stub` | make-service | `{{ namespace }}`, `{{ module }}`, `{{ service }}` |
| `controller.stub` | make-controller | `{{ namespace }}`, `{{ basePath }}`, `{{ module }}`, `{{ controller }}`, `{{ service }}`, `{{ resource }}`, `{{ request }}` |
| `resource.stub` | make-resource | `{{ namespace }}`, `{{ resource }}` |
| `request.stub` | make-request | `{{ namespace }}`, `{{ request }}` |
| `routes-api.stub` | make-routes | `{{ basePath }}`, `{{ module }}`, `{{ controller }}`, `{{ resourceName }}` |
| `migration.stub` | make-migration | `{{ table }}` |

The package does not publish stubs by default. To customize generated files you would need to copy these stubs into your project and point the commands at your copies (or extend the commands). Namespaces and paths are derived from the module name and `config('orkestri.base_path')`.

---

## Testing

The project uses [Orchestra Testbench](https://github.com/orchestral/testbench) and [Pest](https://pestphp.com/) for tests. Tests focus on the generator behaviour: correct directories, file paths, namespaces, stub content, config, and that existing modules/files are not overwritten.

```bash
composer install
./vendor/bin/pest
```

Test suites:

- **Feature** — `MakeModuleTest`, `MakeModelTest`, `MakeServiceTest` (and any other command tests). They assert structure, content, pluralization, custom `base_path`, and idempotency (e.g. second `make-module` for the same name exits with code 1).

---

## License

The MIT License (MIT). See the [LICENSE](LICENSE) file for details.
