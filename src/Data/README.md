# Datatypes for ILIAS

This service should contain standard datatypes for ILIAS that are used in many
locations in the system and do not belong to a certain service.

The keywords “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”,
“SHOULD NOT”, “RECOMMENDED”, “MAY”, and “OPTIONAL” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [Result](#result)
* [Color](#color)
* [URI](#uri)
* [DataSize](#datasize)
* [Password](#password)
* [ClientId](#clientid)
* [ReferenceId](#referenceid)
* [ObjectId](#objectid)
* [Alphanumeric](#alphanumeric)
* [PositiveInteger](#positiveinteger)
* [DateFormat](#dateformat)
* [Range](#Range)
* [Order](#order)
* [Clock](#clock)
* [Dimension](#dimension)
* [Dataset](#dataset)

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
itIsTrueThat($pi->isOK());
itIsTrueThat(!$pi->isError());

// Do some transformation with the value.
$r = 10;
$A = $pi->map(function($value_of_pi) use ($r) { return 2 * $value_of_pi * $r; });

// Still ok and no error.
itIsTrueThat($A->isOk());
itIsTrueThat(!$A->isError());

// Retrieve the contained value.
$A_value = $A->value();
itIsTrueThat($A_value == 2 * 3.1416 * 10);

// No error contained...
$raised = false;
try {
	$A->error();
	itIsTrueThat(false); // Won't happen, error raises.
}
catch(\LogicException $e) {
	$raised = true;
}
itIsTrueThat($raised);

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
itIsTrueThat(!$error->isOK());
itIsTrueThat($error->isError());

// Transformations do nothing.
$A = $error->map(function($v) { itIsTrueThat(false); });

// Attempts to retrieve the value will throw.
$raised = false;
try {
	$A->value();
	itIsTrueThat(false); // Won't happen.
}
catch (\ILIAS\Data\NotOKException $e) {
	$raised = true;
}
itIsTrueThat($raised);

// For retrieving a default could be supplied.
$v = $error->valueOr("default");
itIsTrueThat($v == "default");

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
	itIsTrueThat($e === "Do not know value of Pi.");
	return $f->ok(3); // for large threes
});

itIsTrueThat($pi->value() === 3);

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

itIsTrueThat($rgb->asHex() === '#ffff00');
itIsTrueThat($hex->asRGBString() === 'rgb(255, 255, 0)');
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

itIsTrueThat($uri->getBaseURI() === 'https://example.org:12345/test');
itIsTrueThat($uri->getSchema() === 'https');
itIsTrueThat($uri->getAuthority() === 'example.org:12345');
itIsTrueThat($uri->getHost() === 'example.org');
itIsTrueThat($uri->getPath() === 'test');
itIsTrueThat($uri->getQuery() === 'search=test');
itIsTrueThat($uri->getFragment() === 'frag');
itIsTrueThat($uri->getPort() === 12345);
itIsTrueThat($uri->getParameters() === ['search' => 'test']);
itIsTrueThat($uri->getParameter('search') === 'test');
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

itIsTrueThat($data_size->getSize() === 123.0);
itIsTrueThat($data_size->getUnit() === 1000000000);
itIsTrueThat($data_size->inBytes() === 123000000000.0);
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

itIsTrueThat($password->toString() === 'secret');
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

itIsTrueThat($client_id->toString() === 'Client_Id-With.Special#Chars');
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

itIsTrueThat($ref_id->toInt() === 9);
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

itIsTrueThat($ref_id->toInt() === 9);
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

itIsTrueThat($numeric->getValue() === 963);
itIsTrueThat($alphanumeric->getValue() === '23da33');
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

itIsTrueThat($positive_integer->getValue() === 963);
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

itIsTrueThat($standard->toString() === "Y-m-d");
itIsTrueThat($standard->toArray() === ['Y', '-', 'm', '-', 'd']);

itIsTrueThat($german_short->toString() === "d.m.Y");
itIsTrueThat($german_short->toArray() === ['d', '.', 'm', '.', 'Y']);

itIsTrueThat($german_long->toString() === "l, d.m.Y");
itIsTrueThat($german_long->toArray() === ['l', ',', ' ', 'd', '.', 'm', '.', 'Y']);

itIsTrueThat($custom->toString() === "y-m-d");
itIsTrueThat($custom->toArray() === ['y', '-', 'm', '-', 'd']);
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

itIsTrueThat($range->unpack() === [10, 20]);
itIsTrueThat($range->getStart() === 10);
itIsTrueThat($range->getLength() === 20);
itIsTrueThat($range->getEnd() === 30);
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

itIsTrueThat($order1->get() === ['subject1' => 'ASC']);
itIsTrueThat($order2->get() === ['subject1' => 'ASC', 'subject2' => 'DESC']);
itIsTrueThat($join === 'sort subject1 ASC, subject2 DESC,');
?>
```

## Clock

This package provides a fully psr-20 compliant clock handling.

### Example

#### System Clock

The `\ILIAS\Data\Clock\SystemClock` returns a `\DateTimeImmutable` instance always referring to the
current default system timezone.

```php
<?php
$f = new \ILIAS\Data\Factory;

$clock = $f->clock()->system();
$now = $clock->now();
?>
```

#### UTC Clock

The `\ILIAS\Data\Clock\UtcClock` returns a `\DateTimeImmutable` instance always referring to the
`UTC` timezone.

```php
<?php
$f = new \ILIAS\Data\Factory;

$clock = $f->clock()->utc();
$now = $clock->now();
?>
```

#### Local Clock

The `\ILIAS\Data\Clock\UtcClock` returns a `\DateTimeImmutable` instance always referring to the
timezone passed to the factory method.

```php
<?php
$f = new \ILIAS\Data\Factory;

$clock = $f->clock()->local(new \DateTimeZone('Europe/Berlin'));
$now = $clock->now();
?>
```

## Dimension

### CardinalDimension
Object representing a metric order, where the distances of the categories are known
and can be described quantitatively.
Construct a cardinal dimension object with numerical or textual variables representing 
the categories.

#### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct dimension
$cardinal = $f->dimension()->cardinal(["low", "medium", "high"]);

itIsTrueThat($cardinal->getLabels() === ["low", "medium", "high"]);
?>
```

### RangeDimension
Object representing a range on a cardinal dimension.
Construct a range dimension object with an existing cardinal dimension.

#### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct dimensions
$cardinal = $f->dimension()->cardinal(["low", "medium", "high"]);
$range = $f->dimension()->range($cardinal);

itIsTrueThat($range->getLabels() === $cardinal->getLabels());
?>
```

## Dataset
Object representing a dataset for one or more dimensions.
Construct a dataset with an amount of named dimensions.
Extend a dataset with one or more items by determining e.g. points for each
dimension of the dataset.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

// construct dimensions and dataset
$cardinal = $f->dimension()->cardinal([
    0 => "very low",
    1 => "low",
    2 => "medium",
    3 => "high",
    4 => "very high"
]);
$range = $f->dimension()->range($cardinal);
$dataset = $f->dataset([
    "Measurement 1" => $cardinal,
    "Measurement 2" => $cardinal,
    "Target" => $range
]);
$dataset = $dataset->withPoint(
    "Item 1",
    [
        "Measurement 1" => 1,
        "Measurement 2" => 0,
        "Target" => [0, 1.5],
    ]
);
$dataset = $dataset->withPoint(
    "Item 2",
    [
        "Measurement 1" => -1,
        "Measurement 2" => 1.75,
        "Target" => [0.95, 1.05],
    ]
);

itIsTrueThat($dataset->getMinValueForDimension("Measurement 1") === -1.0);
itIsTrueThat($dataset->getMaxValueForDimension("Target") === 1.5);
?>
```

## Helper

To make this run, we need a little helper:

```php
<?php

function itIsTrueThat(bool $truth) {
    if (!$truth) {
        throw new \LogicException("Some code in the Data/README.md is wrong!");
    }
}

?>
```

## HTML metadata

will follow.

### Example

```php
<?php

$f = new \ILIAS\Data\Factory;

$html_metadata = $f->htmlMetadata()->userDefined('viewport', 'widht=device-width');

$og_html_metadata = $f->htmlMetadata()->openGraph()->website(
    'ILIAS',
    $f->uri('https://docu.ilias.de'),
    'hello world!'
);
?>
```
