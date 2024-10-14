# Contact Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

- The "Contact" component of ILIAS provides a channel for users to contact each other.
- The component only handles the contact and sharing of information via the contact functionality.
  There are multiple other types of context-related contacts in ILIAS that are not part of this component, such as
  course member/tutor relations or test correction processes, etc.
- The global activation of the component can be checked with the
  "Administration > Communication > Contact > Activate 'Contacts'" setting.
- The personal activation of the component can be toggled with the
  "Profile and Privacy > Visibility > Allow Contact Requests" setting.
- Contacts can be requested within member lists, like in courses and groups.
- Furthermore, when a user profile is published, it gives all users access to the profile via the "Who is online" tool
  and, through that, the option to request contact with that profile.
  - This can be activated in your profile under "Profile and Privacy > Publish Profile > Visible for logged-in Users"
    setting.
  - This setting does not allow or prevent the contact request itself.
- A contact can be used for two actions:
  - You can add contacts to your mailing lists
  - You can invite contacts to chatrooms
- Furthermore, approved contacts are shown within the "Who is online?" tool.
- The component does not track or recognize the data being exchanged through emails or chats. It has no information
  about the source of the content (i.e. a course) or whether it includes user-related data.

## Data being stored

- Each contact request is stored, including the ID of the requesting user and the ID of the requested user, as well as a
  timestamp of the request time. This does not require the consent of both target users.
- When the requested user actively ignores the request (by choosing the option "Ignore contact"), this data entry is
  added with an "ignore" flag.
- After confirmation, each contact is stored, including the ID of the requesting user and the ID of the requested user as
  well as a timestamp of the request time.
- The storage of requests and approved contacts are separated.
- Each contact added as an entry to a mailing list is stored using the user_id.
- Any user relations that originate from the contact are not related to or dependent on the active contact. There is no
  traceable relation of contacts within other components.

## Data being presented

- Contact data is presented in "Communication > Contacts" and in a separate section of the "Who is online?" tool.
- The presentation includes a presentation of the user's profile (restricted depending on the user's profile publishing
  settings) and control elements presenting the user's options for contact requests ("Confirm Contact"/"Ignore Contact").
- Initially the options are restricted to requesting a contact.
- When a contact is requested, the requesting user has the option to cancel the request.
- When a contact is requested, the requested user has the option to actively ignore or confirm the request.
- When a contact is confirmed, both contacts have the option to unlink the contact without any further consent.
- Each party can detect a change of the contact relation within its own contact options. The only exception to this
  is the active ignoring of a request. The requesting user can not detect if his request was ignored.
- A list of all contacts is shown as a selection when a user adds members to a newly created mailing list.
- The "Look up Users" functionality within the composition of a mail can provide a list of contacts if such matches with
  the search term exist.
- Furthermore, a list of user accounts with membership assignment is shown by clicking the "List Members" action for an
  objects within the "My Courses/My Groups" view, accessible directly from the "Compose" view of "Mail"
  component, or by clicking the "Contacts" link item in the main bar. For each account, the username is presented.
- Additionally, full names are presented according to the "Profile and Privacy > Publish Profile > Visible for logged-in
  Users" setting. The "Contact Status" is shown as well.
- The "Mail to Members" view of membership-enabled object types, which is also located in the "Contact" component,
  provides a list of participating user accounts. For each account, the username is presented and additionally, the full
  name, if published.
  - This information does not show any information about contact relations.
- Contacts are referenced within the mail service in the sub tab "Contacts".
- A contact relation cannot be perceived within the user interface by any user other than the contacts themselves.

## Data being deleted

- If a contact request is canceled, the stored entry is removed without any traceable footprint.
- If a contact request is ignored, the flagged entry in the database will be preserved to prevent a repeated contact
  request from the same initiator.
- If a contact is unlinked by one of the contacts, the stored entry is removed without any traceable footprint.
- Any user relations that originate from the contact are not affected by its deletion. This includes preservation in
  mailing lists and participation within chatrooms.
- If a user is deleted, their contacts, sent and received contact requests, mailing lists and mailing list 
  participations are removed without any traceable footprint.

## Data being exported

- The component does not provide any export option itself.
- A contact request can trigger a notification, which may be displayed as a "Toast" to the requested user. A "Toast" is
  a notification element that contains the user's presentation name (depending on their visibility settings) and the
  user's intention for the contact request.
  - The notification is handled by the "Notification" component, is self-persistent, and therefore not dependent on the
    origin request.
- Otherwise, the information of contacts is not stored in any export of the related components like "Mail", "Chatroom" or "ILIASObject".
