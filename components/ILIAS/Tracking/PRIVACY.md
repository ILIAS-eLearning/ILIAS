# Tracking and LearningProgress Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information
The Learning Progress (LP) tracks whether or not a person has interacted with a specific object and how much. This data is used to provide feedback to those learning and teching about the progress and success of learning activities.

### Activation 
Display of Learning Progress data is to be globally activated /deactivated. If activated globally, then within each individual object the Learning Progress must be activated, too, in order to be displayed. 

Even if the display of Learning Progress data is deactivated, the service always records some data. In case the display of Learning Progress data is deactivated, the tracked completion status can still be used to award badges and certificates. Administration > Achievements > Statistics and Learning Progress > Completion Status has to be confiured.
Deactivation the display of Learning Progress data does not remove data already stored.

### Important Concept: Status
The Learning Progress can have the following statuses for a user-object-relationships: 
- _**'Not started'** - Person has not even clicked on the title of an object._ 
- _After at least clicking the title of an object a person gets the status 'In Progress' which is valid until the object is fully worked though._
- _After fully satisfying the requirements of the Learning Progress Mode a Person is assigned the Status 'Completed'' respectively 'Passed' or 'Failed'._

### Important Concept: Mode
The Learning Progress **Mode** defines how the status 'completed' is triggered.  The Learning Progress **Modes** differ in how they arrive at the 'completed' status  
- The 'completed' status is derived automatically from the interaction. 
- The 'completed' status is set manually: 
  - _By accounts who decide that they have accomplished the object and set their own status to 'completed'._
  - _By an account with the permission 'Edit Learning Progress Settings' observing learning interaction of other people and assign the status 'completed' , 'passed' or 'failed'._ 
_Every object has the default setting 'Learning Progress is Deactivated: The Learning Progress status is not displayed and does not influence parent objects'._

_The didactical properties of the object type shape the modes. Different object types have different Learning Progress Modes for example:_
- _Visited_: Learning Progress status is set  to 'completed' when object has been presented to user. 
- _Users Monitor and Set Status Themselves_: Users decide themselves if they have accomplished the object. Once they are done, they have to set their status to ‘Completed’ on the 'Info'-tab. 
- _Collection of Media Objects_: The Learning Progress status will be determined by the viewing status of selected media     objects. 

### Important Concept: Collection of Items
In container objects (i.e. Course or Learning Sequence) can comprise several objects, which can be selected to pay into the overall Learning Progress of said container. Typically completing all objects of the collection is mandatory, if all objects are ‘Completed’ the overall status of the container is ‘Completed’ . 

### Changes
- If new objects are added to collections of items the learning progres status of an account reverts from 'Completed' to 'In Progress'. If objects are de-assigned from collections of items the learning progres status of an account changes from 'In Progress' to 'Completed'.  
- If the Learning Progress mode is changed, the Learning Progress of an account reverts from 'Completed' to 'In Progress' or vice versa. 
- Moving an object in the repository will results in different entries in the subtab 'Users' of the tab 'Learning Progress', depending on the context: In a category the 'User' subtab will present all people who touched the Learning Module. If moved to a group the same Learning Modules 'Users' subtab will only present entries for Group Members. 
- Linking an object will show different entries in the subtab 'User' depending on the context as well. 

### Role-specific tracking
Statsistics of Learning Progress serve educational purposes. Accoount in administrative roles will not show up in these statistics. 
- Interactions of accounts with global role 'Administration' will not create entries in the subtab 'Users' of objects. 
- Interactions of account with local role 'Course Tutor' or 'Course Administrator' will not create entries in the subtab 'Users' of objects. 
Outside of membership roles the status 'not attempted' will not be presented to keep personal data presentation to a minimum. 

## Integrated Services
The component employs the following services, please consult the respective PRIVACY.md files:
- Privacy Security, "Allow export of user profile data in courses" and "Allow export of user profile data in groups" must be activated to use custum user fields. (Take this out make own privacy to re-use in course and group) 
- ILIASObject
- [AccessControl](../../AccessControl/PRIVACY.md)
- Positions
- [OrgUnit](../../OrgUnit/PRIVACY.md)

Note: User  Component is not integrated, but directly queries the User Component. 

For specifics concerning components which integrate the Learning Progress please consult:
- [Certificate](../../Certificate/PRIVACY.md)
- [Badge](../../Badge/PRIVACY.md)
- [COPage](../../COPage/PRIVACY.md)
- [Portfolio](../../Portfolio/PRIVACY.md)

## Data being stored
Whether or not the Learning Progress data is presented some personal data is always recorded by ILIAS in the “Change Event” / “Read Event” table for each object and each account: 
- _User ID,_
- _Object ID,_ 
- _Timestamps of first and last access,_ 
- _Time spent in seconds,_
- _Total number of Accesses,_ 

