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
