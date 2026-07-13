# Web Feed Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

The Web Feed component (Feeds) employs the following services, please consult the respective privacy documentation:

- [News](../News/PRIVACY.md) provides the content (**news items**) for the feeds.
- The **User** service handles authentication for private feeds (**feed password**, **login**) and provides user-specific feed identification (**feed hash**).
- The **Object** service (ILIASObject) is used to retrieve object **titles** and **types**.
- [AccessControl](../AccessControl/PRIVACY.md) is used to check the visibility of objects and content.
- [Refinery](../Refinery/PRIVACY.md) is used for input transformation and validation of requested parameters.
- The **Tree** service is used to generate the repository **path** for objects included in the feeds.
- [Blog](../Blog/PRIVACY.md) provides blog-specific RSS feeds.
- [MediaCast](../MediaCast/PRIVACY.md) is integrated for special handling of media cast objects and their **enclosures**.

## General Information

The Web Feed component provides the infrastructure to generate and deliver RSS and Atom feeds in ILIAS. It allows users to subscribe to news items from objects or their personal news stream. Personal data primarily consists of user identifiers and news content associated with the user's activities or memberships.

## Configuration

- **Global News Settings**: Administrators can enable or disable RSS feeds globally.
- **Object Settings**: Authors can enable or disable public feeds for specific objects.
- **User Profile**: Users can manage their **feed password** in their personal profile (handled by the **User** service).

## Data being stored

The Web Feed component itself does not persist personal data in its own database tables. It retrieves and formats data stored by other services:

- **user ID**: Used to identify the user for personal feeds.
- **feed hash** / **feed password**: Used for authentication of private feeds (stored in the user preferences of the **User** service).

## Data being presented

The component presents data to any user or external application that has access to the feed URL (public feeds) or valid credentials (private feeds):

- **Feed Content**: The component presents news **titles**, **descriptions**, **publication dates**, and **links** to the original content.
- **Authentication**: **user IDs** and **feed hashes** are used in the URLs for personal/private feeds to authorize access. Private feeds also use HTTP Basic Authentication with the ILIAS **login** and **feed password**.

## Data being deleted

As the Web Feed component does not store personal data itself, there is no specific data deletion logic. Data removal is handled by the respective source services:

- **User Deletion**: When a user is deleted, their **feed password** and news associations are removed by the **User** and **News** services.
- **Object Deletion**: Deleting an object removes its news items and associated feed settings (handled by **Object** and **News** services).

## Data being exported

- **RSS/Atom Feeds**: The feeds themselves represent a data export of news items in standardized XML formats, allowing users to consume ILIAS content in external feed readers.
