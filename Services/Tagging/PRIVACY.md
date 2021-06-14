# Tagging Service Privacy

This documentation comes with no guarantee of completeness or correctness. Please report any issues (missing or wrong information) in the ILIAS issue tracker.

## Data being stored

- Each **tag** stores the **user ID** (creator) of the tag and the referenced **object** in the database.
  
## Data presentation

- ILIAS presents the **own tags** of the user on various screens, e.g. in repository lists or in the main menu.
- An ILIAS administrator can enable an optional presentation of **all tags of all users related to an object** on the info screen of an object. This data is aggregated, no user names will be presented with the tags. However in context with a small user base, other users may be able to guess the authors of single tags.
- An ILIAS **administrator** can **list users of specific tags** in the ILIAS administration. The purpose is to identify users that add tags that may conflict with the terms of use in the system.

## Data Deletion

- Individual tags may be removed by the user from objects.
- Tags related to an object are deleted on object deletion.
- Tags related to a user are deleted on user deletion.