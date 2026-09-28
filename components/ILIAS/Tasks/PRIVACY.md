# Tasks Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Tasks component provides a derived tasks service that aggregates and displays pending tasks for the currently logged-in user. It acts as a framework collecting task entries from other ILIAS components (Exercise, Forum, Survey, Blog) based on the current user's ID and presents them in a unified list view. The component itself does not store any personal data; all task data originates from the integrated source components.

## Integrated Components

The Tasks component integrates with the following ILIAS components, each of which may handle personal data independently:

- [Exercise](../../Exercise/PRIVACY.md)
- [Forum](../../Forum/PRIVACY.md)
- [Survey](../../Survey/PRIVACY.md)
- [Blog](../../Blog/PRIVACY.md)

## Data being stored

The Tasks component does not store any personal data.

## Data being presented

The Tasks component presents the following information exclusively to the currently logged-in user on the Personal Desktop block and the derived tasks list page:

- **Task title** – the title of a pending task, derived from the source component (e.g., an exercise submission deadline or a forum posting obligation).
- **Object title and type** – the name and type (e.g., course, exercise, blog) of the ILIAS object the task is linked to, looked up by the user's repository reference ID or workspace ID.
- **Task start date** – the starting time of the task, if provided by the source component.
- **Task deadline** – the deadline of the task, if provided by the source component.

All presented data is user-specific: it is fetched at runtime using the current user's ID (`usr_id`) and shown only to that user. No other users or administrators see another user's task list through this component.

## Data being deleted

The Tasks component does not delete any personal data. Deletion of underlying task-relevant data is handled by the respective source components (Exercise, Forum, Survey, Blog).

## Data being exported

The Tasks component does not export any personal data.
