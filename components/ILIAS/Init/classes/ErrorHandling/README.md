# Error Responders

This package provides responders for rendering HTTP error pages in ILIAS.

## When to use which responder

- **ErrorPageResponder** (`Http\ErrorPageResponder`): Full ILIAS page: UI MessageBox when the fourth argument is `UIServices`, or `tpl.error.html` block `plain_html_fallback` when it is `ilGlobalTemplateInterface` (no `ui.factory` / `ui.renderer`). Constructor: `global_screen`, `language`, `http`, `shell`. On any `Throwable` from bootstrap or `respond()`, use `PlainTextFallbackResponder` (see `ilias.php` / `error.php`).

- **PlainTextFallbackResponder** (`Http\PlainTextFallbackResponder`): Use when the DI container or other infrastructure is *not* available — for instance in the catch block of `error.php` when the bootstrap itself has failed. Sends a minimal plain-text response with `Content-Type: text/plain; charset=UTF-8` and logs the exception via `error_log`. The HTTP status code defaults to 500; pass a different code when the failure context is known.

## Consumer responsibility

**The consumer MUST implement a try-catch block.** Call `respond()` explicitly:

1. Wrap bootstrap (and `ErrorPageResponder::respond()` when applicable) in one `try` block.
2. In `catch (Throwable)`, call `PlainTextFallbackResponder` (optionally pass a user-facing message if you set it before the failure).

Example:

```php
try {
    entry_point('ILIAS Legacy Initialisation Adapter');
    global $DIC;
    (new ErrorPageResponder(
        $DIC->offsetExists('global_screen') ? $DIC->globalScreen() : null,
        $DIC->language(),
        $DIC->http(),
        $DIC->ui()
    ))->respond($message, 500, $back_target);
} catch (Throwable $t) {
    (new PlainTextFallbackResponder())->respond($t);
}
```
