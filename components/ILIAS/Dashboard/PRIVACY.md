# Dashboard Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**


## General Information

The Dashboard is the central landing page after login. It aggregates personal data from various
ILIAS components and presents it in configurable blocks: Favourites (selected items), My Memberships,
Recommended Content, Learning Sequences, and Study Programmes. The Dashboard itself stores a small
amount of personal data (favourites and view preferences). All other personal data shown on the
Dashboard is managed by the respective integrated components.

The Favourites feature can be globally enabled or disabled via the Repository settings
(`rep_favourites`). When disabled, no favourites data presented. Similarly,
the "My Memberships" view can be globally toggled (`mmbr_my_crs_grp`). The Achievements area
(learning history, competences, learning progress, badges, certificates) is accessible from the
Dashboard but each sub-feature has its own activation setting.

Whether a user can switch between list and tile presentation depends on the "change_presentation"
permission on the Dashboard Settings administration object.

## Integrated Components

- The Dashboard component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) - manages permissions for Dashboard
      administration and the "change_presentation" permission.
    - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/PRIVACY.md) - the Dashboard can display personal notes and comments
      if enabled in the settings.
    - [News](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/News/PRIVACY.md) - the Dashboard side panel can display news items for
      the user.
    - [Mail](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Mail/PRIVACY.md) - the Dashboard side panel can display a mail block
      showing recent messages.
    - [Contact](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Contact/PRIVACY.md) - the Dashboard forwards to the contact/buddy list
      interface.
    - [COPage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/COPage/PRIVACY.md) - the Dashboard supports customizable page content
      per language via the COPage service.
    - Badge - badge information is accessible via the Achievements
      area on the Dashboard.
    - [Skill](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Skill/PRIVACY.md) - personal skills are accessible via the Achievements
      area on the Dashboard.
    - [Certificate](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Certificate/PRIVACY.md) - user certificates are accessible via the
      Achievements area on the Dashboard.
    - [StudyProgramme](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/StudyProgramme/PRIVACY.md) - study programme progress is shown
      in a dedicated Dashboard view.
    - [LearningSequence](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/LearningSequence/PRIVACY.md) - learning sequences are shown
      in a dedicated Dashboard view.
    - [Repository](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Repository/PRIVACY.md) - the Favourites feature references repository
      objects and checks access permissions through the Repository.
    - [Group](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Group/PRIVACY.md) - memberships in groups are listed in the "My Memberships"
      view. Users can unsubscribe from groups via the Dashboard.
    - Calendar - the Dashboard side panel can display a calendar block. Calendar has no
      PRIVACY.md yet.
    - User - the Dashboard accesses user preference data for sorting and presentation
      settings.
    - Tracking - the Dashboard preloads learning progress status for listed items.
      Tracking has no PRIVACY.md yet.
    - [LearningHistory](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/LearningHistory/PRIVACY.md) - the Achievements area provides access to the learning history.
    - Course - memberships in courses are listed in the "My Memberships" view. Users can
      unsubscribe from courses via the Dashboard. Course has no PRIVACY.md yet.

## Data being stored

- **User ID of the favourites owner**: When a user adds a repository object to their favourites,
  the **user ID** is stored together with the **reference ID** of the object and its **type** in
  the `desktop_item` table. This links each user to the objects they have marked as favourites.
- **Reference ID of the favourited object**: The **item_id** (reference ID) of the repository
  object is stored in the `desktop_item` table to identify which object the user has favourited.
- **Object type of the favourited object**: The **type** of the favourited repository object is
  stored in the `desktop_item` table to enable type-based filtering when retrieving favourites.
- **Presentation preference**: Each user's chosen presentation mode (list or tile) per Dashboard
  view is stored as a **user preference** with the key `pd_view_pres_{view}`.
- **Sorting preference**: Each user's chosen sorting mode per Dashboard view is stored as a
  **user preference** with the key `pd_order_items_{view}`.
- **Manual sort order data**: When a user selects manual sorting, the custom order of items is
  stored as a **user preference** with the key `pd_order_data_{view}_{mode}` in JSON format.

## Data being presented

- **Each user** can view their own Dashboard, including:
    - their own favourited repository objects (title, type icon, description, parent location).
    - their own course and group memberships (title, type, description, period dates).
    - their recommended content, learning sequences, and study programme items.
- **Each user** can manage their own favourites by adding or removing items. They can also
  unsubscribe from courses and groups directly from the "My Memberships" view, provided they
  have the "leave" permission on the respective object.
- **Persons with the "change_presentation" permission** on the Dashboard Settings administration
  object can switch between all enabled modes. Users without this permission see
  only the default presentation mode configured by a person with the "Edit Settings" permission.
- **Persons with the "read" permission** on the Dashboard Settings administration object can
  view the Dashboard configuration, including which views are enabled and their sorting and
  presentation defaults.
- **Persons with the "write" permission** on the Dashboard Settings administration object can
  modify Dashboard configuration, including enabling or disabling views (Favourites, Memberships,
  Study Programmes, Learning Sequences), enabling and setting default sorting and presentation modes, and
  configuring side panel modules (Calendar, News, Mail, Tasks).

## Data being deleted

- **When a user removes a single favourite**: The corresponding entry (user ID, item reference ID,
  type) is deleted from the `desktop_item` table.
- **When a user removes multiple favourites at once**: Each selected entry is deleted from the
  `desktop_item` table.
- **When a repository object is deleted**: All favourite entries referencing that object are removed
  from the `desktop_item` table via `removeFavouritesOfRefId()`, removing the association for all
  users who had favourited that object.
- **When a user account is deleted**: The Dashboard listens for the `deleteUser` event from the
  User service. Upon receiving it, all entries in the `desktop_item` table belonging to that user
  are deleted. Additionally, a database update step ensures orphaned entries (where the
  user no longer exists in `usr_data`) are cleaned up.
- **When a user unsubscribes from a course or group via the Dashboard**: The user's membership is
  removed from the respective course or group. This is handled by the Course or Group component,
  not by the Dashboard itself. The Dashboard only triggers the action if the user has the "leave"
  permission.
- **User preferences**: When a user account is deleted, user preferences (presentation mode,
  sorting mode, manual sort data) are deleted together with the user account by the User component.

## Data being exported

- The Dashboard component does not provide any dedicated export functionality for personal data.
- Favourites data is not included in any XML or file-based export.
- Data presented on the Dashboard (e.g., course memberships, learning progress, badges) is managed
  and potentially exported by the respective integrated components.