Percentage 
The table “Ut_LP-Marks” saves 
- _User ID,_ 
- _Object ID,_ 
- _Status (the learning progress state, e.g. not attempted, in progress, completed, failed),_ 
- _Status (stores manually assigned Learning Progress),_ 
- _Mark,_
- _Comment,_ 
- _Timestamp of last status change and percentage if the object allows for it._ 

_The purpose of the consistent tracking of Learning Progress in ILIAS is to ensure verifiable qualification records, auditability, and proof of mandatory training completion in regulated environments. The scope is limited to legitimate educational interests._ 

## Data being presented
Accounts with access to the Administration > Achievements > Statistics and Learning Progress > Settings can switch on the display of ‘Learning Progress’ at any time and then all historical data will be presented according to the settings: 
- 'Learning Progress' and ‘Anonymized’ are activated, then ILIAS will show aggregated data in LP-tab of objects, the subtab 'Summary ' will display aggregated data only .
- 'Learning Progress' is activated and ‘Anonymized’ is deactivated, LP-tabs of objects show individual data sets for each account in the 'Users' subtab and the 'Summary' subtab.  
In the 'Users' tab the following personal data is peresented for each account: Login, First Name, Last Name, First Access DD.MM.YYY HH:MM , Last Access DD.MM.YYY HH:MM , Access Number, Time Spent HH:MM:SS, Status, Last Status Change DD.MM.YYY HH:MM , Mark,  Remark. Additionally more data of User Object Standard Fields and Custom Fields may be presented. This depends on configuration and settings in Personal Profile.
In some objects additional tab 'Matrix View' is presented. In 'Matrix View' the individual status of objects / sub-items is presented. Additional columns are the same as in 'Users' subtab. 
- Learning Progress' , ‘Anonymized’ and 'View Own Learning Progress' are activated, accounts with Read permission see their Personal Learning Progress in the tab.  They are presented with their own status. 
Accounts with 'Edit Learning Progress Settings'-permissions get the 'Summary' subtab , which displays aggregated data only without personal identification. 

### Other presentations
- Presentation of Learning Progress can be included in a [Portfolio](../../Portfolio/PRIVACY.md), please consult for details. 
- Presentation of Learning Progress are presented in [LearningHistory](../../LearningHistory/PRIVACY.md), please consult for details. 
- In Administration> User and Roles > User Management > User NAME > Learning Progress presents all courses this account has a Learning Progress record for, their sub-items can be investigated. For each obnjecvt the Status, Last Status Change DD.MM.YYY HH:MM , Percentage, Mark, Comment is shown
- In Achievements > Learning Progress presents all courses this account has a Learning Progress record for, their sub-items can be investigated. For each obnjecvt the Status, Last Status Change DD.MM.YYY HH:MM , Percentage, Makr, Comment is shown. 
- In Dashboard, Repository, Search own Learning Progress Status is presented. 

### Permissions required to be presented with Learning Progress data:
- A person sees their own Learning Progress status within objects they have access to.
- Learning progress overviews (e.g. in courses or groups) are presented to persons with the permission "View learning progress of other users". If orgunit-specific persmissions is activated, the list of accounts will be reduced to those accounts over which the person has authority / is a superior. 
- With "Edit Learning Progress Settings" the Learning Prorgess Mode can be changed. 
- Manual Learning Progress changes (including the actor) may be visible to persons with the permission “Manage Members ” on the Member tab.
- In the Members tab of courses accounts with "Manage Members" and the setting "Determination of Status 'Passed'" set to "Only Manual by Tutors" accounts can manually set the Passed Status. 

## Data being deleted
- Accounts cannot delete tracking or Learning Progress data. 
- Deleting an object removes all associated tracking and Learning Progress data. 
- Deleting an account removes all associated tracking and Learning Progress data.
- Data is permanently removed only after it is “delete from trash”.

## Data being exported
The following personal data can be exported in objects in the Learning Progress tab as .cvs or Excel: 
- Login, full name, first access, last access, number of visits, time spent, percentage, status, last status change and mark per account. Which data is actually included depends on the global Learning  Progress settings in Administration > Achievemnts > Statistics and Learning Progress. If set to Anaonymized only Aggregated progress data is exported. 
- Report Exports: Tutors and administrators can export Learning Progress reports as CSV or Excel files, which include personal data like names and statuses.
In Administration> Achievements > Statistics and Learning Progress only aggreated data is presented and can be exported. 
In Administration > Users and Roles> User Management > User Accounts > Name > Learning Progress very detailed Learning Progress data is exported but not comprising the personal data of the associated account. Export includes only the full name  of person pulling the export 
