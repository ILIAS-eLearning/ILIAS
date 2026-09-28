# Excel Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Excel component is a thin utility wrapper around the PhpSpreadsheet library (formerly PHPExcel). It provides other ILIAS components with a unified API for creating, formatting, importing, and exporting Microsoft Excel files in both `.xlsx` (FORMAT_XML) and `.xls` (FORMAT_BIFF) formats. The component itself does not implement any business logic, does not interact with the database, and does not handle personal data directly. Personal data may be written into Excel files by the calling components that use this service — the privacy implications of such data belong to those components, not to the Excel component.

## Integrated Components

The Excel component does not integrate with any other ILIAS component that handles personal data. It has no declared dependencies in its `maintenance.json` and uses only the external PhpSpreadsheet library and general ILIAS utilities (`ilFileUtils`, `ilFileDelivery`, `ilStr`).

## Data being stored

The Excel component does not store any personal data. It performs no database writes and maintains no persistent state of its own. Workbooks are held transiently in memory during a request and are either delivered directly to the client or written to a temporary file that is not retained by this component.

## Data being presented

The Excel component does not present any personal data on its own. It is a backend utility; it has no user interface and renders no output directly to end users.

## Data being deleted

The Excel component does not retain any data, so there is nothing to delete. Temporary files created during export (`ilFileUtils::ilTempnam()`) are not managed by this component after creation.

## Data being exported

The Excel component provides the technical mechanism for exporting spreadsheet files (`.xlsx` or `.xls`) to the client via `sendToClient()`, or writing them to a path via `writeToFile()` / `writeToTmpFile()`. The content of those files — including any personal data they may contain — is supplied entirely by the calling component. The Excel component itself does not decide what data is exported.
