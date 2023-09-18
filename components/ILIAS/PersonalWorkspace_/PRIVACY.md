# Personal Workspace Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

The personal workspace allows users to manage personal resources like files, blogs and weblinks in a structure of folders. Initially these resources can only be accessed by the user itself. The resources may contain personal data, e.g. files or blogs may include information about the user.

The workspace allows to share these resources with other users, groups, courses or even publicly to the web without authentication.

Please note that the component **Personal Workspace** implements **only** the **Personal Resources** management. The default main menu entry **Personal Workspace** in ILIAS collects other components as well like tagging, calendar, tasks, portfolios and more. These are separate components which are not tackled in this document.

## Integrated Services

- The Personal Workspace component employs the following services, please consult the respective privacy.mds
  - The **Object** service stores the account which created the
        object as it's owner and creation and update timestamps for the
        object.
  - Various object components like Blogs, Files, Folders and Weblinks.
  - The **Mail/Contacts** service implements a user search for finding users to share resources with.

## Configuration

**Global**

- **Personal Resources Activation**: The personal resources are activated under "Administration > Personal Workspace > Personal Resources". The same screen allows to configure which resource types can be created by the users.
- It is possible to activate a feature that enables users to **publish** their resources to the **outside web**, without any authentication. The availability of this option is controlled by the setting "Anonymous Access > Enable User Content Publishing" under "Administration > System Settings and Maintenance > General Settings > Basic Settings".

**Resources**

- The owner of the resources controls its content and how it is shared with others. This is done in the **Share** tab of a resource. Resources can be shared with single users, all members of a group, all members of a course, all registered users or even externally to the web (see global configuration).

## Data being stored

**Resources of User**
- By using the tree component, ILIAS stores the **user id** together with the **object ids** of the resources to represent the "ownership" of the resources.

**Sharing with Users**
- If resources are shared, ILIAS stores the ids of **users**, **groups** or **courses** to keep track, which users may access the resource.

## Data being presented

**Own Resources**
- The user can fully access the own resources under "Personal Workspace > Personal and Shared Resources".

**Shared Resources**
- Other users can access resources shared with them through "Personal Workspace > Personal and Shared Resources > Resources of Other Users".

**User Data**
- The sharing screens allow to search for other users, the presentation of users, their account/login names, first and last names are controlled by the Mail/Contacts components. Usually first and last name are not presented, if the user has the personal profile deactivated.

## Data being deleted

- If a resource is deleted the reference to the user is deleted, too.
- If a resource is deleted the references of the resource to the users the resource is shared with is deleted, too.

## Data being exported

- Exports of resources are implemeted by the corresponding components (blog, file, ...). The **Personal Workspace** does not implement any exports of personal data.