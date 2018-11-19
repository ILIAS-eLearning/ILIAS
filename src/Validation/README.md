# Validations

This service abstracts validations of values and provides some basic validations
that could be reused throughout the system.

A validation checks some supplied value for compliance with some constraints.
Validations MUST NOT modify the supplied value.

Having an interface to Validations allows to typehint on them and allows them
to be combined in structured ways. Understanding validation as a separate service
in the system with objects performing the validations makes it possible to build
a set of common validations used throughout the system. Having a known set of
validations makes it possible to perform at least some of the validations on
client side someday.

```php

// In reality this has dependencies that need to be satisfied...
$f = new ILIAS\Validation\Factory;

// Build some basic constraints
$gt0 = $f->greaterThan(0);
$lt10 = $f->lessThan(10);

// Check them and react:
if (!$gt0->accepts(1)) {
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

// Combine them in a way that the constraints are checked one after another:
$between_0_10 = $f->seq([$gt0, $lt10]);

// Or in a way that they are checked independently:
$also_between_0_10 = $f->par([$gt0, $lt10]);

// One can also create a new error message by supplying a builder for an error
// message:

$between_0_10->withProblemBuilder(function($txt, $value) {
	return "Value must be between 0 and 10, but is '$value'.";
});

// To perform internationalisation, the provided $txt could be used, please
// see `ILIAS\Validation\Constraint::withProblemBuilder` for further information.

```
