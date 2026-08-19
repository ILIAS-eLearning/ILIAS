# ScormAicc Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The ScormAicc component handles SCORM 1.2 and AICC e-learning content packages in ILIAS. It tracks detailed learner interactions with SCORM content, recording completion status, scores, time spent, suspend data, and individual interaction records per user per SCO (Shareable Content Object). Tracking data collection and display of the tracking tab are subject to the global privacy setting "SAHS Protocol Data" (`enabledSahsProtocolData()`). If this setting is disabled, tracked data is still written during content playback but the tracking overview is not accessible to privileged users.

## Integrated Components

The ScormAicc component employs the following components that handle personal data — please consult their respective PRIVACY.md files:

- User — user account data (login, name, email, department) is read when displaying or exporting tracking data.
- [Certificate](../Certificate/PRIVACY.md) — the component integrates certificate generation; user-specific placeholder values (scores, completion dates) are passed to the certificate service.
- Tracking — read events (number of attempts, total time) are recorded via `ilChangeEvent::_recordReadEvent()`.

## Data being stored

Personal data is stored in two database tables:

**Table `scorm_tracking`** — one row per user, SCO, and CMI key-value pair:
- `user_id` — reference to the ILIAS user account.
- `sco_id` — identifier of the SCORM content item (SCO) within the package.
- `lvalue` / `rvalue` — CMI data model key and its value, covering fields such as `cmi.core.lesson_status`, `cmi.core.score.raw`, `cmi.core.score.max`, `cmi.core.total_time`, `cmi.core.suspend_data`, and interaction records.
- `c_timestamp` — timestamp of the last write for this key-value pair.

**Table `sahs_user`** — one row per user per SCORM/AICC learning module:
- `user_id` — reference to the ILIAS user account.
- `obj_id` — reference to the learning module object.
- `last_access` — timestamp of last access to the module.
- `last_visited` — identifier of the last visited SCO.
- `status` — aggregated completion/pass/fail status.
- `package_attempts` — number of attempts the user has started.
- `percentage_completed` — percentage of SCOs completed.
- `sco_total_time_sec` — total time spent in the module in seconds.
- `module_version` — version of the module used when the tracking record was created.
- `hash` / `hash_end` — a session security hash and its expiry timestamp used to authenticate API calls from the SCORM player.

## Data being presented

- **Learner view**: A user sees their own progress (lesson status, score, time) within the SCORM player during content playback.
- **Tracking overview** (only if `enabledSahsProtocolData()` is enabled): A person with "read_learning_progress" or "edit_learning_progress" permission can view a list of all tracked users showing last name, first name, last access date, number of attempts, and module version. They can also drill down to per-SCO tracking data (completion status, scores, time, interactions) for individual users.

## Data being deleted

- **On user account deletion**: `_removeTrackingDataForUser()` is called, which deletes all records for the user from both `scorm_tracking` and `sahs_user` across all modules.
- **Manual deletion by a person with "edit_learning_progress" permission**: `deleteTrackingDataOfUsers()` removes all `scorm_tracking` and `sahs_user` records for selected users within a specific module and triggers a learning progress status update.
- **On learning module deletion**: All associated `scorm_tracking` and `sahs_user` records are removed together with the object.

There is no automatic time-based expiry of tracking data.

## Data being exported

A person with "edit_learning_progress" permission can export tracking data as a CSV file. The export includes learning module ID, title, module version, last access date, overall status, number of attempts, percentage of SCOs completed, and total time in seconds.

Whether personal identifiers are included in the export depends on the global privacy setting `enabledExportSCORM()`:
- If disabled (default): only the numeric `user_id` is included.
- If enabled: login name, full name (lastname, firstname), email address, and department are included in every exported row.

Multiple export types are available, each offering a different view of the CMI data: core summary, raw CMI values, interactions, objectives, and success status.
