# Repository Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).


## General Information

The repository manages learning resources (aka objects) in a tree structure. Please consult the respective privacy.md files of the specific object types you are using.

## Integrated Services

- In general the specific object components decide which sub-services they are using, you will find this information in the respective privacy.md files of these components. All object types are using the access control service:
  - [AccessControl](../../Services/AccessControl/PRIVACY.md)

## Configuration

**Global**

The following configurations configure privacy related features:

- **Favourites** can be activated in the administration under "Reposity and Objects" > "Repository".
- **Recommended Content** can be configured in the administration for each role under "Users and Roles" > "Roles". Open a role and click "Recommended Content" tab.


## Data being stored

- If a user selects a repository object as a "favourite" object, the user ID and the object ID are being stored together in table desktop_items. _Reason_: This data is essential to provide the Favourits feature at all.
- Recommended content is managed by storing the object ID and the role ID. Content is currently only recommended to roles. User IDs are assigned to roles in the [AccessControl](../../Services/AccessControl/PRIVACY.md) service. 
- The service holds an implementation that to store user IDs to object IDs directly, however this is currently unused (and may never be used), see discussion at https://docu.ilias.de/goto_docu_wiki_wpage_5620_1357.html

## Data presentation

- ILIAS presents selected Favourites on the Dashboard and in a menu slate. A user can only see its own favourites.
- Recommended content is presented on the Dashboard. ILIAS presents all recommended content related to roles assigned to the user.

## Data Deletion

- **Users** can always delete their **favourites**.
- The relation of recommended objects to roles is managed by **administrators** having access to the role administration.

## Data Export

- Neither favourites or recommended content relations are exportable.