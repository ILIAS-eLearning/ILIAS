# SystemCheck Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The SystemCheck component provides an administration interface for running system integrity checks
and managing the repository trash (deleted objects). It organises check tasks into groups, tracks
their execution status, and offers both a manual trash management UI and a configurable cron job
(`sysc_trash`) for automatic trash purging.

SystemCheck does not store personal data itself. Its database tables (`sysc_groups`, `sysc_tasks`)
contain only system-internal identifiers, task statuses, and timestamps that are not linked to
individual users. The trash management functionality operates on objects owned by other components;
any personal data affected by trash restoration or removal is governed by those objects' respective
privacy documentation.

## Integrated Components

- The SystemCheck component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/AccessControl/PRIVACY.md) — manages permissions that control access
      to the SystemCheck administration interface and its individual features.
    - [Repository](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/Repository/PRIVACY.md) — the SystemCheck component forwards to the
      Repository's Object Ownership Management GUI to display objects without an owner.
    - Cron — the SystemCheck component provides a cron job (`sysc_trash`) for automatic trash
      purging, built on the Cron service's base class.
    - ILIASObject — the SystemCheck object (`ilObjSystemCheck`) inherits from `ilObject`, which
      manages general object metadata such as creation timestamps and owner.
    - Tree — the SystemCheck component delegates tree integrity check tasks to the Tree
      component (`ilSCTreeTasksGUI`).

## Data being stored

The SystemCheck component does not store personal data. Its two database tables contain only
system-internal information:

- The `sysc_groups` table stores **group ID**, **component identifier**, **status**, and
  **last update timestamp** for each check group. These values identify which ILIAS component
  a group of check tasks belongs to and are not linked to individual users.
- The `sysc_tasks` table stores **task ID**, **group ID**, **status**, **identifier**, and
  **last update timestamp** for each check task. These values track the execution status of
  system integrity checks and are not linked to individual users.

The cron job settings (trash purge limits for number, age, and object types) are stored via
`ilSetting` under the module key `sysc`. These are configuration values without personal data.

## Data being presented

- **Persons with the "Read" permission** on the SystemCheck administration object can view the
  overview of system check groups, including task titles, statuses, and last-update timestamps.
  No personal user data is displayed in this view.
- **Persons with the "Write" permission** on the SystemCheck administration object can additionally:
    - access the trash management interface to restore or permanently remove trashed repository
      objects. Object titles and types from the trash are displayed, but these belong to the
      respective repository objects, not to SystemCheck.
    - view the Object Ownership Management interface (provided by the Repository component),
      which may display object ownership information. Privacy documentation for this data is
      covered by the [Repository](../Repository/PRIVACY.md) component.
    - execute system check task actions (e.g., tree integrity repairs).
- **Persons with the "Edit Permission" permission** can additionally access the permission
  settings tab for the SystemCheck administration object.

## Data being deleted

The SystemCheck component does not store personal data, so there is no personal data of its own
to delete.

The trash management functionality (both manual and via the `sysc_trash` cron job) permanently
removes or restores trashed repository objects. When objects are removed from the system, the
deletion cascades to each object's own data through the respective component's `delete()` method.
The privacy implications of those deletions are governed by the individual components that own
the affected objects.

The `sysc_groups` and `sysc_tasks` records are created during ILIAS setup and component
definition processing. They are not deleted during normal operation or when user accounts
are deleted.

## Data being exported

The SystemCheck component does not provide any export functionality for its data.
