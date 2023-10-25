# Logging Service

Starting with release 5.1 a new logging service based on [Monolog](https://github.com/Seldaek/monolog) is available.

The service provides support for different log levels per component.

## Activate Logging for Components

To use different log levels for your component, you have to enable "logging" in your module.xml or service.xml.

```php
<?xml version = "1.0" encoding = "UTF-8"?>
<module xmlns="http://www.w3.org" version="$Id$" id="grp">
...
<!-- add a line "logging" the component definition file -->
	<logging />
</service>
```

Call `composer du` to trigger the reading of the XML files.

Different log levels for components can be defined in "ILIAS -> Administration -> Logging -> Components".
If no component specific log level is given, the the global log level is used.

## Definition of Log Levels

ILIAS (monolog) support the following log levels defines in [RFC 5424](https://datatracker.ietf.org/doc/html/rfc5424):

- DEBUG: Detailed debug information
- INFO: Interesting event. E.g user logs in
- NOTICE: Normal but significant events
- WARNING: Exceptional occurences that are no errors. E.g calls of deprecated methods
- ERROR: Runtime errors that do not require immediate action
- CRITICAL: Critical conditions - e.g. a module service is unasable due to missing librarys.
- ALERT: Immediate action is required.  E.g. no database connection
- EMERGENCY: The system is unusable.

## Using the Logging Service

An instance of the logging service is available via `$DIC->logger()`. You should reveice the logger for your component by calling a method with your component id on this object.

```php

// Get component logger
$grp_logger = $DIC->logger->grp();
 
// write a message with info log level
$grp_logger->info('info message');
```

## Using Placeholders

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
