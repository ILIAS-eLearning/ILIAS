# Awareness Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Awareness component employs the following services, please consult the respective privacy.mds
    - The **User** service provides account data, profile-publication information, profile pictures, user preferences and the current list of online users.
    - The **User Action** service provides actions available for listed users. These actions may contain additional action-specific data depending on the enabled action providers.
    - [Contact](../Contact/PRIVACY.md) provides contact requests and approved contacts.
    - [Mail](../Mail/PRIVACY.md) provides mail/contact-related data used by contact-related user actions.
    - The **Course** service provides course contacts and current course members.
    - [Group](../Group/PRIVACY.md) provides group membership contexts used to find users from shared memberships.
    - The **Membership** service provides user IDs from shared group/course memberships.
    - [AccessControl](../AccessControl/PRIVACY.md) provides RBAC and access checks for course and membership-based user lists.
    - The **System Support Contacts** service provides configured support contacts.
    - The **Legal Documents** service removes users whose online status must be hidden according to legal-document settings.
    - [Notifications](../Notifications/PRIVACY.md) provides the on-screen display integration, if on-screen display is enabled.

## General Information

- The Awareness component provides the "Who is online?" / awareness overlay in the metabar. It shows other users that are relevant to the current user according to configured providers, e.g. contacts, support contacts, course contacts, current course members, users from shared memberships or, if enabled, all users.
- The component is intended for communication and quick user interaction. It does not provide a free-text content model of its own, but it presents personal data from user accounts, online-status information and user-action providers.
- Anonymous users and the current user are not listed in the awareness overlay.

## Configuration

- **Global activation**: Administrators can enable or disable the Awareness component. If disabled, the widget is not shown.
- **Caching period**: Administrators can configure how long awareness counters are cached in the session before being recalculated. _Reason_: Reduce server load.
- **Maximum number of entries**: Administrators can limit the number of users shown in the awareness list.
- **Maximum inactivity time**: Administrators can configure the inactivity time used by the online-status handling. 
- **On-screen display**: Administrators can enable or disable the use of on-screen display functionality.
- **Default visibility of the own online status**: Administrators can define whether users are shown in the awareness list by default.
- **User privacy setting**: Users can override the default in their privacy settings and choose whether their own online status is shown in the awareness list or hidden.
- **Provider activation modes**: Administrators can configure each awareness provider as inactive, online users only, or including offline users. This controls which user groups can appear in the awareness list.

## Data being stored

- The Awareness component stores global settings in the ILIAS settings storage, including:
  - whether awareness is enabled,
  - the caching period,
  - the maximum number of entries,
  - whether on-screen display is used,
  - the activation mode per user provider.
- The default for hiding or showing the own online status is stored as an ILIAS setting.
- User-specific visibility of the own online status is stored as a user preference named `hide_own_online_status`.
- The component stores only technical counter data in the current session:
  - `awrn_last_update`: timestamp of the last counter update,
  - `awrn_cnt`: number of regular awareness entries,
  - `awrn_hcnt`: number of highlighted awareness entries,
  - `awrn__online_users_ts`: timestamp value for online-user handling, if used.
- The Awareness component does not persist its own list of users, names, profile pictures, online states or user actions. These data are retrieved at runtime from the integrated services.
- Debug logging in the Awareness component may contain **user IDs**, provider IDs, object IDs and counts when debug logging for the `awrn` logger is enabled.

## Data being presented

- The awareness overlay is shown only to logged-in users when Awareness is enabled.
- Depending on the activated providers and their activation modes, the list can include:
  - approved contacts and contact-request related users,
  - system support contacts,
  - course support contacts,
  - members of the current course when the current user has write access or member display is enabled and the current user has read access,
  - users from courses and groups shared with the current user,
  - all users, if the corresponding provider is enabled.
- The list excludes anonymous users, the current user, users hidden by their `hide_own_online_status` preference according to the global default, and users hidden by Legal Documents settings.
- For each listed user, the overlay presents the **account/login name**.
- If the user's profile is public, the overlay additionally presents **first name** and **last name** and uses them for sorting and filtering.
- The overlay presents the user's **profile picture** path loaded from the User service.
- The overlay presents whether the user is currently **online**. Online status is derived from the ILIAS online-user handling and the configured inactivity time.
- The overlay presents user actions provided by the User Action service. Depending on the active action providers, these actions can include action text, links and action-specific data.
- Users can filter the awareness list. The filter value is returned in the AJAX response for display; it is not persisted by the Awareness component.
- The metabar counter presents only the number of available awareness entries and highlighted entries, not names or account data.

## Data being deleted

- Awareness-specific session counter data are removed with the user's session according to the general session handling.
- If a user deletes or resets the privacy setting for online-status visibility, the user preference `hide_own_online_status` is removed and the global default applies again.
- If a user account is deleted, account data, profile-publication settings, profile pictures and user preferences are handled by the User service. The Awareness component does not keep a separate copy of these data.
- If contacts, memberships, course roles or support-contact assignments are removed, the affected users disappear from the awareness list because the list is generated at runtime from the corresponding services.
- The Awareness component has no own content, submission, history or object data that needs separate deletion.

## Data being exported

- The Awareness component has no export or download feature.
- Awareness data are not included in a component-specific XML, HTML, ZIP, PDF or archive export by the Awareness component itself.
- Personal data may still be exported by integrated services such as User, Contact, Course, Group, Membership or Notifications according to their own export functionality and privacy documentation.