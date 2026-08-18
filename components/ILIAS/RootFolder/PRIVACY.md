# RootFolder Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The RootFolder component represents the top-level container of the ILIAS repository tree (object type `root`). It is a singleton object that cannot be deleted — attempting to call `delete()` on it throws an `ilException`. The component manages multilingual translations of the root folder's title and description, and provides the main repository entry point for all users. It extends the general container infrastructure (`ilContainer`, `ilContainerGUI`) and delegates permission management, page editing, trash handling, and content styling to dedicated sub-components.

## Integrated Components

The RootFolder component does not integrate any ILIAS component that handles personal data on its own. It delegates to general container infrastructure components (permission management, page editing, trash, content style) which are not specific to this component.

## Data being stored

The RootFolder component does not store any personal data.

The only database writes performed by this component target the `object_translation` table (`INSERT INTO object_translation`, `DELETE FROM object_translation`). This table stores multilingual title (`title`), description (`description`), language code (`lang_code`), and default language flag (`lang_default`) for the root folder object itself. None of these fields contain personal data.

## Data being presented

The RootFolder component does not present any personal data.

The component renders the top-level repository view, which displays repository objects (courses, categories, etc.) as container items. No personal data such as user names, email addresses, or login names is displayed by this component directly.

## Data being deleted

The RootFolder component does not delete any personal data.

Translations stored in the `object_translation` table can be removed individually via `deleteTranslation()` or entirely via `removeTranslations()` by a person with `write` permission on the root folder. These operations affect only the title and description strings, not personal data.

The root folder object itself cannot be deleted — `delete()` unconditionally throws an exception.

## Data being exported

The RootFolder component does not export any personal data.
