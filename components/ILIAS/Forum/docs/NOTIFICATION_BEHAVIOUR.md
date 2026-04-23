# Forum: member notifications (settings and persistence)

This document describes the **intended behaviour** of course/group forum notifications (the *Settings → Notifications* tab, `showMembers`) and the underlying tables. It complements Testrail cases and explains mode switches and persistence for developers.

## Terms and storage

| Term | Storage | Meaning (short) |
|------|---------|-----------------|
| Notification **mode** | `frm_settings` (via `ilForumProperties`) | `DEFAULT` / `ALL_USERS` / `PER_USER` (`NotificationType`) |
| Forum-wide flags | `frm_settings`, e.g. `container_enforces_noti`, `member_may_disable_noti`, `interested_events` | Course/group or admin-level defaults |
| Member / subscription rows | `frm_notification` (forum-wide rows typically `thread_id = 0`) | Per `user_id` + `frm_id`: enforced?, may deactivate?, which events? |

Main code entry points:

- `ilForumSettingsGUI` — form, save (`updateNotificationSettingsCommand`), *Notifications* tab
- `ilForumNotification::applyTypeConfigurationFor()` — sync rows after mode / property changes
- `ilForumNotification::readAllForcedEvents()` / `getForcedEventsObjectByUserId()` — request cache for the **member table** (PER_USER)
- `ilForum::isForumNotificationEnabled()` — header bell: currently **counts** rows for `user_id` + `frm_id` (no check whether events are “active”)

## Three modes (`NotificationType`)

1. **DEFAULT** — members decide themselves (classic subscription via forum UI); no forced container provisioning through this tab.
2. **ALL_USERS** — global rules for all members; the checkbox under **All members** uses the `user_toggle_noti` language key (German: *Benachrichtigungen können nicht deaktiviert werden.*). **`usr_toggle` is inverted in the GUI**: checked means locked (`member_may_disable_noti` stored as *false* / members may not turn notifications off). The event checkboxes belong to this mode and are stored in `frm_settings` (including `interested_events`).
3. **PER_USER** — global frame data in `frm_settings`; **per member** e.g. `member_may_disable_noti` and `interested_events` in `frm_notification`, editable via the table and bulk actions `member_may_disable_noti_lock` / `member_may_disable_noti_allow` (UI labels use the existing lang keys `enable_hide_user_toggle` / `disable_hide_user_toggle`; for copy changes, update **`ilias_de.lang`** and **`ilias_en.lang`** only so other locales stay untouched in PRs).

**Context:** The *Notifications* tab is only shown when the forum sits under a course or group with membership (`isParentMembershipEnabledContainer()`); see general forum documentation elsewhere.

## Mode switches and what stays in `frm_settings`

- **`member_may_disable_noti` in `frm_settings`** (maps to the checkbox under **All members**):  
  It is **no longer** forced to “off” when you only switch to **Default** or **Per user** and save. The value last saved under **All members** remains the default for new members and when you open that option again.
- **`usr_toggle` is taken from POST** only when mode **All members** is active and the form is saved — without saving in that mode, the stored value does not change. The checkbox label is `user_toggle_noti`; the value posted as checked is converted to `member_may_deactivate` / `member_may_disable_noti` semantics as described above.

## `applyTypeConfigurationFor()` — syncing `frm_notification`

After saving settings, member rows are aligned with the selected mode.

| Target mode | Main behaviour |
|-------------|----------------|
| **DEFAULT** | For each existing row: `container_enforces` from properties; **`member_may_disable_noti` and event interest stay per row** (not overwritten with a global flag) so PER_USER fine-tuning is not lost after temporarily switching to Default. |
| **ALL_USERS** | Existing rows: container and member-may-disable flags from forum properties; if members **may not** deactivate, interest events are reset to the forum default, otherwise per-user chosen events are kept. Missing members: insert with forum defaults. |
| **PER_USER** | Existing rows: keep `member_may_disable_noti` per row; if “may not deactivate”, set events to forum default. Missing members: insert using `frm_settings` (including default for “may deactivate”). |

## *Notifications* tab / member table (PER_USER)

- **`readAllForcedEvents()`** loads **all** forum-wide rows (`frm_id`, `thread_id = 0`) into a **request cache**, not only `container_enforces_noti = 1`. Members with a normal subscription or without enforcement are in the cache without wrong assumptions on mere display.
- **`getForcedEventsObjectByUserId()`** must **not** `INSERT` a new `frm_notification` row for **UI-only** use (table, modal) when the member has no forum-wide row: otherwise `ilForum::isForumNotificationEnabled()` (COUNT > 0) would wrongly turn the header bell on. Instead an **in-memory** display object is used (`createDisplayOnlyForumNotification()`), including `member_may_disable_noti = true` when there is no DB row (“no admin lock on that row”).

## Known technical limitation (header bell)

`isForumNotificationEnabled()` currently only checks whether **any** row exists in `frm_notification` for user + forum — not whether `interested_events` are meaningfully “active”. Changing that would be a separate product/refactoring topic.

## Tests / Testrail

- PHPUnit: e.g. `components/ILIAS/Forum/tests/ilForumNotificationTest.php`
- Acceptance: often in **Testrail**; this file holds the **expected business/technical invariants** so manual tests and tickets stay aligned.

## Change history (brief)

Behaviour around mode switches, cache/INSERT, and preserving `member_may_disable` was refined for the intended UX and documented here (see Git history of the classes mentioned above).
