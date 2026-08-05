# Locator Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Locator component provides the breadcrumb navigation bar displayed at the top of ILIAS pages. It renders the hierarchical path from the repository root to the current object (e.g., Repository > Category > Course > Folder), allowing users to navigate back to parent containers. The Locator does not store, modify, or delete any data itself. It reads object titles and hierarchy information from the repository tree at render time and discards this information after the page is generated.

The breadcrumb path may be shortened within courses depending on the global setting "rep_breadcr_crs" and per-course container settings. This affects only which repository nodes are displayed in the breadcrumb, not any personal data.

## Integrated Components

- The Locator component employs the following components, please consult the respective PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) — the Locator checks the "visible" permission on each repository node before including it in the breadcrumb path.
    - [Container](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Container/PRIVACY.md) — the Locator reads per-course container settings to determine whether the breadcrumb path should be shortened to the course level.
    - ILIASObject — the Locator uses `ilObject` to look up object IDs, types, and icons for breadcrumb items.
    - Tree — the Locator uses `ilTree` to retrieve the hierarchical path from the repository root to the current node.
    - [Course](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Course/PRIVACY.md) — the Locator references `ilObjCourseGUI` constants to interpret course-level breadcrumb settings.

## Data being stored

The Locator component does not store any personal data. It operates as a read-only navigational component that assembles breadcrumb entries from the repository tree at render time. No database writes, user preferences, or persistent state are created by this component.

## Data being presented

The Locator component does not present any personal data. It displays only **object titles** (e.g., course names, category names, folder names) and **object type icons** as breadcrumb links. No user names, user IDs, or other personal information are rendered by this component.

Each breadcrumb item is only shown if the current user has the "visible" permission on the corresponding repository node. Items for which the user lacks this permission are silently omitted from the breadcrumb path.

## Data being deleted

The Locator component does not store any data and therefore has no data to delete. Since the breadcrumb is generated dynamically on each page load, no deletion scenarios apply.

## Data being exported

The Locator component does not provide any export functionality. No personal data is exported by this component.
