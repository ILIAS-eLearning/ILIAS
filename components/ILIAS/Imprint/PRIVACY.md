# Imprint Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Imprint component provides a single, installation-wide legal notice page (also referred to as "Legal Notice" or "Impressum"). It is accessible through the ILIAS footer without authentication, meaning any visitor -- whether logged in or not -- can view the imprint content when it is activated.

The imprint page uses the ILIAS page editor (COPage) for content management. There is exactly one imprint page per ILIAS installation (page ID 1, parent type `impr`). Since the imprint is a page object, all personal data associated with its creation and editing is managed by the COPage component, not by the Imprint component itself.

## Integrated Components

- The Imprint component employs the following components, please consult the respective PRIVACY.md files:
    - [COPage](../COPage/PRIVACY.md) -- the page editor manages all content storage, including page history, author tracking (creator user ID, last change user ID), and page versioning for the imprint page.
    - [AccessControl](../AccessControl/PRIVACY.md) -- manages permissions for editing the imprint page via the RBAC system (`ilPermissionGUI`).
    - ILIASObject -- provides the base object framework (`ilObject2`, `ilObject2GUI`) for the legal notice administration object.

## Data being stored

The Imprint component does not store personal data in its own database tables. All data storage is delegated to the COPage component, which stores the following for the imprint page in the `page_object` table:

- **User ID of the page creator**: stored as `create_user` when the imprint page is first created.
- **User ID of the last editor**: stored as `last_change_user` each time the imprint page is saved.
- **Page history entries**: each edit creates a history record including the **user ID** of the editor and a **timestamp**, stored in the `page_history` table.

For details on how this data is handled, see the [COPage PRIVACY.md](../COPage/PRIVACY.md).

## Data being presented

- **Any visitor** (including unauthenticated users) can view the imprint page content when it is activated. The content itself is static text entered by persons with editing permissions and does not display system-managed personal data of other users.
- **Persons with the "Write" permission** on the Legal Notice administration object can edit the imprint page. Through the page editor, they have access to the **page history**, which includes **timestamps** and the **user IDs** of persons who made previous edits (as documented by COPage).
- If the imprint page is **not activated**, a preview is shown only to persons with the "Write" permission, along with a notice that the imprint is inactive.

## Data being deleted

- The Imprint component does not provide its own deletion mechanism for personal data. The imprint is a system object that exists exactly once per installation and is not intended to be deleted.
- **Page history** entries for the imprint page are managed by the COPage component. For details on how page history and author information are handled upon user account deletion, see the [COPage PRIVACY.md](../COPage/PRIVACY.md).
- **When a user account is deleted**, the COPage component retains the page content but no longer displays the account name, first name, or last name of the deleted user in page history or authorship information.

## Data being exported

- The Imprint component does not provide any export functionality for the imprint page.
- No personal data is exported through this component.
