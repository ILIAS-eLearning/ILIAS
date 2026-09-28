# Badge Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Badge service allows the awarding of digital badges to users for various achievements.
Badges can be awarded automatically (triggered by events like course completion) or manually by persons with
appropriate permissions. Users can view and manage their received badges under
**Achievements > Badges**.

Badge instances are typically managed on **courses** and **groups** (when the badges
feature is activated for the object). Other components can provide badge types via the
badge provider API (e.g. profile badges from the User service).

Events such as trigger automatic awards:

- **Learning progress completion** of a course (`Tracking/updateStatus`)
- **Profile updates** for profile badges (`User/afterUpdate`)

Badges are not awarded retrospectively when settings change; only future actions are
evaluated.

The Open Badges backpack **export** from ILIAS is no longer supported and has been disabled.
Mozilla has shut down the Open Badges backpack service, and ILIAS has removed the export functionality
(see [Mantis #20124](https://mantis.ilias.de/view.php?id=20124)).

## Integrated Services

The Badge component employs the following services (please consult their respective PRIVACY.md files):

- **[User](../User/PRIVACY.md)**: Stores user references for assignments and triggers
  badge evaluation on profile updates.
- **[Tracking](../Tracking/PRIVACY.md)**: Triggers badge awards on learning progress
  status changes.
- **[Mail](../Mail/PRIVACY.md)**: Sends email notifications when badges are awarded.
  The Mail service is used as a transport channel; badge titles and links are passed
  through as message content.
- **[Notifications](../Notifications/PRIVACY.md)**: Sends on-screen notifications when
  badges are awarded.
- **[AccessControl](../AccessControl/PRIVACY.md)**: Controls who can manage badges and
  view badge assignments.
- **[LearningHistory](../LearningHistory/PRIVACY.md)**: Presents awarded badges in the
  user's learning history timeline.
- **[ResourceStorage](../ResourceStorage/PRIVACY.md)**: Stores badge images referenced
  by badge definitions.
- **GlobalScreen**: Displays badge notification counters in the main bar.

## Data being stored

- **Badge Definition**: Badge instances are stored in the database table `badge_badge`.
  This includes title, description, criteria, configuration, and references to the
  parent object and badge image. These definitions do not contain personal data by
   themselves but may reference user-configured content in their text fields.

- **Badge Assignment**: When a badge is assigned to a user, the following data is
  stored in the database table `badge_user_badge`:
  - The user ID of the badge recipient
  - The timestamp when the badge was awarded
  - The user ID of the person who manually awarded the badge (only for manual awards;
    not displayed as a person's name in the user interface)
  - A position value (`pos`) controlling visibility and ordering on the user's profile
    (`null` = not shown on the public profile)

- **User Preferences**: The following user preferences are stored via the user
  preference system:
  - `badge_last_checked`: Timestamp of when the user last viewed their badges
  - `badge_mozilla_bp`: Optional email address for badge backpack integration

- **Static Open Badges Files**: Legacy static JSON files for Open Badges publishing may
  exist under the webspace directory `pub_badges/`. These are removed when
  assignments or badge definitions are deleted.

## Data being presented

- **Personal Badge View**: Each user can view their own badges under
  **Achievements > Badges**. This includes badge title, description, image, award date,
  and the name of the awarding object (parent repository object).

- **Public User Profile**: Users can choose which badges are visible on their public
  profile (controlled via the `pos` field). Other users who can access the profile see
  badge title, image, and awarding object. Only badges with a positive position value
  are shown.

- **Badge Management View**: Persons with **write** permission on a course or group can
  access the badge management interface. This view presents:
  - Names (firstname, lastname) of users who have been awarded badges
  - Login names of badge recipients
  - The date when badges were issued
  - The badge type and title
  - The parent object where the badge was awarded (in container context)

- **Administration View**: Persons with **read** permission on the Badge
  Administration can view all object badges and their assignments across the system.
  This includes:
  - List of users with badge assignments, showing name, login, badge type, title, and
    issue date
  - The parent object where the badge was awarded

- **Badge Award Information**: When viewing a badge in the tile view, the name of the
  awarding object is displayed to the badge recipient. The label "awarded by" refers to
  the parent object, not to the user who performed a manual award.

- **Notifications**: When badges are awarded, users receive:
  - An **email** containing badge titles and a link to their badge profile
  - An **on-screen notification** with badge titles and a link

- **Learning History**: Awarded badges appear in the user's learning history with badge
  title, context object, and timestamp.

## Data being deleted

- **User Deletion**: When a user account is deleted, all badge assignments for that
  user are automatically deleted (`ilBadgeAssignment::deleteByUserId`).

- **Badge Deletion**: When a badge definition is deleted, all assignments of that
  badge to users and associated static files are automatically deleted.

- **Manual Removal**: Persons with **write** permission on a repository object can
  manually remove badge assignments from individual users via the "Users" tab in badge
  management.

- **Object Deletion**: When a repository object (e.g. course, group) is permanently
  deleted, badge definitions in `badge_badge` and their assignments are **not**
  automatically cleaned up. The method `ilBadgeAssignment::deleteByParentId` exists
  but is not hooked into the object deletion workflow. Orphaned badge definitions may
  remain in the database referencing deleted objects.

## Data being exported

- **No Export of Assignments**: There is no built-in export functionality for badge
  assignment data.

- **Backpack Export (Disabled)**: Exporting badges to an external Open Badges backpack
  is currently disabled.

- **Backpack Import / Display**: Code for displaying badges from an external Mozilla
  Open Badges backpack (using the email stored in `badge_mozilla_bp`) still exists, but
  this feature is not actively maintained.

- **Object Copy**: When a course is copied, badge definitions are cloned to the new
  object. User assignments are not copied.
