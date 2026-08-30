# Exceptions Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Exceptions component provides the base exception infrastructure for ILIAS. It defines the `ilException` class (extending PHP's built-in `Exception`) which serves as the base class for all ILIAS-specific exception types, and the `Exceptions` component registration class implementing the `Component\Component` interface. The component contains no business logic, no database interaction, and no data processing of any kind. It is a pure infrastructure utility.

## Integrated Components

The Exceptions component does not integrate with any other ILIAS components. It has no dependencies listed in `maintenance.json` (`used_in_components: []`) and its `init()` method is empty.

## Data being stored

The Exceptions component does not store any personal data. No database writes, reads, or queries of any kind are present in this component.

## Data being presented

The Exceptions component does not present any personal data. It contains no UI output, no templates, and no controllers.

## Data being deleted

The Exceptions component does not delete any personal data. No deletion logic exists in this component.

## Data being exported

The Exceptions component does not export any personal data. No export functionality exists in this component.
