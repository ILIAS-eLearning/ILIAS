# Study Programme Privacy

Disclaimer: This documentation does not warrant completeness or correctness. Please
report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

### General Information

The Study Programme combines Courses and other Study Programmes to a curriculum. Automations can add Users and Courses based on certain triggers.
When any one Course within a Study Programme is passed, the Study Programme awards the points needed for its completion. Alternatively, a Study Programmes takes the sum of points from all Study Programmes it contains towards the required point score for completion. This leads to a qualification which may create a Certificate including a PDF for printing.

A qualification gained through a Study Programme has real world implications in many institutions. It may be a tool to award official academic degrees, further a career path or settle a dispute in case of an accident. Therefor, deadlines, expiration dates, completion dates etc. are highly relevant personal information especially when presented alongside a name.

The Study Programme mostly points to and interacts with other objects. It stores very little personal data in itself but may display and export some of the data from these other objects.

The qualification gained from completing a Study Programme may be set to expire. In this case, Members can be re-assigned and may repeat the curriculum. Data of a past assignments and qualifications are kept. This may record an educational history spanning a long time.

### Integrated Services

- The Study Programme component employs the following services, please consult the respective privacy.mds:
    - [Metadata](../MetaData/Privacy.md)
    - [AccessControl](../AccessControl/PRIVACY.md)
    - [Object](../ILIASObject/PRIVACY.md)
    - [InfoScreen](../InfoScreen/PRIVACY.md)
    - [Certificate](../Certificate/PRIVACY.md)
- [Users](../User/PRIVACY.md) from [Courses](../Course/PRIVACY.md), [Groups](../Group/PRIVACY.md) and [OrgUnits](../OrgUnit/PRIVACY.md) can be assigned manually or by an automation. The automation doesn't keep the users synchronized and only works one way. Users may no longer be members of the objects referenced during manual or automatic import.
- The Study Programme has a Learning Progress and observes the Learning Progress of the Courses and Study Programmes within via [Tracking](../Tracking/PRIVACY.md). It also uses its own systems to manage the point status and the qualification for every assigned Member individually which may or may not be a result of any Learning Progress.

## Data being stored

- An **Assignment** is each pass for a User attempting to reach a qualification. It includes the following personal information:
  - **User IDs of Members**: Adding Members to the Study Programme references their User object by ID.
  - **Assigned by**: A Member may have been added to the Study Programme by an automation. The type of source (not the exact source) is stored. Alternatively, it may be the ID of User who manually added the Member.
  - **Time and date of assignment** to the Study Programme.
  - **Progress**
    - **Learning Progress** of the Study Programme.
    - **Status**: Indicates if a Study Programme was completed or not. Can be forced manually by a Custom Plan or may be the result of the point system calculated based on the Learning Progress of objects within the Study Programme.
    - **Deadline** by which the Study Programme assignment has to be completed. May be unique to a specific User.
    - **Points:** Current points and required amount to pass the assignment. May be unique to a specific User.
    - **Custom Plan**: A User with the corresponding permission can change the conditions for taking and passing the Study Programme for each individual Member. The database stores these individual values and whether a Member is on a custom plan.
    - **Completed by**: Stores the ID of objects that awarded the points necessary to pass the Study Programme to an individual Member.
    - **Completion date** when the conditions for completion were met.
    - **last changed by** a User or an Object referenced by ID
- **Status of Qualification** is the result of Progress compared with the requirements set for each Assignment.
  - **Expiration** may be a fixed date, but could also be an individual time span starting from the date of completion.
  - **Validity:** The qualification may be validated manually. This ignores the actual conditions for completion of the Study Programme.
  - **Numbers of Days before expiry for restart** if there is a schedule for re-assigment to renew the qualification.
  - **Certificate PDF:** When a certificate is awarded as part of completion or validation, the User gets a Certificate in the ILIAS Achievements section. A certificate template can be customized by a User with high-level permissions. It is likely to contain personal information such as the full name, completion and expiration dates.
- **Automations** for adding content and Members
  - Time stamp and author User ID for most recent edit


## Data being presented

- **Users with high-level permissions can see personal information of others in various lists and tables**
  - **Assignment**
    - **Users**: Last and first name, as well as the login name
    - **Assigned by**: Type of source e.g. Group, Organisational Unit etc. (not the exact source)
    - **Time and date of assignment** to the Study Programme.
    - **Progress**
      - **Learning Progress** of individual objects
      - **Status** of completion
      - **Deadline**
      - **Current Points**
      - **Required Points**
      - **Custom Plan:** Whether a Member is on a custom plan or not. If so, point requirements and deadlines may be unique to them.
      - **Completed by:** Link to the object(s) that lead to points being awarded for a passing status.
      - **Completion date**
  - **Status of qualification** for all Members and all past assignments.
    - **Expiration**
    - **Validity**
    - **Numbers of Days before expiry for restart**
  - **Automations** for adding content and Members
    - Time stamp and author User ID for most recent edit
