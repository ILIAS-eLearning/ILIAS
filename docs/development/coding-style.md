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

## Code Style Checks and Fixes

The ILIAS code style can be checked/applied with/by different tools.

When working with the `PhpStorm` IDE developers can import the
[PhpStorm Code Style](/CI/PHP-CS-Fixer/example/PhpStorm/ILIAS_CtodeStyle.xml) example.

Furthermore multiple [Git Hooks](./git-hooks.md#code-style-hooks) are provided
to check or fix the code style of changed files in a Git commit.

Developers can additionally use the [PHP Coding Standards Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
to check or fix one or multiple files.

### Checking Code Style

```bash
libs/composer/vendor/bin/php-cs-fixer fix --dry-run --stop-on-violation --using-cache=no --diff --config=./CI/PHP-CS-Fixer/code-format.php_cs [FILE]
```
### Fixing Code Style

```bash
libs/composer/vendor/bin/php-cs-fixer fix --stop-on-violation --using-cache=no --diff --config=./CI/PHP-CS-Fixer/code-format.php_cs [FILE]
```