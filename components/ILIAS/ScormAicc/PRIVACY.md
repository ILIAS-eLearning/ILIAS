# ScormAicc Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The ScormAicc component provides SCORM 1.2 and AICC learning module functionality within ILIAS.
When a user launches a SCORM learning module, the component tracks detailed learning progress data
per user and per SCO (Shareable Content Object), including lesson status, scores, session times, and
interaction data. This tracking data is the core personal data handled by this component.

Several privacy-relevant settings control data handling in this component:

- **Privacy setting "SCORM Protocol Data"** (`enabledSahsProtocolData`): Controls whether the
  tracking data tab is shown to persons with learning progress permissions. When disabled, the
  tracking data view is not accessible, but tracking data is still collected during player sessions.
- **Privacy setting "SCORM Export"** (`enabledExportSCORM`): Controls whether personal identity
  data (login, full name, email, department) is included in tracking data exports. When disabled,
  only the numeric user ID is shown.
- **Student ID and Student Name settings**: Per learning module, the data passed to the SCORM
  content as `cmi.core.student_id` and `cmi.core.student_name` can be configured. Options include
  passing the numeric user ID or the login name, and appending the reference or object ID. The
  student name can be set to "lastname, firstname", "firstname lastname", the full name with
  salutation, or hidden entirely (setting value 9).

## Integrated Components

- The ScormAicc component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – manages permissions for editing, reading
      learning progress, and modifying tracking data.
    - [Certificate](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Certificate/PRIVACY.md) – handles certificate generation and download for
      users who complete a SCORM learning module.
    - [MetaData](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/PRIVACY.md) – stores metadata for SCORM learning module objects.
    - Tracking – the ScormAicc component integrates with the ILIAS learning progress (LP) system
      to update and report user completion status.
    - ILIASObject – the Object service stores the account which created the learning module object
      and its timestamps.
    - [LearningHistory](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/LearningHistory/PRIVACY.md) – SCORM completion events may appear in a user's learning history.
    - Verification – the SCORM Verification object allows users to create portfolio-embeddable
      proof of their SCORM certificate.

## Data being stored

