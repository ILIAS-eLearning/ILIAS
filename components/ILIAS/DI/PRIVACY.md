# DI Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The DI component provides the central dependency injection container for ILIAS. It extends `Pimple\Container` and exposes all major platform services — including the database interface, RBAC system, current user object, access handler, HTTP layer, UI framework, logging, filesystem, and dozens of component-level service facades — through typed PHP methods. The component acts purely as a service locator and does not implement any application logic of its own. It neither reads nor writes personal data and contains no SQL queries.

## Integrated Components

The DI component does not integrate with other ILIAS components in a way that involves processing personal data. It provides references to services from virtually all ILIAS components (e.g. User, RBAC, News, Mail, Portfolio, Survey, Certificate, and others), but the DI container itself does not access, store, or transmit personal data — any personal data handling occurs within the respective components whose services are exposed.

## Data being stored

The DI component does not store any personal data.

## Data being presented

The DI component does not present any personal data. It provides no user interface and renders no output.

## Data being deleted

The DI component does not delete any personal data.

## Data being exported

The DI component does not export any personal data.
