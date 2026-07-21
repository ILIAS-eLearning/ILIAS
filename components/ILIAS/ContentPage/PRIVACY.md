# Content Page Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**


## General Information

The Content Page component provides a standalone content page object for use in the
repository. It allows the creation of rich-text content pages using the ILIAS page editor (COPage).
Content Pages can be placed in categories, courses, groups, and folders.

The Content Page component itself does not store personal data. Its two database tables
(`content_page_data` and `content_page_metrics`) contain only object-level configuration
(stylesheet reference) and computed page metrics (estimated reading time per language), neither of
which includes user IDs or other personal data. All personal data handling – including learning
progress tracking, page edit history, notes, and metadata authorship – is delegated to the
respective integrated components listed below.

A global administration setting controls whether the estimated **reading time** is displayed to
users in repository listings. This setting does not affect personal data, as reading time is a
property of the page content, not of individual users.

## Integrated Components

- The Content Page component employs the following components, please consult the respective
  PRIVACY.md files:
    - [COPage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/COPage/PRIVACY.md) — provides the page editor and page rendering engine. The
      COPage component manages the page content, its edit history, and internal media objects.
    - [MetaData](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/PRIVACY.md) — stores metadata (e.g., author information) associated
      with the Content Page object.
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) — manages permissions for reading, editing, and
      administering Content Page objects.
    - [Export](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Export/PRIVACY.md) — provides the XML export functionality for Content Page
      objects, including page content, metadata, and styles.
    - [InfoScreen](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/InfoScreen/PRIVACY.md) — renders the Info tab, which may display metadata
      and allows private notes. The Info tab can be enabled or disabled per Content Page.
    - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/PRIVACY.md) — enables private notes for users on the Content Page Info
      screen.
    - [KioskMode](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/KioskMode/PRIVACY.md) — provides an embedded presentation mode used when a
      Content Page is viewed inside a course or learning sequence context.
    - Style — manages content styles applied to the Content Page. No personal data is handled by
      the Style component in this context.
    - ILIASObject — the Object service stores the account which created the Content Page object
      and its timestamps.
    - Tracking — tracks user progress on Content Page objects. Supports three modes:
      deactivated, manual completion, and content visited. In manual mode, users can toggle their
      completion status. In content-visited mode, progress is recorded automatically when the page
      is viewed.
    - [Container](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Container/PRIVACY.md) — used to store per-object settings such as Info tab visibility via container
      settings.

## Data being stored

The Content Page component itself does not store personal data. Its database tables contain only:

- `content_page_data`: the **Content Page ID** and a **stylesheet** reference (integer). No user
  IDs or personal information.
- `content_page_metrics`: the **Content Page ID**, **page ID**, **language**, and computed
  **reading time** in minutes. No user IDs or personal information.

All personal data storage (such as learning progress records, page edit history, notes, and object
ownership) is handled by the integrated components listed above.

## Data being presented

- **Each user** with the "Read" permission can view the page content of the Content Page.
- **Each user** with the "Read" permission has their learning progress tracked automatically
  when the Content Page uses the "content visited" learning progress mode.
- **Persons with the "Write" permission** can access the page editor to edit content, change
  settings (title, description, online status, Info tab visibility), manage content styles,
  manage translations, edit metadata, and access the export tab.
- **Persons with access to the Learning Progress** (as determined by
  `ilLearningProgressAccess::checkAccess`) can view the Learning Progress tab, which may display
  user names and completion statuses. The presentation of this data is handled by the
  LearningProgress component.
- **Persons with the "Edit Permission" permission** can manage the permissions tab.
- If the **Info tab** is enabled (configurable per Content Page), persons with the "Visible" or
  "Read" permission can access it. The Info tab may display metadata and allows private notes
  (handled by the InfoScreen and Notes components).

The Content Page component does not directly display user names, login names, or other personal
identifiers in its own user interface. Any such presentation occurs through delegated components
(e.g., LearningProgress, COPage, Notes).

## Data being deleted

- **When a Content Page object is deleted from trash**: all data in `content_page_data` and
  `content_page_metrics` for that object is deleted. The associated page object is deleted via
  the COPage component. Metadata, learning progress records, and other associated data managed
  by integrated components are deleted according to their own lifecycle rules.
- **When a Content Page object is moved to trash**: the object becomes inaccessible but its data
  remains in the database until the trash is emptied manually or by a cron job.
- **When a user account is deleted**: the Content Page component does not store user IDs, so no
  data within its own tables is affected. Learning progress records, notes, and other
  user-specific data associated with the Content Page are handled by the respective integrated
  components.

The Content Page component does not implement its own user-specific deletion methods (such as
`deleteByUserId`), as it does not store personal data.

## Data being exported

- **Persons with the "Write" permission** can export a Content Page object via the Export tab.
  The XML export includes the Content Page's title, description, and Info tab visibility setting.
  It also includes dependent data from integrated components: page content (COPage), metadata
  (MetaData), content styles (Style), and common object data (ILIASObject). The export does not
  include learning progress records or user-specific data.
