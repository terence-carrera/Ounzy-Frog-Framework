# Dependency Injection

## Overview

Frog's `Container` offers lightweight auto-wiring for classes, controller constructors, and closure route parameters.

## Binding

```php
container()->singleton(MyService::class, MyService::class);
container()->bind(InterfaceName::class, ConcreteClass::class); // transient
```

## Resolving

```php
$svc = container()->make(MyService::class);
```

## Constructor Injection

```php
class ReportController {
  public function __construct(ReportService $reports) {}
}
```

Controller creation is automatic.

## Closure Injection

```php
$router->get('/hi/{name}', function(Request $r, array $params, GreetingService $g) {
  return response()->html($g->greet($params['name']));
});
```

## Callables

`container()->call([$object, 'method'])` resolves dependencies.

## Instances

```php
$client = new HttpClient();
container()->instance(HttpClient::class, $client);
```

## When Auto-Wiring Fails

- Class does not exist
- Unresolvable scalar without default

Add explicit binding or default value.

## Tips

- Prefer interfaces for swappable implementations.
- Use singletons for stateless services.
- Avoid heavy logic in constructors.
