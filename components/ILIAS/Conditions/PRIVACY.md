# Conditions Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any
> missing or incorrect information via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).**

## General Information

The Conditions component manages preconditions (also called "access conditions") that control when
users may access specific ILIAS objects. A condition links a **trigger object** (e.g., a test, a
course) to a **target object** (e.g., a learning module, a folder) through an **operator** (e.g.,
"passed", "finished", "learning progress"). When a user fulfils all required conditions on the
trigger objects, access to the target object is granted.

Conditions are purely object-to-object relationships. The component does **not** store any personal
user data itself. At runtime, it reads the current user's ID to evaluate whether conditions are met
(e.g., by querying learning progress status from the Tracking component), but this evaluation is
transient and does not result in any data being written to the `conditions` table.

Container objects such as courses may take over condition control for their children through the
"Container as Condition Controller" interface (`ilConditionControllerInterface`). In this case,
conditions are provided dynamically by the container rather than being persisted in the database.

## Integrated Components

- The Conditions component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) — manages permissions for creating, editing,
      and deleting preconditions on repository objects.
    - [ILIASObject] — the Object service calls condition deletion methods when an object is removed
      from trash. The Object Activation GUI embeds the Conditions GUI as a sub-tab.
    - Tracking — the Conditions component checks learning progress status via `ilLPStatus` to
      evaluate conditions with the "learning progress" operator. Learning progress is only available
      as a condition operator when the Tracking service is enabled.
    - [Course](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Course/PRIVACY.md) — the Conditions component references course objectives (`ilCourseObjective`) for the
      "result range percentage" operator. Course groupings (`ilObjCourseGrouping`) provide the
      "not member" condition operator.

## Data being stored

The Conditions component does **not** store personal user data. The `conditions` database table
contains exclusively object-to-object relationships with the following columns:

- **condition_id**, **target_ref_id**, **target_obj_id**, **target_type** (target object identifiers)
- **trigger_ref_id**, **trigger_obj_id**, **trigger_type** (trigger object identifiers)
- **operator** and **value** (the condition logic, e.g., "passed", "result_range_percentage")
- **ref_handling**, **obligatory**, **num_obligatory**, **hidden_status** (configuration flags)

None of these columns contain user IDs, user names, timestamps attributable to individual users, or
any other personal data. The table documents which objects depend on which other objects, not which
users have fulfilled conditions.

## Data being presented

The Conditions component does **not** present personal user data.

- **Persons with the "Write" permission** on a target object can view and manage the list of
  preconditions for that object. The precondition list displays trigger object titles, condition
  operators, and obligatory status — no user-related information is shown.
- **Each user** may be affected by conditions at runtime: if a user has not fulfilled the
  conditions on a target object, the object may be hidden or shown as inaccessible. The evaluation
  result (access granted or denied) is computed transiently and is not stored.

## Data being deleted

Since the Conditions component stores only object-to-object relationships and no personal data,
deletion pertains to condition records rather than personal data:

- **When a person with the "Write" permission deletes a precondition**: the corresponding row in
  the `conditions` table is removed.
- **When a repository object is removed from trash**: `ilObject::delete()` calls
  `ilConditionHandler::delete()`, which removes all condition rows where the deleted object appears
  as either a trigger or a target.
- **When a user account is deleted**: no condition data needs to be removed, because the
  `conditions` table does not contain any user-identifying information.

## Data being exported

- The Conditions component provides an XML-based export (`ilConditionsExporter`) that is included
  when a parent object (e.g., a course) is exported. The exported XML contains object IDs, object
  types, condition operators, values, and configuration flags (hidden status, obligatory settings).
  No personal user data is included in the export.
- There is no standalone export of condition data independent of a parent object export.
