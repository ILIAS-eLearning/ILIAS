# Notifications

The key words “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”,
“SHOULD NOT”, “RECOMMENDED”, “MAY”, and “OPTIONAL” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
<!-- TOC -->

  - [ILIAS configuration](#ilias-configuration)
    - [General Settings](#general-settings)
      - [Enable on-screen notifications](#enable-on-screen-notifications)

<!-- /TOC -->

## ILIAS Configuration

### General Settings

#### Enable on-screen notifications

If enabled, users will be informed by pop-up notifications about certain events.

If disabled, the users will no longer be informed and all existing OSD Notifications will be deleted

![](./docu/images/settings_enable_osd_en.png)

***Note**: Less time allows more timely notifications, but increases server load.*

## Usage

### OSD Notifications

Services can provide notifications to the system by calling;

```php
$DIC->notifications()->system()->toUsers($config, $users, $async);
```

with a `ilNotificationsConfig` object, a list of user ids and a flag marking async calls.

The config needs a type to be created. This type defines your scope of notifications in which you are able to create and delete
notifications. You may use an existing type if communicated with the maintainer of its scopes service. If given the type used
should be identical with the type of your `NotificationProvider`.

Furthermore you can set following properties on your notification configuration:

- `$config->setTitleVar($title)` The title of your Notifications. The passed argument may be an ILIAS language variable.
    - The notification system tries to translate the title if a respective translation could be determined. Otherwise the passed string will be processed untouched and finally passed to the output channels. If you want to translate it you have the option
      to add an additional parameter for replaceables and another for a language module to load e.g. `$config->setTitleVar($title, [$username], 'usr')`
- `$config->setShortDescriptionVar($description)` The description for pace limited presentations (e.g. popups). It translates the same as title.
- `$config->setLongDescriptionVar($description)` The full description (not used for OSD). It translates the same as title.
- `$config->setLinks($links)` A list of `ilNotificationLinks` which will presented as translated links below the description.
- `$config->setIconPath($path)` The path to the icon for your notifciation type. It will be shown in the indent of the notification or on top of groups of your notification type.
- `$config->setValidForSeconds($time)` The time will be visible for the user in target presentations.
- `$config->setIdentification($identification)` A unique `NotificationIdentification` which can be used to identify this specific notification case for future deletions.
- `$config->setHandlerParam($handler_name, $params)` Parameters that will be given to the handler for additional informations (not used for OSD).

**Important: A notification, which is no longer valid, will not be seen in any UI, but still persists in the database. Therefore all consumers
of the notification service are obliged to remove their notifications on the appropriate system events (e.g. a notification emitted because of a contact request can be removed if the request is approved) .**

You can remove a notification by using the following functions within the `ilNotificationOSDHandler`:
- `removeOSDNotificationByIdentification($type, $identification)` This removes all notifications within your `$type` which are
  compatible with the `NotificationIdentification` given by `$identification`. Optional you may add a user id as parameter to remove
  this notification only for one user.