# CSV Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The CSV component provides a single utility class (`ilCSVWriter`) for building CSV-formatted strings in memory. It allows callers to configure a column separator (default: `,`), a field delimiter (default: `"`), and to add rows and columns programmatically before retrieving the assembled CSV string via `getCSVString()`. The component itself performs no database access, no file I/O, and no user-session handling. Any personal data that ends up in a CSV output is supplied entirely by the calling component; the CSV component is agnostic to the content it formats.

## Integrated Components

The CSV component does not integrate with any other ILIAS component directly. It is a stateless formatting utility consumed by other components that handle user data themselves.

## Data being stored

The CSV component does not store any personal data. It holds assembled CSV content only in a private in-memory string (`$csv`) for the lifetime of a single `ilCSVWriter` instance and writes nothing to the database.

## Data being presented

The CSV component does not present any data to end users on its own. It returns a plain string to the caller; the caller is responsible for any output or display.

## Data being deleted

The CSV component does not persist any data and therefore has no deletion logic.

## Data being exported

The CSV component does not initiate any exports itself. It provides the formatting infrastructure (RFC-4180-style escaping and delimiters) that other components use when exporting data to CSV files. Personal data in such exports is determined solely by the calling component.
