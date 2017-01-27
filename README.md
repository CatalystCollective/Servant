# Servant
Catalyst Servant - Dependency Inhibitor Library

### What is Servant ?

Servant is a dependency inhibitor based on a chain of responsibility. Servant
provides the ability to resolve a given dependency based on a class name or a
`ReflectionParameter`-Instance.

### Usage

```php
use Catalyst\Servant\{
    RepositoryServant,
    BlindServant
};

$servant = new RepositoryServant();
$servant->chain(new BlindServant());
$servant->ensure(DateTimeInterface::class, function() {
    return date_create();
});

$dateTime = $servant->resolve(DateTimeInterface::class);
```

### Boxed Servants

This package serves the following `ServantInterface`-Implementations:

- `RepositoryServant` - A repository based servant that allows to assign
  aliases and interfaces to concretes.
- `BlindServant` - A blind servant that allows the instancing of objects
  out of the blue.
- `NullServant` - A null servant that returns null and acts as an end point
  to guarantee a null-result when no dependency resolver was successful.
  
### License and Maintainer(s)

This package is licensed under the MIT license. This package is actively
maintained by:

- Matthias Kaschubowski