- **Members can see**
  - their own **Progress**
    - their own **Learning Progress**
    - **Status** of completion of their assignment - indirectly through qualification and Learning Progress updating
    - their own **Deadline**
    - their own current and required **Points**
    - **completion** date of their assignment
  - their own **Status of qualification:**
    - **Expiration** date of their qualification
    - **Validity**
    - **Numbers of Days before expiry for restart** according to their assignment plan

## Data being deleted

- When deleting an assignment:
  - **assignment**
    - **User ID** reference
    - **Assigned by**
    - **Time and date of assignment**
    - **Progress**
      - **Learning Progress** of this Study Programme. The Learning Progress of each linked Course or Study Programme is not changed.
      - **Status** of completion
      - **Deadline**
      - **Current Points**
      - **Custom Plan**
      - **Completed by**
      - **Completion date**
      - **last changed by**
  - **Status of qualification**
    - **Expiration**
    - **Validity**
    - **Certificate PDF** is handled by the Certificate service

- When deleting an automation:
  - Time stamp and author User ID for most recent edit

- When deleting a Study Programme, the aforementioned data is deleted for
  - all assignments (including repeated assignments)
  - all automations

Notice that linked objects and their Learning Progress aren't deleted. If you create a Study Programme with similar settings, then acknowledge the Progress from the same linked objects for the same Users, it results in similar qualification states. The assignment history has been lost, but the state of the most current assignments right before deletion can be somewhat reconstructed.

## Data being exported

- A Study Programme repository object cannot be exported.
- Users with the specific permission can export the Assignments of a Study Programme if this functionality has been enabled in the Administration settings. The following data can be selected to be included in a CSV or Excel export:
  - (First and Last) Name 
  - Login (Name)
  - Completion Status 
  - Completion date 
  - Completion by (Course(s) or Study Programme(s)) 
  - Points Obtainable 
  - Points Required 
  - Points Current 
  - Custom Plan 
  - Belongs to (Name of Study Programme)
  - Assignment date 
  - Assigned by (type of object)
  - Deadline 
  - Expiry date (of qualification)
  - Validity (of qualification)
  - Active

## Summary

| Data                                      | Stored in DB  | Shown to general user  | Shown to high-level user | Assignment Export        | deletes w/ assignment         | deletes w/ Study Programme |
|-------------------------------------------|---------------|------------------------|--------------------------|--------------------------|-------------------------------|----------------------------|
| All Member Users                          | references ID | no                     | as full name, login name | as full name, login name | yes                           | yes                        |
| Assigned by (object type)                 | references ID | no                     | as title, link           | yes                      | yes                           | yes                        |
| Time and date of assignment               | yes           | no                     | yes                      | yes                      | yes                           | yes                        |
| Learning Progress (of Study Programme)    | yes           | own                    | yes                      | no                       | yes                           | yes                        |
| Status                                    | yes           | own                    | yes                      | yes                      | yes                           | yes                        |
| Deadline                                  | yes           | own                    | yes                      | yes                      | yes                           | yes                        |
| Current Points                            | yes           | own                    | yes                      | yes                      | yes                           | yes                        |
| Required Points                           | yes           | own                    | yes                      | yes                      | no, yes for custom plan       | yes                        |
| Custom Plan                               | yes           | no                     | yes                      | yes                      | yes                           | yes                        |
| Completed by (object(s))                  | references ID | no                     | yes                      | yes                      | yes, but depends on other obj | yes                        |
| Completion date                           | yes           | own                    | yes                      | yes                      | yes                           | yes                        |
| last changed by (user or object)          | references ID | no                     | no                       | no                       | yes                           | yes                        |
| Qualification Expiration                  | yes           | own                    | yes                      | yes                      | yes                           | yes                        |
| Qualification Validity                    | yes           | own                    | yes                      | yes                      | yes                           | yes                        |
| Numbers of Days before expiry for restart | yes           | own as calculated date | no                       | no                       | no                            | yes                        |
| Certificate                               | yes           | own                    | no                       | no                       | yes                           | yes                        |
| Automation last edited by User            | references ID | no                     | yes                      | no                       | no                            | yes                        |
| Automation last edited time               | yes           | no                     | yes                      | no                       | no                            | yes                        |
