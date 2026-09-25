# Like Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Like component provides a reaction/emoticon service that allows authenticated users to express
reactions (Like, Dislike, Love, Laugh, Astounded, Sad, Angry) on ILIAS objects, subobjects, and
news items. It is primarily used within the News timeline. The Like component is a service
component: it does not define its own repository objects and has no standalone access control.
Access to the Like widget is governed entirely by the consuming component (e.g., the News
timeline checks "read" permission on the parent object). Anonymous users are explicitly prevented
from storing reactions.

## Integrated Components

- The Like component employs the following components, please consult the respective PRIVACY.md
  files:
    - [News](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/News/PRIVACY.md) – the primary consumer of the Like widget. The News timeline
      embeds Like reactions on each news item and controls access via its own permission checks.
    - User – user names and profile pictures are resolved from the User
      component when displaying the detail modal of who reacted.

## Data being stored

- **User ID of the reacting user**: When a user adds a reaction to an object, subobject, or news
  item, their **user ID** is stored as part of the primary key in the `like_data` table. This
  identifies who expressed the reaction.
- **Reaction type**: An **integer value** (`like_type` column) representing which of the seven
  expression types (Like, Dislike, Love, Laugh, Astounded, Sad, Angry) the user selected. Stored
  in the `like_data` table as part of the primary key.
- **Timestamp of the reaction**: The **date and time** (`exp_ts` column) when the reaction was
  added is stored in the `like_data` table. This is used to sort reactions chronologically.
- **Object and subobject context**: The **object ID** (`obj_id`), **object type** (`obj_type`),
  **subobject ID** (`sub_obj_id`), **subobject type** (`sub_obj_type`), and **news ID**
  (`news_id`) are stored to associate the reaction with its target. These are structural
  identifiers, not personal data by themselves, but together with the user ID they form a record
  of a user's activity.

## Data being presented

- **Each user** can see the aggregated reaction counts (number of Likes, Loves, etc.) on any
  object or news item they have access to. Clicking on the reaction counters opens a detail modal.
- **Each user** with access to the containing object (e.g., persons with "read" access to the
  parent object in the News timeline) can see the detail modal, which shows:
    - the **name** and **profile picture** of each user who reacted,
    - the **type of reaction** each user selected, and
    - the **date and time** of each reaction.
- **Each user** can see which of their own reactions are currently active (highlighted in the
  emoticon popover).
- Access to the Like widget is not controlled by a Like-specific permission. Instead, it is
  governed by the consuming component. For example, in the News timeline, any person with the
  "read" permission on the parent object can see and interact with the Like widget.

## Data being deleted

- **When a user removes their own reaction**: the corresponding record is deleted from the
  `like_data` table immediately. There is no soft-delete or trash mechanism for individual
  reactions. Once removed, the record is permanently gone and there is no way to determine that a
  reaction once existed.
- **When the parent object is deleted**: the Like component does not independently clean up its
  data when a parent object (e.g., a course or news item) is deleted. The `like_data` records
  referencing the deleted object's `obj_id` become orphaned but remain in the database.
- **When a user account is deleted**: there is currently no deletion hook that removes `like_data`
  records for the deleted user. The records (containing the former **user ID**, reaction type, and
  timestamp) remain in the `like_data` table as residual data. Since the user account no longer
  exists, the user ID can no longer be resolved to a name.

## Data being exported

- The Like component does not provide any export functionality. There is no XML export, file-based
  export, or API endpoint for exporting reaction data.
