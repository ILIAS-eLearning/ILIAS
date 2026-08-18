# Saml Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Saml component provides SAML 2.0 Single Sign-On (SSO) authentication for ILIAS using the SimpleSAMLphp library. It manages Identity Provider (IdP) configurations and mediates the authentication handshake between ILIAS and external IdPs. When user synchronization is enabled for an IdP, the component automatically creates new ILIAS accounts or updates existing ones by mapping SAML attributes received from the IdP to ILIAS user profile fields. An account migration flow is available when an external account cannot be matched automatically; during this flow, SAML attributes are stored temporarily in the PHP session (`tmp_attributes`, `tmp_return_to`) until the migration is completed or the session expires.

## Integrated Components

- User — used to create and update ILIAS user accounts from SAML attributes via `ilUserImportParser` and `ilObjUser`; also used to look up and write the `auth_mode` field

## Data being stored

When user synchronization is enabled for a SAML IdP, personal data received as SAML attributes from the Identity Provider is written to the `usr_data` table through the User component's import mechanism. The following user profile fields can be populated depending on the attribute mapping configured by the administrator:

- **Login** (`usr_data.login`) — derived from the configured login claim
- **External account identifier** (`usr_data.ext_account`) — the unique SAML UID claim value used to link the external identity
- **Authentication mode** (`usr_data.auth_mode`) — set to `saml_<idp_id>` to mark the user as a SAML-authenticated account
- **First name** (`usr_data.firstname`), **last name** (`usr_data.lastname`), **email** (`usr_data.email`), **second email** (`usr_data.second_email`), **gender** (`usr_data.gender`), **institution**, **department**, **title**, **street**, **city**, **zipcode/postal code**, **country**, **phone (office, home, mobile)**, **fax**, **referral comment**, **matriculation**, **birthday**, **hobby**, and **user-defined fields** — each field is populated only when a mapping rule exists in the IdP's attribute mapping configuration.

IdP configuration itself (entity ID, uid claim, login claim, sync status, default role, account migration setting) is stored in the `saml_idp_settings` table and does not contain personal data.

## Data being presented

The SAML IdP list and individual IdP configuration — including entity ID, attribute mapping rules, and synchronization settings — are displayed in the ILIAS administration area. This information is visible only to persons with the "write" permission on the administration node for authentication. No personal user data (such as names or email addresses) is displayed within the Saml component's own views.

## Data being deleted

When a SAML IdP is deleted via `ilSamlIdp::delete()`, the following occurs:

- All attribute mapping rules associated with the IdP are deleted (`ilExternalAuthUserAttributeMapping::delete()`).
- The `auth_mode` field in `usr_data` is reset from `saml_<idp_id>` to `default` for all user accounts that were authenticated via that IdP.
- The IdP configuration record is removed from `saml_idp_settings`.

The Saml component does not delete user accounts or other personal data directly. Deletion of user accounts and their personal data is handled by the [User](../User/PRIVACY.md) component.

## Data being exported

The Saml component generates SP metadata XML (via the `Metadata` class) for exchange with Identity Providers. This metadata describes the ILIAS service provider endpoints and certificates, and does not contain personal data. No export of personal user data is provided by the Saml component.
