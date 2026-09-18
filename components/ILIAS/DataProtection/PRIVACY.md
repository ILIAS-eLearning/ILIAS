# DataProtection Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

Most of the functionality is provided by the `LegalDocuments` component.
Where applicable the corresponding section of it's [privacy file](../LegalDocuments/PRIVACY.md) is referenced.

## Data being stored
- [Agreement](../LegalDocuments/PRIVACY.md#agreement)
- [SelfRegistration](../LegalDocuments/PRIVACY.md#selfregistration)
- [Acceptance History](../LegalDocuments/PRIVACY.md#acceptance-history)

## Data being presented
- [Agreement](../LegalDocuments/PRIVACY.md#agreement)
- [SelfRegistration](../LegalDocuments/PRIVACY.md#selfregistration)
- [Show document in footer](../LegalDocuments/PRIVACY.md#show-document-in-footer)
- [UserManagementFields](../LegalDocuments/PRIVACY.md#usermanagementfields)
- [Acceptance History Administration](../LegalDocuments/PRIVACY.md#viewing-the-acceptance-history)

## Data being deleted
- [Withdrawal Process](../LegalDocuments/PRIVACY.md#withdrawal-process)
- [Acceptance History](../LegalDocuments/PRIVACY.md#acceptance-history)

Additionally a user with admistrative rights can delete the [Last Accept Date](../LegalDocuments/PRIVACY.md#last-accept-date) for all users (not individually). Please note that this information can still be obtained through the acceptance history, which is not affected by this action.

For this action the user must have the `write` right for the DataProtection Administration.

## Data being exported

The DataProtection component does not provide any dedicated export functionality for personal data.
