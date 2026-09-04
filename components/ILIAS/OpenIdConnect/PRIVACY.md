# OpenID Connect Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The OpenID Connect component enables authentication against an external OpenID Connect (OIDC)
provider. When a user authenticates via OIDC, the component receives identity claims from the
provider, maps them to ILIAS user profile fields, and either creates a new ILIAS user account or
updates an existing one. The component itself does not maintain its own personal data tables but
instead delegates all user data storage to the User component via `ilUserImportParser`.

Whether personal data is written depends on the **profile attribute mapping** and the
**user synchronisation** settings, both configured in the ILIAS administration. Profile fields
are only populated from the OIDC provider when a mapping is configured for the respective field,
and existing user data is only overwritten when the "update on login" option is enabled for that
field. If user synchronisation is disabled, no new accounts are created -- only existing accounts
with a matching external account identifier are authenticated.

During an active session, the OIDC **ID token** is stored in the ILIAS session when global
logout scope is configured. This token is used to perform a provider-side sign-out when the user
logs out of ILIAS.

## Integrated Components

- The OpenID Connect component employs the following components, please consult the respective
  PRIVACY.md files:
    - Authentication – the OpenID Connect component listens
      to the `beforeLogout` event from the Authentication component to trigger provider-side
      sign-out. Authentication manages session data and login coordination.
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – permission checks ("read", "write") on
      the administration settings node control who may view or modify the OpenID Connect
      configuration, including profile attribute mappings and role mappings.
    - User – the OpenID Connect component uses `ilUserImportParser` and
      `ilObjUser::_checkExternalAuthAccount` to create or update user accounts. All personal
      data from the OIDC provider is stored via the User component.

## Data being stored

The OpenID Connect component does not maintain its own database tables for personal data. All
personal data received from the OIDC provider is written into the User component's data
structures. However, the component controls **which** data is written and **when**:

- **External account identifier**: When a user authenticates via OIDC, the claim value
  configured as the UID field (e.g. `sub`, `email`, or another claim) is stored as the
  **external account** (`ext_account`) in the `usr_data` table, together with the authentication
  mode `oidc`. This links the ILIAS account to the OIDC identity.
- **Profile fields from the OIDC provider**: Depending on the configured profile attribute
  mapping and the requested OIDC scopes, the following user profile fields may be written or
  updated on each login:
    - **First name**, **last name**, **gender**, **birthday** (scope `profile`)
    - **Email address** (scope `email`)
    - **Street**, **city**, **postal code**, **country** (scope `address`)
    - **Phone number** (scope `phone`)
    - **Title**, **institution**, **department**, **hobby**, **fax**, **phone (office)**,
      **phone (mobile)**, **second email**, **matriculation number** (if mapped to custom
      claims)
    - **User-defined fields** (if mapped to custom claims)
- **OIDC ID token in session**: When the logout scope is set to "global", the **ID token**
  received from the OIDC provider is stored in the ILIAS session (`oidc_auth_idtoken`). This
  token may contain personal claims (such as name or email, depending on the provider). It is
  cleared when the user logs out.
- **Redirection target in session**: The **target URL** the user was trying to reach is stored
  in the session (`oidc_target`) during the authentication flow. This is a transient technical
  value, not personal data.

## Data being presented

- The OpenID Connect settings interface does not display personal user data. It presents
  configuration data such as the provider URL, client ID, scopes, profile attribute mappings,
  and role mappings.
- **Persons with the "read" permission** on the Authentication administration node can view the
  OpenID Connect configuration, including the profile attribute mapping rules and role mapping
  rules.
- **Persons with the "write" permission** on the Authentication administration node can modify
  all OpenID Connect settings, including profile mappings that determine which personal data is
  imported from the OIDC provider into ILIAS user profiles.
- User profile data imported via OpenID Connect is presented through the User component's
  interfaces and is subject to the User component's privacy controls.

## Data being deleted

- The OpenID Connect component does not store personal data in its own tables and therefore has
  no dedicated deletion logic for personal data.
- **When a user account is deleted**: The external account identifier (`ext_account`) and the
  authentication mode (`oidc`) stored in `usr_data` are deleted as part of the User component's
  account deletion process. All profile data imported from the OIDC provider is likewise deleted
  with the user account.
- **When the ILIAS session ends or the user logs out**: The ID token stored in the session
  (`oidc_auth_idtoken`) and the redirection target (`oidc_target`) are removed with the session
  data.
- **Configuration data**: The OpenID Connect settings (stored in the `settings` table under the
  module `oidc`) are system configuration and do not contain personal data. They persist until
  explicitly changed by a person with the "write" permission.

## Data being exported

- The OpenID Connect component does not provide any export functionality for personal data.
- User profile data that was originally imported from the OIDC provider may be exported through
  the User component's export features, but this is managed entirely by the User component.
