# KeyValueStorage Privacy

Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- KeyValueStorage offers other components a place to keep application state under a namespace and a key, for example the sort column of a table or the step a wizard is on.
- The component defines no data of its own. Whatever is stored is handed over by another component and is described in that component's PRIVACY.md.
- Three scopes exist. Session scope keeps data in the ILIAS session. Global persistent scope and subject scope share `kvs_store`. Global rows have an empty `provider` and `subject`. Subject rows are one set per provider and subject.

## Integrated components
- Authentication stores the session scope in the ILIAS session, resolves the logged-in user to a subject of the provider `authentication`, and purges that subject when the account is deleted.
- [Database](../Database/PRIVACY.md) provides the connection used for the persistent scope.

## Data being stored
- Session scope: data lives in the ILIAS session and is bound to one user's session. It is therefore implicitly personal data for as long as the session exists.
- Global persistent scope: data lives in `kvs_store` with an empty `provider` and `subject` and the columns `namespace`, `keyword` and `value`. Those rows have no user reference. The component itself stores no personal data there.
- Subject scope: data lives in the same table, with `provider`, `subject`, `namespace`, `keyword` and `value`. `provider` names the component the subject belongs to, `subject` is an id within that provider. For an authenticated ILIAS user, Authentication stores `authentication` and the user id, so those rows are personal data.
- KeyValueStorage never inspects the values it is given. If a component stores personal data through it, that component is responsible for documenting and handling it.

## Data being presented
- KeyValueStorage presents no data. It has no user interface.

## Data being deleted
- Session scope: data is gone when the session ends, and can be removed earlier by the storing component.
- Global persistent scope: data is removed only when the storing component removes it. There is no automatic expiry. Deleting a user account does not remove rows whose `provider` and `subject` are empty.
- Subject scope: data is removed when the storing component clears it, or when `Services::purgeSubject()` runs for that subject. Purging only reaches the rows of the provider that named the subject. Authentication, as provider `authentication`, calls that method for the user id when the user account is deleted (`deleteUser`).

## Data being exported
- KeyValueStorage exports no data.
