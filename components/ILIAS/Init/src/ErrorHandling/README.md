# Error Handling

This folder contains types and concepts for ILIAS error and exception handling.

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
* [ShouldNotAutoReport marker interface](#shouldnotautoreport-marker-interface)
  * [Purpose](#purpose)
  * [Behaviour in ilErrorHandling](#behaviour-in-ilerrorhandling)
  * [Usage example](#usage-example)

## ShouldNotAutoReport marker interface

### Purpose

`ILIAS\Init\ErrorHandling\Exception\ShouldNotAutoReport` is a **marker interface** for
exception classes that MUST NOT be reported by ILIAS' global error handling.

"Reporting" here means:

* Writing a dedicated log file (e.g., via `ilLoggingErrorFileStorage`) when the
  exception is handled by the default Whoops handler,
* Writing the exception to the application log (e.g. `$ilLog->error(...)`),
* Sending the exception message to the PHP system logger (`error_log()`),
* (Outlook) Sending exceptions to third-party log or error-tracking services
  (e.g., Sentry).

Exceptions that implement `ShouldNotAutoReport` still reach the error handler and the
user may still see an error page (e.g., redirect to `error.php`). Only the
reporting step is skipped: no log file is created and no log entries are
written for that exception.

Use this for **expected** or **high-volume** exceptions where reporting would
cause unnecessary I/O, log noise, or cost, for example:

* Client errors that are common under load (e.g., bots, orphaned search indexes),
* Validation or business-rule exceptions that are already communicated to the
  user and do not require administrator attention,
* Avoiding cost or quota consumption when exceptions are forwarded to
  third-party log or error-tracking services (e.g., Sentry) that charge per
  event.

### Behaviour in ilErrorHandling

When Whoops handles an uncaught exception:

1. **Default handler (production):** If the exception implements `ShouldNotAutoReport`,
   `ilErrorHandling` does NOT write a log file and does NOT show a "logfile has
   been created" message. The user sees a generic error message.

2. **Logging handler:** If the exception implements `ShouldNotAutoReport`, the logging
   handler does NOT write to the application log or to the system logger. It
   returns without reporting.

Exceptions that do not implement `ShouldNotAutoReport` are reported as before (log
file when configured, application log, and `error_log()`).

### Usage example

Implement the interface on your exception class so that instances are not
reported when they bubble up to the global handler:

```php
<?php

namespace MyComponent\Exception;

use Exception;
use ILIAS\Init\ErrorHandling\Exception\ShouldNotAutoReport;

class InvalidRequestException extends Exception implements ShouldNotAutoReport
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
```

If this exception is thrown and not caught, the user will still see an error
page, but no log file will be created and no log entries will be written. This
is useful for expected client errors (e.g., invalid or tampered request
parameters) that you do not want to treat as incidents requiring administrator
attention or disk I/O.
