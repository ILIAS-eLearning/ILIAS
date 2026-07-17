# Awareness Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS issue tracker](https://mantis.ilias.de). When using the issue tracker, please select the corresponding component in the **Category** field.**

## General information
The Awareness component provides the “Who is online?” / awareness overlay in the metabar. The component is intended for communication and quick interaction. The “Who is online?” / awareness overlay presents other accounts to the currently logged-in account. It presents personal data as set by accounts in their personal settings, online-status information and selected user-actions.
ccounts with access to the global administration can enable or disable the Awareness component. If disabled, the “Who is online?” / awareness overlay is not shown.

**What can be presented** is configured globally at Administration > Communication > “Who is online?”-Tool.

- Awareness Providers: Awareness can be provided for Contact Requests, Technical Support, Tutorial Support, Current Course, Approved Contacts, My Groups and Courses, or, if enabled, All Users. For each of the Awareness Providers the presentation mode Not Listed, Online Only or Online and Offline can be set individually. This controls which accounts can appear in the awareness list.

- User Actions: Settings in the subtab Administration > Communication > “Who is online?”-Tool > User Actions control which interaction options will be offered in the “Who is online?” / awareness overlay. These User Actions require the corresponding service to be activated and all preconditions met (e.g. permissions requirements). User Actions are Contact Request, Send Mail, View Profile, Access Shared Resources, Invite to Public Room, Invite to On-Screen Chat, Add to Group, Invite to Talks.

**What is actually presented for an account** is set by the person in their Profile and Privacy settings. They decide on the default in their privacy settings and choose whether their own online status is shown in the awareness list or hidden, and whether they want to use the contact service.

How long an account is listed as online depends on the Caching Period and the Maximum Inactivity Time. A global setting governs whether or not a popup indicates new online users in the “Who is online?” list and what the Default Value for the Visibility in “Who-Is-Online?” is.

Anonymous accounts and the currently logged-in account are never listed in the awareness overlay.

## Integrated components

The Awareness component employs the following components, please consult the respective privacy.mds.
- The User service provides account data, profile-publication information, profile pictures, user preferences and the current list of online users.
- The User Action component provides actions available for listed accounts. These actions may contain additional action-specific data depending on the enabled action providers.
- [Contact](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Contact/PRIVACY.md) provides contact requests and approved contacts.
- [Mail](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Mail/PRIVACY.md) provides mail / contact-related data used by contact-related user actions.
- The Course component provides course contacts and current course members.
- [Group](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Group/PRIVACY.md) provides group membership contexts used to find users from shared memberships.
- The Membership component provides user IDs from shared group / course memberships.
- [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) provides RBAC and access checks for course and membership-based user lists.
- The System Support Contacts service provides configured support contacts.
- The Legal Documents component removes users whose online status must be hidden according to legal-document settings.
- [Notifications](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notifications/PRIVACY.md) provides the on-screen display integration, if on-screen display is enabled.


## Data being stored
- The Awareness component does not persist its own list of users, names, profile pictures, online states or user actions. These data are retrieved at runtime from the integrated services.
- Debug logging in the Awareness component may contain user IDs, provider IDs, object IDs and counts when debug logging for the awrn logger is enabled.
- The Awareness component stores global settings in the ILIAS settings storage, including:
    - whether awareness is enabled,
    - the caching period,
    - the maximum number of entries,
    - whether on-screen display is used,
    - the activation mode per user provider.  
- The default for hiding or showing the own online status is stored as an ILIAS setting.
- User-specific visibility of the own online status is stored as a user preference named hide_own_online_status.
- The component stores only technical counter data in the current session:
    - awrn_last_update: timestamp of the last counter update,
    - awrn_cnt: number of regular awareness entries,
    - awrn_hcnt: number of highlighted awareness entries,
    - awrn__online_users_ts: timestamp value for online-user handling, if used.

## Data being presented
- The awareness overlay is shown only to logged-in accounts when Awareness is enabled.
- Depending on the activated providers and their activation modes, the list can include:
    - approved contacts and contact-request related accounts,
    - system support contacts,
    - course support contacts,
    - members of the current course when the currently logged -in account has write access or member display is enabled and the currently     - logged- in account has read access,
    - accounts from courses and groups shared with the currently logged-in account,
    - all users, if the corresponding provider is enabled.
- The list excludes accounts hidden by their hide_own_online_status preference according to the global default, and accounts hidden by Legal Documents settings.
- For each listed account, the overlay presents    
    - the **account/login name**
    - if the profile is public: **first name** and **last name**
    - if available: **profile picture** 
    - whether the account is currently **online**  
    - user actions provided by the User Action service. Depending on the active action providers,these actions can include action text, links and action-specific data. User Actions may allow for sending Contact Requests, sending Mails, viewing Personal Profile, accessing Shared Resources, inviting to Public Chat Room, starting an On-Screen Chat, adding person to a Group, inviting to Talks. 
- Accounts can filter the awareness list. The filter value is returned in the AJAX response for display; it is not persisted by the Awareness component.
The metabar counter presents only the number of available awareness entries and highlighted entries, not names or account data.


## Data being deleted
- The Awareness component has no own content, submission, history or object data that needs separate deletion.
- Awareness-specific session counter data are removed with the user's session according to the general session handling.
- If an account deletes or resets the privacy setting for online-status visibility, the user preference hide_own_online_status is removed and the global default applies again.
- If an account is deleted, account data, profile-publication settings, profile pictures and user preferences are handled by the User service. The Awareness component does not keep a separate copy of these data.
- If contacts, memberships, course roles or support-contact assignments are removed, the affected account disappear from the awareness list because the list is generated at runtime from the corresponding services.


## Data being exported
- The Awareness component has no export or download feature.
- Awareness data are not included in a component-specific XML, HTML, ZIP, PDF or archive export by the Awareness component itself.
- Personal data may still be exported by integrated services such as User, Contact, Course, Group, Membership or Notifications according to their own export functionality and privacy documentation.
