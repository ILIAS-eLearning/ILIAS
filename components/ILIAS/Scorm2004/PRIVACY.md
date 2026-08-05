# Scorm2004 Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Scorm2004 component implements the SCORM 2004 3rd Edition runtime environment within ILIAS.
It handles the delivery and tracking of SCORM 2004 learning content packages. When a user launches
a SCORM 2004 module, the component creates and maintains detailed tracking data according to the
SCORM 2004 data model, including completion status, scores, interaction responses, and time spent.

The Scorm2004 component is tightly coupled with the ScormAicc component, which provides the parent
class for the learning module GUI and shared tracking functionality. The `sahs_user` table, which
stores per-user session data, is shared between both SCORM 1.2 and SCORM 2004 modules.

Whether personal data (login, name, email, department) is included in tracking data exports depends
on the **SCORM privacy setting** (`ilPrivacySettings::enabledExportSCORM()`). When this setting is
disabled, only the user ID is shown in exports; when enabled, login, full name, email, and department
are included. Similarly, the **SAHS protocol data setting** (`ilPrivacySettings::enabledSahsProtocolData()`)
controls whether the "modify tracking data" sub-tab is accessible at all.

The SCORM 2004 runtime delivers the **user's name** (first and last name) to the SCORM content package
via the SCORM data model element `cmi.learner_name`. This is required by the SCORM specification and
happens automatically whenever a user launches a SCORM 2004 module.

## Integrated Components

- The Scorm2004 component employs the following components, please consult the respective
  PRIVACY.md files:
    - ScormAicc — provides the parent learning module class, shared tracking item export logic,
      and the `sahs_user` table management.
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) — manages permissions for accessing the
      learning module and its tracking data.
    - [MetaData](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/PRIVACY.md) — metadata editing is available for SCORM 2004 learning
      modules via `ilObjectMetaDataGUI`.
    - [InfoScreen](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/InfoScreen/PRIVACY.md) — the info screen displays object information to users.
    - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/PRIVACY.md) — users can add notes and comments to the learning module.
    - [Certificate]([../Certificate/PRIVACY.md](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Certificate/PRIVACY.md)) — certificates can be configured for SCORM 2004
      learning modules via `ilCertificateGUI`.
    - [LTIProvider](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/LTIProvider/PRIVACY.md) — LTI provider settings can be configured via
      `ilLTIProviderObjectSettingGUI`. Score outcomes are communicated to LTI consumers when a
      single-SCO module reports a score.
    - Tracking — the component updates learning progress status via `ilLPStatusWrapper` and
      syncs read events via `ilChangeEvent`. Tracking does not have a PRIVACY.md yet.

## Data being stored

The Scorm2004 component stores extensive personal tracking data across multiple database tables:

- **User ID and session data** (`sahs_user` table): When a user launches a SCORM 2004 module,
  a record is created linking the **user ID** to the **object ID**. This record stores
  **package attempts** (number of times the user launched the module), **module version**,
  **last access timestamp**, **last visited SCO**, **total time** (in seconds, both from LMS
  and from SCO reports), **percentage completed**, **overall status**, and a **session hash**
  with expiry timestamp for authentication during the SCORM session.

- **CMI node tracking data** (`cmi_node` table): For each SCO (Shareable Content Object) a user
  interacts with, a detailed tracking record is stored containing the **user ID**, **completion
  status**, **success status**, **score** (raw, min, max, scaled), **total time**, **session time**,
  **progress measure**, **suspend data**, **launch data**, **location** (bookmark within the SCO),
  **user's name** (first and last name, stored as `cmi.learner_name`), **entry mode**, **credit setting**, **audio preferences**,
  and **timestamps** for creation and modification.

- **CMI interaction data** (`cmi_interaction` table): When the SCORM content reports user
  interactions (e.g., quiz responses), each interaction is stored with its **description**,
  **user response**, **result**, **type**, **weighting**, **latency**, and **timestamp**,
  linked to the user via the parent `cmi_node`.

- **CMI objective data** (`cmi_objective` table): Objective-level tracking data is stored per
  user per SCO, including **completion status**, **success status**, **score** (raw, min, max,
  scaled), and **progress measure**.

- **CMI comment data** (`cmi_comment` table): Comments entered by the user within the SCORM
  content are stored with a **comment text**, **location**, **timestamp**, and whether the
  source is the LMS.

- **CMI correct response data** (`cmi_correct_response` table): Expected correct response
  patterns for interactions are stored, linked to the user via the parent interaction and node.

- **Global objectives** (`cmi_gobjective` table): Cross-SCO objective data is stored per
  **user ID** and **scope ID** (package or system-wide), including **status**, **satisfied**,
  **measure**, **score** (raw, min, max), **completion status**, and **progress measure**.

- **Suspend data** (`cp_suspend` table): When a user suspends a SCORM session, the full ADL
  activity tree state is stored per **user ID** and **object ID** as a serialized data blob,
  enabling session resumption.

- **Shared data** (`adl_shared_data` table): When the SCORM package uses shared global data
  between SCOs, data stores are created per **user ID** and **learning module ID**.

- **Custom runtime data** (`cmi_custom` table): Custom SCORM runtime data is stored per
  **user ID** and **object ID**.

## Data being presented

