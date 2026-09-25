# Xml Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Xml component is a low-level infrastructure utility that provides abstract base classes and helpers for XML processing within ILIAS. It offers SAX-based XML parsing through `ilSaxParser` (an abstract base class for expat/SAX parsing of XML files or strings) and `ilSaxController` (a dispatcher that routes SAX element events to specialised handler objects implementing `ilSaxSubsetParser`). It also provides `ilXmlWriter`, a helper class for sequentially building XML documents as strings. Both `ilSaxParser` and `ilXmlWriter` are marked as deprecated and will be removed in ILIAS 11 in favour of native PHP XML handling. The component itself does not implement any business logic and does not process or store personal data on its own.

## Integrated Components

The Xml component does not integrate with any other ILIAS components that handle personal data. It is a pure infrastructure library consumed by other components.

## Data being stored

The Xml component does not store any personal data. No database write operations (INSERT, UPDATE, or equivalent ILIAS DB API calls) are present in any of the component's PHP files.

## Data being presented

The Xml component does not present any personal data. It provides generic XML parsing and writing primitives; any data flowing through these utilities is handled by the calling component, not by this component.

## Data being deleted

The Xml component does not delete any personal data. It holds no data of its own that would need to be removed.

## Data being exported

The Xml component does not export any personal data directly. The `ilXmlWriter` class is a general-purpose XML string builder; any export of personal data via XML is the responsibility of the components that use this utility.
