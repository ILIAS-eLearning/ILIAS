 # Transformations

This service abstracts transformations between datatypes and provides some basic
transformations to be reused throughout the system.

A transformation is a function from one datatype to another. It MUST NOT perform
any sideeffects, i.e. it must be morally impossible to observe how often the
transformation was actually performed. It MUST NOT touch the provided value, i.e.
it is allowed to create new values but not to modify existing values. This would
be an observable sideeffect.

The actual usage of this interface is quite boring, but we could typehint on
`Transformation` to announce we indeed want some function having the aforementioned
properties.

```php

require_once(__DIR__."\Factory.php");

$f = new \ILIAS\Transformation\Factory;

$int_to_string = $f->custom(function ($v) {
	if (!is_int($v)) {
		throw new \InvalidArgumentException("Expected int, got ".get_type($v));
	}
	return "$v";
});

$str = $int_to_string->transform(5);
assert($str === "5");
assert($str !== 5);
```
