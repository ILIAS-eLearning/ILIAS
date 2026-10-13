# Language Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Language component manages all user interface translations in ILIAS. It handles the
installation, maintenance, import, export, and customization of language files. The component
stores translation strings in the database and provides them to all other ILIAS components.

The Language component itself stores very little personal data. The primary privacy-relevant
aspects are:

- When a language file is exported, the **full name and email address** of the exporting user
  are embedded in the file header.
- When a new language entry is created through the page translation interface, the **login name**
  of the creating user is stored as a remark in the database.
- An optional language usage logging feature (disabled by default, enabled via DEVMODE or
  `LANGUAGE_LOG` configuration) records which language variables are used, but this data contains
  no user identifiers.

## Integrated Components

- The Language component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – permission checks for language
      maintenance, translation, and administration are managed through RBAC.
    - User – the component reads the user's preferred language. When
      exporting language files, the current user's full name and email address are retrieved
      from the User component. When creating new language entries, the user's login is stored
      as a remark.
    - ILIASObject – language objects (`lng`) and the language folder (`lngf`) extend the base
      ILIAS object class and are stored in `object_data`.

## Data being stored

- **Login name of the creating user (as remark)**: When a new language entry is created through
  the page translation interface, the **login name** of the creating user is stored in the
  `remarks` column of the `lng_data` table via `ilObjLanguage::replaceLangEntry()`. This serves
  as an attribution of who contributed the translation.
- **Timestamp of local changes**: When a translation value is modified through the maintenance
  interface, a **timestamp** is stored in the `local_change` column of the `lng_data` table.
  This records when the modification was made, but does not store which user made the change.

## Data being presented

- **Each user** interacts with translated text strings provided by the Language component, but
  this does not constitute personal data presentation.
- **Persons with the "read" and "write" permissions** on the language folder can access the
  language maintenance interface, where they can view and edit translation values, import and
  export language files, and manage language settings. No personal data of other users is
  displayed in this interface.
- **Persons with the "write" permission** on the language folder can view the language overview
  table, which includes the number of users per installed language. This is an aggregate count
  and does not reveal individual user identities.

## Data being deleted

- **When a language is uninstalled** by a person with the "write" permission on the language
  folder: all translation data for that language is deleted from `lng_data` and `lng_modules`.
  Any remarks (including login names stored as attribution) in `lng_data` for that language are
  deleted. Additionally, all users who had selected the uninstalled language as their preferred
  language are reset to the system default language (via `resetUserLanguage()` in the `usr_pref`
  table).
- **When local changes are cleared** by a person with the "write" permission: translation data
  is flushed and re-inserted from the original language files, removing all local modifications
  including timestamps and remarks.
- **When a user account is deleted**: the Language component does not independently delete any
  data. However, any login names that were previously stored as remarks in `lng_data` remain as
  residual data, since these are plain text strings not linked by user ID.

## Data being exported

- **Language file export**: Persons with the "read" and "write" permissions on the language
  folder can export language files. The exported file header contains the **full name** and
  **email address** of the person performing the export (stored in the `created_by` parameter).
  The body of the exported file contains only translation strings (module, identifier, value)
  and does not include personal data.
- There is no XML-based or GDPR-specific personal data export within the Language component.
