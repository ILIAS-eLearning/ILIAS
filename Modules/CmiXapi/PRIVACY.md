# Module cmix Privacy

Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information

This module cmix allows uploading an object or using an external object. An object is a Learning Record Provider (LRP), usually content or instructional activities. Personal data flow from an LRP to a Learning Record Store (LRS).
LRS and LRP are to store and manage states and statements on learning activities or learning progress of a person.  
ILIAS only holds the identification of a person to connect Learning Progress or statements on learning activities to an ILIAS account.

*   The LRP communicates directly with the LRS: ILIAS queries Learning Progress or statements of learning activities from the LRS. To connect this to an account, ILIAS must identify a record with a user-ID.
    *   If the LRP is launched by ILIAS, ILIAS provides a User-ID.
    *   If the LRP is launched without ILIAS, a person provides their e-mail address used for the LRS.
*   The LRP communicates directly with ILIAS: ILIAS behaves as an LRS for the LRP. ILIAS functions as a proxy between LRP and LRS. Thus ILIAS can filter statements of learning activities being sent to the LRS.


## Data being stored

1. LRP / content generates and sends states like page last visited, number of page views, results of questions, number of times a question was viewed or answered, number of times a page was viewed, language or audio preference. ILIAS does not process this data.
2. LRP / content generates and sends statements on learning activities to the LRS. These statements follow a grammar subject-verb-object may comprise context data. However, the data is generated in the LRP / content and sent to the LRS. Statements are triggered by events and document what happened in a granular fashion i.e.:
    *   User x viewed the page z on date DD.MM.YYYY HH:MM:SS for duration d.
    *   User x wrote the comment r on that page z on date DD.MM.YYYY HH:MM:SS.
    *   User a launched the test q on date DD.MM.YYYY HH:MM:SS.
    *   User a answered the question l selected answer option u, gained 4 of 7 points on date DD.MM.YYYY HH:MM:SS.
    *   User b experienced PDF File p on date DD.MM.YYYY HH:MM:SS.
    *   User b completed test q with score t on date DD.MM.YYYY HH:MM:SS.
3. If cmi5 is used, then ILIAS generates and sends only the following fixed set of statements on learning activities to the LRS:
    *   "user x launched the activity y",
    *   "user q abandoned the activity p" and
    *   "user w satisfied the activity z".

After a statement was sent, changing ILIAS settings i.e. learning progress, cannot change the statement.

Typically enourmous amounts of behavioral data, often times personalized behavioral data, is generated and sent around.   
In all these cases ILIAS stores some kind of user identification, i.e. User-ID, E-Mail address or hash value.   
This "unregulated" scenario is not advisable to run.

The unimpeded communication between LRP / content and LRS should be curbed by using configuration:   
At Administration > Extending ILIAS > xAPI/cmi5 a person with "Edit Settings" permission can add an additional LRS-Type.
*   Activating the "CronJob necessary for Learning Progress" prevents curbing the amount of data sent.
*   In the option "User Identification" one can select which piece of information is transmitted to the flow of data to LRP and LRS. Selecting "Hash combined with a unique ILIAS platform id formatted as an E-Mail address" is advised. One and the same LRS can be shared between learning platforms. It MUST be ensured that User Identification data is hashed at least.
*   In the option "User Name" one can select which piece of information is transmitted to the flow of data to LRP and LRS. Selecting "No one" is advised.
*   The settings offer an option to reduce the amount of statements being send from the LRP / content to LRS, i.e.
*   Activate "Save learning success data only" to retain only statements comprising specific and selected verbs.
*   Activate "Blacken Data" to replace actual calendar dates or durations by fixed values.
*   Activate "Do not store substatements" to discard subordinate statements.
*   To force privacy settings on repository objects: "Configurations Options: Settings are not changeable for Objects".

In the repository an object type "xAPI/cmi5" can be added, the LRS-Type MUST be selected in the ILIAS creation dialogue.
*   If in Administration > Extending ILIAS > xAPI/cmi5 > Add LRS-Type > Configuration Options is set to "Default Settings, changeable for Objects", then a person can overrule the above mentioned options and create their own privacy policy. This option makes it impossible to specify a document for consent, since at any given time the privacy regime could be changed by a person with "Edit Settings" permission for an xAPI/cmi5-object.
*   If in Administration > Extending ILIAS > xAPI/cmi5 > Add LRS-Type > Configuration Options is set to "Settings are not changeable for Objects", then the globally set privacy options cannot be changed in an xAPI/cmi5-object. To specify a document for consent, several LRS-Types should be set up to serve different educational practices i.e. exams require more personal data while some content can be quite restrictve privacy-wise.
*   Creating an "xAPI/cmi5" object in the repositry allows selecting an LRS-Type. In Administration > Extending ILIAS > xAPI/cmi5 > Add LRS-Type the setting "Availability" determines if an LRS-Type is available in the creation dialogue. LRS-Types set to "Not available" do not receive new data. This prepares an orderly deletion of LRS data sets.

The Module cmix employs the following services, please consult the respective privacy.mds: Metadata, AccessControl, Learning Progress.


## Data being presented

*   No xAPI related data is presented in the global administration.
*   A person with "Edit Settings" permission for an xAPI/cmi5 object in the repository can activate statement viewer and ranking viewer in the tab Settings.
    *   If activated, the person with "Read" permission is presented with new tabs "Learning Experiences" and "Ranking".
        *   A table in "Learning Experiences" presents data sets comprising statements on the person him- or herself: On Date, User, Verb, Object. A modal dialogue can be opened to present the full statement which may comprise a lot more data like duration, selected answer option, success, context and so forth. The amount of data tracked depends on the LRP.
        *   In tab "Ranking", tables are presented according to options selected in the Settings-tab: Own rank of person, top ranking of other persons, both. Date, Percentage and duration might be presented.
    *   A Person with "View learning experiences of other users" is always is presented with tabs "Learning Experiences" and "Ranking".
        *   A table in "Learning Experiences" presents all data set on all the persons that have interacted with object: On Date, User, Verb, Object. This person can also view all full statements on all users.
        *   In "Ranking", tables are presented according to options selected in the Settings-tab: Own rank of person, top ranking of other persons, both. Date, Percentage and duration might be presented.
*   The data stored in the LRS are always displayed only in relation to the LRP.


## Data being deleted

The xAPI specification does not envisage any kind of deletion of data in the LRS at all.
When an object "xAPI/cmi5" in the repository is deleted, the personal data of the statements persists in the LRS. Once personal data is communicated to the LRS, ILIAS has no control over the deletion this personal data.
There is no way to delete data in the LRS from ILIAS.
Thus you MUST ensure that the data produced is properly pseudonymized as laid out in the section on storage.