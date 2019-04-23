# ILIAS Coding Style

This is the coding style for the [ILIAS project](https://github.com/ILIAS-eLearning/ILIAS).

## PSR-2 and additions

The ILIAS coding standard is aligned to the widely used and established [PSR-2 standard](https://www.php-fig.org/psr/psr-2/)
of the [PHP Interop Group (PHP-FIG)](https://www.php-fig.org/), extended by the
following additions:

### Operators

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

### Type cast

Spaces MUST be added after a type cast.

```php
$foo = (int) '12345';
```

### Return Type Declaration

Spaces MUST be added around a colon in return type declaration.

```php
function () : void {}
```
