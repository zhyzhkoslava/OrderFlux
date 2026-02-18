# AGENTS.md

## Purpose
This file defines how coding agents should work in this repository to maximize delivery speed, correctness, and consistency.

## Stack Context
- Laravel 12, PHP 8.4
- PHPUnit, PHPStan, Rector, Pint
- Docker Compose local workflow

## Runtime Assumption
- Run project commands in the `app` container unless explicitly told otherwise.
- Preferred prefix: `docker compose exec -T app`

## Standard Commands
- Install PHP deps: `docker compose exec -T app composer install`
- Install JS deps: `docker compose exec -T app npm install`
- Run tests (parallel): `docker compose exec -T app php artisan test --parallel`
- Run single test file: `docker compose exec -T app php artisan test --compact tests/Feature/Api/OrderApiTest.php`
- Run PHPStan: `docker compose exec -T app ./vendor/bin/phpstan analyse --no-progress --memory-limit=2G`
- Run Pint (fix): `docker compose exec -T app ./vendor/bin/pint --dirty`
- Run Rector dry-run: `docker compose exec -T app ./vendor/bin/rector process --dry-run --no-progress-bar`

## Definition Of Done
A task is done only when all are true:
1. Requested behavior is implemented.
2. Relevant tests were added/updated for changed behavior.
3. Relevant checks were run and pass (minimum: targeted tests + formatting).
4. No unrelated files were modified intentionally.
5. Output explains what changed and which checks were run.

## Implementation Rules
- Follow existing code patterns in neighboring files before introducing new patterns.
- Prefer Laravel conventions over custom abstractions.
- Use Form Requests for validation; do not add inline validation in controllers.
- Use API Resources for API responses when the area already uses them.
- Prefer Eloquent relationships and `Model::query()` over raw `DB::` usage.
- Prevent N+1 queries with eager loading when returning nested data.
- Keep controllers thin; move complex business logic into focused classes.

## Testing Rules
- Every functional change must be covered by tests.
- Cover happy path, validation failures, and major edge cases.
- Run the smallest relevant test set first; run broader suites after.

## Linting And Static Analysis Policy
For non-trivial changes, run in this order:
1. `./vendor/bin/pint --dirty`
2. `./vendor/bin/phpstan analyse --no-progress --memory-limit=2G`
3. `./vendor/bin/rector process --dry-run --no-progress-bar` (if project uses Rector rules for CI)
4. Relevant tests

## Git Safety Rules
- Never run destructive commands (`git reset --hard`, `git checkout --`, bulk `rm`) unless explicitly requested.
- Do not revert user-authored changes outside the task scope.
- If unexpected external modifications appear mid-task, pause and ask.

## Dependency And Schema Changes
- Do not add or upgrade dependencies without explicit user approval.
- For migrations that alter columns, preserve prior attributes unless intentionally changing them.

## Communication Rules
- Be concise and concrete.
- Report failures with file paths and actionable next steps.
- For reviews, list findings first by severity with file references.

## Optional Boost Guidance
If Laravel Boost is enabled:
- Use Boost docs/search tools before uncertain framework decisions.
- Keep `boost.json` committed as project-level config.

## Additional User Guidelines

### Skills
- Use available Skills for:
- Laravel code style
- Testing practices
- Architecture decisions
- DevOps practices
- If a Skill applies, prefer it over repeating rules here.

### AI Agent Guidelines
This file contains canonical development guidelines for ALL AI coding assistants used in this repository (Copilot, Codex, Gemini, Claude, others).

If you are an AI agent:
- Read this file before suggesting code
- Follow these rules unless explicitly instructed otherwise

### Build/Configuration Instructions

#### System Requirements
- PHP 8.4+ (Critical: The project requires PHP 8.4.0 or higher)
- PostgreSQL 17
- Redis 7.2+
- Docker & Docker Compose (Recommended for development)

### Development Scripts
All commands should run from a Docker container. The project includes several useful Composer scripts:

- `docker compose exec -T app composer run dev` - Start all development services (Octane, queue, logs, Vite)
- `docker compose exec -T app composer run ide-helper` - Generate IDE helper files
- `docker compose exec -T app composer run phpstan` - Run static analysis
- `docker compose exec -T app composer run pint` - Check code style
- `docker compose exec -T app composer run pint:fix` - Fix code style issues
- `docker compose exec -T app composer run rector` - Check for code modernization opportunities
- `docker compose exec -T app composer run rector:fix` - Apply code modernization

