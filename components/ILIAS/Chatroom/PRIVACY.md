# Chatroom Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any
> missing or incorrect information via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).**

## General Information

The Chatroom component provides real-time text-based communication within ILIAS. It consists of
two interconnected parts: a PHP backend that manages room configuration, user connections, bans,
and message history via the ILIAS database, and a Node.js chat server that handles real-time
message delivery via WebSocket connections. The Node.js server also manages On-Screen Chat (OSC)
conversations, which enable direct messaging between users outside of chatroom objects.

Several features affect the scope of personal data processing:

- **Chat history**: When enabled per room (via the "Enable History" setting), session records are
  created that log user connection and disconnection times. When disabled, no session records are
  persisted.
- **Custom usernames**: When enabled per room, users may choose a display name different from their
  real name. When disabled, the user's public name is used automatically.
- **On-Screen Chat (OSC)**: When enabled globally, direct conversations between users are stored in
  separate database tables (`osc_conversation`, `osc_messages`, `osc_activity`). Users control
  their participation via the `chat_osc_accept_msg` preference.
- **Message history display**: A per-room setting controls how many past messages are shown when a
  user enters a room.
- **Periodic cleanup**: The Node.js server includes a timed process that deletes old chat messages,
  OSC messages, OSC conversations, and OSC activity records based on a configured age threshold.

## Integrated Components

- The Chatroom component employs the following components, please consult the respective
  PRIVACY.md files:
    - AccessControl – manages permissions for chatroom access,
      moderation, and settings.
    - User – resolves user names and profile information for display in the chat. The Chatroom
      component listens to the User component's `deleteUser` event to clean up ban records.
    - Notifications – delivers on-screen notifications for chat
      invitations.
    - ILIASObject – the Object service stores the account which created a chatroom object and
      its timestamps.
    - InfoScreen – provides the info screen tab for chatroom objects.
    - OnScreenChat – manages direct messaging conversations between users. The Chatroom component
      provides user settings for On-Screen Chat participation.
    - Export – provides the export framework. Chatroom objects can be
      exported as XML including message history.
    - Mail – used for sending chat invitation notifications via email.

## Data being stored

- **User ID of connected users**: When a user enters a chatroom, their **user ID** is stored in
  the `chatroom_users` table together with the **room ID** and a **connection timestamp**. This
  tracks which users are currently connected to which rooms.
- **User data of connected users**: A JSON object containing the user's **login name**, **user ID**,
  and **profile picture visibility** preference is stored in the `userdata` column of the
  `chatroom_users` table. This data is used to display connected users in the chat interface.
- **Session records**: When chat history is enabled for a room and a user disconnects, a session
  record is written to the `chatroom_sessions` table containing the **user ID**, **user data**
  (login name, user ID), **connection timestamp**, and **disconnection timestamp**. This enables
  the history feature to reconstruct who was present during a conversation.
- **Message history**: Chat messages are stored in the `chatroom_history` table as JSON objects
  containing the **sender's user ID**, the **sender's username**, the **message content**, and
  a **timestamp**. Private messages additionally contain the **recipient's user ID** and
  **username**. This persists the conversation for history viewing and export.
- **Ban records**: When a user is banned from a chatroom, the `chatroom_bans` table stores the
  banned **user ID**, the **actor ID** (user who performed the ban), a **timestamp**, and an
  optional **remark** text. This enforces and documents the ban.
- **On-Screen Chat conversations**: The `osc_conversation` table stores a **conversation ID**,
  participant data (including **user IDs** and names as JSON), and a group flag. This enables
  direct messaging between users.
- **On-Screen Chat messages**: The `osc_messages` table stores a **message ID**, **conversation
  ID**, **sender user ID**, **message content**, and a **timestamp**. This persists direct messages.
- **On-Screen Chat activity**: The `osc_activity` table stores a **conversation ID**, **user ID**,
  **timestamp**, and a **closed flag**. This tracks when users last interacted with a conversation.
- **User preference -- accept On-Screen Chat messages** (`chat_osc_accept_msg`): Stores whether
  the user accepts direct messages from other users. Displayed on the user's Privacy Settings page.
