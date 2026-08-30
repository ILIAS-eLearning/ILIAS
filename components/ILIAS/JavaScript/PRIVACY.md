# JavaScript Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The JavaScript component is a minimal infrastructure component that registers a single shared JavaScript asset (`Basic.js`) as a public resource for use across ILIAS. `Basic.js` provides common client-side utilities including: the global `il` namespace, UI layout and scrolling helpers (`il.UICore`, `il.Util`), AJAX request wrappers, rating widget interaction helpers (`il.Rating`), a language string store (`il.Language`), double-submission prevention for forms, MathJax rendering support, and a service worker registration stub. The component itself contains no server-side business logic, no database access, and no processing of personal data. It does not have any special privacy conditions depending on settings or enabled/disabled states.

## Integrated Components

The JavaScript component does not integrate any other ILIAS components that handle personal data.

## Data being stored

The JavaScript component does not store any personal data.

## Data being presented

The JavaScript component does not present any personal data.

## Data being deleted

The JavaScript component does not delete any personal data.

## Data being exported

The JavaScript component does not export any personal data.
