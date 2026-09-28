# Category Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Category component organizes repository objects in a hierarchical structure. In addition to displaying the category and its contents, a category can be used to administer local user accounts and their role assignments. Personal data in the category context therefore mainly concerns the local accounts shown to administrators and the personal data handled by integrated object, container and repository services.

## Integrated Components

The Category component employs the following services and components. Their privacy-relevant data processing is documented separately:

- The **Object** service stores the user ID of the account that created the category as its owner, together with object creation and update timestamps.
- [AccessControl](../AccessControl/PRIVACY.md) stores and enforces permissions and role assignments.
- [Container](../Container/PRIVACY.md) provides the category container, including its content and container settings.
- [COPage](../COPage/PRIVACY.md) provides category page content, which may contain personal data and authorship information.
- [AdvancedMetaData](../AdvancedMetaData/PRIVACY.md) provides configurable metadata fields that may contain personal data.
- [MetaData](../MetaData/PRIVACY.md) provides standard and custom metadata for categories.
- [InfoScreen](../InfoScreen/PRIVACY.md) presents integrated information such as notes, news and metadata.
- [Export](../Export/PRIVACY.md) provides the export interface used by the category.
- The **Tracking** service records read events for category access, including the user ID, access timestamps, read count and time spent. 
- The **User** service provides local user accounts, profile data, user search, account deletion and user administration screens.

## Configuration

- **Local user administration**: The local user administration feature must be enabled in the user account settings before it is available for categories.
- **Category permission `cat_administrate_users`**: Accounts with this permission can list, search, create, import and delete local user accounts in the category and change their assignable roles.
- **Profile publication and visible profile fields**: These User-service settings influence which profile fields are displayed in the local-user table and whether first and last names are included in name presentations. The account name is displayed independently of public-profile publication in administrative contexts.

## Data being stored

The Category component itself does not store any personal data directly.

## Data being presented

- **Local-user administration**: Accounts with `cat_administrate_users` permission can see a table of local accounts associated with the category. The table always includes the **account/login name** and can include **first name**, **last name**, e-mail address, access and login dates, account dates, authentication information, organisational units and configured custom profile fields.
- **User search**: The local-user administration autocomplete searches the **account/login name**, **first name**, **last name** and **e-mail address**. Search results are available only through the protected administration functions.

## Data being deleted

- **Local user deletion**: An administrator with `cat_administrate_users` permission can delete selected local accounts. The User service performs the account deletion and the associated AccessControl assignments are handled by that service.
- **Role assignment changes**: Adding or removing a role changes the user-to-role relation in AccessControl; it does not delete the user account.
- **Category deletion**: When a category is permanently deleted, local accounts associated with it are not deleted. Their `usr_data.time_limit_owner` value is reassigned to the global user folder. The parent Container and Object services handle deletion of the category's container, contents, permissions, settings and other integrated data.


## Data being exported

- **Category XML export**: Accounts with write permission can export a category as XML. The Category exporter writes category translations, container sorting settings and container settings; it does not export local user accounts, user profile data or user-to-role assignments.
- Container, page, metadata and taxonomy information included through export dependencies may contain personal data according to the respective integrated-service documentation.