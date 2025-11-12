# Notifications Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

**This currently only covers push notification**

- The "Notifications" component provides a service to distribute notifications to user through various channels.
- The component only stores your configurations and preferences. It does not store any information about the content or context of target notifications.
- Any user can configure push notifications when two preconditions are given:
  - The ILIAS configuration provides a valid `private_key_path` within `notifications`.
  - Push notifications are globally activated in **Administration** > **Communication** > **Notifications** > **Enable Push Notifications**.
- Users can set configurations in **Settings** > **Push Notifications** > **Set Active**.
- Users can set preferences in the same view inside the Form **Push Notifications**.
  - This for is empty by default. To select preferences, a **PushProvider** needs to be implemented by a component.
- A User can have multiple configurations for different devices/browsers.
- A User can have only one set of preferences globally.

## Data being stored

- On Activation of the Push Notifications a subscription is added to the client and is stored in reference to the user ID
- The stored data contains the following properties:
  - `endpoint`: The access point of the user’s browser to which notifications are sent.
  - `user_id`: The users id
  - `p256dh`: The public key to the communication.
  - `auth`: The user’s authentication towards the push server.
- The user’s preferences are stored as user preferences on submission of the form.
- The stored data contains the following properties:
  - `usr_id`: The users id
  - `keyword`: The PushProviders id
  - `value`: The state of enablement. ('1' when the provider is enabled, '0' if not)

## Data being presented

- The user configurations and preferences are only presented within the view they are set in  **Settings** > **Push Notifications**.
- To be mentioned: The notification subscription is saved within the local storage of the client.
  - This is no active process by ILIAS but due to the nature of the service worker handling.

## Data being deleted

- If Push Notifications are deactivated, the stored subscription entry for this client is removed without any traceable footprint.
- If a user is deleted, all subscription entries are removed without any traceable footprint.
  - The deletion of preferences is handled by the user service.

## Data being exported

- No data is exported in any way
