# Validations

This service abstracts validations of values and provides some basic validations
that could be reused throughout the system.

A validation checks some supplied value for compliance with some restriction.
Validations MUST NOT modify the supplied value.

Having an interface to Validations allows to typehint on them and allows them
to be combined in structured ways. Understanding validation as a separate service
in the system with objects performing the validations makes it possible to build
a set of common validations used throughout the system. Having a known set of
validations makes it possible to perform at least some of the validations on
client side someday.

```php

require_once(__DIR__."/Factory.php");

$f = new Validation/Factory;

// Build some basic restrictions
$gt0 = $f->greaterThan(0);
$lt10 = $f->lessThan(10);

// Check them and react:
if (!$gt0->appliesTo(1)) {
	assert(false); // does not happen
}

// Let them throw an exception:
$raised = false;
try {
	$lt10->check(20);
	assert(false); // does not happen
}
catch (\UnexpectedValueException $e) {
	$raised = true;
}
assert($raised);

// Get to know what the problem with some value is:
assert(is_string($gt0->problemWith(-10)));

// Combine them in a way that the restrictions are checked one after another:
$between_0_10 = $f->seq([$gt0, $lt10]);

// Or in a way that they are checked independently:
$also_between_0_10 = $f->par([$gt0, $lt10]);
```
