# LTI Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

### General information

This component covers both sides of the LTI integration.

As **LTI Consumer**, ILIAS consumes learning content from external tools. Users can switch between these tools seamlessly without re-authentication, and the learning progress achieved in them can be transferred back to ILIAS. A tool can be another LMS, virtual classroom, plagiarism checker, or similar service. A list of supported tools is available at: [IMS Global LTI Platforms](https://www.imsglobal.org/all-learning-tools-interoperability-lti-platforms).

As **LTI Provider**, ILIAS provides learning content to external platforms and consumers. ILIAS acts as Provider in LTI 1.1 and as Tool in LTI Advantage. Users can switch from the other LMS to ILIAS without re-authentication, and the learning progress achieved in ILIAS can be transferred back to the platform.

A person with the “Create LTI Tool (Consumer)” permission cannot add tools of their own, to prevent the uncontrolled transfer of personal data. Instead, they can only select from the list of tools available to all users (“Global Tools for all Users”). This list is managed in Administration > LTI.

Only persons with the permission to add own tool settings in Administration > LTI can add individually configured tools. These are listed as “Tools Defined by Users”. Persons with the additional “Release Objects” permission can accept such a tool as a global tool, thereby adding it to the list available to all users. Both permissions are managed in the permission settings of Administration > LTI.

For improved privacy and enhanced data security, it is highly recommended to use LTI Advantage, as it offers superior protection of personal data compared to LTI 1.1, and to use pseudonymization in the settings of the platform or consumer.

### Integrated Components

The LTI component employs the following components, please consult the respective PRIVACY.md files:
- [AccessControl](../AccessControl/PRIVACY.md): the permissions of the LTI objects and of Administration > LTI, and the roles given to the users of a platform.
- [InfoScreen](../InfoScreen/PRIVACY.md): the Info tab of the LTI objects.
- [MetaData](../MetaData/PRIVACY.md): the metadata of the LTI objects.
- LearningProgress: the learning progress of the LTI objects, set from the scores of the tools, and sent to the platforms.
- [Certificate](../Certificate/PRIVACY.md): the certificates of the LTI objects.
- [Cron](../Cron/PRIVACY.md): the cron job “LTI Grade Service” that sends the learning progress to the platforms.
- User: the accounts of the users who come from a platform.
- [xAPI](../CmiXapi/PRIVACY.md): the learning experiences and the ranking of the LTI objects that use xAPI.

### Data being stored by ILIAS as LTI Tool (Consumer)

For each tool, you must specify whether the “Tool supports Outcome Service” setting is enabled. If the Outcome Service is activated, a Default Mastery Score is set as the threshold for completing the tool resource. This default value can be adjusted individually for each LTI object in the repository. The Mastery Score is used to determine the Learning Progress of the user.

In Administration > LTI, the privacy-related settings can be configured.
- The “User Identification” setting determines which user information is transmitted to the tool. Options include User-ID, Email Address, or a Hash Value. It is highly advisable to hash “User Identification” data or use a format like Random-ID@ILIAS-Platform-ID.ilias to enhance privacy.
- To ensure that course or group administrator accounts are properly identified and granted the appropriate permissions by the tool, the “Instructor E-Mail” is transmitted.
- In the “User Name” setting, you can define which user information is included in the data sent to the tool. It is recommended to select “No one” to maximize privacy.
- If accounts learning with an LTI resource are supposed to identify the account teaching them, the “Instructor Name” must be transmitted to the tool.
- It is advised to keep “Send User Picture” deactivated, as enabling this option will transmit profile pictures to the tool.
- A privacy statement warning can be displayed in the Info Tab by enabling the “External Tool” option.
- If “Tool supports Outcome Service” is activated, Learning Progress can be enabled in the settings of the LTI object in the repository.
- For LTI Advantage tools, the “Assignment and Grade Services” can be activated. A tool then posts scores to ILIAS: for each user, ILIAS stores every score it receives (score given, maximum score, activity progress, grading progress, the time the tool gave and the time it was received) and the resulting result of the user, which sets the Learning Progress. A tool can read back these results, identified by the user id it knows from the launches. A tool can also create line items of its own in the course or group of its objects; they hold no personal data, only the scores posted to them.
- For LTI Advantage tools, the Names and Role Provisioning Services can be activated (“Names and Role Provisioning Services”). A tool then can read the members of the course or group an object of it is in, with their roles (instructor or learner) and their status (active or inactive). Each member is described as a launch of the object describes the user, under the same privacy settings: user id, name and email only as far as “User identification” and “User name” allow. With a random user id per object, only the members who launched the object are given to the tool. ILIAS does not store this list; it is sent to the tool on request.
- An LTI Advantage tool can be registered through Dynamic Registration. ILIAS and the tool exchange their configuration (URLs, keys, client id, the scopes and messages the tool uses); no personal data is part of it. ILIAS stores the tool as an own tool of the user who registered it, as if that user had entered it.
- When a tool supports Deep Linking, the user who creates an LTI object picks its content in the tool. The request describes the user as a launch does, under the same privacy settings, with the role of an instructor. Each resource link the tool sends back becomes an object with the title, description and URL the tool gave; they hold no personal data.
- If the provider or tool supports xAPI statements, refer to the PRIVACY.md file for xAPI. Typically, xAPI generates and transmits large amounts of behavioral data, often highly personalized. Proper privacy considerations must be taken into account when using xAPI.

### Data being stored by ILIAS as LTI Tool (Provider)

ILIAS stores a user identification to match the external user with an ILIAS user. The personal data being stored depends on the settings made in the platform or consumer, and from this ILIAS installation it cannot be controlled how that personal data is dealt with.

If the “Global Role assigned to LTI Users” is set to “LTI User”, external users can only access the specified resource in ILIAS.

If “Names and Role Provisioning Services” is activated for the platform in Administration > LTI and the platform offers them, the launch of an instructor or administrator makes ILIAS read the members of the context at the platform. ILIAS creates an account for each active member that has none yet, with the user id, name and email the platform sends, and gives it the roles of the released object as a launch would. Members the platform reports as inactive or deleted lose the roles of the released object; their account stays. The accounts are created even for members who never launch ILIAS.

A platform can register ILIAS through Dynamic Registration with the URL given in Administration > LTI. ILIAS and the platform exchange their configuration (URLs, keys, client id, deployment id, the scopes and messages ILIAS uses); no personal data is part of it. ILIAS asks for the user id, name and email in launches; what the platform actually sends depends on its own settings.

If the platform requests Deep Linking, an instructor or administrator of the platform picks among the ILIAS objects released to that platform. Nobody is logged in to ILIAS and no account is created for it; ILIAS stores nothing about the user. The title, description and launch URL of the picked objects go back to the platform.

If the platform offers the Assignment and Grade Services or the LTI 1.1 Outcome Service, ILIAS sends it the Learning Progress and the score of the users that launched the object. If the platform gives no gradebook column for the object, ILIAS creates one with the title of the object, and it may read the results of that column to avoid sending a score again.

### Data being logged

The log of the component `lti` names the users of each launch and score by their ILIAS user id and by the user id the platform or tool knows them by. On the Debug level, it also holds the complete LTI messages ILIAS receives and sends, with the name, email and roles of the user as far as the privacy settings of the platform or tool let them through. Tokens, signatures, secrets and cookies are never logged. The Debug level is meant to look into a problem for a limited time.

### Data being presented

- The tab “Gradebook” of an LTI object lists the scores a tool sent with the Assignment and Grade Services: full name of the user, time, score, activity progress, grading progress and the time ILIAS received it. Persons with the “Read” permission see their own scores; persons who may see the learning experiences and ranking of other users (“read_outcomes”) see the scores of all users. The tab is shown only if the tool reports grades.
- The tabs “Learning Experiences” and “Ranking” of an LTI object that uses xAPI show the statements and the ranking of the users. They are shown to persons with the “Read” permission if the object enables them, otherwise only to persons with the “read_outcomes” permission. Full names are only shown to persons with the “read_outcomes” permission. See [xAPI](../CmiXapi/PRIVACY.md).
- The Learning Progress of the users of an LTI object is presented as described for LearningProgress.
- As LTI Tool (Provider), ILIAS presents no personal data of its own. The accounts created for the users of a platform, also those created through the Names and Role Provisioning Services, are presented like any other account, see User and [AccessControl](../AccessControl/PRIVACY.md).

### Data being deleted

- As LTI Tool (Consumer), the scores and results of the users of an object are deleted with the object when it is deleted from trash. Personal data transferred to a tool can only be deleted within that tool; how much is transferred depends on the privacy settings described above.
- As LTI Tool (Provider), the accounts created for the users of a platform are deleted like any other account, in Administration > Users and Roles. Deleting a platform in Administration > LTI deletes its registration and the releases of objects to it, not the accounts.

### Data being exported

No personal data can be exported.
