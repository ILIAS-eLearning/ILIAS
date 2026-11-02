# General Documentation

This part of the documentation deals with concepts and business rules, for technical documentation see [README-technical.md](./README-technical.md).

## Business Rules

- Object groups cannot be used in sessions. There is no concept how presentation should work yet.
- Sessions cannot be used in object groups. See  https://docu.ilias.de/goto_docu_wiki_wpage_682_1357.html JF Mar 2014 comment
- Object groups are only visual components. They are not part of the repository path of items assigned to the object group (they are no real containers). Revoking permissions to the object group will not revoke permissions to assigned items. The access system only checks read permission to the containers (categories, courses), this means items assigned to an object group may still appear in the search results, even if the user has no permission for the object group.
- Object groups do not offer a "description" input field with ILIAS 8. Since the description has not been presented in containers, the property was abandoned, see https://mantis.ilias.de/view.php?id=31856