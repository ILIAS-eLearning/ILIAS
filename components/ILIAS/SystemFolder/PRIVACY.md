# SystemFolder Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The SystemFolder component represents the top-level administration container object (type `adm`) in the ILIAS repository structure. It provides the entry point to the ILIAS administration area and displays an informational message to persons navigating to it. The component retrieves and stores the header title of the system folder (including multilingual translations) via the `object_translation` database table, but this data is purely system configuration and contains no personal data. The current user's language preference is read at runtime only to display the header title in the correct language; it is not stored by this component.

## Integrated Components

- The SystemFolder component uses the [AccessControl](../AccessControl/PRIVACY.md) component to check whether a person has any administrative read permission before granting access to the administration area.

## Data being stored

The SystemFolder component does not store any personal data. The `object_translation` table is used exclusively to store the system folder's title and description in multiple languages (`title`, `description`, `lang_code`, `lang_default`), which are system configuration values, not personal data.

## Data being presented

The SystemFolder component does not present any personal data. It displays a static informational message and the configured system folder title to persons with administrative read permission ("visible" permission in the administration context).

## Data being deleted

The SystemFolder component does not delete any personal data. When header title translations are managed, entries are removed from the `object_translation` table for the system folder object — this affects only system configuration data, not personal data.

## Data being exported

The SystemFolder component does not export any personal data.