- **User preference -- broadcast typing** (`chat_broadcast_typing`): Stores whether the user's
  typing activity is broadcast to other participants in real time. Displayed on the user's
  Privacy Settings page.
- **User preference -- browser notifications** (`chat_osc_browser_notifications`): Stores whether
  the user receives browser notifications for On-Screen Chat messages.
- **User preference -- hide automatic messages** (`chat_hide_automsg_{room_id}`): Stores per room
  whether the user has chosen to hide system-generated messages (join/leave notices).
- **User preference -- invitation notification mute timestamp** (`chatinv_nc_muted_until`): Stores
  a timestamp indicating when the user last dismissed chat invitation notifications.

## Data being presented

- **Each user** can view:
    - the list of currently connected users in a chatroom (username and profile picture).
    - their own and other users' public chat messages, including sender username, message content,
      and timestamp.
    - private messages they have sent or received.
    - their own On-Screen Chat conversations and messages (when OSC is enabled).
- **Each user** can view the chat history (when the "Enable History" setting is active for the
  room), filtered by date range. The history shows message content and sender usernames.
- **Persons with the "moderate" permission** can additionally:
    - view the list of banned users, including **login name**, **first name**, **last name**,
      **ban timestamp**, and the **name of the person who performed the ban**.
    - kick users from the chatroom.
    - ban users from the chatroom.
    - clear the entire message history of a room.
- **Persons with the "Write" permission** can:
    - edit chatroom settings, including enabling or disabling chat history.
    - access the export tab.
- Whether a user's profile picture is shown or an anonymous avatar depends on the
  **profile picture visibility** setting chosen by the user upon entering the room (or
  automatically set when custom usernames are disabled).
- Whether other users are visible for direct messaging depends on each user's
  **`chat_osc_accept_msg`** preference.

## Data being deleted

- **When a user disconnects from a chatroom**: Their entry in `chatroom_users` is deleted. If chat
  history is enabled, a session record is created in `chatroom_sessions` before deletion.
- **When a chatroom object is deleted from trash** by a person with appropriate permissions: All
  related data is deleted, including:
    - all user connection records from `chatroom_users`
    - all message history from `chatroom_history`
    - all ban records from `chatroom_bans`
    - all session records from `chatroom_sessions`
    - the room settings from `chatroom_settings`
- **When the message history is cleared** by a person with the "moderate" permission: All entries
  in `chatroom_history` for that room are deleted. Session records in `chatroom_sessions` with a
  disconnection time in the past are also deleted.
- **When a ban is lifted** by a person with the "moderate" permission: The ban record is deleted
  from `chatroom_bans`.
- **When a user account is deleted**: The Chatroom component listens to the User component's
  `deleteUser` event and deletes all ban records for that user from `chatroom_bans`. **Residual
  data**: Other personal data referencing the deleted user may persist:
    - Messages sent by the deleted user remain in `chatroom_history` with the original user ID
      and username embedded in the JSON message body.
    - Session records in `chatroom_sessions` may retain the deleted user's ID and username.
    - If the deleted user was the actor who banned another user, the `actor_id` field in
      `chatroom_bans` retains their user ID.
    - On-Screen Chat data in `osc_conversation`, `osc_messages`, and `osc_activity` may retain
      references to the deleted user's ID.
- **Periodic cleanup by the Node.js server**: Old chat messages in `chatroom_history`, OSC messages
  in `osc_messages`, OSC conversations in `osc_conversation`, and OSC activity records in
  `osc_activity` are deleted based on a configured age threshold.
- **User preferences** (`chat_osc_accept_msg`, `chat_broadcast_typing`, `chat_osc_browser_notifications`,
  `chat_hide_automsg_*`, `chatinv_nc_muted_until`) are deleted when the user account is deleted,
  as they are stored in the user preferences system.

## Data being exported

- Chatroom objects can be exported as XML via the ILIAS export framework. The export includes room
  settings and the full message history. Message history entries contain the **message content**
  (including sender username and user ID embedded in JSON) and **timestamps**. The export is
  available to persons with the "Write" permission.
- The chat history can be exported as an HTML file for a selected date range by any user with the
  "read" permission (when the "Enable History" setting is active). The HTML export contains
  **sender usernames** and **message content**.
- There is no dedicated personal data export for individual users' chat participation records.
