# Session Privacy
> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Session component manages event objects (e.g., lectures, workshops, seminars) in the ILIAS repository. It supports participant registration (direct, by request, or disabled), waiting lists with optional auto-fill, participation tracking, and configurable membership notifications. A session stores tutor/organizer contact details (name, phone, email) that are manually entered by the session creator. Participant data is tracked per-event and includes registration and participation flags. Learning progress (mark and comment) is stored through the Learning Progress component and continues to be tracked regardless of whether the learning progress mode is set to active or deactivated; the LP mode only controls what is displayed, not what is stored.

## Integrated Components

The Session component makes use of the following components that handle personal data:

- Tracking — stores a mark and comment for each participant per session via `ilLPMarks`
- [Mail](../Mail/PRIVACY.md) — sends automated membership notification e-mails to participants and administrators via `ilSessionMembershipMailNotification`
- Membership — manages participant role assignments and waiting list entries via `ilSessionParticipants`, `ilSessionWaitingList`, and `ilWaitingList`

## Data being stored

The Session component stores the following personal data:

**In the `event` table (session configuration):**
- Tutor/organizer name (`tutor_name`), phone number (`tutor_phone`), and e-mail address (`tutor_email`) — entered manually by the session creator; used to display contact information to participants. These fields are optional.

**In the `event_participants` table (per-user participation records):**
- User ID (`usr_id`) linked to: registration status (`registered`), participation status (`participated`), excused status (`excused`), contact role flag (`contact`), and notification preference (`notification_enabled`) — stored to track whether a user registered for and/or participated in the session.

**Via the Learning Progress component:**
- Mark and comment per participant (stored in `ilLPMarks`) — recorded by a person with "manage_members" permission to document individual session performance.

## Data being presented

The following personal data is visible in the Session component:

- The session participants list (accessible to persons with "manage_members" permission) displays each participant's login name, first name, last name, registration status, participation status, excused status, contact role, mark, and comment.
- Tutor/organizer name, phone, and e-mail are displayed on the session's info page and are visible to all users who can read the session object.
- If the session's "Show Members" option is enabled (configurable per session), the participant list may also be visible to registered participants themselves.

## Data being deleted

- When a session object is deleted from the repository, all records in `event_participants` for that session are removed via `ilEventParticipants::_deleteByEvent()`, and all associated learning progress marks are deleted via `ilLPMarks::deleteObject()`.
- When a user account is deleted, that user's records are removed from `event_participants` across all sessions via `ilEventParticipants::_deleteByUser()`. Note: this method exists in the codebase but must be called by the user administration component on account deletion.
- A person with "manage_members" permission can manually remove individual participants via the participant management interface; this clears the participant's registration and participation flags in `event_participants`.
- A registered user can unregister from a session themselves, which removes them from the waiting list via `ilSessionWaitingList::deleteUserEntry()`.

## Data being exported

The Session component provides an XML export (via `ilSessionExporter` and `ilSessionDataSet`) that includes session configuration data. The export contains the tutor/organizer name (`TutorName`), e-mail address (`TutorEmail`), and phone number (`TutorPhone`) from the `event` table, as well as session scheduling and registration settings. Individual participant records (user IDs and participation flags from `event_participants`) are not included in the export.
