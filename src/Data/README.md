# Datatypes for ILIAS

This service should contain standard datatypes for ILIAS that are used in many
locations in the system and do not belong to a certain service.

## Result

A result encapsulates a value or an error and simplifies the handling of those.

```php
<?php
require_once(__DIR__."/Result.php");
require_once(__DIR__."/Factory.php");

$f = new Data/Factory;

// Build a value that is ok.
$pi = $f->ok(3.1415);

// Value is ok and thus no error.
assert($pi->isOK());
assert(!$pi->isError());

// Do some transformation with the value.
$r = 10;
$A = $f->map(function($pi) use ($r) { return 2 * $pi * $r; });

// Still ok and no error.
assert($pi->isOk());
assert(!$pi->isError());

// Retrieve the contained value.
$A_value = $A->value();
assert($A_value == 2 * 3.1415 * 10);

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
$e = $f->error("There was some error...");

// This is of course an error.
assert(!$pi->isOK());
assert($pi->isError());

// Transformations are nops.
$A = $f->map($function($v) { assert(false); });

// Attempts to retrieve the value will throw.
$raised = false;
try {
	$A->value()
	assert(false); // Won't happen.	
}
catch (\ILIAS\Data\NotOKException $e) {
	$raised = true;
}
assert($raised);

// For retrieving a default could be supplied.
$v = $e->valueOr("default");
assert($v == $default);

?>
```
