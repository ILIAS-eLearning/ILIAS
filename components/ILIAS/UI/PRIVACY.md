# UI Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The UI-Frameworks consists of a set of components along with a semantic description of each component including a set of
guidelines of how to use them. UI Components serve a specific purpose. They are not simply named html structures that
are composed to larger structures, but semantically different identities. It is possible that two different component
look the same and act the same by accident, but still remain different identities. However it is also possible that the
same component, looks different in seperate contexts.

## Integrated Components

The UICore component employs the following components, please consult the respective `PRIVACY.md`:

* Authentication: for storing values inside the session
* [Component](../Component/PRIVACY.md): for exposing public resources 
* [Data](../Data/PRIVACY.md): for representing structured data
* [FileServices](../FileServices/PRIVACY.md): for php upload limit
* [Help](../Help/PRIVACY.md): for retrieving help texts
* Language: for translations
* Refinery: for retrieving and transforming data
* [Style](../Style/PRIVACY.md): for resolving image paths
* [UICore](../UICore/PRIVACY.md): for rendering HTML, embeding JavaScript and CSS resources

## Data being stored

This component does not store any personal data.

## Data being presented

This component (itself) does not present any personal data. It may be used by other components to do so, which would be
reflected in their respective `PRIVACY.md`.

## Data being deleted

This component does not delete any personal data.

## Data being exported

This component does not export any personal data.
