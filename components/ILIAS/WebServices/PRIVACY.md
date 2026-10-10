# WebServices Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The WebServices component provides several subcomponents for external communication and
integration: **ECS** (European Campus/Community System), **SOAP**, **REST**, **RPC** (Lucene
search), and **Curl** (HTTP client). Of these, the ECS subcomponent is the only one that stores
and processes personal data directly.

ECS enables cross-institutional content sharing and user authentication between ILIAS installations
connected via an ECS server. When a user follows a link to a remote object on another institution's
ILIAS, personal data (login, first name, last name, email, institution) is transmitted to that
remote platform. This transmission requires the user's explicit consent, which is recorded per
user, ECS server, and participant (MID).

ECS also creates and manages local user accounts for incoming users from other institutions. These
accounts are managed with the `ecs` auth mode and are assigned time-limited access.

The SOAP, REST, Curl, and RPC subcomponents are infrastructure services that do not store personal
data themselves. SOAP and REST provide API endpoints through which other components may transmit
personal data; however, the storage and processing in those cases is handled by the respective
calling component.

## Integrated Components

- The WebServices component employs the following components, please consult the respective
  PRIVACY.md files:
    - User – the ECS subcomponent creates and updates local user accounts
      for incoming ECS users. User attributes (login, name, email, institution) are written to user
      objects.
    - Authentication – ECS listens to `afterLogin` events and
      provides its own authentication provider (`ilAuthProviderECS`) for ECS-based single sign-on.
    - [AccessControl]([../AccessControl/PRIVACY.md](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md)) – ECS assigns incoming users to a configurable
      global role via RBAC. Permission checks on the ECS administration UI require "write" and
      "read" access.
    - [Group](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Group/PRIVACY.md) – ECS listens to Group membership events (addParticipant,
      deleteParticipant, addSubscriber, addToWaitingList) to update enrolment status for ECS users.
    - [Course](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Course/PRIVACY.md) – ECS listens to Course membership events (addParticipant, deleteParticipant,
      addSubscriber, addToWaitingList) to update enrolment status and extend account duration for
      ECS users.
    - [Mail](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Mail/PRIVACY.md) – ECS sends notification emails about newly created ECS user
      accounts to configured recipients and resets mail options for ECS-created accounts.
    - [AuthShibboleth](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AuthShibboleth/PRIVACY.md) – one of the authentication strategies for
      incoming ECS users when Shibboleth-based authentication is configured.
    - OpenIdConnect – one of the authentication strategies for
      incoming ECS users when OIDC-based authentication is configured.

## Data being stored

The following personal data is stored by the ECS subcomponent:

- **User ID (local)**: When an ECS user authenticates, their local ILIAS **user ID** is stored in
  the `ecs_remote_user` table together with the ECS server ID and member ID (MID), to map the local
  account to the remote user identity.
- **Remote user ID**: The **remote user identifier** (typically the login or external account from
  the originating institution) is stored in the `ecs_remote_user` table to enable user matching
  across institutions.
- **User consent record**: When a user consents to having their personal data transmitted to a
  remote platform, their **user ID**, the **ECS server ID**, and the **participant MID** are stored
  in the `ecs_user_consent` table. This records that the user has explicitly agreed to the data
  transfer.
- **Course member assignment UID**: When ECS course member synchronization maps remote users to
  local course memberships, the remote **user identifier** (`usr_id` column, stored as text) is
  recorded in the `ecs_course_assignments` table alongside the course object ID, CMS ID, server ID,
  and MID.
- **ECS-created user account attributes**: When a new local user account is created for an incoming
  ECS user via `ilAuthProviderECS`, the following personal data is written to the User component:
  **login** (prefixed with the institution abbreviation), **first name**, **last name**, **email**,
  and **institution**. The user's import ID is set to a hash of the form
  `il_[inst_id]_usr_[user_id]`.

## Data being presented

