# Certificate Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

- An account with `Edit Settings` permissions for `Administration > Achievements > Certificates > Settings`
  can enable the `Certificate Service` globally. If enabled, certificate templates can be
  configured and activated for different object types (see list below) depending on the `Edit Settings` permission
  for the particular object in the repository.

## Data being stored

- For each issued persisting user certificate the ID of the user account is stored
  in database table `il_cert_user_cert` (field: `user_id`). The purpose of this ID being stored is the identification of
  the certificate's owner (for filtering purposes) whenever certificates are determined, presented or exported.
- In addition, further user related data is stored (fields: `certificate_content`, `template_values`), depending on the
  placeholders used in the certificate template. All placeholders defined in a certificate template
  (database table `il_cert_template`) will be replaced by the corresponding user data and related data of the
  issuing object given at that particular moment in time, when the relevant domain event (e.g. `User comples object`)
  is raised. It's the nature of a `Persisting Certificate` that requires this data being determined and stored at the
  point in time when the certificate is created. The user certificate and it's data MUST be immutable, not matter if
  user data change afterwards. You can think of this as a "snapshot" or a virtual printer. The certificate
  content (`certificate_content`) is a plain XML string, the placeholder values are stored as a JSON
  string.

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
      - User Defined Fields (if avaiable and enabled for `Certificate` in field definition)

- There might be other user related data stored depending on specific placeholders of the respective consumer. Consumers
  might provide placeholders for the qualification status, test results, points, etc..
- Although other components might provide user related data, and it is the responsibility of the consumer
  to define the nature and extend of this data, the certificate service will store this as part of the final certificate
  content.
- Because the replacement of placeholders for final user certificates (possible bulk processing) can be time-consuming, the
  `Certificate Service` will initially store the issued raw data of the domain event (e.g. `User completes an object`) in an
  (asynchronous) queue (table: `il_cert_cron_queue`). Once a queue item has been processed, the record will be deleted.
  The ID of the user account is stored along with the object related data and a datetime information.

## Data being presented

- The certificate service itself provides a `Deck of Card` user interface listing all achieved certificates for the acting
  user account. The presented data is only object related, enriched with a datetime presentation of the issue date
  of the respective certificate. Furthermore, a PDF document can be generated (on demand/at runtime). The PDF document
  contains the textual representation of the [stored data](#data-being-stored).
- In addition, downloads of certificate PDF documents are provided by consumers of the certificate service. The
  availability of user interface elements triggering a download of a user certificate and corresponding access checks
  when the PDF documents are requested depend on the specific consumer. Please check the privacy documentation
  of the corresponding consumers. 

    Known Consumers:

      - Modules/Course
      - Modules/StudyProgramme
      - Modules/Exercise
      - Modules/Test
      - Modules/ScormAicc
      - Modules/CmiXapi
      - Modules/LTIConsumer

- The certificate provides an internal API. The presentation of this [exported data](#data-being-exported)
  depends on the consumer. Please check the privacy documentation of the corresponding consumers.

    Known API Consumers:

      - Modules/OrgUnit

## Data being deleted

- If a user account is removed from system, all it's user certificates will be completely deleted from
  the database.

## Data being exported 

- As mentioned the cerfificate service provides an internal [API](./README.md#api). The consumer is able
  to request user certificates based on a filter. The API returns an object representation of the
  [stored database records](#data-being-stored) matching the passed filter.
- Additionally, the owner of a certificate is able to request a PDF document download of it's achieved
  certificates (as mentioned above). There might be consumers of the certificate service which provide additional
  export functionality. Please check the privacy documentation of the corresponding consumers.
- No other exports of user related data is provided. Only certificate templates are copied or exported if the
  consuming component (e.g. a course) is copied or a manual export of the certificate is triggered.