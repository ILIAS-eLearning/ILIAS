# RootFolder Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The RootFolder component provides the root of the ILIAS repository. It displays and organizes the repository objects below the root folder and provides access to inherited container and repository functions.

## Integrated Components

The RootFolder component relies on the following components and services for privacy-relevant functionality:

- [AccessControl](../AccessControl/PRIVACY.md) controls read, write, permission-management and other access rights for the root folder and its child objects.
- [Container](../Container/PRIVACY.md) provides the repository listing, container settings, sorting, block settings, container pages, and container export functionality.
- [Repository](../Repository/PRIVACY.md) provides repository navigation and related user-specific repository features.
- The **Object** service stores the root object’s owner and creation and update timestamps, and provides common object properties.
- [COPage](../COPage/PRIVACY.md) can store container page content and page history when a page is configured for the root folder.

## Data being stored

The RootFolder component itself does not store any personal data.

## Data being presented

The RootFolder component itself does not present any personal data.

## Data being deleted

There are not personal data related deletion operations implemented in this component. The root folder object itself cannot be deleted.

## Data being exported

The root folder does not export any data.