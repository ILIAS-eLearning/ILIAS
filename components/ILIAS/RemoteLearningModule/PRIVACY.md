# RemoteLearningModule Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The RemoteLearningModule component represents learning modules that originate from a remote ILIAS
instance and are shared into the local ILIAS installation via the ECS (Campus Connect) protocol.
Objects of type `rlm` are created and updated automatically when an ECS server pushes learning
module metadata; they cannot be created manually by users. The component stores only object-level
metadata (title, description, availability status, remote URL, and the originating institution)
in the local database — actual learning content remains on the remote system.

When a person with `read` permission clicks to open a remote learning module, the component
constructs a redirect URL that includes the current user's personal data as query parameters and
forwards the browser to the remote system. This data transfer to the remote ECS participant is
governed by the ECS server configuration and the participant trust settings, not by a local
opt-out mechanism.

## Integrated Components

The RemoteLearningModule component delegates all ECS communication and base object persistence
to the WebServices/ECS component (`ilRemoteObjectBase`,
`ilECSImportManager`, `ilECSUser`, `ilECSParticipantSetting`). Category rule mapping is handled
by `ilECSCategoryMapping` inside the same ECS component. No further ILIAS components that handle
personal data are used directly by this component.

## Data being stored

The component stores the following data in the `rlm_settings` database table (inherited via
`ilRemoteObjectBase`):

- **obj_id** (integer) — ILIAS internal object identifier, links the row to the ILIAS object tree.
- **remote_link** (text) — The URL of the learning module on the remote ECS participant system.
- **mid** (integer) — The ECS membership ID of the originating institution (not a personal user identifier).
- **organization** (text) — The name of the originating institution, derived from the ECS community
  reader; this is institutional metadata, not personal data.
- **local_information** (text) — Optional free-text annotation that a person with `write` permission
  may enter locally.
- **availability_type** (integer) — Online/offline flag synchronized from the ECS content (0 = offline,
  1 = online); not personal data.

None of the fields stored in `rlm_settings` directly identify an individual user. No `usr_id`,
email address, or other personal user data is persisted locally by this component.

## Data being presented

When a person with `read` permission views the info screen of a remote learning module, the
following non-personal metadata is displayed: title, description, availability status, and the
originating organization name (stored in `rlm_settings.organization`).

When a person with `read` permission follows the link to open the module, their personal data is
transmitted to the remote ECS participant system as URL query parameters by `ilECSUser::toGET()`.
The parameters sent are: `ecs_login` (login name or external account placeholder depending on
participant configuration), `ecs_firstname`, `ecs_lastname`, `ecs_email`, `ecs_institution`,
`ecs_uid_hash` (a pseudonymous identifier of the form `il_{inst_id}_usr_{user_id}`), and
optionally `ecs_external_account`. Whether and how the remote system processes or stores this data
is outside the scope of this component.

A person with `write` permission additionally sees the availability radio buttons (read-only,
controlled by ECS) in the edit form.

## Data being deleted

When a remote learning module object is deleted and removed from the trash (delete from trash),
the `doDelete()` method in `ilRemoteObjectBase` removes the corresponding row from `rlm_settings`
and calls `ilECSImportManager::_deleteByObjId()` to clean up the ECS import tracking record.
Because no personal user data is stored locally by this component, no personal data is deleted
in this step.

Remote learning module objects are typically created, updated, and deleted automatically in
response to ECS server events; a person with `write` permission may also delete the local
repository object manually, which triggers the same cleanup path.

## Data being exported

The RemoteLearningModule component does not provide its own data export. No personal data stored
by this component is included in ILIAS data exports, as the component stores no personal user data
locally. The transmission of user attributes to the remote system on link-follow (see "Data being
presented") is not an export in the ILIAS sense and is not logged locally by this component.
