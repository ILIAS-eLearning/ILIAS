# Benchmark Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Benchmark component is an administration tool that records and displays database query performance data for a single designated user. When enabled, every SQL statement executed during that user's session is captured along with its execution duration. Benchmarking is controlled by two global settings: `enable_db_bench` (on/off switch) and `db_bench_user` (the user ID of the person being monitored). Recording only takes place when both the feature is enabled and the currently authenticated user matches the stored `db_bench_user` ID. Each time a benchmark session is saved, all previously stored entries in the `benchmark` table are deleted before the new measurements are written, so the table always reflects only the most recent recording session.

## Integrated Components

The Benchmark component uses the [User](../User/PRIVACY.md) component to resolve user login names to user IDs (via `ilObjUser::_lookupId`) and to look up the login name from a stored user ID (via `ilObjUser::_lookupLogin`) when displaying the settings form. The user identity of the monitored person is referenced through this integration.

## Data being stored

The component stores data in two places:

**`benchmark` table** — populated only while benchmarking is active for a designated user:
- `id` (integer): auto-incremented record identifier.
- `duration` (float): execution time in seconds of the recorded SQL statement.
- `sql_stmt` (clob/longtext): the full SQL statement that was executed during the monitored user's session. Because these statements reflect real application queries, they may indirectly contain personal data (e.g., user IDs, object ownership references) embedded in WHERE clauses or query parameters.

**Global settings** (stored via `ilSetting`):
- `db_bench_user`: stores the numeric user ID of the person designated for monitoring. This directly identifies a specific ILIAS user.
- `enable_db_bench`: stores whether the benchmark feature is currently active.

The `db_bench_user` setting constitutes personal data because it pinpoints a specific user account for surveillance-level SQL recording.

## Data being presented

Recorded benchmark data from the `benchmark` table is displayed exclusively in the ILIAS administration area under the Benchmark object. A person with `read` permission on the Benchmark administration node can view all stored SQL statements and their execution times, sorted chronologically, by slowest execution time, alphabetically by SQL text, or aggregated by the first referenced database table. Because the raw SQL statements are shown verbatim, any personal data embedded in those queries (e.g., numeric user IDs in WHERE conditions) is visible to that person. The settings form, accessible to a person with `write` permission, displays the login name of the currently designated benchmark user.

## Data being deleted

Benchmark measurement data in the `benchmark` table is deleted automatically each time a new recording session is saved (the entire table is truncated via `DELETE FROM benchmark` before new entries are inserted). A person with `write` permission can also explicitly clear all stored measurements by disabling benchmarking through the settings form, which triggers `clearData()` (executing `DELETE FROM benchmark`) and resets the `db_bench_user` setting to `0`. There is no dependency on object deletion or user account deletion — the data is not tied to the ILIAS object lifecycle and is not removed on user account deletion.

## Data being exported

The Benchmark component does not provide any data export functionality. Stored SQL statements and timing data cannot be exported through the component's interface.
