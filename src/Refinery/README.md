# Refinery

The `Refinery` library is used to unify the way input is
processed by the ILIAS project.

**Table of Contents**
- [Refinery](#refinery)
  * [General](#general)
  * [Quickstart example](#quickstart-example)
  * [Usage](#usage)
    + [Factory](#factory)
      - [Groups](#groups)
        * [to](#to)
          + [Natives](#natives)
          + [Structures](#structures)
        * [kindlyTo](#kindlyto)
          + [Natives](#natives-1)
          + [Structures](#structures-1)
        * [in](#in)
          + [series](#series)
          + [parallel](#parallel)
  * [Libraries](#libraries)
    + [Transformation](#transformation)
    + [Validation](#validation)

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

This is an example to transform a float value to a string value and
will create a data type from the result of this transformation:

```php
global $DIC;

$refinery = $DIC->refinery();

$transformation = $refinery->in()->series(
    array(
        new Refinery\KindlyTo\IntegerTransformation(),
        new Refinery\KindlyTo\StringTransformation()
    )
);

$result = $transformation->transform(5.5);

$data = $refinery->data('alphanumeric')->transform(array($result));

echo $data->getData();
```

The output will be a `string` value: `'5'` 

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
$transformation = $refinery->kindlyTo()->string();
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

##### kindlyTo

The `kindlyTo` group consists of combined validations and transformations
for native data types that establish a baseline for further constraints
and more complex transformations.

A concrete implementation for the `Refinery\KindlyTo\Group` interface
is the `Refinery\KindlyTo\BasicGroup`.

The `kindlyTo` is more tolerant version of the [to group](#to).
Transformations from the `kindlyTo` group will try to convert these e.g.
an `KindlyTo\Transformation\IntegerTransformation` will always try to transform
the input.

```php
$transformation = $refinery->kindlyTo()->int()

$result = $transformation->transform(3.5); // $result => 3;
$result = $transformation->transform('hello'); // $result => 0;
$result = $transformation->transform(10); // $result => 10
```

The above example will always try to convert the given values
with the `KindlyTo\Transformation\IntegerTransformation` into an integer.
The casting of native values into another is based on the
[PHP default behavior for type casting](http://php.net/manual/de/language.types.type-juggling.php)
In comparision with the `to` group equivalent this method tries to convert
all kinds of values.

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
        new Refinery\KindlyTo\IntegerTransformation(),
        new Refinery\KindlyTo\StringTransformation()
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
        new Refinery\KindlyTo\IntegerTransformation(),
        new Refinery\KindlyTo\StringTransformation()
    )
);

$result = $transformation->transform(5.5);
// $result => array(5, '5.5')
```

The result will be an array of results of each transformation.

In this case this is an array with an `integer` and a `string`
value.

## Libraries

These library consists of several sub-libraries,
which have their own descriptions.

### Transformation

[README for Transformation Library](/src/Refinery/Transformation/README.md)

### Validation

[README for Validation Library](/src/Refinery/Validation/README.md)
