# Tree Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Tree component is a structural infrastructure service that manages hierarchical relationships
between ILIAS objects in the repository. It implements the nested set model (with gaps) and the
materialized path model for representing parent-child relationships in the `tree` database table.
The Tree component does not represent a user-facing object type; instead, it is used internally by
the repository and other components to organize, move, trash, restore, and delete objects.

The `tree` table itself contains only structural data (node IDs, parent references, left/right
values, depth, and path information) and does not store personal data. When an object is moved to
the trash, the Tree component delegates the recording of the deletion timestamp and the user ID of
the person who performed the deletion to the ILIASObject component (via `ilObject::setDeletedDates()`
on the `object_reference` table). The Tree component reads this delegation data (e.g., in trash
queries) but does not write it directly to its own tables.

## Integrated Components

- The Tree component employs the following components, please consult the respective PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – the `ilPathGUI` class uses the AccessControl
      service to check "visible" and "read" permissions before rendering linked breadcrumb paths. The
      setup objective `ilTreeAdminNodeAddedObjective` registers RBAC operations for new admin nodes.
    - ILIASObject – when objects are moved to the trash, the Tree component calls
      `ilObject::setDeletedDates()` to store the deletion timestamp and the user ID of the deleting
      person in the `object_reference` table. When objects are restored from trash, it calls
      `ilObject::_resetDeletedDate()` to clear this data. The Tree component also uses
      `ilObject::_lookupObjId()`, `ilObject::_lookupTitle()`, and `ilObject::_lookupType()` to
      resolve object metadata.

## Data being stored

The Tree component does not store personal data in its own tables. The `tree` table contains only
structural information: `tree` (tree ID), `child` (reference ID), `parent` (parent reference ID),
`lft`, `rgt` (nested set boundaries), `depth`, and `path` (materialized path). None of these
columns contain user IDs, names, or other personal data.

When an object is moved to the trash via `ilTree::moveToTrash()`, the **user ID of the person who
performed the deletion** is passed to `ilObject::setDeletedDates()`, which writes it to the
`deleted_by` column of the `object_reference` table. This storage is performed by and documented
in the ILIASObject component, not the Tree component itself.

## Data being presented

The Tree component does not present personal data to users.

- The `ilPathGUI` class renders breadcrumb paths showing object titles (not user data). Path links
  are only rendered for nodes where the current user has the "visible" or "read" permission; for
  other nodes, only the title is displayed without a link.
- The trash query functionality (`ilTreeTrashQueries`) reads `deleted` and `deleted_by` values from
  the `object_reference` table and provides this data to calling components (e.g., the Repository
  trash view). The actual presentation of this data -- including who deleted an object -- is handled
  by the calling component, not by the Tree component itself.
- The System Check tree diagnostics GUI (`ilSCTreeTasksGUI`, `ilSCTreeDuplicatesTableGUI`) displays
  only object titles, types, and descriptions for tree structure validation purposes. No personal
  user data is shown. Access to the System Check is restricted to the ILIAS administration area.

## Data being deleted

The Tree component manages the structural lifecycle of objects in the repository tree:

- **When an object is moved to trash**: The `ilTree::moveToTrash()` method moves the tree node
  (and its subtree) out of the active tree by assigning a negative tree ID. The structural `tree`
  table entries are updated but not deleted. The deletion timestamp and deleting user ID are
  recorded via the ILIASObject component.
- **When an object is restored from trash**: The `ilTree::insertNodeFromTrash()` method removes the
  negative-tree-ID entry and reinserts the node into the active tree. The deletion date can be
  reset via `ilObject::_resetDeletedDate()`.
- **When a tree node is permanently deleted** (delete from trash): The `ilTree::deleteTree()` method
  removes the structural entries from the `tree` table. The `ilTree::deleteNode()` method deletes a
  single node entry and raises a `"deleteNode"` event so other components can react accordingly.
- **System Check repairs**: The `ilSCTreeTasks` class can delete duplicate or orphaned tree entries
  as part of administrative tree repair operations.

Since the Tree component does not store personal data in its own tables, no personal data is
deleted by these operations. The deletion of personal data associated with trashed objects
(such as the `deleted_by` user ID) is handled by the ILIASObject component.

## Data being exported

The Tree component does not provide any export functionality for personal data. There is no
XML export, file-based export, or other mechanism that exports personal data from this component.