- **Each user** who accesses a remote ECS object for the first time is shown a **consent dialog**
  that lists the personal data fields to be transmitted: login, first name, last name, email, and
  institution. The user must explicitly consent before being redirected to the remote platform.
- **Each user** can see the remote objects (e.g., remote courses, remote files) that have been
  imported from other ECS participants, provided they have "read" or "visible" permission on the
  respective repository object.
- **Persons with the "write" permission** on the ECS administration object can:
    - view and configure all ECS server connections, including authentication settings and
      participant configurations.
    - view the community table showing all ECS participants with their organization names.
    - view and manage exported and imported content listings.
    - reset all user consents for a specific ECS participant (MID), removing consent records of
      all users who had consented for that participant.
    - configure which user data fields are mapped and how incoming users are authenticated.
- **Persons with the "read" permission** on the ECS administration object can:
    - view the list of configured ECS servers and their communities.
    - view participant details and export/import settings.
- **Persons with the "write" permission** on a remote object can:
    - view and edit local information (description, availability) for that remote object.
- **Persons with the "visible" permission** on a remote object can:
    - view the remote object's info screen, including its title, remote link, and organization.

## Data being deleted

- **When an ECS server configuration is deleted** by a person with the "write" permission on the
  ECS administration:
    - all associated data is deleted in a cascading manner: ECS CMS data
      (`ilECSCmsData::deleteByServerId`), community cache entries, data mapping settings, event
      queue entries, node mapping assignments, export records (`ecs_export`), and import records
      (`ecs_import`) for that server.
    - the ECS server record itself is removed from `ecs_server`.
    - **Residual data**: course member assignments in `ecs_course_assignments` for that server are
      **not** deleted during the cascade, even though `ilECSCourseMemberAssignment::deleteByServerId()`
      exists. These records remain in the database.
- **When user consents are reset** for a specific participant by a person with the "write"
  permission on the ECS administration:
    - all user consent records for that server ID and MID are deleted from `ecs_user_consent`
      via `ilECSParticipantConsents::delete()`.
- **When an individual user's consent is deleted** (e.g., via `ilECSUserConsents::delete()`):
    - all consent records for that user across all ECS participants are deleted from
      `ecs_user_consent`.
- **When a remote object is deleted**:
    - the remote object record is removed from its type-specific table (e.g., `remote_course_data`)
      via `ilRemoteObjectBase::doDelete()`.
    - the corresponding import record is deleted from `ecs_import` via
      `ilECSImportManager::_deleteByObjId()`.
- **When an ECS-created user account is deleted**:
    - the user account and its data are removed by the User component.
    - the `ecs_remote_user` entry for that user may remain as **residual data**, since there is no
      event listener that deletes remote user records when a user account is removed.
    - the `ecs_user_consent` entries for that user may remain as **residual data**, since user
      consent deletion is not automatically triggered by account deletion.
    - the `ecs_course_assignments` entries referencing that user's remote UID may remain as
      **residual data**.
- **Temporary REST files** are automatically cleaned up after one day by
  `ilRestFileStorage::deleteDeprecated()`, which is called when the REST server is initialized.

## Data being exported

- When a user accesses a remote ECS object, their personal data (**login**, **first name**,
  **last name**, **email**, **institution**, and a **UID hash**) is transmitted to the remote
  platform via GET parameters or JSON encoding through the ECS authentication mechanism
  (`ilECSUser::toGET()`, `ilECSUser::toJSON()`).
- The ECS object export functionality (`ilECSObjectSettings`, `ilECSExport`) exports ILIAS
  repository objects (courses, groups, files, etc.) to the ECS server, making them available to
  other connected institutions. The exported data contains object metadata, not personal user data.
- The SOAP and REST subcomponents provide API endpoints that can be used by external systems to
  retrieve data from ILIAS, potentially including personal data. The specifics depend on which
  SOAP/REST methods are called. Access to these APIs requires authentication.
- There is no dedicated file-based export of ECS-specific personal data (remote user mappings,
  consent records, course assignments).
