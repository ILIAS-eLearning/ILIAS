# PrivacySecurity Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The PrivacySecurity component provides the administration interface and business logic for global privacy and security configuration in ILIAS. It manages two main areas:

- **Privacy settings** (`ilPrivacySettings`): Controls whether user data may be exported from courses, groups, learning sequences, and study programs; configures whether user confirmation is required before export; toggles visibility of last-access times for members in courses, groups, and learning sequences; enables or disables forum statistics and anonymous forum posting; controls RBAC log activation and retention age; and governs whether SCORM protocol data and comments may be exported.
- **Security settings** (`ilSecuritySettings`): Manages system-wide password policies (minimum/maximum length, maximum age, required character classes), the maximum number of failed login attempts, HTTPS enforcement, forced password change on first login, prevention of simultaneous logins, and admin role protection.
- **Export fields info** (`ilExportFieldsInfo`): Determines which user profile fields (e.g. `lastname`, `firstname`, `login`, user-defined fields, and course-defined fields) are selectable for member data exports from courses, groups, and study programs.

All settings are stored as key-value pairs via the ILIAS global settings mechanism (`ilSetting`), persisted in the shared `settings` table. The component itself does not create or maintain its own database tables. It acts as a configuration hub — the privacy-relevant behavior it enables (such as last-access time display or member export) is technically executed by other components (Course, Group, Forum, RBAC, etc.).

## Integrated Components

The PrivacySecurity component interacts with the following ILIAS components that may handle personal data:

- User — user profile fields (firstname, lastname, login, user-defined fields) are read to determine which fields are exportable via `ilExportFieldsInfo`.
- [Course](../Course/PRIVACY.md) — privacy settings control whether course member data export is enabled and whether user confirmation is required.
- [Group](../Group/PRIVACY.md) — same export and confirmation controls apply to groups.
- [LearningSequence](../LearningSequence/PRIVACY.md) — export and confirmation controls also apply to learning sequences.
- [StudyProgramme](../StudyProgramme/PRIVACY.md) — controls whether user data may be exported from study programs.
- [Forum](../Forum/PRIVACY.md) — settings control whether forum statistics (user activity data) are enabled and whether posting is anonymous.
- [AccessControl](../AccessControl/PRIVACY.md) — the component enables or disables the RBAC log and its retention period; RBAC log entries may contain user identifiers.

## Data being stored

The PrivacySecurity component does not store any personal data. All values it persists are administrative configuration flags and numeric thresholds (e.g. password minimum length, maximum login attempts, RBAC log retention age in months) written to the global `settings` table under key prefixes such as `ps_export_course`, `ps_password_min_length`, `ps_login_max_attempts`, `rbac_log`, `rbac_log_age`, and similar. None of these values identify or describe individual users.

## Data being presented

The component itself does not present personal data to end users. Its administration GUI (accessible to persons with `read` permission on the Privacy and Security administration object) displays only global configuration values.

However, the settings managed by this component govern whether personal data is visible elsewhere in ILIAS:

- When `ps_crs_access_times` / `ps_access_times` / `ps_lso_access_times` are enabled, the last-access time of individual members is shown to persons with `manage_members` permission in courses, groups, or learning sequences respectively.
- When `participants_list_courses` is enabled, a participant list (including names) is shown inside courses.
- When `enable_fora_statistics` is enabled, per-user posting statistics become visible in forums.
- When `enable_anonymous_fora` is enabled, author information is hidden from forum posts for regular participants.

## Data being deleted

The PrivacySecurity component does not store personal data and therefore has no deletion logic for personal data. Configuration settings in the `settings` table are overwritten when an administrator saves new values; they are not versioned or retained.

The RBAC log retention age (`rbac_log_age`, in months) configured here governs automatic expiry of RBAC log entries in the AccessControl component, but those entries are managed and deleted by that component, not by PrivacySecurity.

## Data being exported

The PrivacySecurity component does not export personal data itself. It provides the `ilExportFieldsInfo` class, which defines the set of user profile fields that other components (Course, Group, LearningSequence, StudyProgramme) may offer for member data export. Exportable fields include standard profile fields such as `lastname`, `firstname`, and `login`, as well as user-defined fields (`udf_*`) and course-defined fields (`odf_*`) that are marked as visible in the respective context.

Actual export of member data is performed by those consuming components and is restricted to persons who hold both the `export_member_data` permission on the Privacy and Security administration object and the `manage_members` permission on the respective course, group, learning sequence, or study program object.
