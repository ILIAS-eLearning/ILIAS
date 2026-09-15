# Authentication Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**



## General Information

The Authentication component manages user login, session handling, and authentication mode
configuration in ILIAS. It supports multiple authentication backends (local database, LDAP,
Shibboleth, SAML, OpenID Connect, SOAP, Apache, and plugins) and coordinates their sequence.

The component stores active session data in the database and, when the session statistics feature
is enabled (which it is by default), additionally collects aggregated session statistics. The
**IP address** of each user may be stored alongside session records if the `save_ip` setting is
enabled in the ILIAS client configuration (`client.ini.php`). This conditional storage is an
important privacy consideration.

Authentication also enforces security policies that affect personal data: failed login attempts
are counted per user account, and accounts can be automatically deactivated after exceeding a
configurable maximum number of attempts.

## Integrated Components

- The Authentication component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – manages permissions for authentication
      administration settings and determines session types for users with system administration
      access.
    - User – the Authentication component reads and updates user account data during login
      (last login date, login attempts, active status) and resolves user names for session
      statistics export.
    - [COPage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/COPage/PRIVACY.md) – the login page and logout page editors use the COPage
      service for editable content pages.
    - [AuthShibboleth](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AuthShibboleth/PRIVACY.md) – Shibboleth authentication is configured
      and managed through the Authentication administration interface.
    - LDAP – LDAP server settings and authentication are integrated into the Authentication
      administration and login flow.
    - SAML – SAML Identity Provider settings are managed through the Authentication
      administration interface.
    - OpenIdConnect – OpenID Connect authentication settings are managed through the
      Authentication administration interface.
    - Cron – the Authentication component provides a cron job
      (`auth_destroy_expired_sessions`) to periodically destroy expired sessions and aggregate
      session statistics.
    - Logging – the Authentication component logs authentication events (login, logout,
      failed attempts) via the ILIAS logging service.

## Data being stored

- **User ID**: When a session is created or updated, the **user ID** of the authenticated user
  is stored in the `usr_session` table. This links the session to a specific user account.
- **Session ID**: A unique **session identifier** is generated for each session and stored in
  the `usr_session` table. The session ID is also set as a browser cookie.
- **Session creation time**: The **timestamp** when the session was first created is stored in
  the `createtime` column of the `usr_session` table.
- **Session last activity time**: The **timestamp** of the most recent request within the
  session is stored in the `ctime` column of the `usr_session` table. This is used to determine
  session expiration.
- **Session expiration time**: The calculated **expiration timestamp** is stored in the
  `expires` column of the `usr_session` table.
- **Session type**: A numeric **session type** (system, admin, user, or anonymous) is stored
  in the `type` column of the `usr_session` table. The type is determined based on the user's
  permissions.
- **Session context**: The **context type** (e.g., web, WAC) is stored in the `context` column
  of the `usr_session` table when a new session record is inserted.
- **IP address** (conditional): If the `save_ip` setting is enabled in the ILIAS client
  configuration, the user's **IP address** (`REMOTE_ADDR`) is stored in the `remote_addr`
  column of the `usr_session` table. This setting is disabled by default.
- **Session data**: Serialized **PHP session data** is stored in the `data` column of the
  `usr_session` table. This may contain transient application state.
- **Session-based immediate storage**: Key-value pairs are stored in the `usr_sess_istorage`
  table, linked to the **session ID** and a **component ID**. This is used for AJAX-safe
  session storage across components.
- **Raw session statistics**: When session statistics are enabled (default: on), the
  `usr_session_stats_raw` table stores the **session ID**, **user ID**, **session type**,
  **start time**, **end time**, and **end context** (reason for session closure) for each
  controlled session. This data is used to calculate aggregated session load statistics.


## Data being presented

- **Each user** is not directly shown session data by this component. Session-based data is
  transient and serves the running application. Users interact with the session indirectly via
  the **session reminder**, which displays a notification when the session is about to expire.
  The session reminder lead time is configurable per user via a user preference
  (`session_reminder_lead_time`).
- **Persons with the "Read" permission** on the Authentication Administration can:
    - view the configured authentication modes, the number of users per authentication mode,
      and the current default authentication mode.
    - view SOAP authentication settings, Apache authentication settings, and authentication
      mode determination settings.
    - view session statistics (current system load, short-term, long-term, and periodic
      statistics) as charts showing aggregate active session counts over time.
- **Persons with the "Write" permission** on the Authentication Administration can:
    - modify authentication settings, authentication mode sequences, and role-to-authentication
      mode mappings.
    - configure login page and logout page editor content.
    - configure logout behaviour settings.
    - trigger manual synchronization of session statistics.
    - export session statistics as CSV files. The CSV export includes the **full name** of the
      person who initiates the export (as "report owner") but does not contain individual user
      session data -- only aggregated session counts.

## Data being deleted

- **When a user logs out**: The session record for that user is deleted from the `usr_session`
  table. The corresponding records in `usr_sess_istorage` are also deleted. A closing entry is
  written to the `usr_session_stats_raw` table (end time and end context) before the session
  record is removed.
- **When a session expires**: Expired sessions are destroyed either by a random cleanup
  triggered during regular page requests (with a 1-in-50 probability) or by the
  `auth_destroy_expired_sessions` cron job. Both mechanisms delete the session record from
  `usr_session` and `usr_sess_istorage`, and close the corresponding raw statistics entry.
- **When a user account is deleted**: All sessions for that user are destroyed via
  `ilSession::_destroyByUserId()`, which deletes all records from the `usr_session` table
  where `user_id` matches the deleted account.
- **Raw session statistics cleanup**: Aggregated raw session data older than 7 days is
  automatically deleted by the `deleteAggregatedRaw()` method after each aggregation cycle.
  This means individual `usr_session_stats_raw` records (which contain user IDs) are retained
  for at most approximately 7 days after the session ended.
- **Residual data**: The aggregated statistics in `usr_session_stats` are fully anonymized
  (they contain only counts, not user IDs) and are not deleted when individual users are
  removed. Session storage entries in `usr_sess_istorage` and raw session statistics are
  only removed on logout or session expiry (via `ilSession::_destroy()`); account deletion
  via `ilSession::_destroyByUserId()` does not clean these entries, so they may persist
  as residual data after a user is removed. The `auth_ext_attr_mapping` configuration
  data persists until the associated authentication mode is reconfigured or removed.

## Data being exported

- **Session statistics CSV export**: Persons with the "Write" permission on the Authentication
  Administration can export session statistics as CSV files. The export contains the
  installation name, the report date, the **full name of the exporting user** (as report
  owner), and aggregated session data (time slots, active session counts by min/avg/max,
  sessions opened and closed by type). No individual user session records or user IDs are
  included in the export.
- There is no other dedicated export of session or authentication data from this component.
