 # Transformations

This service abstracts transformations between types and structure of data and
provides some basic transformations to be reused throughout the system. These
transformations SHOULD be added to the Factory in this library.

A transformation is a function from one type or structure of data to another.
It MUST NOT perform any sideeffects, i.e. it must be morally impossible to observe
how often the transformation was actually performed. It MUST NOT touch the provided
value, i.e. it is allowed to create new values but not to modify existing values.
This would be an observable sideeffect.

The actual usage of this interface is quite boring, but we could typehint on
`Transformation` to announce we indeed want some function having the aforementioned
properties. Typehinting on `Transformation` will be useful when code talks about
structures containing data in some sense, e.g. lists or trees, where the code is
involved with containing structure but not with the contained data. This would be
a classic case for generics in languages that support them. PHP unfortunately is
a language that does not support generics.

The use case that actually led to the proposal of this library is the forms 
abstraction in the UI framework, where the abstraction deals with forms, extraction
of data from them and validation of data in them. The concept of transformation
is required, not matter if we typehint on them or not. Other facilities in PHP
do not allow a more accurate typehinting, due to lack of generics.

Having common transformations ready in a factory, connected with the promise
given by the developer that the `Transformation` indeed respects the intended
properties, should be useful in other scenarios as well, especially at the
boundaries of the system, where data needs to be re- and destructured to fit
interfaces to other systems or even users.

```php

require_once(__DIR__."\Factory.php");

$f = new \ILIAS\Transformation\Factory;

// Adding labels to an array to name the elements.
$add_abc_label = $f->addLabels(["a", "b", "c"]);
$labeled = $add_abc_label->transform([1,2,3]);
assert($labeled === ["a" => 1, "b" => 2, "c" => 3]);

// Split a string at some delimiter.
$split_string_at_dot = $f->splitString(".");
$split = $split_string_at_dot->transform("a.b.c");
assert($split === ["a", "b", "c"]);

// Use a closure for the transformation.
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
