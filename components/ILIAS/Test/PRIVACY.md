# Test Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**

## General information

The module Test and the module TestQuestionPool are still tied together in most intricate ways. The primary component of concern in regards to privacy related evaluations is the Test. As the lines between these components are blurred, it is advised to never look at only one of the components but always at both.

## Integrated components

The Test component employs the following services, please consult the respective privacy.mds

  - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md): Is used for permission handling and is able to present personal data.
  - [Certificate](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Certificate/PRIVACY.md): Is able to use personal data for certificate generation.
  - [COPage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/COPage/PRIVACY.md): Is used for content creation/presentation and is able to store, present and delete personal data.
  - CSV: Is used for export creation and is able to export personal data.
  - Excel: Is used for export creation and is able to export personal data.
  - Export: Is used for export creation and is able to export personal data.
  - [InfoScreen](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/InfoScreen/PRIVACY.md): Is used as in any repository object and is able to present personal data.
  - [KioskMode](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/KioskMode/PRIVACY.md)
  - [LTI Provider](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/LTIProvider/PRIVACY.md) Is used to provide the Test via LTI and is able to present personal data.
  - [Metadata](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/Privacy.md): Stores the full name of the author of the test.
  - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/Privacy.md): Is used to create, edit and present comments for the test.
  - [Object](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ILIASObject): Stores the account which created the object as it's owner and creation and update timestamps for the object.
  - [Skill (Competence) Service](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Skill/PRIVACY.md)
  - [Taxonomy](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Taxonomy/PRIVACY.md)
  - [TestQuestionPool](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/TestQuestionPool/PRIVACY.md)
  - Tracking: Is used for Learning Progress and is able to store, present and delete personal data.

## Configuration

### Administration - Test and Assessment

At the Administration node for Test and Assessment users having 'Edit Settings' permissions are able to configure some functionality, which has an impact on personal data handling.

#### Settings

The field 'Unique user criteria' is used to specify which unique user criteria is used for test result import/export. Because of the following options this may changes the personal data contained at the export of a test:
- usr_id
- login
- email
- matriculation
- ext_account

Those information originate from the user service (see above).

#### Log Data

At the subtab 'Settings' there are the following options for logging (of personal data):

- The field 'Activate Test and Assessment Logging' activates the logging at the 'History' of the test.
- The field 'Log IP' acitvates the saving of the IP address of users at the 'History' of the test.

### Settings of a test

The settings of a test can be changed by users having 'Edit Settings' permissions.

#### General

At the subtab 'General' from the settings of a test it is possible to choose one of the following options for the field 'Privacy':
- Results with names (pre-selected)
- Results without names / anonymous test

If the second option is selected, no personal data of test attempts is presented at the test. If the user is not logged in while performing the test attempt, additionally no personal data is stored. Please have a look at the detailled information at the following sections.

In addition the 'Exam View' and it's sub-option 'Show Name of Participant' can be activated.

#### Scoring and Results

At the subtab 'Scoring and Results' from the settings of a test a user having 'Edit Settings' permission is able to:

- specify wether users have access to their own test results (and therefore to their own personal data).
- activate the 'Rankings' functionality, which potentially presents personal data to all participating users.

## Data being stored

The data being stored is listed by the tabs of the test.

### Test - data being stored while performing a test

