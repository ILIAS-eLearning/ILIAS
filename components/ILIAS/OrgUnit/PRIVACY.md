# OrgUnit Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

### General Information

Organisational Units (OrgUnits) themselves provide an access mechanism to certain 
information according to an hierachical, organigram-like structure of actors and 
their positions within an organisation.
User accounts are assigned to one or many nodes of a tree and are given positions,
which grant "authority" over other positions within the same or consecutive nodes.
These authorities may be connected to permissions on object-level, so that e.g.
'superiors' of 'department A' may see results for their 'employees' in an asessment.

### Integrated Services

- The OrgUnit component employs the following services, please consult the respective privacy.mds
    - [Metadata](../MetaData/Privacy.md)
    - [AccessControl](../AccessControl/PRIVACY.md)
    - [Object](../ILIASObject/PRIVACY.md)
    - [Users](../User/PRIVACY.md) 


### Configuration

- **Global**
  - Enable/Disable OrgUnit Permissions for object types (Administration > Organisational Units > Settings).
  - Add/remove/configure Authorities (Administration > Organisational Units > Positions).

- **OrgUnit**
  - Assign Staff (user accounts) to an OrgUnit and Position


## Data being stored

- **user_id**: Adding Staff references the User object by ID.
- **position_id**: References the OrgUnit Position
- **orgu_id**: References the Organisational Unit

## Data being presented

- Users with the according permissions may see 
  - **Login**
  - **First Name**
  - **LastName**
  - **Active status**
  in the "Staff" tab,
  grouped by the assigned **Positions**


## Data being deleted

- When deleting an OrgUnit, the user assignments to this OrgUnit are deleted.

## Data being exported

- XML/XLS exports of OrgUnits do not contain any personal data.
  The structure of the OrgUnit tree is exported with id and parent_id, type, title and description
  of the OrgUnits.
  References to user accounts are not part of the export.


## Summary

| Data                    | Stored in DB       | Shown to general user | Shown to high-level user | Exported | deletes w/ record | deletes w/ obj |
|-------------------------|--------------------|-----------------------|--------------------------|----------|-------------------|----------------|
| Staff (User)            | reference to by ID | no                    | as login/name            | no       | yes               | yes            |
| Assignment to Position  | reference          | no                    | as affiliation           | no       | yes               | yes            |
| Assignment to OrgUnit   | reference          | no                    | as affiliation           | no       | yes               | yes            |