- **User ID**: When a user launches a SCORM learning module, their **user ID** is
  stored in the `sahs_user` table (linked to the learning module's `obj_id`) and in the
  `scorm_tracking` table (linked to each SCO). This identifies which user accessed which content.
- **Package attempts**: The **number of times** a user has launched the learning module is stored
  in the `sahs_user` table (`package_attempts` column). This is incremented each time the user
  starts the module.
- **Last access timestamp**: The **date and time** of the user's most recent access to the learning
  module is stored in the `sahs_user` table (`last_access` column). This records when the user
  last interacted with the content.
- **Last visited SCO**: The **identifier of the last visited SCO** is stored in the `sahs_user`
  table (`last_visited` column). This enables resuming the learning module at the correct position.
- **Module version**: The **version of the learning module** at the time of the user's access is
  stored in the `sahs_user` table (`module_version` column). This documents which version of the
  content the user worked with.
- **Completion status**: The **overall completion status** (not attempted, in progress, completed,
  failed) is stored in the `sahs_user` table (`status` column). This records the user's progress
  through the learning module.
- **Percentage completed**: The **percentage of completed SCOs** is stored in the `sahs_user`
  table (`percentage_completed` column). This tracks partial completion across multiple SCOs.
- **Total time in seconds**: The **cumulative time** the user spent in the learning module is
  stored in the `sahs_user` table (`sco_total_time_sec` column). This measures total learning time.
- **Authentication hash**: A temporary **session hash** and **hash expiry timestamp** are stored
  in the `sahs_user` table (`hash`, `hash_end` columns). These authenticate SCORM API calls
  without requiring the full session and are automatically expired.
- **SCO-level tracking data**: For each SCO the user interacts with, the SCORM runtime stores
  key-value pairs in the `scorm_tracking` table, including:
    - `cmi.core.lesson_status` -- the per-SCO **completion status** (browsed, completed, failed,
      incomplete, not attempted, passed).
    - `cmi.core.score.raw`, `cmi.core.score.max`, `cmi.core.score.min` -- the user's **scores**.
    - `cmi.core.total_time`, `cmi.core.session_time` -- **time tracking** per SCO.
    - `cmi.core.entry`, `cmi.core.exit`, `cmi.core.credit` -- **session state** data.
    - `cmi.suspend_data`, `cmi.launch_data` -- **content-specific state data** for resuming.
    - `cmi.interactions.*` -- detailed **interaction data** (responses, results, latency, type)
      if the "Store Interactions" setting is enabled on the learning module.
    - `cmi.objectives.*` -- **learning objective** tracking data (status, scores) if the
      "Store Objectives" setting is enabled on the learning module.
- **Timestamp per tracking entry**: Each entry in `scorm_tracking` includes a **timestamp**
  (`c_timestamp`) recording when the data was last updated.

## Data being presented

- **Each user** can view their own learning progress status (completed, in progress, failed) via
  the learning progress screens and the info screen of the learning module.
- **Each user** can download their own certificate (if configured) via the SCORM presentation view.
- **Persons with the "Read Learning Progress" permission** can view detailed tracking data for
  all participants, including:
    - **user name** (last name, first name), **last access** time, number of **attempts**, and
      **module version** per user.
    - per-SCO tracking data in various report formats: core data (lesson status, scores, times),
      raw CMI data (all key-value pairs), interaction details, and objective details.
    - Whether personal identity data (login, email, department) is visible in these views depends
      on the **"SCORM Export" privacy setting** (`enabledExportSCORM`). When disabled, only the
      numeric user ID is shown instead of name, login, email, and department.
- **Persons with the "Edit Learning Progress" permission** can view the same tracking data as
  above and additionally:
    - access the modify tracking data view (only when the "SCORM Protocol Data" privacy setting
      is enabled).
    - import tracking data from CSV files.
    - delete tracking data for selected users.
    - export tracking data for selected or all users.
- **Persons with the "Write" permission** can access the SCORM editing interface and configure
  learning module settings, including the student ID and student name format passed to the SCORM
  content.
- **Tracking data tab visibility**: The tracking data tab is only shown to persons with the
  "Edit Learning Progress" or "Read Learning Progress" permission, and only when the **"SCORM
  Protocol Data" privacy setting** is enabled.

## Data being deleted

- **When tracking data for individual users is deleted** by a person with the "Edit Learning
  Progress" permission (via the "Modify Tracking" tab):
    - all entries in `scorm_tracking` for the selected users and this learning module are deleted.
    - the corresponding entries in `sahs_user` for the selected users and this learning module
      are deleted.
    - read events for the selected users are deleted via `ilChangeEvent::_deleteReadEventsForUsers`.
    - the learning progress status is recalculated.
- **When the SCORM learning module object is deleted** (removed from trash):
    - all SCORM tree nodes, items, resources, organizations, and manifests are deleted.
    - all entries in `scorm_tracking` for this learning module are deleted.
    - all entries in `sahs_user` for this learning module are deleted.
    - the `sahs_lm` configuration record is deleted.
    - the data directory on disk is removed.
    - metadata is deleted.
- **When a user account is deleted**:
    - all entries in `scorm_tracking` for that user across all learning modules are deleted
      (via `_removeTrackingDataForUser`).
    - all entries in `sahs_user` for that user across all learning modules are deleted.
- **Residual data**: No personal tracking data is known to survive the deletion scenarios above.
  However, learning progress data managed by the Tracking component is updated separately and
  follows its own deletion lifecycle.

## Data being exported

- **Tracking data export (CSV)**: Persons with the "Edit Learning Progress" permission can export
  tracking data for selected or all users as CSV. The export includes: learning module ID, title,
  version, last access, status, attempts, percentage completed, and total time in seconds.
  Whether the export includes personal identity columns (login, full name, email, department)
  depends on the **"SCORM Export" privacy setting**.
- **Detailed tracking report exports**: The tracking data views (core data, raw data, interactions,
  objectives, success overview) can be exported. These contain per-SCO tracking values linked to
  user IDs and, depending on the privacy setting, personal identity data.
- **SCORM module export (XML/ZIP)**: The SCORM learning module itself can be exported as an ILIAS
  export archive. This export contains the learning module configuration and SCORM package data
  but does **not** include user tracking data.
