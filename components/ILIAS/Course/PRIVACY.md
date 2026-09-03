# Course
Disclaimer: This documentation does not guarantee completeness or correctness. Please report any missing or incorrect information using the ILIAS issue tracker or contribute a fix via Pull Request (docs/development/contributing.md#pull-request-to-the-repositories).
## General Information
Courses support teaching and training events. Courses can contain almost any content. Learners can often create their own learning content, upload files, or share them. This created content is not part of this privacy.md.
 
## Integrated Services
The Course employs the following services, please consult the respective PRIVACY.md files:
- Learning Progress
- Metadata
- Object
- AccessControl
- Info Screen
- Calendar
- Didactic templates
- Tagging service
- Notes and comments
- Certificate
- Skills
- Mail
- News incl. Timeline
- Membership service
- Multilingualism
- Orgunits / Positions 
- Badges 
- Export

## Data being stored
- Settings > Course information> Contact offers storing personal information of the person responsible for offering and running the course.  
- Course > Settings > Startobject stores userID and ObjectID and Timestamp.
- If Administration > Users and Roles > Privacy and Security> Protection of User Profile Data > User Confirmation when Entering Courses is activated, then data fields added to Course > Settings > Course-Specific User Data have to be filled in by users before entering a new course. Input into fields is stored along with userID and ObjectID and Timestamp.
- In Course > Settings > Presentation Type different Course Types can be selected: Timings View, Learning Objective Driven Course Simple View and Session View. Simple View and Session View do not store any personal data. 
- Timings View does store personal data:
  - Changeable Timings with Absolute Dates stores userID and ReferenceID and Timestamps for start and end dates.
  - All Timings with Relative Dates always stores userID and ReferenceID and Timestamps for start and end dates. 
- Learning Objective Driven Course does store personal data:
  - Each Learning Objective stores userID and ObjectiveID and Timestamp, for initial test and achievement test the required minimum and the achieved percentage.
## Data being presented
If it has been entered under Settings > Course information> Contact, the e-mail(s), first and  last name are presented.
Timings Data is presented in the Member tab for each user. This is a mix of Membership service and course from long ago. 
Learning Objective Data are only presented in Learning Progress service / tab. 


## Data being deleted
Basic object, permissions and learning progress data are deleted only, once the object is deleted from trash. The trash can be emptied at Administration > System Settings an Maintenance > Repository and Trash > Trash.
Members and their interactions will be removed from the course if the course is restored from the trash.


## Data being exported
XML Exports of Course contains the first name, last name and e-mail(s), if these pieces of  information were entered under Settings > Course Information> Contact.  
