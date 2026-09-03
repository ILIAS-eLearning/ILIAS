# Error Handling

This package covers HTTP error responses and exception logging for ILIAS.

## Error incidents and log files

If a dedicated error log folder is configured, uncaught exceptions are written
to a file in that folder. The user-facing error message references the same
identifier as the file name (for example `abcde_1234`), so reports can be matched
to log files on disk.

The identifier is represented as an `ErrorIncident` and kept for the current
request in an `ErrorIncidentRegistry`. That way the handler writing the log file
and the handler building the response message share one value.

`ReportErrorIncident` performs the actual reporting. It is invoked from
`RecordErrorIncidentHandler`, which is registered in the Whoops chain before the
response handlers run. Implementation code is under `Init/src/ErrorHandling/`
(`Incident`, `Application`, `Notification`, `Infrastructure`).

## Whoops handler chain

`ilErrorHandling` registers handlers in reverse order (the last pushed handler
runs first):

1. `RecordErrorIncidentHandler` — writes the dedicated log file when configured
2. `loggingHandler()` — application log and `error_log()` where enabled
3. `DelegatingHandler` — selects the response handler (production, SOAP, devmode, …)

## When to use which HTTP responder

- **ErrorPageResponder** (`Http\ErrorPageResponder`): Use when the DI container and all ILIAS services (UI, language, HTTP, etc.) are available. Renders a full ILIAS page with a UI-Framework MessageBox and optional back button. Use for expected errors (e.g. routing failures, access denied) that should be shown as a proper HTML page.

- **PlainTextFallbackResponder** (`Http\PlainTextFallbackResponder`): Use when the DI container or other infrastructure is *not* available — for instance in the catch block of `error.php` when the bootstrap itself has failed. Sends a minimal plain-text response with `Content-Type: text/plain; charset=UTF-8` and logs the exception via `error_log`. This responder always works because it uses only PHP built-ins. The HTTP status code defaults to 500; pass a different code (e.g. 502) when the failure context is known.

## Consumer responsibility

**The consumer MUST implement a try-catch block.** Both responders must be invoked explicitly:

1. Wrap the main logic (bootstrap, routing, etc.) in a `try` block.
2. In the `catch` block, call either `ErrorPageResponder::respond()` (if DIC is available) or `PlainTextFallbackResponder::respond()` (if DIC is not available).

Example:

```php
try {
    entry_point('ILIAS Legacy Initialisation Adapter');
    global $DIC;
    new ErrorPageResponder(
        $DIC->globalScreen(),
        $DIC->language(),
        $DIC->ui(),
        $DIC->http()
    )->respond($message, 500, $back_target);
} catch (Throwable $e) {
    new PlainTextFallbackResponder()->respond($e);
}
```
