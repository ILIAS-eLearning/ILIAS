# Cron Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Cron component provides the central framework for registering, configuring, scheduling, and executing background cron jobs in ILIAS. It manages job lifecycle operations (activation, deactivation, manual execution, reset) and tracks the status and results of each job run. All registered jobs are persisted in the `cron_job` database table. The component itself does not process learning progress or user content data — it serves as an infrastructure layer. Other ILIAS components register their own cron jobs through this framework and are responsible for any personal data their jobs process.

## Integrated Components

The Cron component does not directly integrate with other ILIAS components that handle personal data. It uses `ilUserUtil` for resolving user names for display purposes in the administration interface, but does not have a structural dependency on a User component that would require a PRIVACY.md link.

## Data being stored

The Cron component stores the following personal data in the `cron_job` table:

- **User ID of the person who changed the job status** (`job_status_user_id`): When a person with "write" permission on the Cron administration object manually activates or deactivates a cron job, their internal ILIAS user ID is stored along with the action type (`job_status_type = 1`) and a timestamp (`job_status_ts`). For status changes triggered automatically by the system crontab, the value is stored as `0`.

- **User ID of the person who manually triggered a job execution** (`job_result_user_id`): When a person with "write" permission manually runs an active cron job, their internal ILIAS user ID is stored along with the result type (`job_result_type = 1`). For automatic crontab-triggered executions, this value is stored as `0`.

No other personal data (such as name, email, or login) is stored directly in the `cron_job` table.

## Data being presented

In the ILIAS administration under the "Cron Jobs" section, the cron job management table displays:

- The **name of the person who last changed the job status** (resolved from `job_status_user_id` via `ilUserUtil::getNamePresentation()`) is shown in the "Status Info" column, but only if the change was performed manually.
- The **name of the person who last triggered a manual job execution** (resolved from `job_result_user_id` via `ilUserUtil::getNamePresentation()`) is shown in the "Result Info" column, but only if the execution was manual.

Both pieces of information are visible exclusively to persons with "read" permission on the Cron administration object. Persons with only "read" permission can view the table; persons with "write" permission can additionally perform actions such as activating, deactivating, running, resetting, and editing jobs.

## Data being deleted

The Cron component does not provide a dedicated mechanism to delete the stored user ID references (`job_status_user_id`, `job_result_user_id`). These values are overwritten whenever a subsequent manual or automatic job status change or execution occurs. There is no automatic expiry, no account-deletion hook within this component, and no user-initiated deletion flow. Deletion of the `cron_job` table records occurs only when a job is unregistered (e.g., when a component or plugin is removed from the ILIAS installation), which removes the entire job record.

## Data being exported

The Cron component does not provide any export functionality for the data stored in the `cron_job` table. No personal data from this component is included in any ILIAS export format.
