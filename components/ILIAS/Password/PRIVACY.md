# Password Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Password component is a cryptographic service library for ILIAS. It provides a unified interface (`ilPasswordEncoder`) and multiple concrete encoder implementations for hashing and verifying user passwords:

- **Argon2id** (`ilArgon2IdPasswordEncoder`): Uses PHP's `PASSWORD_ARGON2ID` algorithm with configurable memory cost, time cost, and thread parameters.
- **BcryptPhp** (`ilBcryptPhpPasswordEncoder`): Uses PHP's `password_hash` with `PASSWORD_BCRYPT` and auto-benchmarked cost factor.
- **Bcrypt** (`ilBcryptPasswordEncoder`, deprecated): A legacy encoder combining HMAC-whirlpool with a client-side file-based salt (`pwsalt.txt`) and bcrypt.
- **MD5** (`ilMd5PasswordEncoder`): A legacy MD5-based encoder retained for backward compatibility.

The component also provides `ilPasswordUtils`, a utility for generating cryptographically secure random bytes (used for salts and tokens by other components).

This component does not perform any user-facing operations on its own. It is a shared library consumed by other ILIAS components (such as User management) that are responsible for storing the resulting encoded hashes.

## Integrated Components

The Password component does not directly integrate with other ILIAS components that handle personal data. It is a stateless encoding library. Components that use it (e.g. the User component) are responsible for any personal-data handling around password storage.

## Data being stored

The Password component does not store any personal data. It contains no database queries and performs no data persistence. Encoded password hashes produced by this component are stored by the calling component (e.g. in the `usr_data` table managed by the User component), not by the Password component itself.

## Data being presented

The Password component does not present any data to users or administrators. It has no user interface and exposes no personal data.

## Data being deleted

The Password component does not delete any data. Lifecycle management of encoded password hashes is the responsibility of the component that stores them (e.g. the User component, where passwords are removed as part of account deletion).

## Data being exported

The Password component does not export any data.
