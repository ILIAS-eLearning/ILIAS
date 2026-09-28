# LegalDocuments Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

# General Information

This component provides merely functionality for other components but doesn't provide it's own user interface and stores / processes user data only on behalf of consumers of this component.
At the time of writing this document these are the `TermsOfService` and `DataProtection` components.

The following sections describe privacy relevant functionalities that can be used by it's consuming components. Each functionality is separate from another component using that same functionality (they are bound by the consuming component).

## Acceptance History

Using this functionality, a history is kept for who agreed when and to which document.
When a user accepts a given agreement, a record is saved, containing the **User ID**, a **Date and Time**, the **Document Version** and criterias why the specified document was chosen for the user. The criterias may contain information about the user's **Global Role**, **Language**, and / or **Country** to that date and time.

Currently this data is **never deleted**.

### Viewing the Acceptance History

An Administration GUI is provided where users with corresponding administrative rights can view the Acceptance History records. Additionally to the record data, the user's firstname, lastname and email (only indirectly via the search) is displayed but only if the user still exists (this information is not saved in the acceptance history).

To access this information the user requires the right to read the user administration & to read the corresponding components administration.

## Agreement

This functionality displays a document to users on login which have not agreed to a required document. They cannot access the system until consent is given.
Depending on the settings this step may be optional.

If the user accepts the displayed agreement, a record of this action is saved, see the [Acceptance History](#acceptance-history) for the specifics.

### Last Accept Date
Additionally the **Date and Time** is saved for the user.
This is used to allow a way to withdraw consent without modifying the Acceptance History.
This information can still be obtained through the Acceptance History.
When withdrawing consent, no other information is kept only the information that it has been withdrawn ([Last Accept Date](#last-accept-date) has been deleted but a record in the acceptance History exists for the user).

## SelfRegistration

This serves the same purpose and has the same privacy implications as [Agreement](#agreement) but for new users who register via the self registration.

## Withdrawal Process

This functionality provides a way to withdraw consent to an already accepted document.
Depending on the concrete settings the user is either deleted entirely by using the `User` components `delete` function for the current user (please refer to that component for the specifics)
or only the information about the [Last Accept Date](#last-accept-date) is deleted.
LDAP users are never deleted.

## Show document in footer

If a user has accepted a document via the Agreement or SelfRegistration, they can revisit the document via the ILIAS footer.

## UserManagementFields

This shows the accepted document & the [Last Accept Date](#last-accept-date) in the profile view of the user administration.

This functionality doesn't employ any access rights to this data as this is the responsibility of the user administration where this information is show.

# Data being exported

The LegalDocuments component does not provide any dedicated export functionality for personal data.
