# OnScreenChat Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The OnScreenChat component provides a real-time private messaging interface embedded in the ILIAS main menu under the "Communication" section. It allows authenticated, non-anonymous users to start one-on-one or group conversations with other users directly in the ILIAS interface. The component is only active when both the `chat_enabled` and `enable_osc` global chat settings are enabled by an administrator. Users can individually control whether they accept incoming messages via a privacy setting (`chat_osc_accept_msg`) managed in their personal privacy settings. Users who have disabled message acceptance do not appear in the OnScreenChat user search.

## Integrated Components

- User — User profile data (public name, profile image, login) is read via `ilUserUtil::getNamePresentation` to display conversation participants. The user autocomplete search (`ilUserAutoComplete` with `PRIVACY_MODE_RESPECT_USER_SETTING`) respects the user's visibility settings. The privacy preference `chat_osc_accept_msg` is stored in `usr_pref` and managed via User privacy settings.
- [Chatroom](../Chatroom/PRIVACY.md) — Global on-screen chat availability is governed by Chatroom administration settings (`chat_enabled`, `enable_osc`). The `AllowOnScreenChatConversations` user setting is defined in the Chatroom component.

## Data being stored

The OnScreenChat component reads from and writes to the following database tables, which contain personal data:

- **`osc_messages`**: Stores individual chat messages. Each record contains the author's `user_id`, the `conversation_id`, the `message` text content, and a `timestamp`. This data is created whenever a user sends a message.
- **`osc_conversation`**: Stores conversation metadata. Each record contains a JSON-encoded `participants` field holding the user IDs of all conversation members, and an `is_group` flag indicating whether it is a group conversation.
- **`osc_activity`**: Stores per-user activity records linking a `user_id` to a `conversation_id`. This is used to determine which conversations a user is subscribed to when loading their initial chat state.
- **`usr_pref`** (shared with User component): The preference key `chat_osc_accept_msg` (whether the user accepts messages from others) and `chat_osc_browser_notifications` (whether browser push notifications are enabled) are stored per user. These are managed through the user's personal privacy settings.

## Data being presented

The following personal data is visible within the OnScreenChat interface:

- In the **main menu chat slate**, each conversation entry shows the public names of conversation participants (either firstname and lastname if the participant's profile is public, or their login otherwise), the participant's profile image, the text of the last message in the conversation, and the message timestamp. This information is visible to the conversation participants only (each user sees only the conversations they are a member of, enforced by checking `user_id` membership in `osc_conversation.participants`).
- In the **user search / autocomplete** (when starting a new conversation or inviting a participant), user names are presented to persons initiating a conversation. Only users who have the `chat_osc_accept_msg` preference set to `y` appear in these search results. The autocomplete respects the global user visibility privacy mode (`PRIVACY_MODE_RESPECT_USER_SETTING`).

No administrative view of other users' conversation content was found in this component.

## Data being deleted

No account-specific cleanup is implemented in the OnScreenChat component. Therefore, message content and participant references associated with deleted user accounts may initially remain in the `osc_messages`, `osc_conversation`, and `osc_activity` tables.

Old messages and conversations are cleaned up by the chat server according to its configured retention settings; see [Delete old messages](../Chatroom/README.md#delete-old-messages). The preference `chat_osc_accept_msg` stored in `usr_pref` is subject to the deletion behaviour implemented by the [User](../User/PRIVACY.md) component for user preferences.

## Data being exported

The OnScreenChat component does not provide any data export functionality. No export of conversations or messages was found in the component's code.
