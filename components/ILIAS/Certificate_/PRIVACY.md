# Certificate Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

- An account with "Edit Settings" permissions for "Administration > Achievements > Certificates > Settings"
  can enable the "Certificate Service" globally.
- If the "Certificate Service" is globally activated then the object types

      - Modules/Course
      - Modules/StudyProgramme
      - Modules/Exercise
      - Modules/Test
      - Modules/ScormAicc
      - Modules/CmiXapi
      - Modules/LTIConsumer

  feature an additional sub-tab "Certificate" in the "Settings" tab.
- Accounts with the permission "Edit Settings" for these particular objects can configure and activate certificates.

## Data being stored

For each issued persisting user certificate, the ID of the user account is stored. The purpose of this ID being stored
  is the identification of the certificate being presented to or exported by the respective owner of certificate.
- In addition, certificate templates may contain placeholders.
- All placeholders defined in a certificate template will be replaced by the corresponding user data and contextual
  data i.e., course title or issuing date.
- The issuing of certificate and thus replacing placeholders with user data is triggered by the user who
  completes an object.
- The actual replacing of placeholders is done by processing a queue. The queue holds the user ID along with the object
  related data and a datetime information. Once a queue item has been processed, the record will be deleted.
- The issued certificates are immutable: They contain the user data that replaced the placeholders at the
  moment of issuing. It will not change with time.

    Stored Data (if used as a placeholder in a certificate template):

      - Username
      - Fullname Presentation
      - Firstname
      - Lastname
      - Title
      - Salutation
      - Birthday
      - Institution
      - Department
      - Street
      - City
      - ZIP
      - Country
      - Matriculation
      - User Defined Fields (if avaiable and enabled for "Certificate" in field definition)

## Data being presented

- The "Certificate Service" presents the certificate and the respective personal data only to the owner of the
  issued certificates at "Achievements > Certificates". Owners can download a PDF document of their certificate.
- Certificates are also presented to their owner in "Achievements > Learning History", if activated.
- Certificates may be presented to other accounts in the object types listed above. Please refer to the respective
  "PRIVACY.md" of those object types to see presentation of certificate data in that very object type.
  For example the course object presents certificate download links in the "Member" tab to user accounts with
  "Manage Member" permission.

## Data being deleted

- If a user account is deleted, all it's certificates will be completely deleted from the database.
  There is no trash for users.

## Data being exported 

- Certificate owners can export PDF documents of their certificates at "Achievements > Certificates".
  No other exports of user related data is provided by the "Certificate Service" itself. Exports may be possible
  in the object types listed above. Please refer to the respective "PRIVACY.md" files.