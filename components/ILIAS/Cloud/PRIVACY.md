# Cloud Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Cloud component is a former ILIAS module that integrated external cloud storage services (e.g. Nextcloud, ownCloud) into the repository. It has been decommissioned and no longer provides any active functionality to end users. The component now exists solely to supply a setup agent (`Agent.php`) that executes database update steps (`RemoveCloudDBUpdate.php`) to remove all legacy Cloud data from the ILIAS installation. No new personal data is collected, processed, or stored by this component.

## Integrated Components

The Cloud component does not actively integrate with other ILIAS components that handle personal data. Its only operation is the removal of legacy database entries. No other component's PRIVACY.md is relevant to the current state of this component.

## Data being stored

The Cloud component does not store any personal data. The database update steps it provides only perform deletions: `step_1` drops the legacy table `il_cld_data`, and `step_2` deletes entries of object type `cld` from `object_data` and `object_reference`.

## Data being presented

The Cloud component does not present any personal data. No user interface exists in the current component.

## Data being deleted

The setup agent removes all legacy Cloud object data during the ILIAS update process:

- The table `il_cld_data` is dropped entirely (`step_1`).
- All entries of object type `cld` are deleted from `object_data` and the corresponding entries are deleted from `object_reference` (`step_2`).

These deletions are executed automatically during the ILIAS setup/update procedure and are not triggered by individual users or administrators. The README notes that additional cleanup steps (e.g. removing entries from `tree`, `rbac_ta`, `rbac_operations`, `rbac_templates`, and `settings`) are still pending (see [PR #7605](https://github.com/ILIAS-eLearning/ILIAS/pull/7605)).

## Data being exported

The Cloud component does not export any personal data.
