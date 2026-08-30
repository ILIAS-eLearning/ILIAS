# Administration Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**


## General Information

The Administration component provides the top-level administration interface for ILIAS. It manages
global platform settings, installation contact information, system support contacts, and
accessibility support contacts. It also provides the Recovery Folder (trash) for deleted repository
objects and the Server Information view.

The component stores personal data primarily in the form of installation contact information and
references to user accounts designated as support contacts. These are not typical per-user data
records but rather installation-wide configuration values that may contain personal information
about the designated contact person or support users.

## Integrated Components

- The Administration component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – manages RBAC permissions for all
      administration nodes, controlling who can read and write settings.
    - User – the system support contacts and accessibility support contacts
      features reference user accounts by login name and resolve them to user IDs, email addresses,
      and public profiles.
    - [Mail](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Mail/PRIVACY.md) – the accessibility support contacts feature redirects to the
      internal mail form when a logged-in user reports an accessibility issue.
    - [GlobalScreen](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/GlobalScreen/PRIVACY.md) – provides the administration main menu bar and
      the footer entries for system support and accessibility support contacts.
    - [MetaData](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/PRIVACY.md) – the header title translations use the `object_translation`
      table, which is part of the general ILIAS object infrastructure.

## Data being stored

- **Contact first name** (`admin_firstname`): The first name of the designated installation contact
  person, stored in the `settings` table. Purpose: identifying the responsible contact for the
  ILIAS installation.
- **Contact last name** (`admin_lastname`): The last name of the designated installation contact
  person, stored in the `settings` table. Purpose: identifying the responsible contact for the
  ILIAS installation.
- **Contact title** (`admin_title`): The academic or professional title of the contact person,
  stored in the `settings` table. Purpose: proper addressing of the installation contact.
- **Contact position** (`admin_position`): The professional position of the contact person, stored
  in the `settings` table. Purpose: providing organizational context for the installation contact.
- **Contact institution** (`admin_institution`): The institution of the contact person, stored in
  the `settings` table. Purpose: identifying the organization responsible for the installation.
- **Contact street address** (`admin_street`): The street address of the contact person, stored in
  the `settings` table. Purpose: providing a postal address for the installation contact.
- **Contact zip code** (`admin_zipcode`): The zip code of the contact person, stored in the
  `settings` table. Purpose: providing a postal address for the installation contact.
- **Contact city** (`admin_city`): The city of the contact person, stored in the `settings` table.
  Purpose: providing a postal address for the installation contact.
- **Contact country** (`admin_country`): The country of the contact person, stored in the
  `settings` table. Purpose: providing a postal address for the installation contact.
- **Contact phone number** (`admin_phone`): The phone number of the contact person, stored in the
  `settings` table. Purpose: providing a telephone contact for the installation.
- **Contact email address** (`admin_email`): The email address of the contact person, stored in the
  `settings` table. Purpose: providing an email contact for the installation.
- **System support contact logins** (`adm_support_contacts`): A comma-separated list of ILIAS user
  **login names** referencing user accounts designated as system support contacts, stored in the
  `settings` table. Purpose: identifying users whose public profile and email are displayed to all
  users via the footer link.
- **Accessibility support contact logins** (`accessibility_support_contacts`): A comma-separated
  list of ILIAS user **login names** referencing user accounts designated as accessibility support
  contacts, stored in the `settings` table. Purpose: identifying users who can be contacted for
  accessibility issues via the footer link.

## Data being presented

- **Persons with the "read" permission** on the General Settings administration node can view:
    - all installation contact information (first name, last name, title, position, institution,
      street, zip code, city, country, phone, email) in the "Contact Information" tab.
    - the login names of the system support contacts and accessibility support contacts.
- **Persons with the "write" permission** on the General Settings administration node can modify all
  of the above data.
- **Each logged-in user** can see the system support contacts page via the footer. This page
  displays the **public profile** of each designated system support contact user (rendered through
  the User component's `PublicProfileGUI`).
- **Each logged-in user** who has the "internal_mail" permission on the mail object can use the
  footer link to report an accessibility issue, which opens an internal mail form pre-addressed to
  the designated accessibility support contacts.
- **Anonymous users** (not logged in) see a `mailto:` link to the email addresses of the system
  support contacts in the footer, if support contacts are configured.
- **Anonymous users** also see a `mailto:` link for accessibility support contacts if configured,
  when they do not have the "internal_mail" permission.
- **Persons with the "read" permission** on the Server Information administration node can view
  server technical data (host name, IP address, paths, software versions). This is system data, not
  personal data.

## Data being deleted

- **When the installation contact information is updated**: The previous values are overwritten in
  the `settings` table. There is no versioning or history of previous contact information. The old
  values are effectively deleted when new values are saved.
- **When a system support contact login is removed** from the comma-separated list by a person with
  the "write" permission: The user's login name is removed from the `adm_support_contacts` setting.
  The user account itself is not affected.
- **When an accessibility support contact login is removed** from the list by a person with the
  "write" permission: The user's login name is removed from the `accessibility_support_contacts`
  setting. The user account itself is not affected.
- **When a user account that is listed as a support contact is deleted**: The login name remains
  stored in the settings value, but the `ilSystemSupportContacts::getValidSupportContactIds()` and
  `ilAccessibilitySupportContacts::getValidSupportContactIds()` methods filter out invalid accounts,
  so the deleted user will no longer appear as a contact. The stale login name remains as residual
  data in the `settings` table until the contact list is manually updated.
- **The Administration component does not store per-user data** that would need to be deleted as
  part of a user account deletion workflow. The contact information fields describe the installation
  contact person, not individual user accounts.

## Data being exported

- There is no dedicated export functionality for the installation contact information or support
  contact lists within the Administration component.
- The ROADMAP.md mentions a potential future feature to export settings for an entire installation
  or for individual components, but this is not currently implemented.
