# Link Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Link component provides auto-linking utilities for ILIAS. It integrates the third-party `linkifyjs` library (`linkify.min.js`, `linkify-element.min.js`) and a custom extension (`ilExtLink.js`) to automatically convert plain-text URLs (with a leading `http` or `https` protocol) into clickable hyperlinks in rendered content. E-mail addresses are explicitly excluded from auto-linking. The component also contributes a Setup Agent (`COPage\IntLink\Setup\Agent`) used during ILIAS installation and update routines. The component does not implement any feature that tracks user behaviour or processes personal data.

## Integrated Components

The Link component does not employ any other ILIAS components that handle personal data.

## Data being stored

The Link component does not store any personal data.

## Data being presented

The Link component does not present any personal data.

## Data being deleted

The Link component does not delete any personal data.

## Data being exported

The Link component does not export any personal data.
