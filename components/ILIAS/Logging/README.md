# Logging

The Logging components providers loggers to other components in ILIAS. Those Loggers use [Monolog](https://github.com/Seldaek/monolog),
and  are [PSR-3](https://www.php-fig.org/psr/psr-3/) compliant.

## Configuration

The basic configuration of Logging is done in the `ilias.ini.php` (or alternatively using the Setup). All relevant fields
are in the section `log`. `path` and `file` determine the location of the log directory and the name of the log file,
and `default_level` defines the default log level.

Every component and plugin of an ILIAS installation also has its own log level, configurable in the Logging administration.
If no log level is set explicitly for a component, the default log level is used.

Your `ilias.ini.php` may also contain an additional field `level` under `log`. That field doesn't do anything, and
can be removed.

## Definition of Log Levels

ILIAS (via Monolog) supports the following log levels defined in [RFC 5424](https://datatracker.ietf.org/doc/html/rfc5424):

- DEBUG: Detailed debug information.
- INFO: Interesting event, e.g. a user logs in.
- NOTICE: Normal but significant events.
- WARNING: Exceptional occurences that are no errors, e.g. calls of deprecated methods.
- ERROR: Runtime errors that do not require immediate action.
- CRITICAL: Critical conditions, e.g. a service is unusable due to missing libraries.
- ALERT: Immediate action is required, e.g. no database connection.
- EMERGENCY: The system is unusable.

## Using the Logging Service

work in progress

Loggers are available via their ID through the [`LoggerFactory`](src/Logger/LoggerFactoryInterface.php). To get the
logger for your component or plugin, with its own log level, use its respective ID. The factory also gives out loggers
for any other ID, the default log level is then used.

```php
$logger = $factory->getLazy('crs');
$logger->info('Lorem ipsum');
```

Logging also offers a [`DefaultConfigLoggerFactory`](src/Logger/DefaultConfigLoggerFactoryInterface.php), which does not
depend on the Database component. You should only use it if you need to log anything before the database is initialized.
As a tradeoff, its loggers will always use the default log level, no matter the ID.

Note that both factories share the same cache, so it's not possible to get two different loggers with the same ID. If
your component needs to use both a database-unaware logger and a logger with the correct log level, use a different ID
for the former (e.g. `crs_default`).

### Using Placeholders

The Logger exposes the placeholder feature of Monolog. Placeholders should be used to allow escaping of user input just
as `$database->quote(...)` is used to escape user input in SQL queries.

```php
$logger->debug('Lorem ipsum {foo} dolor {bar}.', [
    'foo' => 'Lorem',
    'bar' => 'ipsum',
]);
```

## Further reading

Please read the [PSR-3 Specification](https://www.php-fig.org/psr/psr-3/) for further information.
