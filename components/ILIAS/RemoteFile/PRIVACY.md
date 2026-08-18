# RemoteFile Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The RemoteFile component (`rfil`) represents files that are shared into an ILIAS instance from a remote campus system via the ECS (E-Campus-Sharing) protocol. Remote file objects are created and updated automatically by the ECS interface when content is published by a remote participant; they are not created manually by local users. When a user accesses a remote file, the component constructs an authenticated URL and passes user identity information to the remote system as URL query parameters (via `ilECSUser::toGET()`). This identity data is transmitted to the remote server but is not stored in the local ILIAS database.

## Integrated Components

The RemoteFile component is part of the **ECS Interface** (`belong_to_component: ECS Interface`) and extends `ilRemoteObjectBase` from the WebServices/ECS component. It has no further components listed in `used_in_components`. Personal data handling related to the ECS token mechanism, user identity forwarding, and participant management is handled by the ECS component.

## Data being stored

The RemoteFile component does not store any personal data.

The DB table `rfil_settings` stores the following fields per remote file object, none of which are personal data:

- `obj_id` — ILIAS object identifier (internal reference, not linked to a person)
- `remote_link` — URL pointing to the file on the remote system
- `local_information` — optional locally added descriptive text
- `mid` — ECS member institution ID (identifies the remote institution, not a person)
- `organization` — name of the remote institution (not a personal identifier)
- `version` — version number received from the remote ECS system
- `version_tstamp` — Unix timestamp of the version, received from the remote ECS system

The object owner is always set to the fixed system user ID (`OBJECT_OWNER = 6`) and does not reflect the identity of any real user.

## Data being presented

The RemoteFile component does not present personal data.

In the repository list view, the object title, description, organization name, and version information (version number and timestamp) of the remote file are shown to persons with "read" permission. None of these constitute personal data.

When a person with "read" permission activates the link to the remote file, user identity data is transmitted to the remote system as URL query parameters. What is displayed on the remote system is outside the scope of this component.

## Data being deleted

When a remote file object is deleted from the ILIAS repository (permanently via delete from trash), the corresponding row is removed from `rfil_settings` and the associated ECS import record is deleted via `ilECSImportManager::_deleteByObjId()`. No personal data is affected, as none is stored.

Remote file objects are typically removed automatically by the ECS interface when the remote participant unpublishes the content.

## Data being exported

The RemoteFile component does not export personal data. The component has no export functionality of its own. The remote link (URL) stored in `rfil_settings` points to content on the remote system and is not an ILIAS export.