- **Each user** can view their own SCORM module by launching it (requires the "Read" permission).
  During playback, the SCORM runtime delivers the user's own tracking data back to the SCORM
  content, including their scores, completion status, suspend data, and name (via `cmi.learner_name`).

- **Persons with the "Read Learning Progress" permission** on the SCORM learning module can:
    - view detailed tracking reports organized by user or by SCO, including **user ID** (or, if
      the SCORM export privacy setting is enabled: **login**, **full name**, **email**, and
      **department**), **completion status**, **success status**, **scores**, **total time**,
      **session time**, **interaction responses**, **objective results**, and **global objective
      status**.
    - filter and select individual users or SCOs for detailed reporting.

- **Persons with the "Edit Learning Progress" permission** on the SCORM learning module can:
    - access the "modify tracking data" sub-tab (only if the SAHS protocol data privacy setting
      is enabled), which displays a list of tracked users with their **full name** and **user ID**.
    - import and export tracking data.
    - delete tracking data for selected users.

## Data being deleted

- **When a person with the "Edit Learning Progress" permission deletes tracking data for
  selected users**: all CMI tracking records for those users in this specific SCORM module are
  deleted. This includes data from `cmi_node`, `cmi_interaction`, `cmi_objective`, `cmi_comment`,
  `cmi_correct_response`, `cmi_custom`, `sahs_user`, and `cmi_gobjective` for the user/package
  combination. Read events in the change event tracking are also deleted.

- **When the SCORM learning module object is deleted from trash**: all CMI tracking data for all
  users is deleted via `removeCMIDataForPackage`, which removes records from `cmi_node`,
  `cmi_interaction`, `cmi_objective`, `cmi_comment`, `cmi_correct_response`, `cmi_custom`,
  `sahs_user`, and `cmi_gobjective`. Additionally, the content package structure data (`cp_node`,
  `cp_tree`, `cp_package`, and all `cp_*` tables) is removed via `removeCPData`.
  Global-to-system objectives referencing this package are also cleaned up from `cmi_gobjective`
  where `scope_id = 0`.

- **When a user account is deleted**: `ilSCORM13Package::_removeTrackingDataForUser` is called,
  which delegates to `ilSCORM2004DeleteData::removeCMIDataForUser`. This removes all SCORM 2004
  tracking data for that user across all packages: `cmi_node` (and related `cmi_interaction`,
  `cmi_objective`, `cmi_comment`, `cmi_correct_response`), `cmi_custom`, `sahs_user`, and
  `cmi_gobjective`.

- **Residual data**: The `cp_suspend` and `adl_shared_data` tables are not explicitly cleaned up
  during user account deletion by the Scorm2004 deletion methods. Suspend data for the current
  session is deleted upon delivery to the client (in `getSuspendDataInit`), but abandoned sessions
  may leave orphaned records. The `adl_shared_data` table is cleaned per-session
  (`DELETE FROM adl_shared_data WHERE slm_id = %s AND user_id = %s`) during package re-initialization
  but is not cleaned during account deletion.

## Data being exported

- The Scorm2004 component provides multiple tracking data export views accessible to persons with
  the "Read Learning Progress" permission. These exports are displayed as on-screen tables (with
  download capability) and include:
    - **Core data export**: user identification, SCO titles, completion/success status, scores,
      time data, timestamps, suspend data, and launch data.
    - **Interaction data export**: user identification, SCO titles, interaction details including
      user responses, results, types, and timestamps.
    - **Objective data export**: user identification, SCO titles, objective completion and success
      status, scores, and progress measures.
    - **Global-to-system objectives export**: user identification, global objective status, measures,
      and scores.
    - **Success overview export**: user identification, overall status, scores, attempts, and time.
- Whether user-identifying information (login, name, email, department) is included in these
  exports depends on the **SCORM export privacy setting** (`ilPrivacySettings::enabledExportSCORM()`).
  When disabled, only the numeric user ID is shown.

## Summary

| Data | Stored in DB | Shown to user | Shown to "Read LP" | Shown to "Edit LP" | Exported | Deleted w/ user | Deleted w/ object |
|------|:------------:|:-------------:|:-------------------:|:------------------:|:--------:|:---------------:|:-----------------:|
| User ID + session data | `sahs_user` | no | yes | yes | yes | yes | yes |
| CMI node tracking | `cmi_node` | via SCORM runtime | yes | no | yes | yes | yes |
| Interaction responses | `cmi_interaction` | via SCORM runtime | yes | no | yes | yes | yes |
| Objective data | `cmi_objective` | via SCORM runtime | yes | no | yes | yes | yes |
| Comments | `cmi_comment` | via SCORM runtime | no | no | no | yes | yes |
| Correct responses | `cmi_correct_response` | via SCORM runtime | no | no | no | yes | yes |
| Global objectives | `cmi_gobjective` | via SCORM runtime | yes | no | yes | yes | yes |
| Suspend data | `cp_suspend` | via SCORM runtime | no | no | no | partial | yes |
| Shared data | `adl_shared_data` | via SCORM runtime | no | no | no | partial | yes |
| Custom data | `cmi_custom` | via SCORM runtime | no | no | no | yes | yes |
| User's name (`cmi.learner_name`) | `cmi_node` | via SCORM runtime | no | no | no | yes | yes |
