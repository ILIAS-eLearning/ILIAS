# RemoteGroup Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The RemoteGroup component is part of the ECS (E-Learning Community Server) Interface. It represents a group object imported from a remote partner institution into the local ILIAS repository. The component manages the visibility and availability of the remote group entry (offline, unlimited, or time-limited). Access to the remote group entry depends on the configured availability type: if the object is set to offline, only persons with "write" permission on the object can see it.

## Integrated Components

The RemoteGroup component builds on the ECS Interface base classes (`ilRemoteObjectBase`, `ilRemoteObjectBaseListGUI`) for all ECS-related functionality. Advanced metadata substitutions are delegated to `ilAdvancedMDSubstitution`. None of these integrated components introduce additional personal data storage specific to RemoteGroup.

## Data being stored

The RemoteGroup component does not store any personal data. The database table `rgrp_settings` stores only the object identifier (`obj_id`), the availability type (`availability_type`), and the optional time-window fields (`availability_start`, `availability_end`). These are configuration values describing the remote group object itself, not any user-related information.

## Data being presented

The RemoteGroup component does not present personal data. In the repository list view, it displays the group's online/offline status and, if configured, the availability period. In the info screen, the group visibility setting is shown. None of this output contains user-specific or personally identifiable information.

## Data being deleted

When a RemoteGroup object is deleted from the repository and removed from the trash ("delete from trash"), the corresponding row in `rgrp_settings` is removed as part of standard ILIAS object deletion. No personal data is involved in this process.

## Data being exported

The RemoteGroup component does not provide any export functionality for personal data.
