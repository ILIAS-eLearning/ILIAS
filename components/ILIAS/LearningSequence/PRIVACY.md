# Learning Sequence Privacy

Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong
information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix
via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

### General Information

The Learning Sequence leads users through multiple pieces of content in a row. It dynamically adapts to the Learning Progress of its child objects and can summarize them into one combined Learning Progress. Therefor, the Learning Sequence mostly contains references but may display a snapshot of a user's learning path and hold the final evaluation as one resulting Learning Progress.

At the time of writing, Users are not required to be Members to access and progress through the Learning Sequence's content by default. If a child object is configured in a way that Users have access, the Users still have access when the object is added to the Learning Sequence. This may put the privacy and access concerns primarily onto the child objects. However, with additional configuration and permission management, the Learning Sequence can function as a gatekeeper for access control. Alternatively, a Learning Sequence may be placed into a Course and use its features for access control.

### Integrated Services

- The Learning Sequence component employs the following services, please consult the respective privacy.mds:
    - [Metadata](../MetaData/Privacy.md)
    - [AccessControl](../AccessControl/PRIVACY.md)
    - [Object](../ILIASObject/PRIVACY.md)
    - [InfoScreen](../InfoScreen/PRIVACY.md)
    - [Mail](../Mail/PRIVACY.md) for notifications
    - [Tracking](../Tracking/PRIVACY.md) may control if a user can progress the sequence and the sequence has its own Learning Progress as well
    - [Membership](../Membership/PRIVACY.md)
- Most of the actual data storage is not done by the Learning Sequence object itself. Instead the services Membership and Tracking are heavily used.

## Data being stored

- **Participants (Members)**: Adding users as Participants (Members) to the Learning Sequence references their User object by ID.
- **Progress Data**: For the individual participants, the Learning Sequence stores:
  * The time and date of the first access to the Learning Sequence.
  * The time and date of the last access to the Learning Sequence.
  * A reference to the item that the participant last worked on.
  * State information about the pages in the Learning Sequence. The exact information that is stored here depends on the individual objects in the Learning Sequence.

## Data being presented

- **Learning Progress of child objects**: Members can see their Learning Progress for the individual child objects. Global Admins and any User with the corresponding permission (unset by default) can see the Learning Progress for all Sequence child objects of every individual Member. The Learning Progress list may display data of more Users beyond the Users who are currently subscribed Members.
- **Last Visited Step:** Admins can see which child object any Member accessed last.
- **Availability and Visibility**: Users might be able to see or access a child object depending on its settings regardless, of their membership to the Learning Sequence. This depends on the User's roles and other privacy settings of the child object.
- **Member Gallery**: When enabled, all Members can see information about other Members. However, Users can uncheck individual pieces of information in their profile settings to prevent them from being shown publicly. This may result in some information being marked as "Undisclosed".

## Data being deleted

- **Removing a Participant**
  - removes the reference to their User object and consequently their entry from any Member list or gallery view
  - does not cause their Learning Progress to be deleted
- **Deleting a User from the system** also removes them from any member and learning progress list in the Learning Sequence.
- **Deleting Learning Sequence**
  - Deleting the Learning Sequence removes all references to Members, settings and states stored for the sequence itself. Traces of the child objects are left in the database but are not accessible in ILIAS.

## Data being exported

- Learning Sequence options (like how a "User may proceed" depending on Learning Progress cues) are exported referencing child objects by ID.
- No User object data, including reference IDs or Admin role assignments, is exported.

## Summary

| Data                       | Stored in DB                       | Shown to general user                      | Shown to admin                                 | Exported   | deletes when rmv Member | deletes when rmv Sequence |
|----------------------------|------------------------------------|--------------------------------------------|------------------------------------------------|------------|-------------------------|---------------------------|
| Participant (Member) Users | reference to by ID                 | if Member Gallery active                   | yes - name, login                              | no         | yes                     | yes                       |
| Child objects              | reference to by ID                 | by default access regardless of membership | yes                                            | optionally | no                      | no                        |
| Learning Progress          | for sequence and its child objects | own                                        | all to global admin and with custom permission | no         | no                      | no                        |
| Last Visited Step          | reference to by ID                 | no                                         | yes                                            | no         | yes                     | yes                       |
| Admin role promotion       | yes                                | no                                         | yes                                            | no         | yes                     | yes                       |
