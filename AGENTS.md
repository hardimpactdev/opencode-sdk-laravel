# OpenCode SDK Laravel

## Project Overview
Laravel SDK for the OpenCode AI coding agent HTTP API, built with Saloon.

## Architecture
- **Connector**: `src/OpenCode.php` - Saloon connector, entry point
- **Resources**: `src/Resources/` - Fluent API layer (SessionResource, QuestionResource, EventResource, ProviderResource, ProjectResource)
- **Requests**: `src/Requests/` - Saloon request classes per endpoint
- **DTOs**: `src/Data/` - Spatie Laravel Data DTOs
- **Enums**: `src/Enums/` - PHP enums for event types, part types, etc.
- **SSE**: `src/Support/EventStream.php` - Server-Sent Events parser

## Conventions
- Namespace: `HardImpact\OpenCode`
- DTOs use `spatie/laravel-data`
- HTTP via `saloonphp/saloon`
- Tests via Pest
- Static analysis via PHPStan/Larastan

## Maintenance Rules
- When adding or removing features, update the feature parity table in `README.md`
- Keep usage examples in `README.md` current with the actual API surface

## OpenCode API
- Local server at `http://localhost:4096` (default)
- No authentication required
- REST + SSE endpoints
- Core resources: sessions, messages, events, permissions, providers, projects
