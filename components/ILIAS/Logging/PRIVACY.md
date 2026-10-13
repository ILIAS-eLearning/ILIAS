# Logging Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Logging component provides centralized application logging infrastructure for ILIAS, built on the Monolog library. It writes structured log entries to log files on the filesystem (path configured via `ILIAS_LOG_DIR` and `ILIAS_LOG_FILE` constants) and optionally outputs log entries to the browser developer console for a configurable set of users. A separate error-log subsystem writes error protocol files to a dedicated folder and can notify a recipient by email. Administrators can configure per-component log levels stored in the `log_components` database table and control global logging behavior through settings persisted via `ilSetting` (module `logging`).

## Integrated Components

The Logging component uses the Cron component to run the automatic error-file cleanup job (`ilLoggerCronCleanErrorFiles`). No other ILIAS components that handle personal data are directly integrated.

## Data being stored

**User login names** (`browser_users`): A serialized array of ILIAS user login names for whom browser console logging is activated. Stored via `ilSetting` in the `settings` table with `module = 'logging'` and `keyword = 'browser_users'`. These logins are set by an administrator explicitly in the logging settings form.

**Error log recipient email address**: An email address stored in `client.ini.php` under the `[log]` section key `error_recipient`. This is the address to which error protocol notifications are sent. It is set by an administrator in the error settings form.

**Session ID prefix in log files**: Every log entry written to the application log file includes a `suid` field containing the first five characters of the current PHP session ID (`substr(session_id(), 0, 5)`). This pseudonymised identifier links log lines belonging to the same request session but does not directly identify a person.

**Log message content**: The content of individual log messages is determined entirely by other ILIAS components that call the logging infrastructure. Those messages may contain personal data (such as user IDs or object references) depending on the calling component. The Logging component itself does not add user-identifying information beyond the session prefix described above.

The `log_components` database table stores only component identifiers (`component_id`) and their configured log level integers (`log_level`); it contains no personal data.

## Data being presented

The list of browser log users (login names stored in `browser_users`) is displayed in the logging administration interface under Administration > Logging > General Settings. Only a person with "read" permission on the logging settings object can view this list. A person with "write" permission can modify it.

## Data being deleted

**Error log files**: Automatically deleted by the `ilLoggerCronCleanErrorFiles` cron job. Files in the configured error-log folder whose modification date is older than a configurable threshold (default: 31 days) are removed permanently by `unlink()`. The threshold is adjustable by a person with "write" permission on the cron settings.

**Browser log user list**: The `browser_users` setting is removed from the `settings` table when an administrator saves the logging settings form with an empty user list. There is no account-deletion hook; the entry must be removed manually by an administrator.

**Application log files**: The Logging component itself provides no mechanism for automatic rotation or deletion of the main application log file. Lifecycle management of that file is the responsibility of the system administrator at the operating-system level.

## Data being exported

The Logging component provides no built-in export functionality. Application log files and error protocol files reside on the server filesystem and are accessible only to system administrators with direct file-system access.
