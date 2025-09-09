# Architecture Overview

Frog is intentionally small. Below you will find a high-level map of components.

## Core

- App: Singleton holder for the Router & Container.
- Container: Lightweight dependency injection (constructor + callable resolution).

## Routing

- Router: Stores routes (method, URI pattern, handler, middleware).
- Pattern syntax: `/path/{param}` becomes a named capture group.
- Dispatch flow: Request -> Global Middleware -> Route Middleware -> Handler.

## HTTP

- Request: Immutable wrapper around superglobals.
- Response: Fluent builder for status/headers/html/json.
- Middleware: `handle(Request $r, callable $next): Response`.

## Console

- Entry script: `frog` (registered as bin via composer).
- Commands extend `Console\Command` and override `handle()`.
- Available out of box: list, serve, route:list, make:controller.

## Views

- Simple PHP templates with extracted variables.

## DI & Auto-wiring

- Types in controller constructors or closure parameters resolved automatically.
- Services can be bound manually as singleton or transient.

## Helpers

- `app()`, `container()`, `response()`, `view()`.

## Extending

- Add more middleware types (logging, sessions, CSRF) easily by implementing the interface.
- Add command: create new class, register in `Console\Application`.

## Lifecycle

1. Front controller loads Composer + creates `App`.
2. Routes bootstrap registers routes/services.
3. Router matches incoming request.
4. Middleware pipeline executes.
5. Handler returns Response (or string -> wrapped).
6. Response sent.
