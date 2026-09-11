# KeyValueStorage Privacy

Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- KeyValueStorage offers other components a place to keep application state under a namespace and a key, for example the sort column of a table or the step a wizard is on.
- The component defines no data of its own. Whatever is stored is handed over by another component and is described in that component's PRIVACY.md.
- Two scopes exist. Session scope keeps data in the ILIAS session; persistent scope keeps data in the database table `kvs_store`.

## Integrated components
- Authentication stores the session scope in the ILIAS session.
- [Database](../Database/PRIVACY.md) provides the connection used for the persistent scope.

## Data being stored
- Session scope: data lives in the ILIAS session and is bound to one user's session. It is therefore implicitly personal data for as long as the session exists.
- Persistent scope: data lives in `kvs_store` with the columns `namespace`, `keyword` and `value`. The table has no user column and no user reference. The component itself stores no personal data there.
- KeyValueStorage never inspects the values it is given. If a component stores personal data through it, that component is responsible for documenting and handling it.

## Data being presented
- KeyValueStorage presents no data. It has no user interface.

## Data being deleted
- Session scope: data is gone when the session ends, and can be removed earlier by the storing component.
- Persistent scope: data is removed only when the storing component removes it. There is no automatic expiry.
- The component has no routine tied to the deletion of a user account, because the persistent scope holds no user reference.

## Data being exported
- KeyValueStorage exports no data.
