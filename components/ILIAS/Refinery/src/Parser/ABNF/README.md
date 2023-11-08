# ABNF Parser

This service provides an API to create parser definitions with an ABNF like syntax.

## Usage

To use the ABNF parser the class `Brick` must be used.

See [ABNF methods](#abnf-methods) for all implemented methods that directly correspond to an ABNF operator.

## Brick

This class can be used to write a parser definition.

To keep this class as small as possible, please consider extracting parts of this class into new classes when adding / modifying code.

See [Parser internals](#parser-internals) for more information.

### Basic Example

This example shows how ABNF code can be translated to an equivalent syntax with this API.

Please note that this example does not create a parser but validates only. See Section [Parser Example](#parser-example) for a full and useful example.

ABNF code (taken from https://datatracker.ietf.org/doc/html/rfc3339):

```
date-fullyear   = 4DIGIT
date-month      = 2DIGIT  ; 01-12
date-mday       = 2DIGIT  ; 01-28, 01-29, 01-30, 01-31 based on
                          ; month/year
time-hour       = 2DIGIT  ; 00-23
time-minute     = 2DIGIT  ; 00-59
time-second     = 2DIGIT  ; 00-58, 00-59, 00-60 based on leap second
                          ; rules
time-secfrac    = "." 1*DIGIT
time-numoffset  = ("+" / "-") time-hour ":" time-minute
time-offset     = "Z" / time-numoffset

partial-time    = time-hour ":" time-minute ":" time-second
                  [time-secfrac]
full-date       = date-fullyear "-" date-month "-" date-mday
full-time       = partial-time time-offset

date-time       = full-date "T" full-time
```

```php
use ILIAS\Refinery\Parser\ABNF\Brick;
use ILIAS\Refinery\Parser\ABNF\Intermediate;

$brick = new Brick();

$two_digits = $brick->repeat(2, 2, $brick->digit());

$date_fullyear   = $brick->repeat(4, 4, $brick->digit());
$date_month      = $two_digits;
$date_mday       = $two_digits;

$time_hour       = $two_digits;
$time_minute     = $two_digits;
$time_second     = $two_digits;

$time_secfrac    = $brick->sequence(['.', $brick->repeat(1, null, $brick->digit())]);
$time_numoffset  = $brick->sequence([$brick->either(['+', '-']), $time_hour, ':', $time_minute]);
$time_offset     = $brick->either(['Z', $time_numoffset]);

$partial_time    = $brick->sequence([$time_hour, ':', $time_minute, ':', $time_second, $brick->repeat(0, 1, $time_secfrac)]);

$full_date       = $brick->sequence([$date_fullyear, '-', $date_month, '-', $date_mday]);
$full_time       = $brick->sequence([$partial_time, $time_offset]);

$date_time       = $brick->sequence([$full_date, 'T', $full_time]);

$result = $brick->apply($date_time, '2000-06-30T23:59:60Z');
```

## Parsing

The previous example mirrors the ABNF syntax but doesn't parse any input (The structure is not touched).

To extract the structure from a definition, this API provides two ways to achieve this:

### Transformation

The method `Brick::transformation(Transformation, Parser)` is used to create user defined values from a given parser definition.

#### Example

```php
$brick = new Brick();
global $DIC;

$parser = $brick->sequence(['foo', 'bar']);

$new_parser = $brick->transformation($DIC->refinery()->custom(function ($parsed) {
    // $parsed === 'foobar'.
    return 'My own value.'; // You can return anything here, it doesn't need to be a string.
}), $parser);

$brick->apply($new_parser, 'foobar'); // => new Ok('My own value.')
```

### Array keys with sequence and either

Except from the functionality described by the ABNF definition of concatenation and alternatives, one can provide array keys, to give the parser a name in `Brick::transformation(Transformation, Parser)`.

#### Example

```php
$brick = new Brick();
global $DIC;

$branch = $brick->either([
    'number' => $brick->digit(),
    'alpha' => $brick->alpha(),
]);

$parser = $brick->sequence([
    'first' => $branch,
    'second' => 'bar',
]);

$new_parser = $brick->transformation($DIC->refinery()->custom(function ($parsed) {
    // In the case of Brick::either, only the taken path is available so: if isset($parsed['first']['numner']) then the 'numner' branch was taken.
    // is_array($parsed) === true && $parsed['first']['number'] === '7' && $parsed['second'] === 'bar'.
    return ['baz' => $parsed];
}), $parser);

$brick->apply($new_parser, '7bar'); // => new Ok(['baz' => ['first' => ['number' => '7'], 'second' => 'bar']])
$brick->apply($new_parser, 'xbar'); // => new Ok(['baz' => ['first' => ['alpha' => 'x'], 'second' => 'bar']])
```

### Parser Example

```php
use ILIAS\Refinery\Parser\ABNF\Brick;

class Time
{
    public function __construct(
        private string $hour,
        private string $minutes,
        private string $seconds
    ) {
    }
}

class Date
{
    public function __construct(
        private string $year,
        private string $month,
        private string $day
    ) {
    }
}

class DateAndTime
{
    public function __construct(
        private Date $date,
        private Time $time
    ) {
    }
}

$brick = new Brick();
global $DIC;
$custom = [$DIC->refinery()->custom(), 'transformation'];

$two_digits = $brick->repeat(2, 2, $brick->digit());

$date_fullyear   = $brick->repeat(4, 4, $brick->digit());
$date_month      = $two_digits;
$date_mday       = $two_digits;

$time_hour       = $two_digits;
$time_minute     = $two_digits;
$time_second     = $two_digits;

$time_secfrac    = $brick->sequence(['.', $brick->repeat(1, null, $brick->digit())]);
$time_numoffset  = $brick->sequence([$brick->either(['+', '-']), $time_hour, ':', $time_minute]);
$time_offset     = $brick->either(['Z', $time_numoffset]);

$partial_time    = $brick->sequence(['hour' => $time_hour, ':', 'minutes' => $time_minute, ':', 'seconds' => $time_second, $brick->repeat(0, 1, $time_secfrac)]);

$full_date       = $brick->sequence(['year' => $date_fullyear, '-', 'month' => $date_month, '-', 'day' => $date_mday]);
$full_date       = $brick->transformation($custom(fn ($from) => new Date($from['year'], $from['month'], $from['day'])), $full_date);

$full_time       = $brick->sequence([$partial_time, $time_offset]);
$full_time       = $brick->transformation($custom(fn ($from) => new Time($from['hour'], $from['minutes'], $from['seconds'])), $full_time);

$date_time       = $brick->sequence(['date' => $full_date, 'T', 'time' => $full_time]);
$date_time       = $brick->transformation($custom(fn ($from) => new DateAndTime($from['date'], $from['time'])), $date_time);

$result = $brick->apply($date_time, '2000-06-30T23:59:60Z');
```

## ABNF methods

### Brick::range

ABNF equivalent to value ranges:

`fu = %x30-37` is equivalent to `$fu = $this->range(0x30, 0x37)`.

### Brick::alpha

ABNF equivalent to ALPHA:

`fu = ALPHA` is equivalent to `$fu = $this->alpha()`.

### Brick::digit

ABNF equivalent to DIGIT:

`fu = DIGIT` is equivalent to `$fu = $this->digit()`.

### Brick::either

ABNF equivalent to alternative:

`fu = a / b` is equivalent to `$fu = $this->either([a, b])`.

You can use `Brick::either(['foo', 'bar']);` to match a string directly.

See [Array keys with sequence and either](#array-keys-with-sequence-and-either) for additional information features beyond the ABNF equivalence.

### Brick::sequence

ABNF equivalent to concatenation:

`fu = a b` is equivalent to `$fu = $this->sequence([a, b])`.

You can use `Brick::sequence(['foo']);` to match a string directly.

See [Array keys with sequence and either](#array-keys-with-sequence-and-either) for additional information features beyond the ABNF equivalence.

### Brick::repeat

ABNF equivalent to variable repetition:

`fu = <a>*<b>element` is equivalent to `$fu = $this->repeat(<a>, <b>, element)`.

`fu = *<b>element` is equivalent to `$fu = $this->repeat(0, <b>, element)`.

`fu = 1*element` is equivalent to `$fu = $this->repeat(1, null, element)`.

`fu = *element` is equivalent to `$fu = $this->repeat(0, null, element)`.

`fu = [element]` is equivalent to `0*1element and therefore equivalent to $fu = $this->repeat(0, 1, element)`.

You can use `Brick::repeat(0, 1 'foo');` to match a string directly.

## Parser internals

The parser is an implementation of a parser compinator.

To manipulate the execution of a parser chain [continuations](https://en.wikipedia.org/wiki/Call-with-current-continuation) or [CPS](https://en.wikipedia.org/wiki/Continuation-passing_style) are used.

Type of Parser: (Intermediate, Continuation): Result

Type of Continuation: (Result): Result

Only immutable values are used.

`$cc` stands for _current continuation_.

This is used to be able to capture the next computation and run it again with different input in case of an error.

The `$cc` is used for example in the `either` method to rerun the computation chain with the next branch if the current failed.

`$x` holds the _state_. Everything that is parsed, the current value and the data that still needs to be parsed (Either `Result<Intermediate>` or `Intermediate`)

To restart the whole computation (on error):

```php
$parser = function ($x, $cc) {
    return some_parser($x, $cc)->except(fn () => parse_something_else($x, $cc));
};
```

To get the result of the parsers "children" (in case of `either` these would be the branches):

```php
$parser = function (Intermediate $x, Closure $cc): Result {
    return some_parser($x, fn($x): Result => (
        $x->then(fn(Intermediate $x): Result => ( // $x is the successful value.
            $cc(new Ok($x))
        ))->except(fn($error) => (
            $cc(new Error($error)) // This is necessary because the $cc MUST always be called when returning a value.
        ))
    ));
};
```

To consume a value:

```php
$parser = function (Intermediate $x, Closure $cc): Result {
    return $cc(some_predicate($x->value()) ? $x->accept() : $x->reject());
};
```

The class `Primitives` is used for the basic composition and consumtion of the parser compinator basics.

The class `Transform` does the actual transformations from the origin values to compound values.

The class `Brick` bundles both classes together into a useful API.

