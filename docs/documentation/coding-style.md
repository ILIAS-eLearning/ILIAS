# ILIAS Coding Standard
This coding standard is for the [ILIAS project](https://github.com/ILIAS-eLearning/ILIAS)


**Table of Contents**
* [PSR-2](#psr-2)
* [Additions](#additions)
  * [Indention](#indention)
  * [Exceptions](#exceptions)
  * [Abstract](#abstract)
  * [Interfaces](#interfaces)
  * [Operators](#operators)
  * [Type cast](#type-cast)
  * [Return Type Declaration](#return-type-declaration)
  * [Strings](#strings)
  * [Comparison](#comparison)
  * [Comments](#comments)
  * [Early return](#early-return)
  * [Native Types](#native-types)
  * [Conditions](#conditions)
  * [Arrays](#arrays)

# PSR-2 

The ILIAS coding standard is aligned to the widely used and established
[PSR-2 standard](https://www.php-fig.org/psr/psr-2/)
of the [PHP Interop Group (PHP-FIG)](https://www.php-fig.org/).

_Info: Please be aware of the [additions](#additions)
defined for the ILIAS project that MUST be used on top of PSR-2_

# Additions

This document is an addition to PSR-2 Standard used in the ILIAS project.
This section was inspired by the
[Doctrine Coding Standards](https://github.com/doctrine/coding-standard).

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this document are to be
interpreted as described in [RFC 2119](https://tools.ietf.org/html/rfc2119).

## Indention

Divergent from the PSR-2 indentation, the code
MUST be indent by tabs and NOT spaces.

## Exceptions

Abstract exception class names and exception interface names SHOULD be
suffixed with `Exception`.

## Abstract

Abstract classes SHOULD NOT be prefixed or suffixed with Abstract.

## Interfaces

Interfaces SHOULD NOT be prefixed or suffixed with Interface.

## Operators

Operators MUST have a space before and after the operator.

Mathematical Operators:

```php
$result = 1 + $value;
```

Comparsion Operators:

```php
if ($size > 0) {
    // ...
}
```

Concatenation Operator:

```php
$test = 'hello ' . $name . '!';
```

## Type cast

Spaces MUST be added after a type cast.

```php
$foo = (int) '12345';
```

## Return Type Declaration

Spaces MUST be added around a colon in return type declaration.

```php
function () : void {}
```

## Strings

Strings MUST use single quotes(apostrophes) for enclosing tags.
Double quotes MUST NOT be used for enclosing tags.

```php
$text = 'hello world!';
```

## Comparison

Strict comparisons MUST be used.

```php
if (3 === $value) {
   // ...
}
```
## Comments
`@author`, `@since` and similar annotations that duplicate Git information MUST NOT be used.
phpDoc for parameters/returns with native types SHOULD BE omitted, unless adding description.

## Early return

Prefer early exit/return over nesting conditions or using else.

## Native Types

Native types MUST be used where possible.

## Conditions

Assignment in condition MUST NOT be used.

## Arrays

An array that consisting of maximum `3` elements
MAY be written in one line.

```php
$fruits = array('apple', 'banana', 'kiwi');
```

If the elements within the array exceeds 80
characters the elements MUST be separated
to an array with 1 element per line.
The single elements of such arrays MUST be indented.

Arrays that consist of more than `3` elements
MUST be separated to an array with `1` element
per line.
The single elements of such arrays MUST be indented.

```php
$fruits = array(
    'apple',
    'banana',
    'kiwi',
    'ananas',
    'plum'
);
```
