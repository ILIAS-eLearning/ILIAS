# Multilingualism Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Multilingualism component provides utilities for multi-language support in ILIAS content objects. It supports three variants: objects may have no translations at all, translations for title and description only, or translations for page-editing content as well.

When content translation is active, a master language must be set and is recorded in the `obj_content_master_lng` table. A fallback language may also be configured: if content is unavailable in a user's language, the fallback language is used instead. Titles and descriptions for all translations are stored in the `object_translation` table (with the master language flagged by the `lang_default` field), and the `object_data` table always holds the title and description in the current default language (fallback if set, master otherwise).

The component itself contains no executable PHP code and therefore performs no direct database writes. All database interactions happen through the ObjectTranslation classes in the ILIASObject component, on which Multilingualism has a high dependency.

## Integrated Components

- ILIASObject — The Multilingualism component relies heavily on the ILIASObject component, in particular its ObjectTranslation classes, which perform the actual database operations for translation data.

## Data being stored

The Multilingualism component does not store any personal data. The tables it interacts with (`object_translation`, `obj_content_master_lng`, `object_data`) contain content-object metadata (titles, descriptions, and language settings) rather than personal user data.

## Data being presented

The Multilingualism component does not present any personal data.

## Data being deleted

The Multilingualism component does not delete any personal data.

## Data being exported

The Multilingualism component does not export any personal data.
