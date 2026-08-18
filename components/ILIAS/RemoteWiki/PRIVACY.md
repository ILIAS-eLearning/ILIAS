# RemoteWiki Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The RemoteWiki component is part of the ECS (E-Learning Community Server) Interface and represents a remote wiki object imported from an external institution's system via the CampusConnect/ECS protocol (`/campusconnect/wikis`). It acts as a link inside ILIAS that redirects users to a wiki hosted on a remote server. When a user accesses a RemoteWiki object, ILIAS constructs a full remote link that includes ECS authentication tokens and personal user data transmitted as URL GET parameters to the remote server. Visibility of the object depends on its online/offline availability setting, which is synchronized from the ECS server.

## Integrated Components

The RemoteWiki component extends `ilRemoteObjectBase` from the WebServices/ECS component, which handles ECS server communication, import management, and the transmission of user data to remote systems.

## Data being stored

The RemoteWiki component stores object configuration data in the `rwik_settings` database table. The fields stored are: `obj_id`, `local_information`, `remote_link`, `mid` (member/institution ID), `organization`, and `availability_type`. None of these fields contain personal data of ILIAS users.

However, when a person with "read" permission accesses a RemoteWiki, the base class (`ilRemoteObjectBase::getFullRemoteLink()`) transmits personal user data to the remote ECS server as URL GET parameters via `ilECSUser::toGET()`. The following user attributes are included in this outgoing request:

- `ecs_login` — the user's login name
- `ecs_firstname` — the user's first name
- `ecs_lastname` — the user's last name
- `ecs_email` — the user's email address
- `ecs_institution` — the user's institution
- `ecs_uid_hash` — a pseudonymous identifier in the format `il_<inst_id>_usr_<user_id>`

This data is not stored locally by ILIAS but is sent to the remote institution as part of the ECS single-sign-on handshake. Whether the remote system stores this data is outside ILIAS's control.

## Data being presented

A person with "read" permission sees the RemoteWiki info screen, including the object title, description, availability status (online/offline), and the organization name imported from the ECS server. No personal data of other users is displayed. A person with "write" permission additionally sees the edit form showing the availability type (read-only, synchronized from ECS).

## Data being deleted

When a RemoteWiki object is deleted, the corresponding row in the `rwik_settings` table is removed. Additionally, the ECS import record is deleted via `ilECSImportManager::_deleteByObjId()`. Deletion is performed by a person with sufficient administrative permission on the object, or when the object is permanently removed from the trash (delete from trash). No personal user data is stored locally by this component, so no user-specific deletion applies.

## Data being exported

The RemoteWiki component does not provide any export functionality for personal data. The component only transmits user data outbound to the remote ECS server at access time (see "Data being stored" above); this transfer is not an ILIAS export in the conventional sense and cannot be initiated or controlled independently by the user.
