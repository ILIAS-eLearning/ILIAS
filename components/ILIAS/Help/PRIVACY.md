# Help Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

The Help component employs the following services, please consult the respective privacy files:

- The **Object** service stores the account which created the object as its owner and creation and update timestamps for the object.
- [Learning Module](../LearningModule/PRIVACY.md) stores the content of the help modules.
- [AccessControl](../AccessControl/PRIVACY.md) handles permissions for accessing help settings and guided tours.
- [COPage](../COPage/PRIVACY.md) handles the page content and history for guided tour steps.
- [Global Screen](../GlobalScreen/docs/PRIVACY.md) is used to integrate help elements into the main user interface.

## General Information

The Help component provides context-sensitive help and guided tours to users. It allows administrators to map learning module chapters to specific screen IDs in ILIAS and create guided tours that walk users through the interface.

## Configuration

- **Global**: Administrators can activate or deactivate the help system and individual help modules.
- **User**: Users can choose to hide or show help tooltips in their personal settings.

## Data being stored

The Help component stores the following personal data:

- **user ID**: To track which guided tours a user has finished, the component stores the **user ID** and the corresponding **tour ID** in the table `help_gt_user_finished`.
- **User preference**: The component stores the user's choice to hide help tooltips (`hide_help_tt`) in the user's preferences.

## Data being presented

- **User**: Users see help content and tooltips based on their settings.
- **Administrator**: Administrators can see and manage guided tours and help mappings. The component does not currently present individual user completion statuses of guided tours to other users or administrators.

## Data being deleted

- When a **Guided Tour** is reset by an administrator, all completion records for that tour are removed from the `help_gt_user_finished` table.
- When a **User Account** is deleted, the preference `hide_help_tt` is removed along with the user account and all completion records for that user are removed from the `help_gt_user_finished` table.


## Data being exported

- The Help component provides an export for help modules and guided tours.
- These exports include the configuration and content of the help system but **do not include** any personal data like user completion statuses or individual user preferences.