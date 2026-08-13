# Environment Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Environment component is a technical utility that provides runtime environment detection and inspection for the ILIAS platform. Its sole class, `ilRuntime`, exposes information about the current PHP or HHVM runtime: version string, SAPI type (e.g. FPM), configured error-reporting levels, whether errors are logged or displayed, and the path to the PHP binary. All data it surfaces is derived from PHP configuration constants and `ini_get()` calls at request time; nothing is persisted. The component has no settings of its own and no condition under which its behaviour changes based on user-specific configuration.

## Integrated Components

The Environment component does not employ any other ILIAS components that handle personal data.

## Data being stored

The Environment component does not store any personal data.

## Data being presented

The Environment component does not present any personal data.

## Data being deleted

The Environment component does not delete any personal data.

## Data being exported

The Environment component does not export any personal data.
