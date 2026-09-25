# Table Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Table component provides reusable table GUI infrastructure for ILIAS. It offers two main classes: `ilTable2GUI` (deprecated since ILIAS 12), a comprehensive framework for rendering sortable, filterable, and paginated tables with optional export; and `ilTablePropertiesStorageGUI`, which saves per-user table UI preferences (such as filter visibility, sort order, rows per page, selected columns, and active filter values) persistently in the database. Table preferences are stored per user and per table instance. For anonymous users, all preferences are stored in the PHP session instead of the database.

## Integrated Components

The Table component does not integrate other ILIAS components that handle personal data on its own. It acts as infrastructure used by many other components; privacy implications of displayed or exported data depend entirely on the component that uses the Table framework.

## Data being stored

The Table component stores user-specific table UI preferences in the database table `table_properties`. Each row consists of:

- `table_id` — identifier of the specific table instance
- `user_id` — the ILIAS internal numeric user ID, linking the preference to a specific person
- `property` — the property name (one of: `filter`, `direction`, `order`, `rows`, `selfields`, `selfilters`, `filter_values`)
- `value` — the stored value for that property

The purpose of storing this data is to restore each user's individual table settings (e.g. whether the filter panel is visible, which columns are selected, current sort direction and order field, number of rows per page, and current filter input values) across page loads and sessions.

The pagination offset (`offset`) is stored only in the PHP session and is not persisted to the database.

Anonymous users have all properties stored in the PHP session only; no data is written to `table_properties` for anonymous users.

## Data being presented

The Table component itself does not define or present personal data. It is a rendering framework; the actual content shown in a table — which may include personal data such as names, login names, or email addresses — is supplied by the ILIAS component that instantiates the table. Access to that data is governed by the permissions defined in the using component, not by the Table component itself.

## Data being deleted

The Table component does not implement any mechanism to delete entries from `table_properties` when a user account is deleted or anonymized. No `deleteByUser` or equivalent method exists in the component's code. Entries in `table_properties` linked to a `user_id` may therefore persist after account deletion unless another component or a database cleanup routine handles this. No automatic expiry of stored preferences is implemented.

## Data being exported

`ilTable2GUI` supports exporting the current table content as an Excel file (`.xlsx`) or as a CSV file, triggered by the user via an export dropdown in the table toolbar. The exported content reflects whatever data the using component has loaded into the table and may include personal data depending on that component. The Table component itself does not restrict what can be exported; access control is the responsibility of the using component.
