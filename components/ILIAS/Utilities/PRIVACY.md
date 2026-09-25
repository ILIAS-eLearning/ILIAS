# Utilities Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Utilities component is a collection of deprecated, stateless helper classes (`ilUtil`,
`ilShellUtil`, `ilStr`, `ilArrayUtil`, `ilLegacyFormElementsUtil`) that provide general-purpose
functions such as string manipulation, HTML sanitization, image conversion, cookie handling, file
delivery, and array sorting. The entire `ilUtil` class has been marked as deprecated by the ILIAS
Technical Board and is scheduled for removal with ILIAS 12.

The Utilities component does not define any repository objects, does not own any database tables,
and does not store, present, or manage personal data. All functions operate statelessly on
parameters passed by calling components. When functions within this component access DIC services
(e.g., database, RBAC, user) they do so as pass-through operations on behalf of the calling code.

## Integrated Components

The Utilities component does not integrate other components for the purpose of managing personal
data. Its Setup agent depends on the following component:

- [Refinery](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Refinery/PRIVACY.md) — used for configuration transformation during installation
  setup.

## Data being stored

This component does not store any personal data. It does not create, own, or write to any database
tables. The Setup agent writes system configuration (paths to external tools such as ImageMagick
`convert`, `zip`, and `unzip`) into `ilias.ini.php`, which contains no personal data.

## Data being presented

This component does not present any personal data. While some utility functions (e.g.,
`_getObjectsByOperations`) perform RBAC permission checks and database queries, these are
stateless helper functions called by other components. The Utilities component itself has no
graphical user interface and does not render any data to users.

## Data being deleted

This component does not store personal data and therefore has no data to delete. No deletion
methods for personal data exist within this component.

## Data being exported

This component does not export any personal data.
