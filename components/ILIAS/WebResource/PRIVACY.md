# WebResource Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The WebResource component allows users to create and manage collections of web links (URLs) within the ILIAS repository and personal workspace. Links can point to external websites or to internal ILIAS objects. A web resource object can contain a single link (which opens directly) or multiple links displayed as a list.

A privacy-relevant feature is the **dynamic parameters** system. When enabled in the ILIAS administration (setting `links_dynamic`), persons with the "Write" permission can attach dynamic parameters to individual links. These parameters automatically append personal data of the **current user** (user ID, login name, or matriculation number) to the link URL when the link is called. This means personal data is transmitted to the target URL each time a user with the "Read" permission clicks on such a link. The dynamic parameters feature is **disabled by default** and must be explicitly enabled by a person with the "Write" permission on the WebResource Administration.

## Integrated Components

- The WebResource component employs the following components, please consult the respective PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) — manages permissions for viewing, editing, and administering web resource objects.
    - [MetaData](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/PRIVACY.md) — stores metadata (title, description, keywords) for web resource objects.
    - [InfoScreen](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/InfoScreen/PRIVACY.md) — displays the info screen for web resource objects, including metadata sections.
    - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/PRIVACY.md) — private notes are enabled on the info screen of web resource objects.
    - [Export](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Export/PRIVACY.md) — provides XML export functionality for web resource objects.
    - Container — the Container component's sorting service is used for manual link ordering within web resource lists.
    - Tracking — read events are recorded via `ilChangeEvent::_recordReadEvent` when a user calls a link, storing a read access event for the user.
    - [PersonalWorkspace](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/PersonalWorkspace/PRIVACY.md) — web resource objects can be created in a user's personal workspace.
    - ILIASObject — the Object service stores general object data (creator, creation date) for web resource objects.

## Data being stored

The WebResource component stores link data in its own database tables (`webr_items`, `webr_lists`, `webr_params`). These tables do **not** contain user IDs or other directly personal data. The stored data consists of link titles, descriptions, URLs, active status, creation dates, last update timestamps, and parameter configurations.

However, the component stores and processes personal data in the following ways:

- **Dynamic parameter configuration**: When the dynamic parameters feature is enabled, the `webr_params` table stores a **parameter type** for each configured dynamic parameter. The parameter type determines which personal data element (user ID, login name, or matriculation number) is appended to a link URL. The parameter configuration itself does not contain personal data, but it controls the transmission of personal data at runtime.
- **Read event records**: When a user clicks a link, the component calls `ilChangeEvent::_recordReadEvent`, which records the **user ID** together with the object and reference IDs. This data is stored by the Tracking component.

## Data being presented

- **Each user** with the "Read" permission can view the list of web links (titles, descriptions, and URLs). No personal data of other users is presented.
- **Each user** who clicks a link with dynamic parameters configured will have their own personal data (depending on configuration: **user ID**, **login name**, or **matriculation number**) appended to the URL as a query parameter. This data is transmitted to the target URL and is visible in the browser's address bar.
- **Persons with the "Write" permission** can:
    - view and edit all links, including their dynamic parameter configurations. The parameter info display shows the parameter name and type (e.g., `param_name=USER_ID`, `param_name=LOGIN`, `param_name=MATRICULATION`) but does not show actual user data values.
    - access the settings, metadata, and export tabs.
    - manage link sorting and list settings.
- **Persons with the "Write" permission on the WebResource Administration** can enable or disable the dynamic parameters feature globally.

## Data being deleted

- **When individual links are deleted** by a person with the "Write" permission: the link record and all its associated dynamic parameters are deleted from the `webr_items` and `webr_params` tables.
- **When the web resource object is moved to trash**: the object becomes inaccessible but the link data in `webr_items`, `webr_lists`, and `webr_params` remains in the database.
- **When the web resource object is deleted from trash**: the `ilObjLinkResource::delete()` method deletes all items, all parameters, the list record, and the associated metadata.
- **When a user account is deleted**: the WebResource component does not store user IDs in its own tables, so no component-specific data needs to be cleaned up. Read event records stored by the Tracking component are handled by that component's own deletion routines.
- Dynamic parameter configurations persist independently of user accounts, as they describe a data transmission rule rather than storing personal data of a specific user.

## Data being exported

- The **XML export** (via `ilWebResourceExporter`) exports the web resource structure including link titles, descriptions, targets, active status, sorting settings, and dynamic parameter configurations (parameter name and type). No personal user data is included in the XML export.
- The **HTML export** (via the `exportHTML` action) generates a simple HTML page listing all active links with their titles, descriptions, creation dates, and last update timestamps. No personal user data is included.
- Dynamic parameter types (userId, userName, matriculation) are exported as part of the XML structure, documenting that the link is configured to transmit personal data — but no actual personal data values are included in the export.
