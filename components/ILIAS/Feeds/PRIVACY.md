# Web Feed Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## Integrated Services

The Web Feed component (Feeds) employs the following services, please consult the respective privacy documentation:

- [News](../News/PRIVACY.md) provides the content (**news items**) for the feeds.
- The **User** service handles authentication for private feeds (**feed password**, **login**) and provides user-specific feed identification (**feed hash**).
- [AccessControl](../AccessControl/PRIVACY.md) is used to check the visibility of objects and content.
- [Blog](../Blog/PRIVACY.md) provides blog-specific RSS feeds.
- [MediaCast](../MediaCast/PRIVACY.md) is integrated for special handling of media cast objects and their **enclosures** and public-file setting.
- [MediaObjects](../MediaObjects/PRIVACY.md) provides media items used for feed enclosures.

## General Information

The Web Feed component provides the infrastructure to generate and deliver RSS and Atom feeds in ILIAS. It allows users to subscribe to news items from objects or their personal news stream. The component itself does not store personal data; it presents content and authentication-related identifiers managed by other services.

Public personal feeds are identified by a **user ID** and **feed hash** in the feed URL and contain news items that are publicly visible. Public object feeds can be accessed without authentication when the relevant feed settings are enabled. Private personal feeds require HTTP Basic Authentication with the user's ILIAS **login** and **feed password** and can include news that is not publicly visible.

## Configuration

- **Global News Settings**: Administrators can enable or disable internal RSS feeds globally and can enable or disable private feeds.
- **Object Settings**: Authors can enable or disable public feeds for specific objects. This controls whether news from the object can be accessed through a public object feed.
- **User Profile**: Users can manage their **feed password** in their personal profile (handled by the **User** service).

## Data being stored

The Web Feed component itself does not persist personal data in its own database tables. It retrieves and formats data stored by other services. The **user ID**, **feed hash**, **feed password** and **news items** used by the feeds are stored by the **User** and [News](../News/PRIVACY.md) services, not by Feeds.

## Data being presented

The component presents data to any user or external application that has access to the feed URL or valid credentials:

- **Feed content**: The component presents news **titles**, **descriptions**, **creation dates** and **links** to the original content. News descriptions may contain user-created content and personal data.
- **Public personal feeds**: The URL contains the **user ID** and **feed hash**. Anyone with the URL can access the publicly visible news items in the feed.
- **Private personal feeds**: HTTP Basic Authentication uses the ILIAS **login** and **feed password**. The feed URL contains the **user ID**, and the feed can include news that is not publicly visible but is accessible to the authenticated user.
- **Public object feeds**: Anyone with the feed URL can access publicly visible news items when the global RSS setting and the object's public-feed setting are enabled.
- **Media enclosures**: Feeds for MediaCast objects may include media URLs, file sizes and MIME types when public files are enabled.

## Data being deleted

As the Web Feed component does not store personal data itself, there is no specific data deletion logic. Data removal is handled by the respective source services:

- **User Deletion**: When a user is deleted, their **feed hash** and **feed password** are removed by the **User** service. News data is handled by the [News](../News/PRIVACY.md) service.
- **Disabling feeds**: Disabling global or object-level feed settings prevents the corresponding feeds from being generated but does not delete the underlying user or news data.

## Data being exported

- **RSS/Atom feeds**: The feeds deliver news items in standardized XML formats to external feed readers. They may contain **titles**, **descriptions**, **creation dates**, links and, for eligible MediaCast items, media enclosures. The content is generated from data managed by the [News](../News/PRIVACY.md), **User** and media services.