While a user performs a test, the following data is stored. After finishing the test, this data is presented at various other tabs (see [Data being presented](#Data being presented)).
This is needed in order to provide the functionality of the test component. All listed data is at least linked to the 'User ID'.

- User ID
- Client IP
- Starting time stamp of the test attempt
- Last access time stamp of the test attempt
- Duration of the test attempt
- Answer content and timestamp of answer content submission
- Status of questions (Not answered/Answered)
- Scoring for questions (achieved points) ?
- Mark and status of the test attempt ?
- Log Entry Type and Interaction Type

### Settings

In general the change of settings is logged. Therefore the 'User ID', the 'Log Entry Type' and 'Interaction Type' is stored.

#### Edit Introduction & Edit Concluding Remarks

Those tabs use the COPage service and store personal data within this usage (see above).

#### Personal Test Settings Templates

- At the creation process of Personal Test Settings Templates the field 'Author' is prefilled with the full name of the user, which is creating the template. If this value is not changed, the name of the user is stored.
- Together with the field 'Author' the 'Creation Date' of the template is stored.

### Questions

- At the creation process of questions the field 'Author' is prefilled with the full name of the user, which is creating the question. If this value is not changed, the name of the user is stored.
- At the editing of questions the field 'Author' contains the previous saved value. If the value is changed and personal data is entered, it will be stored.
- Ownership of Questions: Owners of questions are stored in the Test as reference to the 'User ID'. The data is required to manage detailed access and permissions on usage and editing of the question.
- The creation, change and deletion of questions is stored as log entry. Therefore the 'User ID', the 'Log Entry Type' and 'Interaction Type' is stored.

### Participants
- A user having 'Edit Settings' permission is able to assign users as participants of a test.
- In addition, users which perform a test are added as participants automatically.
- For all users, which are assigned as participants, the 'User ID' is stored as link to the test.
- If a 'Client IP Range' is set for a participant, the entered value is stored, linked to the 'User ID'.
- For some actions, which are offered at this tab, log entries are stored. Therefore the 'User ID' (of the user with 'Edit Settings' permission), the 'Log Entry Type' and 'Interaction Type' is stored.

### Scoring
If the Scoring of a test attempt is changed at this tab, this event is logged. Therefore the 'User ID' (of the user with 'Edit Settings' permission), the 'Log Entry Type' and 'Interaction Type' is stored.

### Metadata

This tab uses the Metadata service and stores personal data within this usage (see above).

## Data being presented

The data being presented is listed by the tabs of the test.

### Test - data being presented while performing a test

While performing a test, the 'Name' of the participant himself is shown, if the 'Exam View' and it's sub-option 'Show Name of Participant' is activated.

### Settings

#### Edit Introduction & Edit Concluding Remarks

Those tabs use the COPage service and present personal data within this usage (see above).

#### Personal Test Settings Templates

- At this tab the values of the field 'Author' for all Personal Test Settings Templates are shown, which may contain personal data.
- The 'Creation Date' of the template is also prsented, which is directly linked to the value for 'Author'.

### Questions

At the overview of the questions of a test, the values of the field 'Author' for all questions are shown, which may contain personal data.

The following sections describe the possible actions for the questions of a test.

#### Statistics

The 'Author' of other tests is displayed at the table 'This question is used in the following tests', if the question is used in other tests, too. This information originates from the metadata service (see above).

#### Print Answers

All answers to a question with the 'Name' of the participants are presented here. If the test is set to anonymous (see above), this data is not presented.

### Participants

The table 'Participants' shows following personal data linked to the test attempt. Those are also shown, if the action 'Show Results' is used.

- Name (originates from the user service)
- Login (originates from the user service)
- Matriculation Number (originates from the user service)
- Starting time stamp
- Duration
- Number of Attempts Made
- Status of the test attempt
- Scored points
- Number of questions answered
- Percentage Score
- Passed-status
- Grade
- Scoring completed- 
- Last access time stamp of the test attempt

If the test is set to anonymous (see above), 'Name', 'Login' and 'Matriculation Number' are not presented.

### My Results

At the subtabs 'Test Results' and 'Printable List of Answers' users are able to access their own data. Those data contains their 'Name' and 'Matriculation Number'.

If the test is set to anonymous (see above), 'Name' and 'Matriculation Number' are not presented.

### Scoring

The tab 'Scoring' shows following personal data linked to the test attempt. 

- Name
- Login
- Test ID
- Number of the Scored Test Attempt
- Scored points for all questions
- Scoring completed

If the test is set to anonymous (see above), 'Name' and 'Login' are replaced by the 'Test ID'.

### History

At the 'History' log entries are shown, which originate in the change of the test settings and questions or in the participation of the test. The following personal data is presented:

- Date and Time of the event
- Name and Login of Author or Participant
- Client IP (of participants)
- Log Entry Type
- Interaction Type

If the test is set to anonymous, no entries are shown for the participation of the test.

### Metadata

This tab uses the Metadata service and presents personal data within this usage (see above).

### Administration - Test and Assessment - Log Data - Log Data Output

At this tab of the Administration, the same personal data is listed for all tests at the platform as listed at the section 'History'.

## Data being deleted

The data being deleted is listed by the tabs of the test. In general, only users with the 'Edit Settings' permission are able to delete data. Exceptions are explicitly listed.

### Test
 
Users with the 'Read' permission are able to delete their own answers while performing a test, which are linked to their 'User ID'. The answers are not linked to the 'User ID', if the test is set to anonymous and is performed without being logged in.

### Questions

It is possible to delete questions and within this the personal data at the field 'Author' is deleted.

### Participants

At this tab the following personal data can be deleted:

- Test results of participants and all linked personal data
- Assignment of users as participants and all linked personal data

### Administration - Test and Assessment - Log Data - Log Data Output

Users with the 'Edit Settings' permission of this administration node are able to delete any log entries and all linked personal data from all tests of the platform.

## Data being exported

The data being exported is listed by the tabs of the test. In general, only users with the 'Edit Settings' permission are able to export data. Exceptions are explicitly listed.

### Settings - Personal Test Settings Templates

When exporting a Personal Test Settings Template, the field 'Author' and the 'Creation Date' is exported.

### Participants

At this tab the following export files are available:

- Scored Test Attempt
- All test attempts
- as Certificate (PDF): uses the Certificate Service (see above)

Those export files contain the following personal data of the participants, which all originate from the user service (see above):

- Name
- Login
- E-Mail
- Matriculation Number
- Salutation
- Street
- City, State
- Zip Code / Post Code
- Country
- Institution
- Department

### My Results

At the subtabs 'Test Results' and 'Printable List of Answers' users with the 'Read' permission are able to download their own data via the button 'Print'. Those data contains their 'Name' and 'Matriculation number'.

If the test is set to anonymous (see above), 'Name' and 'Matriculation Number' are not included.

### History

At this tab the 'Export Legacy Log Data' is available, which can be used for historical purposes, and any data shown at the table can be exported (see above for details).

### Export

- There are three export files, which contain personal data (see below).
- If export files are created, they contain the personal data, which is available at the time of the creation.
- If personal data is to be deleted after the creation of an export file (which contains such data), the export file has to be deleted, too.

#### Archive File

- The Archive file contains all personal data, which is being stored and presented at the test (see above).
- It's purpose is to have this data easily accesible outside of ILIAS.

#### XML

- The XML export contains the personal data 'Author' of the questions and of the test itself within the metadata.
- It's purpose is to being imported in ILIAS again, although the contained personal data is easily accesible.

#### XML (incl. Participant Results)

- The XML export incl. Participant Results contains all personal data, which is being stored and presented at the test (see above).
- The data in the 'History' tab is an exception, this is not included.
- It's purpose is to being imported in ILIAS again, although the contained personal data is easily accesible.

### Administration - Test and Assessment - Log Data - Log Data Output

Persons with the 'Edit Settings' permission of this administration node are able to export any log entries and all linked personal data from all tests of the platform.
