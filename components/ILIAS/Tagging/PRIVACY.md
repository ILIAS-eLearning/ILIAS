# Tagging Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).


## Data being stored

- Each tag stores the user ID of the account that created the tag and the referenced
  object in the database. The purpose is to being able to identify the author of the
  tag i.e. to show own tags in main menu.


## Data being presented

- An account with "Edit Settings" permissions Administration > Personal Workspace >
  Tagging > Edit Settings can enable the Tagging Service globally.
  - Only accounts that created tags will be presented with them. 
  - ILIAS presents the tags created by an account on various screens, e.g. in
    repository lists, the "Info"-tab or in the main menu.
- An account with "Edit Settings" permissions Administration > Personal Workspace > Tagging > Edit Settings can additionally enable the presentation of all tags
  of all users related to an object. If that setting is activated, then all tags
  of all accounts related to an object are presented on its "Info"-tab as "Tags of
  All Users". This presentation of all tags of all accounts does not contain any
  personal data besides tag term.
- An account with "Edit Settings" permissions Administration > Personal Workspace > Tagging > Users can all up list users of specific tags. The purpose is to
  identify accounts adding tags conflicting with the terms of use in the system.


## Data being deleted

- Accounts can remove tags they created from an object on the objects "Info"-tab. 
- Tags related to an object are deleted once an object is deleted from trash.
- Tags related to a user are deleted once the user is deleted. 


## Data being exported 

Tags cannot be exported.