### Running Tests
All tests must be run in a Docker container.

#### With Docker
```bash
# Run all tests
docker compose exec -T app php artisan test

# Run with coverage
docker compose exec -T app php artisan test --coverage
```

### Code Style & Development Practices

#### Eloquent ID Access
- Never access `$model->id` directly. Use `$model->getKey()` (or `$model->getKeyName()` when you need the column name) to respect custom primary keys and keep code forward-compatible.

#### Code Quality Tools
- Laravel Pint - Code formatting based on Laravel preset with strict rules
- PHPStan (Level 7) - Static analysis with Larastan for Laravel-specific checks
- Rector - Automated code modernization for PHP 8.4 and Laravel 12.0
- Cognitive Complexity - Limits complexity (class: 85, function: 8)

#### Code Style Rules
- Strict Types: All PHP files must declare `declare(strict_types=1)`
- Type Declarations: Full type hints required
- Strict Comparisons: Use `===` instead of `==`
- Modern PHP: Use PHP 8.4 features and modern type casting
- Class Organization: Specific order for class elements (constants, properties, methods)
- Array Formatting: Trailing commas in multiline arrays and parameters
- Eloquent Models: Use `getKey()` method in models instead of `id`
- Eloquent Models: Use `query()` method in models queries
- Eloquent Relationships: Use `with()` method for eager loading
- Eloquent Relationships: Use `withCount()` method for eager loading counts
- Eloquent Relationships: Use `withTrashed()` method for eager loading trashed models

### Architecture Patterns
- Domain Organization: Features organized by domain (Auth, MentorPrograms, etc.)
- Repository Pattern: Not explicitly used, relies on Eloquent models
- Service Layer: Implemented via Action classes
- Database Migrations: Every change in the DB structure should be reflected in a new migration
- Database Seeders: Every change in DB data should be reflected in a seeder
- Database Factories: Every change in DB data should be reflected in a factory
- Database Queries: Prefer Eloquent models over raw queries
- Database Relationships: Prefer Eloquent relationships to raw queries
- Database Eager Loading: Prefer Eloquent eager loading over raw queries
- Database Pagination: Prefer Eloquent pagination over raw queries
- Database Scopes: Prefer Eloquent scopes over raw queries
- Database Soft Deletes: Prefer Eloquent soft deletes over raw queries

### Performance Considerations
- Laravel Octane: Uses FrankenPHP for high-performance application server
- Redis: Used for caching, sessions, and queue management
- Database: PostgreSQL with proper indexing
- Asset Optimization: Image optimization tools included in Docker setup

### Development Tools
- Telescope: Laravel debugging assistant (enabled in testing)
- Log Viewer: Web-based log viewing interface
- IDE Helpers: Comprehensive IDE support with auto-generated helpers
- Xdebug: Available in Docker development environment

### CI/CD Pipeline
The project uses GitHub Actions with:

- Linting: PHPStan, Laravel Pint, Rector
- Testing: PHPUnit with parallel execution and coverage reporting
- Code Coverage: Codecov integration
- Parallel Execution: Tests run in parallel for faster feedback

### Git Operations
- NEVER create commits automatically - only commit when explicitly requested by the user
- NEVER push to remote without explicit user request
- NEVER force push or run destructive git commands without explicit approval
- When changes are ready, inform the user and wait for their instruction to commit/push
- Always show `git diff` or `git status` to let the user review changes before committing

### Environment-Specific Notes
- Local Development: Use Docker Compose for consistent environment
- Testing: Separate PostgreSQL instance for tests
- Production: Optimized for Laravel Octane with FrankenPHP
- Debugging: Xdebug available in development, Telescope for application debugging

### Common Commands
```bash
# Code quality checks
docker compose exec -T app ./vendor/bin/phpstan analyse
docker compose exec -T app ./vendor/bin/pint --dirty
docker compose exec -T app ./vendor/bin/rector process --dry-run

# Fix code issues
docker compose exec -T app ./vendor/bin/pint
docker compose exec -T app ./vendor/bin/rector process

# Generate IDE helpers
docker compose exec -T app php artisan ide-helper:generate
docker compose exec -T app php artisan ide-helper:models

# Clear caches
docker compose exec -T app php artisan config:clear
docker compose exec -T app php artisan cache:clear
docker compose exec -T app php artisan view:clear
```


## Migrated From CLAUDE.md

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.15
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/octane (OCTANE) - v2
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- rector/rector (RECTOR) - v2

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== boost/core rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== pint/core rules ===

# Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
