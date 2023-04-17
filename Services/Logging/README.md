# How to use the logging service

The ilLogger exposes the placeholder feature given by the monolog bundle, which implements a PSR-3 compliant logger interface.

Placeholders should be used to allow escaping of user input just as `$database->quote(...)` is used to escape user input in SQL queries.

### Example usage

```php
$logger->debug('Lorem ipsum {foo} dolor {bar}.', [
    'foo' => 'Lorem',
    'bar' => 'ipsum',
]);
```

## Further reading

Please read the [PSR-3 Specification](https://www.php-fig.org/psr/psr-3/) for further information.
