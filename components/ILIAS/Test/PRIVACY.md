# Test Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**

## General information

The Test component is used to create, manage and run tests, which includes creating and managing questions. There are many use cases for the Test component, which is why it has so many settings. The most common scenarios are likely to be self-assessment tests and examinations.

To ensure that this functionality meets the expectations of all stakeholders, a great deal of data needs to be stored and displayed within this component.

The Test component and the TestQuestionPool component are still tied together in most intricate ways. The primary component of concern in regards to privacy related evaluations is the Test. As the lines between these components are blurred, it is advised to never look at only one of the components but always at both.

## Integrated components

The Test component employs the following components, please consult the respective privacy.mds

  - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md)
  - [Certificate](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Certificate/PRIVACY.md): Is used for certificate creation and uses test-specific placeholders [RESULT_PASSED], [RESULT_PERCENT], [MAX_POINTS], [RESULT_MARK_SHORT], [RESULT_MARK_LONG]
  - [COPage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/COPage/PRIVACY.md): Is used for content creation/presentation within questions, Introduction and Concluding Remarks and is able to store, present and delete personal data.
  - [CSV](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/CSV)
  - [Excel](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Excel)
  - [Export](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Export)
  - [InfoScreen](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/InfoScreen/PRIVACY.md)
  - [KioskMode](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/KioskMode/PRIVACY.md)
  - [LTI Provider](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/LTIProvider/PRIVACY.md): Is used to provide the Test via LTI and is able to present personal data.
  - [Metadata](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/Privacy.md): Stores the full name of the author of the test.
  - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/Privacy.md)
  - [Object](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ILIASObject)
  - [Skill (Competence) Service](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Skill/PRIVACY.md)
  - [Taxonomy](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Taxonomy/PRIVACY.md)
  - [TestQuestionPool](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/TestQuestionPool/PRIVACY.md)
  - [Tracking](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Tracking)
  - [User](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/User): Provides information about the account being used using the Test component in order to store those.

## Configuration

### Administration > Repository and Objects > Test and Assessment

At the Administration node for Test and Assessment accounts having 'Edit Settings' permissions are able to configure some functionality, which has an impact on personal data handling.

At Administration > Repository and Objects > Test and Assessment > Settings it is possible to select which 'Unique user criteria' is used in test imports/exports. The selected type of personal data will be included in export type 'XML incl. Participants Results' of a test for each account. Options are: usr_id, login, email, matriculation, ext_account. This personal data is required to match accounts results when importing an export file at the same or another platform.

At Administration > Repository and Objects > Test and Assessment > Log Data accounts with 'Edit Settings' permission can activate the History-tab via the checkbox 'Activate Test and Assessment Logging'. Additionally the setting 'Log IP' allows for logging the IP-adress of participants along with the interactions as well as specific settings of said test during the interaction.
The purpose of both options is to store important events and information for configuring and performing tests. This gives the possibility to check those information in case of issues or concerns after performing tests.

### Test > Settings > General

At the subtab Test > Settings > General for accounts with 'Edit Settings' permission it is possible to choose one of the following options for the field 'Privacy':
- Results with names (pre-selected)
- Results without names / anonymous test

If the second option is selected, no personal data of test attempts is presented at the test. If the user is not logged in while performing the test attempt, additionally no personal data is stored. Please have a look at the detailled information at the following sections.

In addition the 'Exam View' and it's sub-option 'Show Name of Participant' can be activated by accounts with 'Edit Settings' permission, which has an impact on the presented personal data.

### Test > Settings > Scoring and Results

At the subtab Test > Settings > Scoring and Results accounts having 'Edit Settings' permission are able to specify whether accounts have access to their own test results (and therefore to their own personal data). When activating the access to the accounts own results, it is possible to dedicated activate the presented data:

- ‘Passed’ / ‘Failed’ Status
- Grade
- Detailed Results (these contain all given answers and points scored)

In addition accounts with 'Edit Settings' permission are able to activate the 'Rankings' functionality, which potentially presents personal data to all participating accounts. There are several sub-settings for 'Rankings', which specify the presented personal data:

- Mode: Own Position, Top Ranks, Own Position and Top Ranks
- Number of Top Ranks
- Without Names: prevents the presentation of accounts names
- Date / Time: additional column in the rankings table showing when the test was finished
- Point Score
- Percentage Score
- Time Spent: additional column in the ranking table showing the time taken to complete the test

## Data being stored

### Test > Test - data being stored while performing a test

