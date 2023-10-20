> This documentation does not warrant completeness or correctness, and is probably outdated. Reports of
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contributions via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories)
are greatly appreciated.

# Using the Logging Service

Starting with release 5.1 a new logging service based on [Monolog](https://github.com/Seldaek/monolog) is available.

The new service provides support for different log levels per component.

## Activate Logging for Components

To use different log levels for your component, you have to enable "logging" in your module.xml or service.xml

```php
<?xml version = "1.0" encoding = "UTF-8"?>
<module xmlns="http://www.w3.org" version="$Id$" id="grp">
...
<!-- add a line "logging" the component definition file -->
	<logging />
</service>
```

Add a control structure reload to the database update file:

```php
<#4751>
<?php
	$ilCtrlStructureReader->getStructure();
?>
```

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

An instance of the logging service is available via the global variable `$ilLog`, but should be replaced with compontent based logger instances. The calls of $ilLog->write(...) are deprecated and should be replaced in future releases.

```php
// Get component logger and write debug log
ilLoggerFactory::getLogger('grp')->debug('debug message');
 
// code that is not assigned to any module or service can use the root logger for writing messages
ilLoggerFactory::getRootLogger()->info('info message');
```
