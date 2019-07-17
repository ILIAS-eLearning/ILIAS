# Refinery

The `Refinery` library is used to unify the way input is
processed by the ILIAS project.

**Table of Contents**
- [General](#general)
- [Quickstart example](#quickstart-example)
- [Usage](#usage)
  * [Factory](#factory)
    + [Groups](#groups)
      - [to](#to)
        * [Natives](#natives)
        * [Structures](#structures)
      - [in](#in)
        * [series](#series)
        * [parallel](#parallel)
  * [Custom Transformation](#custom-transformation)
    + [DeriveApplyToFromTransform](#deriveapplytofromtransform)
      - [Error Handling](#error-handling)
    + [DeriveTransformFromApplyTo](#derivetransformfromapplyto)
      - [Error Handling](#error-handling-1)
- [Libraries](#libraries)
  * [Transformation](#transformation)
  * [Validation](#validation)

## General

This library contains various implementations and
interfaces to establish a way to secure input
and transform values in a secure way.

The initial concept for this library can be found
[here](/docs/documentation/input-processing.md).

These library also consists of sub-libraries,
that can be used for transformations and
validation.
Checkout the [chapter](#libraries) about these
additional libraries.

## Quickstart example

```php
global $DIC;

$refinery = $DIC->refinery();

$transformation = $refinery->in()->series(
    array(
        new Refinery\To\IntegerTransformation(),
        new Refinery\To\IntegerTransformation()
    )
);

$result = $transformation->transform(5);

$data = $refinery->data('alphanumeric')->transform(array($result));

echo $data->getData();
```

The output will be a `integer` value: `5` 

## Usage

### Factory

The factory of the refinery interface can create
an implementations of very different [groups](#groups).
These groups can be used for several validations and
transformations.

A concrete implementation of the `Refinery\Factory`
interface is `Refinery\Factory\BasicFactory`.
This implementation will create new instances of the
different [groups](#groups).
The `Refinery\Factory\BasicFactory` can also be accessed
via the `ILIAS Dependency Injection Container(DIC)`.

```php
global $DIC;

$refinery = $DIC->refinery();
$transformation = $refinery->to()->string();
// ...
```

Checkout the [examples](/src/Refinery/examples) to
see how these library can be used.

_Info: These examples are just for a show case.
These examples are non-operable from the console,
because of the missing initialization of ILIAS_

#### Groups

The different groups are used to validate and/or transform
the input given to the certain transformation.

Because of the usage of the `Transformation` interface
these groups can interact with each other and
with other implementation interfaces.
E.g. transformation from the `to` group can be used in
the `in` group and vice versa.

##### to

The `to` group consists of combined validations and transformations
for native data types that establish a baseline for further constraints
and more complex transformations.

A concrete implementation for the `Refinery\To\Group` interface
is the `Refinery\To\BasicGroup`.

To learn more about transformations checkout the
[README about Transformations](/src/Refinery/Transformation/README.md).

The transformations of this group are very strict, which means
that there are several type checks before the transformation is
executed.

```php
$transformation = $refinery->to()->int();

$result = $transformation->transform(3.5); // Will throw exception because, values is not an integer value
$result = $transformation->transform('hello'); // Will throw exception because, values is not an integer value
$result = $transformation->transform(3); // $result = 3
```

In this example the `Refinery\To\IntegerTransformation` of the `to` group is
used.
The `Refinery\To\IntegerTransformation` of this group is very strict,
so only elements of the `integer` type are allowed.
Every non-matching value will throw an exception.

To avoid exception handling the `applyTo` method can be used instead.
Find out more about the `applyTo` method of instances of the `Transformation`
interface in the
[README about Transformations](/src/Refinery/Transformation/README.md).

###### Natives

As seen in the example of the [previous chapter](#to)
there are transformations which cover the native data
types of PHP (`int`, `string`, `float` and `boolean`).

* `string()`   - Returns an object that allows to transform a value to a string value.
* `int()`      - Returns an object that allows to transform a value to a integer value.
* `float()`    - Returns an object that allows to transform a value to a float value.
* `bool()`     - Returns an object that allows to transform a value to a boolean value.

###### Structures

Beside the [native transformations](#natives) there also
transformation to create structures like `list`, `dictonary`,
`record` and `tuple`.

* `listOf()`   - Returns an object that allows to transform an value in a given array
                 with the given transformation object.
                 The transformation will be executed on every element of the array.
* `dictOf()`   - Returns an object that allows to transform an value in a given array
                 with the given transformation object.
                 The transformation will be executed on every element of the array.
* `tupleOf()`  - Returns an object that allows to transform the values of an array
                 with the given array of transformations objects.
                 The length of the array of transformations MUST be identical to the
                 array of values to transform.
                 The keys of the transformation array will be the same as the key
                 from the value array e.g. Transformation on position 2 will transform
                 value on position 2 of the value array.
* `recordOf()` - Returns an object that allows to transform the values of an
                 associative array with the given associative array of
                 transformations objects.
                 The length of the array of transformations MUST be identical to the
                 array of values to transform.
                 The keys of the transformation array will be the same as the key
                 from the value array e.g. Transformation with the key "hello" will transform
                 value with the key "hello" of the value array.
* `toNew()`    - Returns either an transformation object to create objects of an
                 existing class, with variations of constructor parameters OR returns
                 an transformation object to execute a certain method with variation of
                 parameters on the objects.
* `data()`     - Returns a data factory to create a certain data type

##### in

The `in` group is a group with a dict of `Transformations`
as parameters that define the content at the indices.

A concrete implementation for the `Refinery\In\Group` interface
is the `Refinery\In\BasicGroup`.

There are currently two different strategies supported by this group,
that are accessible by the methods:

* [series](#series)
* [parallel](#parallel)

###### series

The transformation `series` takes an array of transformations and
performs them one after another on the result of the previous transformation.

```php
$transformation = $refinery->in()->series(
    array(
        new Refinery\To\IntegerTransformation(),
        new Refinery\To\StringTransformation()
    )
);

$result = $transformation->transform(5.5);
// $result => '5'
```

The result will be the end result of the transformations that were executed
in the strict order added in the `series` method.

In this case it is a `string` with the value '5'.

###### parallel

The transformation `parallel` takes an array of transformations and
performs each on the input value to form a tuple of the results.

```php
$transformation = $refinery->in()->parallel(
    array(
        new Refinery\To\IntegerTransformation(),
        new Refinery\To\IntegerTransformation()
    )
);

$result = $transformation->transform(5);
// $result => array(5, 5)
```

The result will be an array of results of each transformation.

In this case this is an array with an `integer` and a `string`
value.

##### Custom

The `Custom` group contains of `Transformations` and `Constraints`
that can be used to create individual transformations and constraints.

##### Logical

The `Logical` group contains of `Constraints` that can be used to create
different logical operation that can be used on concrete `Constraints`-

##### Null

`Null` group contains of constraints that can be used to identify the
`null` value via a `Constraint`.

##### Numeric

`Numeric` group consists of a constraints that can be used to identify a
numeric value via a `Constraint`.

##### Password

`Password` consists of a contains that can be used to create constraints
for validating password.

### Custom Transformation

Sometimes the default transformations of this library are not enough, so a
custom transformation is needed.

As every other transformation it must implement the
`ILIAS\Refinery\To\Transformation` interface.

By default these transformation need an implementation for the 
methods `transformation` and `applyTo`.
Because these methods are always containing the same basic process
(with different results types and exception handling),
this library contains traits to ease the creation of new transformation.

The traits that can be used are:
 * [DeriveApplyToFromTransform](#deriveapplytofromtransform)
 * [DeriveTransformFromApplyTo](#derivetransformfromapplyto)

An example shows how one of the traits can be used.

```php
class BooleanTransformation implements Transformation
{
	use DeriveApplyToFromTransform;

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		if (false === is_bool($from)) {
			throw new ConstraintViolationException(
				'The value MUST be of type boolean',
				'not_boolean'
			);
		}
		return (bool) $from;
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke($from)
	{
		return $this->transform($from);
	}
}
```

In the above example we use the trait `DeriveApplyToFromTransform`
and only define the `transform` method.

Please be aware that the error handling can vary
by using these traits.
Checkout the  following chapters for more information.

#### DeriveApplyToFromTransform

This trait is used define `applyTo` on its own.
Just the `transform` method needs to be created in the new transformation class.

##### Error Handling

Exceptions thrown inside the `transformation` method will be
caught and added to new
[error result object (`Result\Error`)](/src/Data/README.md#result).

The origin exception can be accessed through this error object.

#### DeriveTransformFromApplyTo

This trait is used define `transform` on its own.
Just the `applyTo` method needs to be created in the new transformation class.

##### Error Handling

Exceptions thrown inside the `applyTo` method will be
**not be** caught.
On return of an [error result object (`Result\Error`)](/src/Data/README.md#result)
the `transform` method will throw an exception.

* If the content of the error object is an exception the exception will be
  thrown.
* If the content of the error object is an string this string will be added
  to a an `Exception` which will be thrown.

## Libraries

These library consists of several sub-libraries,
which have their own descriptions.

### Transformation

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

$f = new \ILIAS\Refinery\Factory;

// Adding labels to an array to name the elements.
$add_abc_label = $f->container()->addLabels(["a", "b", "c"]);
$labeled = $add_abc_label->transform([1,2,3]);
assert($labeled === ["a" => 1, "b" => 2, "c" => 3]);

// Split a string at some delimiter.
$split_string_at_dot = $f->string()->splitString(".");
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
### Validation

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
$f = new ILIAS\Refinery\Factory;

// Build some basic constraints
$gt0 = $f->integer()->greaterThan(0);
$lt10 = $f->integer()->lessThan(10);

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
$between_0_10 = $f->logical()->sequential([$gt0, $lt10]);

// Or in a way that they are checked independently:
$also_between_0_10 = $f->logical()->parallel([$gt0, $lt10]);

// One can also create a new error message by supplying a builder for an error
// message:

$between_0_10->withProblemBuilder(function($txt, $value) {
	return "Value must be between 0 and 10, but is '$value'.";
});

// To perform internationalisation, the provided $txt could be used, please
// see `ILIAS\Refinery\Validation\Constraint::withProblemBuilder` for further information.

```
