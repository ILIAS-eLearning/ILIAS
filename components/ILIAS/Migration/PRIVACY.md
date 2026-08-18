# Migration Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Migration component provides deprecated infrastructure utilities for database schema changes during ILIAS updates. It contains two helper classes: `ilDBUpdate3136` for copying and adding content style classes across all existing style objects, and `ilDBUpdateNewObjectType` for registering new ILIAS object types and managing RBAC permission assignments. Both classes are marked as deprecated in favour of dedicated setup objective classes in the respective feature components. The Migration component itself does not implement any feature that is user-facing, and it does not handle personal data at any point.

## Integrated Components

The Migration component does not employ any other ILIAS components that handle personal data.

## Data being stored

The Migration component does not store any personal data.

The DB writes found touch only system-level configuration tables:
- `style_char` and `style_parameter` — content style definitions (no personal data)
- `object_data` — ILIAS object type registrations inserted with `owner = -1` (system owner, no personal data)
- `rbac_ta` and `rbac_pa` — RBAC type-operation and permission assignments (no personal data)
- `rbac_templates` — RBAC role template entries (no personal data)

## Data being presented

The Migration component does not present any personal data.

## Data being deleted

The Migration component does not delete any personal data.

`ilDBUpdateNewObjectType::deleteRBACOperation` and `deleteRBACTemplateOperation` remove entries from `rbac_ta` and `rbac_templates` respectively, but these tables contain only system-level RBAC configuration, not personal data.

## Data being exported

The Migration component does not export any personal data.
