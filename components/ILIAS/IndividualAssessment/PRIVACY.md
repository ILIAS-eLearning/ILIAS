# Individual Assessment Privacy

Disclaimer: This documentation does not warrant completeness or correctness. Please
report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

### General Information

The Individual Assessment is intended as a performance and skill evaluation tool.
The submitted data can shape a person's career. Therefore, certain data is intentionally
made impossible to delete or change for most or all user roles. This ensures that Individual
Assessment records are more likely to be accepted as proof in court.

### Integrated Services

- The Individual Assessment component employs the following services, please consult
the respective privacy.mds:
    - [Metadata](../MetaData/Privacy.md)
    - [AccessControl](../AccessControl/PRIVACY.md)
    - [Object](../ILIASObject/PRIVACY.md)
    - [InfoScreen](../InfoScreen/PRIVACY.md)
- Users from [Course](../Course/PRIVACY.md) and [Group](../Group/PRIVACY.md) can be
  added to an Individual Assessment. Even after a person's membership to the original
  object has been removed, it may be obvious where the Users came from.
- An Individual Assessment may be part of another object's Learning Progress
  [Tracking](../Tracking/PRIVACY.md).

## Data being stored

- **User IDs of Participants**: Adding accounts as Participants to the Individual
  Assessment references their User object by ID.
- **User IDs of Examiners**: Examiners are users that have graded one or more participants.
  Their User ID is stored with the individual record of some participant.
- **Location, time and date of an assessment**: The Examiner can enter when and where
  an exam took place. These fields can be set as required in the Settings.
- **Grading**: The Examiner selects whether the Participant completed or failed the
  assessment. Grading might influence the overall Learning Progress of another object.
- **Record Notes**: Examiners can write notes - both public to the individual participant
  and internal record notes only visible to users with specific permissions.
- **File**: Examiners can add a file to the record. This field can be set as required
  in the Settings.
- **Changes after finalization:** Accounts with the corresponding permission can change
  a record after it was finalized. Such a change is logged and stores the User ID of
  the account making the change, as well as time and date of the change.
- **Contact Information**: In the tab `Settings > Info Settings`, contact information
  can be entered. This may include a person's name, responsibility, phone, email and
  consultation hours.

## Data being presented

- **Accounts with high-level permissions (view learning progress of other users, amend
  finalized grading, manage members) can see:**
    - **Users**: User search results (last and first name, login name of a user) can
      be seen in the toolbar of the overview and while adding members.
    - **Participants**: The name of Participants is presented in the overview screen
      and while adding or editing a Participant Record.
    - **Examiner**: The name of the Examiner is shown in overviews and editing screens.
    - **Location, time and date of an assessment** in overview and during editing.
    - **Changed after finalization**: If the record was edited after finalization, the
      name of the account that made the change, as well as the date of this change
      will be shown.
    - **Grading**: is shown in the overview and the editing view of the Participant Record
    - **Record Notes**: Both the public and the internal record note are shown in the
      overview and editing view of  records.
    - **File**: Attached files can be downloaded from the overview screen.
    - **Contact Information** can be set and viewed within the Settings.
- **General users** can only access the top level info page and see a few items:
    - their **Grading**
    - the public **Record Note** of their record
    - the **File** uploaded to their record if the visibility option was set
    - the manually set **Contact Information** from the general settings

## Data being deleted

- When deleting a single Participant record before finalizing it, the following personal
  data stored so far will be deleted:
    - reference to user ID for Participant
    - Location, time and date of an assessment
    - Grading
    - both Record Notes
    - uploaded file
- After finalizing, Participant Records cannot be deleted individually. The entire
  Individual Assessment object needs to be deleted to remove data.
- When deleting the entire Individual Assessment, all records will be deleted,
  including the following potentially stored personal data:
    - user ID for Participant, Examiner, Changer
    - time and date of the last change
    - Location, time and date of an assessment
    - Grading
    - both Record Notes
    - uploaded file
    - manually provided, optional contact information

## Data being exported

- Only the settings of the Individual Assessment and no Participant Records are exported.
  Therefore, the only sensitive data included at this point is manually provided, optional
  contact information.

## Summary

| Data                                             | Stored in DB       | Shown to general user | Shown to high-level user | Exported | deletes w/ record [^finaliz] | deletes w/ obj |
|--------------------------------------------------|--------------------|-----------------------|--------------------------|----------|------------------------------|----------------|
| Participant User                                 | reference to by ID | no                    | as name                  | no       | yes                          | yes            |
| Examiner User                       | reference to by ID | no                    | as name                  | no       | n.a.                         | yes            |
| Location, time and date of assessment            | yes                | no                    | yes                      | no       | yes                          | yes            |
| Record Note                                      | yes                | one personal          | yes                      | no       | yes                          | yes            |
| Internal Record Note                             | yes                | no                    | yes                      | no       | yes                          | yes            |
| File                                             | reference to by ID? | one personal          | yes                      | no       | yes?                         | yes?           |
| Grading                                          | yes                | one personal          | yes                      | no       | yes                          | yes            |
| Changer User                                     | reference to by ID                | no                    | yes                      | no       | n.a.                         | yes            |
| time and date for last change                    | yes                | no                    | yes                      | no       | n.a.                         | yes            |
| Search result: Any User's first, last, user name | no                 | no                    | yes                      | no       | n.a.                         | n.a.           |
| Search result: Any Group or Course name          | no                 | no                    | yes                      | no       | n.a.                         | n.a.           |
| manually provided, optional contact information  | yes                | yes                   | yes                      | yes      | no                           | yes            |

[^finaliz]: before finalization. After finalizing a record, it can only be amended. To delete a finalized record, the
entire object must be deleted.
