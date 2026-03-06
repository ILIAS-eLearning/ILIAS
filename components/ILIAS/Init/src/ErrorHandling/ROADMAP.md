# Roadmap

## Short Term

### Get rid of redirects to "error.php"

**Current behaviour**

When an uncaught exception is handled by `ilErrorHandling`'s default
(production) handler, the handler:

1. Optionally writes a log file and builds a message that references it,
2. Sets the message in the UI template component or session,
3. **Redirects the user to `error.php`** (via
   `$DIC->ctrl()->redirectToURL('error.php')` or
   `header('Location: error.php')`).

The user therefore sees the error page only after a **second HTTP request**
to `error.php`.

**Problem**

In almost all cases this redirect is unnecessary:

- The same error page (status 500, generic "Sorry, an error occurred" or
  log-file message) could be sent **in the same request** as the response
  body, with the correct status code and no redirect.
- The redirect causes extra latency, an additional round-trip, and more load
  (two requests instead of one). On busy installations or under bot traffic,
  this multiplies unnecessarily.

**Goal**

- Remove the HTTP redirect to `error.php` from
  `ilErrorHandling::defaultHandler()`.
- Respond **in-place** with the error page content and HTTP 500 (or the
  appropriate status), reusing the same rendering logic as `error.php`
  (via a response builder), so that the user receives one response instead
  of a redirect followed by a second request.
- Remove the `error.php` from the ILIAS codebase, as it is no longer needed
  as the primary target of the default exception handler.

**Outcome**

- One response per error instead of redirect and second request.
- Fewer requests and lower latency for users when an error occurs.
- Same user-visible error page and behavior, without the 99.9% redundant
  redirect.

### Unified log file reporting for all handlers

**Current behaviour**

Only the **default handler** (production) writes exceptions to a dedicated log
file (via `ilLoggingErrorFileStorage`) when configured. Other handlers (e.g.,
SOAP, testing, devmode handlers) do not write to that log file.

**Goal**

- Make the ability to report an exception to the dedicated log file available to
  **all** Whoops handlers (default, SOAP, testing, devmode, etc.), not only the
  default handler.
- Ensure a consistent reporting path: whenever an exception is handled and
  logging is enabled, it can be written to the configured log file regardless
  of which handler rendered the response.

**Outcome**

- Administrators and developers get a single, consistent log of reported exceptions
  across all entry points and handler types.
- Easier auditing and debugging when errors occur in SOAP, tests, or other
  contexts that today do not use the dedicated log file.
