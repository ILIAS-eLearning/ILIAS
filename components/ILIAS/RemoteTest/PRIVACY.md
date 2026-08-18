# RemoteTest Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The RemoteTest component provides a local repository object of type `rtst` that acts as a placeholder reference to a test hosted on a remote ILIAS installation, accessed via the ECS (e-Competence System) campus-connect interface (`/campusconnect/tests`). It stores only configuration data for the local object (availability status and optional time window). The component does not execute or track any test-taking activities locally; all actual test interactions occur on the remote system. Visibility of the object in the local repository depends on the configured availability type (offline, unlimited, or time-limited).

## Integrated Components

The RemoteTest component does not employ any other ILIAS components that handle personal data on the local installation.

## Data being stored

The RemoteTest component does not store any personal data. The database table `rtst_settings` stores only object-level configuration fields: `obj_id` (repository object reference), `availability_type` (integer flag: offline/unlimited/limited), `availability_start`, and `availability_end` (Unix timestamps for time-limited availability). No user identifiers, names, or other personal data are written to this table.

## Data being presented

The RemoteTest component does not present any personal data. The info screen displays only the availability period of the remote test object. A person with the `write` permission can also see the availability configuration in the edit form; however, no personal user data is shown.

## Data being deleted

The RemoteTest component does not delete any personal data. Deleting a RemoteTest object removes the entry from `rtst_settings` along with the standard ILIAS repository object record. No personal data is involved in this process. Permanent deletion occurs when the object is deleted from trash.

## Data being exported

The RemoteTest component does not export any personal data.