While an account performs a test, the following data is stored. After finishing the test, this data is presented at various other tabs (see [Data being presented](#Data being presented)). This is needed in order to provide the functionality of the test component. All listed data is at least linked to the 'User ID'.

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

### Test > Settings

In general the change of settings at the tab Test > Settings is logged. Therefore the 'User ID', the 'Log Entry Type' and 'Interaction Type' is stored.

At the creation process of a template at Test > Settings > Personal Test Settings Templates the field 'Author' is prefilled with the full name of the account, which is creating the template. If this value is not changed, the name of the account is stored. In addition the 'Creation Date' of the template and the User-ID is stored. Those information are needed in order to present the origin of a Personal Test Settings Template.

### Test > Questions

In genereal the creation, change and deletion of questions at the tab Test > Questions is stored as log entry. Therefore the 'User ID', the 'Log Entry Type' and 'Interaction Type' is stored.

At the creation and editing process of questions the field 'Author' is prefilled with the full name of the account, which is creating the question. If this value is not changed, the name of the account is stored. If the value is changed and personal data is entered, it will be stored. By storing (and presenting) the value for 'Author' it is possible to contact the account, if there are problems with the question or the configuration of it. In addition it supports the collaborative development of questions.

Owners of questions (accounts which have created a question) are stored in the Test as references to the ‘User ID’. This data is required to manage detailed access and permissions for the usage and editing of the question.

### Test > Participants

An account having ‘Edit Settings’ permission is able to assign accounts as participants of a test at the tab Test > Participants. In addition, accounts which perform a test are added as participants automatically. For all accounts that are assigned as participants, the ‘User ID’ is stored as a link to the test. If a ‘Client IP Range’ is set for a participant, the entered value is stored, linked to the ‘User ID’. Those pieces of information are needed in order to manage access to the test.

For some actions, which are offered at this tab, log entries are stored. Therefore the 'User ID' (of the account with 'Edit Settings' permission), the 'Log Entry Type' and 'Interaction Type' is stored.

### Test > Scoring

If the Scoring of a test attempt is changed at the tab Test > Scoring, this event is logged. Therefore the 'User ID' (of the account with 'Edit Settings' permission), the 'Log Entry Type' and 'Interaction Type' is stored. This ensures the traceability of test results.

## Data being presented

### Test > Test - data being presented while performing a test

While performing a test, the 'Name' of the participant himself is shown, if the 'Exam View' and it's sub-option 'Show Name of Participant' is activated. When a test is performed in person, the presentation of this information can be used for validating the logged in account.

### Test > Settings > Personal Test Settings Templates

The values of the field 'Author' for all Personal Test Settings Templates at the subtab Test > Settings > Personal Test Settings Templates are presented, which may contain personal data. The 'Creation Date' of the template is also presented, which is directly linked to the value for 'Author'.

### Test > Questions

At the overview of the questions at the tab Test > Questions, the values of the field 'Author' for all questions are shown, which may contain personal data.

When using the 'Statistics' action from a question, the 'Author' of other tests is displayed at the table 'This question is used in the following tests', if the question is used in other tests, too. This information originates from the metadata component (see above).

When using the 'Print Answers' action from a question, all answers to a question with the 'Name' of the participating accounts are presented here. If the test is set to anonymous (see above), this data is not presented.

### Test > Participants

The table ‘Participants’ at the tab Test > Participants shows the following personal data linked to the test attempt. This data is also shown if the action ‘Show Results’ is used. The purpose of the presentation is to give accounts with ‘Edit Settings’ or ‘Test Results’ permission an overview of all test attempts and the related data.

- Name (originates from the user component)
- Login (originates from the user component)
- Matriculation Number (originates from the user component)
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

### Test > My Results

At the subtabs Test > My Results > Test Results and Test > My Results > Printable List of Answers accounts are able to access their own test results. This data contains their ‘Name’ and ‘Matriculation Number’. If the test is set to anonymous (see above), the ‘Name’ and ‘Matriculation Number’ are not presented.

### Test > Scoring

The tab Test > Scoring shows the following personal data linked to the test attempt for accounts with ‘Edit Settings’ permission. This is needed in order to review and possibly change the scoring of test attempts. If the test is set to anonymous (see above), the ‘Name’ and ‘Login’ are replaced by the ‘Test ID’.

Accounts with 'Score anonymously' permission are able to access the tab Test > Scoring, but are not able to see 'Name' and 'Login'.

- Name
- Login
- Test ID
- Number of the Scored Test Attempt
- Scored points for all questions
- Scoring completed

### Test > History

At the tab Test > History log entries are shown, which originate from changes to the test settings and questions or from participation in the test. The purpose of the tab ‘History’ is mainly to check events in case of issues or concerns after performing tests. The following personal data is presented:

- Date and Time of the event
- Name and Login of Author or Participant
- Client IP (of participants)
- Log Entry Type
- Interaction Type

Some examples for Interaction Types are 'Test Run Started', 'Question Shown', 'Answer Submitted' and 'Test Run Finished' for participating accounts and 'Main Settings Modified', 'Run of Participant Closed', 'Grading Reset' and 'Participant Data Removed' for accounts with 'Edit Settings' permission.

If the test is set to anonymous, no entries are shown for the participation of the test.

### Administration > Repository and Objects > Test and Assessment > Log Data > Log Data Output

Accounts with the ‘Edit Settings’ permission for the administration node Administration > Repository and Objects > Test and Assessment > Log Data > Log Data Output are able to list the same personal data for all tests on the platform as listed in the section ‘History’ of any test.

## Data being deleted

In general, only accounts with  'Edit Settings' permission are able to delete data. Exceptions are explicitly listed.

At the tab Test > Questions it is possible to delete questions, and thereby the personal data in the field ‘Author’ is deleted.

At the tab Test > Participants the test results of participants and all linked personal data can be deleted. Additionally, the assignment of accounts as participants can be cancelled, which deletes all linked personal data.

Accounts with the ‘Edit Settings’ permission for the tab Administration > Repository and Objects > Test and Assessment > Log Data > Log Data Output are able to delete any log entries and all linked personal data from all tests on the platform.

Accounts with ‘Read’ permission are able to delete their own answers, which are linked to their ‘User ID’, while performing a test at the tab Test > Test. The answers are not linked to a ‘User ID’ if the test is set to anonymous and is performed without being logged in.

If the option 'Allow Deletion of Non-Scoring Attempts' at Settings > Scoring and Results > Access to Test Results is activated, accounts with 'Read' permission are able to delete their own non-scoring test attempts and all linked personal data at the tab Test > My Results.

## Data being exported

In general, only accounts with the 'Edit Settings' permission are able to export data. Exceptions are explicitly listed.

When exporting a Personal Test Settings Template at Test > Settings > Personal Test Settings Templates, the fields 'Author', 'Creation Date' and "User-ID" are exported. This secures being able to identify the origin of a Personal Test Settings Template after importing it to an account.

At the tab Test > Participants the export files 'Scored Test Attempt', 'All test attempts' and 'as Certificate (PDF)' are available. The purpose of the different export files is to extract the dedicated test results of one or more accounts. This can, for example, be used for discussing the results with the participant. Those export files contain the following personal data of the participants, which all originate from the user component (see above):

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

At the tab Test > History the 'Export Legacy Log Data' is available, which can be used for historical purposes, and any data shown at the table can be exported (see above for details).

At the subtabs Test > My Results > Test Results and Test > My Results > Printable List of Answers accounts with 'Read' permission are able to download their own data via the button 'Print'. This data contains their ‘Name’ and ‘Matriculation Number’. If the test is set to anonymous (see above), the ‘Name’ and ‘Matriculation Number’ are not included.

### Test > Export

There are three export files at the tab Test > Export which contain personal data (see below). If export files are created, they contain the personal data that is available at the time of their creation. If personal data is deleted after the creation of an export file (which contains such data), the export file must also be deleted.

The ‘Archive file’ contains all personal data which is being stored and presented in the test (see above). Its purpose is to have this data easily accessible outside of ILIAS, e.g. for long-term archiving of the data.

The 'XML' export contains the personal data 'Author' of the questions and of the test itself within the metadata (see above). Its purpose is to be imported into ILIAS again, although the contained personal data is easily accessible.

The ‘XML export incl. Participant Results’ contains all personal data which is being stored and presented in the test (see above). The data in the ‘History’ tab is an exception; it is not included. Its purpose is to be imported into ILIAS again, although the contained personal data is easily accessible. This export file can be used to, e.g., provide the test results to the participants at another ILIAS installation.

### Administration > Repository and Objects > Test and Assessment > Log Data > Log Data Output

Accounts with the ‘Edit Settings’ permission for the administration node Administration > Repository and Objects > Test and Assessment > Log Data > Log Data Output are able to export any log entries and all linked personal data from all tests on the platform.

## Summary

| Data | Stored in DB | Presented to accounts with 'Read' Permission | Presented to accounts with 'Edit Settings' Permission |  Presented to accounts with 'Test Results' Permission | Presented to accounts with 'Score anonymously' Permission | Exported | Deleted with removing test attempt | Deleted with removing test object | special settings |
|------|---------------|---------------------------------------------|------------------------------------------------------|----------------------------------------------------------------|------------------------------------------------------|------------------------------------------------------|------------------------------------|--------------------------------------|-------------------|
| test attempt data (e.g. account, timestamps, scoring) | reference to by ID | if activated | yes | yes | yes, but anonymised| yes | yes | yes | anonymous test without account presentation, gamification with presentation to all accounts |
| question data (e.g. author, statistics) | yes & reference to by ID | no | yes | no | no | yes | no | if question only was used at the deleted test object | value for author can be changed manually |
| assignment of account as 'Participant' | reference to by ID | if setting 'Select Participants Manually' is used | yes | yes | no | yes | no, but with deletion of assignment | yes | anonymous test without account presentation |
| Personal Test Settings Templates author | yes & reference to by ID | no | only own templates | only own templates | only own templates | yes | no | no, can be deleted separately |
