# Repository Service

This part of the documentation deals with concepts and business rules, for technical documentation see [README-technical.md](./README-technical.md).

## Manage screen

- To enter the manage screen write permission for a container is needed.
- To stay in manage screen when navigating either write permission for a container or a non empty clipboard is necessary.
- Being in the manage screen does not add any additional permissions. Actions can only be performed, if the necessary permission is given.

## Trash

- To view the trash of a container class the "write" permission of the container is needed.
- To finally delete objects from a container trash, the "write" permission of the container is needed. Note: it is currently not possible to check permission on trashed objects.

## Recommended Content

- Recommended content is configured in the settings of a role. A content assigned to the roles of a user will be initially listed as recommended content on the dashboard. [1]
- Items are removed from the recommended content list, if they are selected as a favourite by the user. [1]

## Last visited / favourite lists in slate

- The SCORM objects currently offer a setting where to open these objects on click (new tab/screen). These settings are not offered to other components via a centralised API, thus the last visited or favourites list do not support this behaviour.

[1] https://docu.ilias.de/goto_docu_wiki_wpage_5620_1357.html