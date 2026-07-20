# Feeds Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Feeds component generates RSS/Atom feeds for ILIAS content. It does not store any personal
data itself. Instead, it reads data from other components (primarily News and User) and presents
it as XML feeds accessible via URL endpoints.

Note: This component is referred to as "Web Feed" in the ILIAS user interface, e.g. in the
Administration settings screen "News and Web Feeds" (object type `nwss`), which configures the
`enable_rss_for_internal` and `enable_private_feed` settings described below. The component itself
is named `Feeds` in the codebase.

There are two feed types:

- **Public feeds** (`feed.php`): Provide news items for a specific user or repository object. Access
  is controlled by a per-user **feed hash** (a 32-character token embedded in the feed URL). The
  feed hash is stored and managed by the User component. Public feeds only include news items
  marked with public visibility, unless a user ID is provided. This feature is only available when
  the global setting "enable_rss_for_internal" is active. For object feeds, the per-object
  "public_feed" block setting must also be enabled.
- **Private feeds** (`privfeed.php`): Require HTTP Basic Authentication with the user's ILIAS login
  and a dedicated **feed password** (stored as the `priv_feed_pass` user preference by the User
  component). Private feeds include all news items the authenticated user has access to. This
  feature is only available when the global setting "enable_private_feed" is active.

The feed URLs contain the **user ID** as a query parameter, which means that a user's numeric ID
is exposed in the URL string shared with or used by RSS reader applications.

## Integrated Components

- The Feeds component employs the following components, please consult the respective PRIVACY.md
  files:
    - [News](../News/PRIVACY.md) -- core data source. All feed content (news item titles,
      descriptions, creation dates, and context information) is retrieved from the News component.
      The News component determines which items are visible based on the feed type (public or
      private) and the configured RSS period.
    - [User](../User/PRIVACY.md) -- provides authentication data for feed access. The feed hash (`feed_hash` column in
      `usr_data`) is used to validate public feed requests. The feed password (`priv_feed_pass`
      user preference) is used for HTTP Basic Authentication on private feeds. User login names
      are used for authentication verification.
    - [Refinery](../Refinery/PRIVACY.md) -- `feed.php` validates and casts the incoming `user_id`,
      `ref_id`, `purpose`, `blog_id`, and `hash` query parameters via `$DIC->refinery()->kindlyTo()`
      before they are used to look up personal data. Note: `privfeed.php` reads the equivalent
      parameters directly from `$_GET` without going through Refinery.
    - [Tree](../Tree/PRIVACY.md) -- resolves the full repository path for a news item's reference ID
      (`ilTree::getPathFull()`) to build the deep link included in each feed item.
    - [Blog](../Blog/PRIVACY.md) -- the `feed.php` endpoint delegates Blog RSS delivery to the
      Blog component via `ilObjBlog::deliverRSS()`.
    - [MediaCast](../MediaCast/PRIVACY.md) -- for MediaCast objects, feed items may include
      media enclosures (audio/video URLs, file sizes, MIME types). Online status and public file
      settings of MediaCast objects are checked before inclusion.
    - [MediaObjects](../MediaObjects/PRIVACY.md) -- media item paths and formats are resolved
      for enclosure generation in MediaCast-related feed items.
    - [Wiki](../Wiki/PRIVACY.md) -- wiki page titles are resolved to generate correct deep links
      for wiki-related news items in the feed output.
    - [Forum](../Forum/PRIVACY.md) -- forum thread IDs are resolved to generate correct deep
      links for forum posting-related news items in the feed output.

## Data being stored

The Feeds component does not store any personal data. It is a read-only presentation layer that
generates RSS XML output on the fly from data managed by other components.

All personal data involved in feed generation and authentication (feed hashes, feed passwords,
user IDs, news items) is stored and managed by the User and News components.

## Data being presented

- **Each user** can access their own public feed via a URL containing their **user ID** and
  **feed hash**. This URL is displayed to the user in the News block on the personal desktop.
  The feed contains news item titles, descriptions, creation dates, and links to the corresponding
  ILIAS objects. Only news items with public visibility are included.
- **Each user** who has configured a feed password can access their own private feed via HTTP
  Basic Authentication. The private feed contains all news items the user has access to,
  including items that are not publicly visible. The feed URL contains the user's **user ID**
  and **feed hash**.
- **Anyone** with access to a public object feed URL can view the feed for that object, provided
  the object has the "public_feed" block setting enabled and "enable_rss_for_internal" is active
  globally. Object feeds contain news titles, descriptions, creation dates, and links. For
  MediaCast objects, media enclosures (URLs, file sizes, MIME types) may be included if public
  files are enabled.
- The Feeds component itself does not perform permission checks. Access control is delegated to:
  the feed hash validation (User component), HTTP Basic Authentication (User component), the
  news visibility logic (News component), and per-object feed settings (News block settings).

## Data being deleted

The Feeds component does not store any personal data and therefore has no deletion logic.

Deletion of feed-related personal data is handled by the components that store it:

- **When a user account is deleted**: The feed hash (`feed_hash` in `usr_data`) and the feed
  password preference (`priv_feed_pass`) are deleted by the User component. After deletion,
  feed URLs previously generated for this user become invalid.
- **When RSS settings are disabled**: Disabling the "enable_rss_for_internal" or
  "enable_private_feed" settings prevents feed generation, but does not delete any stored data.

## Data being exported

The Feeds component does not provide any data export functionality. Its sole purpose is to
present existing news data as RSS feeds. The RSS feed output itself could be considered a form
of data presentation (not export in the ILIAS sense), and its content is determined entirely by
the News component.
