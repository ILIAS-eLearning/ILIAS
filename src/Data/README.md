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

### Example 1: Ok

```php
<?php

$f = new \ILIAS\Data\Factory;

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

?>
```

### Example 2: Error

```php
<?php

$f = new \ILIAS\Data\Factory;

// Build a value that is ok.
$pi = $f->ok(3.1416);

// Build a value that is not ok.
$error = $f->error("There was some error...");

// This is of course an error.
assert(!$error->isOK());
assert($error->isError());

// Transformations do nothing.
$A = $error->map(function($v) { assert(false); });

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
$v = $error->valueOr("default");
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

## Color
Color is a data type representing a color in HTML.
Construct a color with a hex-value or list of RGB-values.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

//construct color with rgb-values:
$rgb = $f->color(array(255,255,0));

//construct color with hex-value:
$hex = $f->color('#ffff00');

assert($rgb->asHex() === '#ffff00');
assert($hex->asRGBString() === 'rgb(255, 255, 0)');
?>
```

## URI
Object representing an uri valid according to RFC 3986 with restrictions imposed on valid characters and obliagtory parts.
Construct a uri with a valid string.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct uri
$uri = $f->uri('https://example.org:12345/test?search=test#frag');

assert($uri->getBaseURI() === 'https://example.org:12345/test');
assert($uri->getSchema() === 'https');
assert($uri->getAuthority() === 'example.org:12345');
assert($uri->getHost() === 'example.org');
assert($uri->getPath() === 'test');
assert($uri->getQuery() === 'search=test');
assert($uri->getFragment() === 'frag');
assert($uri->getPort() === 12345);
assert($uri->getParameters() === ['search' => 'test']);
assert($uri->getParameter('search') === 'test');
?>
```

## DataSize
Object representing the size of some data.
Construct a data size object with a size and an unit.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct data size
$data_size = $f->dataSize(123, 'GB');

assert($data_size->getSize() === 123.0);
assert($data_size->getUnit() === 1000000000);
assert($data_size->inBytes() === 123000000000);
?>
```

## Password
Object representing a password.
Construct a password with a string.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct password
$password = $f->Password('secret');

assert($password->toString() === 'secret');
?>
```

## ClientId
Object representing a a alphanummeric string plus #, _, . and -.
Construct a client with a valid string.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct client id
$client_id = $f->clientId('Client_Id-With.Special#Chars');

assert($client_id->toString() === 'Client_Id-With.Special#Chars');
?>
```

## ReferenceId
ReferenceId is a data type representing an integer.
Construct a reference id with an integer.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct reference id
$ref_id = $f->refId(9);

assert($ref_id->toInt() === 9);
?>
```

## ObjectId
ObjectId is a data type representing an integer.
Construct an object id with an integer.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct object id
$ref_id = $f->objId(9);

assert($ref_id->toInt() === 9);
?>
```

## Alphanumeric
Alphanumeric is a data type representing an alphanumeric value.
Construct an alphanumeric with an integer and an alphanumeric value.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct alphanumric with integers
$numeric = $f->alphanumeric(963);

// construct alphanumeric with mixed values as string
$alphanumeric = $f->alphanumeric('23da33');

assert($numeric->getValue() === 963);
assert($alphanumeric->getValue() === '23da33');
?>
```

## PositiveInteger
PositiveInteger is a data type representing an positive integer.
Construct an positive integer with an integer.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct a positive integer
$positive_integer = $f->positiveInteger(963);

assert($positive_integer->getValue() === 963);
?>
```


## DateFormat
DateFormat is a data type representing a dateformat.
Construct a date format representing a standard, german_short, german_long or custom date format.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct standard date format
$standard = $f->dateFormat()->standard();

// construct german short date format
$german_short = $f->dateFormat()->germanShort();

// construct german long date format
$german_long = $f->dateFormat()->germanLong();

// construct custom date format
$custom = $f->dateFormat()->custom()->twoDigitYear()->dash()->month()->dash()->day()->get();

assert($standard->toString() === "Y-m-d");
assert($standard->toArray() === ['Y', '-', 'm', '-', 'd']);

assert($german_short->toString() === "d.m.Y");
assert($german_short->toArray() === ['d', '.', 'm', '.', 'Y']);

assert($german_long->toString() === "l, d.m.Y");
assert($german_long->toArray() === ['l', ',', ' ', 'd', '.', 'm', '.', 'Y']);

assert($custom->toString() === "y-m-d");
assert($custom->toArray() === ['y', '-', 'm', '-', 'd']);
?>
```

## Range
Range is a data type representing a naive range of whole positive numbers.
Construct an range with a start integer and a length integer.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct a range
$range = $f->range(10, 20);

assert($range->unpack() === [10, 20]);
assert($range->getStart() === 10);
assert($range->getLength() === 20);
assert($range->getEnd() === 30);
?>
```

## Order
Order is a data type representing a subject with a specific order.
Construct an order with a subject and a direction.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct a order
$order1 = $f->order('subject1', 'ASC');

// append subject to order
$order2 = $order1->append('subject2', 'DESC');

// join the subjects to an order statement
$join = $order2->join('sort', function($pre, $k, $v) { return "$pre $k $v,"; });

assert($order1->get() === ['subject1' => 'ASC']);
assert($order2->get() === ['subject1' => 'ASC', 'subject2' => 'DESC']);
assert($join === 'sort subject1 ASC, subject2 DESC,');
?>
```
