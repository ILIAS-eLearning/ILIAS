# Calendar Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Calendar component enables users to manage personal, shared, and object-linked calendar entries in ILIAS. Personal calendars are owned by a specific user account (`cal_categories.type = 1`, `obj_id` = user ID). Object-linked calendars (type 2) are created automatically when courses, groups, sessions, or exercise assignments are created or updated, via the event listener `ilCalendarAppEventListener`. The component also supports consultation hour calendars (type 4) and booking manager calendars (type 5). Users can subscribe to calendars via authenticated iCal URLs; the generated iCal feed is cached per user token. Calendar visibility preferences are stored per user and affect which calendars appear in the personal dashboard view.

## Integrated Components

The Calendar component employs the following components; please consult their respective PRIVACY.md files:

- User — user IDs are stored as calendar owners, booking participants, notification recipients, and calendar sharing targets.
- [AccessControl](../AccessControl/PRIVACY.md) — calendar visibility and edit rights for shared calendars are controlled via RBAC roles stored in `cal_shared`.
- [Course](../Course/PRIVACY.md) — course creation/update/deletion events trigger automatic generation or removal of course calendar categories and appointments.
- [Group](../Group/PRIVACY.md) — group events are handled the same way as course events for automatic calendar generation.
- Session — session objects create auto-generated calendar appointments.
- [Exercise](../Exercise/PRIVACY.md) — exercise assignment creation/update/deletion events create or remove calendar appointments.
- [BookingManager](../BookingManager/PRIVACY.md) — consultation hour slot definitions and bookings are stored in `booking_entry`, `booking_user`, and `booking_obj_assignment`.

## Data being stored

The Calendar component stores the following personal data:

- **Calendar categories** (`cal_categories`): stores `obj_id` as the owning user ID (for personal calendars, `type = 1`) or as the linked object ID (for object calendars). For remote calendar subscriptions (`loc_type = 2`), `remote_url`, `remote_user`, and `remote_pass` are stored in plaintext.
- **Calendar entries** (`cal_entries`): stores `title`, `description`, `location`, `starta` (start datetime in UTC), `enda` (end datetime in UTC), and `last_update`. Entries are not directly associated with a user ID in this table; ownership is derived through the category assignment in `cal_cat_assignments`.
- **iCal authentication tokens** (`cal_auth_token`): stores `user_id`, a `hash` token (MD5 of user ID, selection type, and random value), `selection` type, targeted `calendar` ID, a cached iCal string (`ical`), and a cache timestamp (`c_time`). These tokens enable external calendar applications to subscribe to a user's calendar without a password.
- **Calendar sharing** (`cal_shared`): stores `cal_id`, `obj_id` (user ID or role ID), `obj_type` (user or role), `create_date`, and `writable` flag. Records which users or roles a calendar has been shared with.
- **Shared calendar status** (`cal_shared_status`): stores `cal_id`, `usr_id`, and `status` (accepted or declined) representing each user's response to a shared calendar invitation.
- **Category visibility** (`cal_cat_visibility`): stores `user_id`, `cat_id`, `obj_id`, and `visible` flag, tracking which calendar categories each user has hidden or shown.
- **Appointment notifications** (`cal_notification`): stores `cal_id` (appointment ID), `user_type`, `user_id`, and `email` for each configured notification recipient of a calendar appointment.
- **Appointment registrations** (`cal_registrations`): stores `cal_id` (appointment ID), `usr_id`, `dstart`, and `dend` for users who have registered for a specific appointment occurrence.
- **Consultation hour bookings** (`booking_user`): stores `entry_id` (appointment ID), `user_id`, and `tstamp` when a user books a consultation hour slot.
- **Consultation hour admin delegation** (`cal_ch_settings`): stores `user_id` and `admin_id`, recording which user has been delegated to manage another user's consultation hours.
- **User calendar preferences**: stored as ILIAS user preferences (not a dedicated table) on the `ilObjUser` object — includes `weekstart`, `day_start`, `day_end`, `show_weeks`, `calendar_selection_type`, and `export_tz_type`.

## Data being presented

- Personal calendar entries are presented exclusively to the owning user in the personal desktop calendar view.
- Course, group, and session calendar entries are presented to all members of the respective object (persons with "read" permission on the course, group, or session).
- Shared calendar entries are presented to the specific users or role members with whom the calendar was shared, after they accept the sharing invitation.
- In the calendar sharing administration view, the first name, last name, and login of candidate users are displayed to the calendar owner (the person who owns the calendar category) via `ilCalendarSharedUserListTableGUI`.
- Consultation hour appointment details, including the number of current and free booking slots, are displayed to the slot owner (person with "read" permission on the consultation hour object) and anonymized booking counts to other users.
- Persons with "read" permission on a consultation hour appointment can see names of users who have booked a slot via the booking entry details view.

## Data being deleted

- **User account deletion**: When a user account is deleted, the following personal data is removed automatically:
  - Rows in `cal_shared` where `obj_id` matches the user ID (`ilCalendarShared::deleteByUser()`).
  - Rows in `cal_shared_status` where `usr_id` matches the user ID (`ilCalendarSharedStatus::deleteUser()`).
  - Rows in `cal_cat_visibility` where `user_id` matches the user ID (`ilCalendarVisibility::_deleteUser()`).
  - Rows in `cal_notification` where `user_id` matches the user ID (`ilCalendarUserNotification::deleteUser()`).
  - Rows in `cal_registrations` where `usr_id` matches the user ID (`ilCalendarRegistration::deleteByUser()`).
  - Personal calendar categories (`cal_categories` with `type = 1`) and all associated entries (`cal_entries`, `cal_cat_assignments`, `cal_recurrence_rules`, `cal_rec_exclusion`) are removed when the personal calendar is deleted.
  - iCal tokens in `cal_auth_token` for the user are deleted on user account removal.
- **Object deletion**: When a course, group, session, or exercise is deleted, the corresponding auto-generated calendar category and all its appointments are deleted via `ilCalendarAppEventListener::deleteCategory()` and `deleteAppointments()`.
- **Manual deletion by user**: A person with "write" permission on a personal calendar category can delete individual appointments or entire categories, which removes the entries from `cal_entries` and `cal_cat_assignments`.
- **Consultation hour booking cancellation**: A user can cancel their own booking, removing the record from `booking_user`. The calendar owner can also cancel bookings, which triggers a cancellation mail notification.
- There is no automatic time-based expiry for personal calendar entries or iCal tokens. The iCal cache in `cal_auth_token.ical` expires based on the synchronisation cache setting configured by the administrator.

## Data being exported

- **iCal export**: A person with access to a calendar can export selected calendars or individual appointments in iCal (`.ics`) format via `ilCalendarExport`. The export includes appointment titles, descriptions, locations, start and end datetimes, and recurrence rules, but does not embed user IDs or names directly in the iCal output.
- **External subscription**: Users can generate a personal iCal subscription URL authenticated by a token stored in `cal_auth_token`. This URL can be used by external calendar applications (e.g., Outlook, Apple Calendar) to subscribe to and continuously retrieve the user's ILIAS calendar. The cached iCal feed is stored in the `ical` column of `cal_auth_token` and served to the subscribing client.
