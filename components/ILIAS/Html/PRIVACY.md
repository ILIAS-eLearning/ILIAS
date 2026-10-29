# Html Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Html component does not employ any other ILIAS components that handle personal data. It can be and is used as a dependency by other components for sanitization purposes, but personal data handling resides entirely within those consumer components.

The component does not implement any user-facing features on its own and contains no database access code.

## Integrated Components

The Html component does not employ any other ILIAS components that handle personal data. It is itself used as a dependency by the [Forum](../Forum/PRIVACY.md) component (for forum post sanitization) and by the Assessment/Test component (for user solution sanitization), but personal data handling resides entirely within those consumer components.

## Data being stored

The Html component does not store any personal data.

## Data being presented

The Html component does not present any personal data.

## Data being deleted

The Html component does not delete any personal data.

## Data being exported

The Html component does not export any personal data.
