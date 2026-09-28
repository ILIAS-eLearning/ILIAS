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

**Done** (ILIAS 12).

Previously only the production default handler wrote exceptions to the dedicated
log file via `ilLoggingErrorFileStorage`. SOAP, testing, and devmode handlers did
not.

Log file writing now happens in `RecordErrorIncidentHandler`, which runs for every
handled exception before the response handler is chosen. It calls
`ReportErrorIncident` and stores the incident in `ErrorIncidentRegistry`. The
production handler reads that value when it builds the message for the user, so
the code in the UI matches the log file name.

Details are documented in `README.md` and implemented under
`Init/src/ErrorHandling/`.
