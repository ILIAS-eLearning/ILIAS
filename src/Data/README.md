# Datatypes for ILIAS

This service should contain standard datatypes for ILIAS that are used in many
locations in the system and do not belong to a certain service.

Other examples for data types that could (and maybe should) be added here:

* Option (akin to rusts type)
* (il)Datetime
* ObjectId, ReferenceId
* HTML, Text, Markdown
* List<int>, List<bool>, ...

This is not to be confused with the service for types. This services is about
the data, not the types thereof. It still uses types to talk about data (like
a lot of code does), but it does not reify types as data (which the service
for types and the PHP-ReflectionClass does).

## Result

A result encapsulates a value or an error and simplifies the handling of those.

```php
<?php
use ILIAS\Data;

$f = new Data\Factory;

// Build a value that is ok.
$pi = $f->ok(3.1416);

// Value is ok and thus no error.
assert($pi->isOK());
assert(!$pi->isError());

// Do some transformation with the value.
$r = 10;
$A = $pi->map(function($value_of_pi) use ($r) { return 2 * $value_of_pi * $r; });

// Still ok and no error.
assert($A->isOk());
assert(!$A->isError());

// Retrieve the contained value.
$A_value = $A->value();
assert($A_value == 2 * 3.1416 * 10);

// No error contained...
$raised = false;
try {
	$A->error();
	assert(false); // Won't happen, error raises.
}
catch(\LogicException $e) {
	$raised = true;
}
assert($raised);


// Build a value that is not ok.
$r_error = $f->error("There was some error...");

// This is of course an error.
assert(!$r_error->isOK());
assert($r_error->isError());

// Transformations are nops.
$A = $r_error->map(function($v) { assert(false); });

// Attempts to retrieve the value will throw.
$raised = false;
try {
	$A->value();
	assert(false); // Won't happen.	
}
catch (\ILIAS\Data\NotOKException $e) {
	$raised = true;
}
assert($raised);

// For retrieving a default could be supplied.
$v = $r_error->valueOr("default");
assert($v == "default");

// Result also has an interface for chaining computations known as promise
// interface (or monad interface for pros!).

$pi = $pi->then(function($value_of_pi) use ($f) {
	// replace contained value with a more accurate number.
	return $f->ok(3.1415927);
});

// $pi is ok("3.1415927") now. If one had used map instead of then, $pi
// would have been ok(ok(3.1415927).

// One could also inject an error with then, this is not possible with map.
$pi = $pi->then(function($_) use ($f) {
	return $f->error("Do not know value of Pi.");
});

// The error can be catched later on and be corrected:
$pi = $pi->except(function($e) use ($f) {
	assert($e === "Do not know value of Pi.");
	return $f->ok(3); // for large threes
});

assert($pi->value() === 3);

?>
```
