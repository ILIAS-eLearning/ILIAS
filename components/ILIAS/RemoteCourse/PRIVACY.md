# RemoteCourse Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The RemoteCourse component (object type `rcrs`) represents a course object that has been imported from a remote institution via the ECS (E-Campus System / CampusConnect) interface at the `/campusconnect/courselinks` endpoint. It creates a local ILIAS repository object that links users to a course hosted on a remote campus system. The component belongs to the "ECS Interface" group and is managed as part of the `ilRemoteObjectBase` inheritance hierarchy.

The availability of a RemoteCourse object can be set to offline, unlimited, or time-limited (controlled via the fields `availability_type`, `r_start`, and `r_end`). When a RemoteCourse is offline, it remains invisible to persons without "write" permission on the object, but the object itself and its settings are retained in the database.

No personal data of ILIAS users is stored by this component. The data stored relates exclusively to the remote course object configuration as received from the external ECS server.

## Integrated Components

The RemoteCourse component relies on the following ILIAS components:

- WebServices/ECS — provides the base class `ilRemoteObjectBase`, handles ECS server communication, import tracking (`ecs_import` table), and data mapping (`ilECSDataMappingSetting`, `ilECSImport`).

The ECS component may itself handle institutional identifiers (e.g., MID — member ID of the sending institution) and advanced metadata mapping. Please refer to its privacy documentation for details.

## Data being stored

The RemoteCourse component does not store any personal data of ILIAS users.

The following non-personal object configuration data is written to the `remote_course_settings` table (via `ilRemoteObjectBase::doCreate` and `doUpdate`):

- `obj_id` — internal ILIAS object identifier
- `local_information` — optional local annotation text for the object
- `remote_link` — the URL pointing to the remote course at the external institution
- `mid` — the member ID (MID) of the sending ECS participant institution (institutional identifier, not a user identifier)
- `organization` — the name of the providing institution as received from the ECS community
- `availability_type` — activation status (0 = offline, 1 = unlimited, 2 = time-limited)
- `r_start`, `r_end` — Unix timestamps for time-limited availability windows

Additionally, ECS import tracking data is written to the `ecs_import` table (managed by `ilECSImport::save`): `obj_id`, `mid`, `econtent_id`, `sub_id`, `server_id`, `content_id`. This data identifies the remote content source and does not contain personal user data.

## Data being presented

The following non-personal data is displayed in the ILIAS repository to persons with "visible" or "read" permission on the RemoteCourse object:

- Course title and description (as received from the remote ECS server)
- Organization name of the providing institution
- Availability status (online/offline or time period)
- A link to the remote course URL

Persons with "write" permission additionally see the object in offline mode, where it is hidden from other users.

## Data being deleted

When a RemoteCourse object is permanently deleted (delete from trash), the following records are removed:

- The corresponding row in `remote_course_settings` (via `ilRemoteObjectBase::doDelete`)
- The corresponding row(s) in `ecs_import` (via `ilECSImportManager::_deleteByObjId`, called from `doDelete`)

No personal user data is deleted, as none is stored by this component.

## Data being exported

The RemoteCourse component does not implement any dedicated export functionality. No personal data is exported by this component.
