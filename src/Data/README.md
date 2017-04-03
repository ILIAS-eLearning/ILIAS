# Datatypes for ILIAS

This service should contain standard datatypes for ILIAS that are used in many
locations in the system and do not belong to a certain service.

## Result

Result encapsulate a value with the possibility of failure.

```php

$f = new Data/Factory;

$pi = $f->ok(3.1415);
```
