# Notification Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Notification component provides a cross-cutting service for managing object-level notification
subscriptions. It allows users to subscribe to (or unsubscribe from) change notifications for
specific objects such as wikis, blogs, polls, exercises, data collections, learning modules, and
booking pools. The component does not send notifications itself; it stores which users have opted in
or out and provides this information to consuming components that then trigger the actual mail
delivery via the Mail component.

The component also provides `ilSystemNotification`, a convenience wrapper for composing and sending
standardised system notification emails. This class is used by many other components (e.g. Badge,
Blog, Exercise, Poll, Survey, BookingManager) to send notification mails to users.

Notification behaviour depends on a per-object mode setting stored in the `obj_noti_settings` table:
- **User-decides mode** (default): users individually opt in.
- **Default-on with opt-out**: all persons enrolled in the course/group are notified by default but may opt out.
- **Default-on without opt-out**: all persons enrolled in the course/group are notified and cannot opt out.

These modes are configured by consuming components (currently Blog and Wiki) through the embedded
`ilObjNotificationSettingsGUI`.

## Integrated Components

- The Notification component employs the following components, please consult the respective
  PRIVACY.md files:
    - [Mail]() – the `ilSystemNotification` class extends `ilMailNotification`
      from the Mail component to compose and send notification emails.
    - User – the component references user IDs in the `notification` table.
      The `ilSystemNotification` class resolves user names via `ilUserUtil::getNamePresentation()`
      for inclusion in notification emails. On user account deletion, the User component calls
      `ilNotification::removeForUser()` to clean up all notification subscriptions.
    - [Group](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Group/PRIVACY.md) – the component checks group membership via
      `ilGroupParticipants` to determine default notification recipients when the object mode is set
      to "default-on".
    - [Course](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Course/PRIVACY.md) – the component checks course membership via `ilCourseParticipants` to determine
      default notification recipients when the object mode is set to "default-on".
    - ILIASObject – the component listens to the `Service/Object` "delete" event to remove
      notification settings when a parent object is deleted.

## Data being stored

- **User ID**: When a user subscribes to notifications for an object (or is recorded as having
  opted out), their **user ID** is stored in the `notification` table together with the notification
  type and object ID.
- **Activation status**: An **activated** flag (1 = subscribed, 0 = opted out) is stored per user
  and object to track the user's notification preference.
- **Last mail timestamp**: A **timestamp** (`last_mail` column) recording when the last
  notification email was sent to the user for a given object. This is used to enforce a 180-minute
  threshold between consecutive notification emails and prevent flooding.
- **Page ID**: For page-level notification types (e.g. wiki pages, learning module pages), the
  **page ID** is stored alongside the user subscription to enable page-specific notification
  tracking.

Note: The `obj_noti_settings` table stores only per-object configuration (notification mode) and
does not contain personal data.

## Data being presented

- **Each user** can see their own notification subscription status (subscribed or not subscribed) in
  the user interface of the consuming component (e.g. a toggle button in a wiki or blog). The
  Notification component itself does not provide a standalone UI for viewing subscriptions.
- The `ilObjNotificationSettingsGUI` presents the current notification mode setting for an object.
  This does not display any personal user data -- it only shows configuration options.
- The `ilSystemNotification` class includes the **name of the user who triggered a change**
  (resolved via `ilUserUtil::getNamePresentation()`) in the body of notification emails sent to
  recipients. This data is transient email content and is not stored by the Notification component.
- No aggregated view of notification subscriptions across users is provided by this component.
  Consuming components are responsible for any additional presentation of notification-related data.

## Data being deleted

- **When a user account is deleted**: The User component calls
  `ilNotification::removeForUser(user_id)`, which deletes all rows from the `notification` table
  for that user. This removes all notification subscriptions, activation statuses, and last-mail
  timestamps across all objects.
- **When a parent object is deleted**: The event listener `ilNotificationAppEventListener` reacts
  to the `Service/Object` "delete" event and calls `ilObjNotificationSettings::delete()`, which
  removes the object's entry from the `obj_noti_settings` table.
- **When an object's notifications are cleaned up by the consuming component**: Consuming components
  (e.g. Blog, Wiki, Exercise, Poll) call `ilNotification::removeForObject(type, id)` during their
  own deletion process. This deletes all rows from the `notification` table for the given object
  type and ID, removing all user subscriptions for that object.
- **Residual data**: If a consuming component does not call `removeForObject()` during its deletion
  process, orphaned notification subscription records may remain in the `notification` table. These
  records contain user IDs but are no longer functional since the referenced object no longer exists.

## Data being exported

- The Notification component does not provide any export functionality. No notification subscription
  data (user IDs, activation statuses, or timestamps) is exported in XML, CSV, or any other format.
