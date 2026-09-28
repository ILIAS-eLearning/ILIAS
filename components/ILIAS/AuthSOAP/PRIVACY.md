# AuthSOAP Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The AuthSOAP component authenticates ILIAS users against an external SOAP service. During login, it calls `isValidSession` on the configured remote SOAP endpoint, passing the user's external username (`ext_uid`), a SOAP password (`soap_pw`), and a flag indicating whether the user is new to ILIAS. If the SOAP server confirms the session as valid and the user already has an ILIAS account (matched via `ilObjUser::_checkExternalAuthAccount` using auth mode `soap`), that account is used directly. If the user is new and the setting `soap_auth_create_users` is enabled, a new ILIAS user account is automatically provisioned using personal data returned by the SOAP server. Automatic account creation is conditional on this setting; without it, new users are rejected even when the SOAP server validates them successfully.

## Integrated Components

- User — User accounts are looked up and created via `ilObjUser`. All personal data for new accounts is stored through this component's persistence layer.
- Registration — When `soap_auth_account_mail` is enabled, `ilAccountRegistrationMail` (using `ILIAS\User\Settings\NewAccountMail\Repository`) sends an account notification email to the newly created user.

## Data being stored

When a new user is authenticated via SOAP for the first time and automatic account creation is enabled, the following personal data is stored via `ilObjUser::create()` and `ilObjUser::saveAsNew()` (which write to the `usr_data` table, managed by the User component):

- **Login** — an ILIAS-internal login generated from the SOAP external username via `ilAuthUtils::_generateLogin`.
- **Firstname and Lastname** — received from the external SOAP server response.
- **Email address** — received from the external SOAP server response.
- **Password** — generated randomly via `ilSecuritySettingsChecker::generatePasswords` and stored in plain text form during creation only when the setting `soap_auth_allow_local` is enabled; otherwise an empty value is stored.
- **Auth mode** — stored as the string `soap` to mark the account as SOAP-managed.
- **External account** — the original SOAP username (`ext_uid`) is stored as the external account identifier to allow future lookups.
- **Language** — the system default language is assigned.
- **Profile incomplete flag** — set to `true` at account creation.

Additionally, on each successful SOAP login the string `soap` is written into the PHP session under the key `used_external_auth_mode` (not a database write).

No personal data is stored by AuthSOAP itself if `soap_auth_create_users` is disabled — in that case, new users are denied access and no account is created.

## Data being presented

The AuthSOAP component does not provide any user interface for presenting personal data. All data written to user accounts is visible through the standard ILIAS user management interface to persons with the `administrate_users` permission.

## Data being deleted

The AuthSOAP component contains no deletion logic of its own. User accounts created via SOAP follow the standard ILIAS user account lifecycle: they can be deleted by a person with the `administrate_users` permission through the User component's administration. Permanent deletion of a user account requires deleting the account from the trash after it has been moved there.

## Data being exported

The AuthSOAP component does not provide any export functionality. User account data created via SOAP can be exported through the standard ILIAS user administration export functions provided by the User component.
