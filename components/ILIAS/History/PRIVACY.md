# History Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The History component previously maintained an audit trail of changes to ILIAS objects, recording the acting user, the affected object, the action type, and a timestamp. As of database update step 11 (`HistoryDatabaseUpdateSteps11::step_1`), the component is decommissioned: both the `history` and the `history_seq` database tables are dropped unconditionally during the ILIAS update process. No active PHP classes exist in this component that write to any database table. The component no longer stores, presents, or exports personal data.

## Integrated Components

The History component does not currently integrate with any other ILIAS components that handle personal data.

## Data being stored

The History component does not store any personal data. The underlying database tables (`history`, `history_seq`) are removed during the ILIAS update via `HistoryDatabaseUpdateSteps11::step_1`.

## Data being presented

The History component does not present any personal data.

## Data being deleted

The `history` and `history_seq` tables are permanently dropped by `HistoryDatabaseUpdateSteps11::step_1` when the ILIAS instance is updated to database step 11 or later. No further deletion mechanism is applicable.

## Data being exported

The History component does not export any personal data.
