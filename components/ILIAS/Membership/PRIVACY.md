# Membership Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General information

Containers do not implement the management of users themselves, the Membership Component provides the Member tab to the container. 

## Integrated Services
- [Course](../Course/PRIVACY.md)
- [Group](../Group/PRIVACY.md)
- [LearningSequence](../LearningSequence/PRIVACY.md)
- [StudyProgramme](../StudyProgramme/PRIVACY.md)
- Session
- Notification
- [InfoScreen](../InfoScreen/PRIVACY.md)
- [AccessControl](../AccessControl/PRIVACY.md)
- Tracking

## Data being stored
- The table object_members stores the objectID and the userID.
- "Access refused" users cannot access a membership-serviced container. 
- Membership Service interacts with the notification service, tutors and administrators can request notifications. 
- Course "passed" status is stored including passed status, time stamp and who set the status for each user. 
- Tutorial Support can be activated in the Member tab for a user with the local role Course / Group etc. Administrator. 
- The Membership Service caches the local role (admin, tutor, member) of an account in the container context.  

### Member_Agreement (for courses and groups) 
- The member_agreement table stores memberID, Timestamp and Status (accepted/ not accepted). 
- General Privacy Agreements for membership-serviced containers can be activated in Administration > Users and Roles > Privacy and Security > User Confirmation.
- When user access the Content-tab for the first time, they are presented with a list of personal data that is visible to tutors and administrators. If they do not agree to storing this data in the course, users cannot access the course content. Which specific personal data fields are included is derived from the user_object settings in Administration > Users and Roles > User Management > Profile from  fields that are set to "Visible in Courses / Groups / Study Programmes". New Custom User Fields can be created for an installation. These fields are included. 
- Course specific user data will be included in the Membership Agreement, if they are provided in Course / Group > Settings > Course-specific User Data. 
- The entire list is also displayed on the Info-tab. 

Membership Limitations is not stored but checked against the rules on the fly. 

## Data being presented
- Account with Manage Member permission are presented with the Member-tab comprising the following personal data : 
  - The first name, last name, and username of members and other fields set to "visible in Groups / courses" in Profile are presented (see above).
  - They are presented with Course "passed" status and the respective time stamp of an individual member and the login of and who set the status.

- Accounts with View learning progress of other users permission are presented with individual Learning Progress status on the Member-tab. 
- To accounts that are Members of the container with Read Permission  
  - If activated, members gallery with the first name, last name, username of group’s member will be presented. 
  - Member Galleries can be set to allow for members to mail to other members. 
  - Member Galleries can be set to include Participant Lists, that can be downloaded comprising personal data. Which personal data specifically can be downloaded can be configured in the subtab Partcipants List. TBD if more info is needed. 

- To Accounts with Visible permission: 
  - On Info-tab, If it was entered under Settings > Course Information> Contact, the e-mail(s), the first and last names of the person who is responsible for the tutorial support are presented.
  - On Info-tab, first and last names of the person who is responsible for the tutorial support are presented. Additionally all fields set to visible by logged in users in that accounts personal profile will be displayed. 

## Data being deleted
The local role “Member” is withdrawn when an account unsubscribes from a course or group, when the “Remove” action is applied in the “Members” tab, or when a “de-assign” action is carried out via a web service. In all these cases, the corresponding entry in object_members is deleted.
Member_Agreement is deleted only when the account is deleted, not if the membership of a course / course is withdrawn. Member_Agreement is also deleted, once the object is purged from the trash. 

## Data being exported
Print view of participants’ data are available in Member > Edit Participants > Generate List,  Member > Member Gallery > Generate List
CVS or XLS Exports of participants’ data are available in Member > Export Data of Participants 
For Print, CSV or XLS the data fields to be exported are selected using checkboxes.  
The XML-export of the object course or group does not contain membership data.
