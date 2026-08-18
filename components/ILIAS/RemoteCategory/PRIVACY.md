# RemoteCategory Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The RemoteCategory component represents a remote category object imported from an external campus system via the ECS (E-Learning Community Server / CampusConnect) protocol. It is a thin object type (`rcat`) that stores a reference to a category hosted on a remote institution's system. When a user with "read" permission follows the remote link, the component generates an authenticated URL that transmits personal data about the current user to the external ECS server for single sign-on purposes. The component itself does not manage any learning activities — it acts purely as a navigational pointer to an external resource.

## Integrated Components

The RemoteCategory component delegates all database operations and ECS communication to the ECS WebServices base infrastructure (`ilRemoteObjectBase`, `ilECSUser`, `ilECSImportManager`, `ilECSParticipantSetting`). That component handles personal data transmission to external systems and manages ECS import records.

## Data being stored

The RemoteCategory component stores records in the `rcat_settings` database table via its parent class `ilRemoteObjectBase`. The following fields are persisted per remote category object:

- `obj_id` (integer): Internal ILIAS object identifier — links the record to the repository object.
- `local_information` (text): Optional free-text annotation that a person with "write" permission may add locally.
- `remote_link` (text): URL pointing to the category on the originating ECS institution's system.
- `mid` (integer): ECS participant ID of the originating institution — an institutional identifier, not a personal one.
- `organization` (text): Display name of the originating institution, derived from the ECS community directory.

None of the fields stored in `rcat_settings` directly identify individual natural persons. The `organization` field contains an institution name, not a personal name.

## Data being presented

The organization name stored in `organization` is displayed as a property in the ILIAS repository list view next to the remote category entry, visible to all users who can see the object in the repository. No personal data of ILIAS users is displayed within the component's own views. When a person with "read" permission clicks the link to open the remote category, the component constructs an authenticated redirect URL that includes the user's `login`, `firstname`, `lastname`, `email`, `institution`, and a hashed user identifier (`uid_hash` in the form `il_<inst_id>_usr_<user_id>`) as URL query parameters (e.g. `ecs_login`, `ecs_firstname`, `ecs_lastname`, `ecs_email`, `ecs_institution`). This data is transmitted to and processed by the external ECS server — it does not remain within the ILIAS instance's own presentation layer.

## Data being deleted

When a remote category object is deleted, the `doDelete()` method in `ilRemoteObjectBase` removes the corresponding ECS import record (via `ilECSImportManager::_deleteByObjId()`) and deletes the row from `rcat_settings` identified by `obj_id`. Deletion is performed by a person with "delete" permission on the repository object, or when the object is permanently removed from the trash (delete from trash). No additional user-related data is cleaned up by the component itself, as it does not store personal data of ILIAS users.

## Data being exported

The RemoteCategory component does not provide an ILIAS export function. The remote link stored in `remote_link` points to content hosted on an external system; that external system controls its own data export. No personal data of ILIAS users is exported by this component.
