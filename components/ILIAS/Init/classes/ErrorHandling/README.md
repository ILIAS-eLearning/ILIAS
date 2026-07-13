# Error Responders

This package provides responders for rendering HTTP error pages in ILIAS.

## When to use which responder

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
