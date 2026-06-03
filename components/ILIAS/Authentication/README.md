# Authentication

## Business Rules

### Authentication Status

ILIAS differentiates between "logged-in" users and the "Anonymous" user.
The "Anonymous" user is a regular user account that has not logged in yet.
Nevertheless, this user account may, depending on the business rules of
other components (e.g. `Repository` or `Administration`), have certain
permissions on resources.

The "Anonymous" user can be identified by its `usr_id` (usually `13`), which
is created during the initial ILIAS installation.

Before [PR 5458](https://github.com/ILIAS-eLearning/ILIAS/pull/5458), cases
also occurred where program logic had to deal with situations where the `usr_id`
of the user in the current authentication context equaled `0`. This was, for example,
the case during the initial ILIAS request.

The class `ilAuthSession` provides methods to retrieve the current authentication
state of a client, identified by its session ID.

#### Status Query Methods

- **`isAuthenticated()`**: Returns `true` if the session is authenticated. This
  includes both logged-in users and the "Anonymous" user (since
  `user_id === ANONYMOUS_USER_ID` is considered authenticated).

- **`isExpired()`**: Returns `true` if the session has expired. Important: Returns
  `false` if the user is the Anonymous user, as "Anonymous" sessions cannot expire.

- **`isValid()`**: Returns `true` if the session is authenticated and not expired.
  This is the combination of `isAuthenticated()` and `!isExpired()`.

- **`isFullyAuthenticated()`**: Returns `true` if the session is valid AND the
  user is not the "Anonymous" user. Use this method to check if a regular user is
  logged in.

- **`isAnonymouslyAuthenticated()`**: Returns `true` if the session is valid AND
  the user is the "Anonymous" user. Use this method to check if the "Anonymous" user
  is currently active.

#### Usage Recommendations

- Use `isFullyAuthenticated()` when you want to check if a regular logged-in user
  is present.
- Use `isAnonymouslyAuthenticated()` when you want to specifically check if the
  "Anonymous" user is active.
- Use `isValid()` when you only want to check if the session is generally valid
  (regardless of user type).
- Note that `isAuthenticated()` returns `true` for both logged-in and "Anonymous"
  users.

## KeyValueStorage Contribution

Authentication contributes the **session** backend for
[`ILIAS\KeyValueStorage`](../KeyValueStorage/README.md):

| Role | Class |
|---|---|
| `SessionStoragePort` | `Authentication\KeyValueStorage\SessionStoragePort` |
| `StorageProvider` | via `StorageProviderFactory::session()` |
| `UI\Storage` | `Authentication\KeyValueStorage\UiStorageAdapter` (session-backed) |

Session data is the natural persistence mechanism for state that MUST NOT survive
logout. See [Design Decisions](#design-decisions) for how namespaces are laid out in
the session.

## Design Decisions

Significant architecture decisions for this component are recorded as lightweight
[Architecture Decision Records](https://github.com/joelparkerhenderson/architecture-decision-record)
(Michael Nygard's *Context / Decision / Consequences* format). Records are
append-only: supersede rather than rewrite.

### ADR 0001 — Flat Session Keys for Session-Scoped KeyValueStorage

**Status:** Accepted.

**Context.** `Authentication\KeyValueStorage\SessionStoragePort` implements
`ILIAS\KeyValueStorage\SessionStoragePort` on top of
`ilSession`. The port MUST support namespace-scoped `clear()` (via
`clearNamespace()`), but `ilSession` only exposes single-key `get` / `set` / `has` /
`clear` — there is no API to list or clear keys by prefix.

An alternative nested layout was considered — storing all entries under one session
root as `$_SESSION['__ilias_kv_storage__'][namespace][key]`. That would make
`clearNamespace()` an O(1) subtree drop without scanning the session, but every
`write` / `remove` would read-modify-write the **entire** root bucket. With ILIAS
session handling (all key/value pairs loaded at request start and written back at
request end), concurrent requests — e.g. parallel AJAX calls from one page — can
overwrite each other's in-flight changes to the same root array. Per-key updates via
`ilSession::set()` avoid that class of lost-update races.

**Decision.** Use flat, per-entry session keys:

```php
$_SESSION['__ilias_kv_storage__.{namespace}.{key}'] = encoded_value
```

`has`, `read`, `write`, and `remove` each call `ilSession` for exactly one key.
`clearNamespace()` is the sole exception: it iterates `$_SESSION` for keys matching
the namespace prefix and clears each match through `ilSession::clear()`.

**Consequences.**

- **+** Per-key writes are atomic at the session layer and less prone to concurrent lost-update races than a shared nested bucket.
- **+** `write` / `read` / `remove` use `ilSession` consistently.
- **−** `clearNamespace()` must scan session keys and accesses `$_SESSION` directly, because `ilSession` offers no prefix-based clearing.
- **−** Namespace clearing is O(n) in the number of session variables (typically acceptable — `clear()` is infrequent).

**Revisit when** `ilSession` gains prefix-based clearing, or a session API appears that
supports safe partial updates of a structured bucket under concurrent requests.
