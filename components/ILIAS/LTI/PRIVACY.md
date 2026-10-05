# LTI Privacy

> Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via the [ILIAS issue tracker](https://mantis.ilias.de) or submit a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

### General information

This component covers both sides of the LTI integration.

As **LTI Consumer**, ILIAS consumes learning content from external tools. Users can switch between these tools seamlessly without re-authentication, and the learning progress achieved in them can be transferred back to ILIAS. A tool can be another LMS, virtual classroom, plagiarism checker, or similar service. A list of supported tools is available at: [IMS Global LTI Platforms](https://www.imsglobal.org/all-learning-tools-interoperability-lti-platforms).

As **LTI Provider**, ILIAS provides learning content to external platforms and consumers. ILIAS acts as Provider in LTI 1.1 and as Tool in LTI Advantage. Users can switch from the other LMS to ILIAS without re-authentication, and the learning progress achieved in ILIAS can be transferred back to the platform.

An account with the “Create LTI Consumer” permission cannot add its own LTI Consumers, to prevent the uncontrolled transfer of personal data. Instead, it can only select from a predefined “white-listed” list of LTI tools that are available to all users. The management of this list is done in Administration > LTI.

Only accounts with the “Add Own LTI Provider Settings” permission can add individually configured LTI Consumers for specific tools. These are referred to as “Providers Defined by Users”. Accounts with the additional “Release Objects” permission can approve a User-Defined Provider as a Global Provider, thereby adding it to the white list. Both permissions are managed in the permission settings of Administration > LTI.

For improved privacy and enhanced data security, it is highly recommended to use LTI Advantage, as it offers superior protection of personal data compared to LTI 1.1, and to use pseudonymization in the settings of the platform or consumer.

### Integrated Components

The LTI component employs the following services, please consult the respective PRIVACY.md files:
- LearningProgress
- User
- [xAPI](../CmiXapi/PRIVACY.md)

### Data being stored by ILIAS as LTI Consumer

For each tool, you must specify whether the “Provider supports Outcome Service” setting is enabled. If the Outcome Service is activated, a Default Mastery Score is set as the threshold for completing the tool resource. This default value can be adjusted individually for each LTI Consumer in the repository. The Mastery Score is used to determine the Learning Progress of the user.

In Administration > LTI, the privacy-related settings can be configured.
- The “User Identification” setting determines which user information is transmitted to the tool. Options include User-ID, Email Address, or a Hash Value. It is highly advisable to hash “User Identification” data or use a format like Random-ID@ILIAS-Platform-ID.ilias to enhance privacy.
- To ensure that course or group administrator accounts are properly identified and granted the appropriate permissions by the LTI Provider, the “Instructor E-Mail” is transmitted.
- In the “User Name” setting, you can define which user information is included in the data sent to the LTI Provider. It is recommended to select “No one” to maximize privacy.
- If accounts learning with an LTI resource are supposed to identify the account teaching them, the “Instructor Name” must be transmitted to the LTI Provider.
- It is advised to keep “Send User Picture” deactivated, as enabling this option will transmit profile pictures to the LTI Provider.
- A privacy statement warning can be displayed in the Info Tab by enabling the “External Provider” option.
- If “Provider supports Outcome Service” is activated, Learning Progress can be enabled in the settings of the LTI Consumer object in the repository.
- For LTI Advantage tools, the “Assignment and Grade Services” can be activated. A tool then posts scores to ILIAS: for each user, ILIAS stores every score it receives (score given, maximum score, activity progress, grading progress, the time the tool gave and the time it was received) and the resulting result of the user, which sets the Learning Progress. A tool can read back these results, identified by the user id it knows from the launches. A tool can also create line items of its own in the course or group of its objects; they hold no personal data, only the scores posted to them.
- For LTI Advantage tools, the Names and Role Provisioning Services can be activated (“Names and Role Provisioning Services”). A tool then can read the members of the course or group an object of it is in, with their roles (instructor or learner) and their status (active or inactive). Each member is described as a launch of the object describes the user, under the same privacy settings: user id, name and email only as far as “User identification” and “User name” allow. With a random user id per object, only the members who launched the object are given to the tool. ILIAS does not store this list; it is sent to the tool on request.
- An LTI Advantage tool can be registered through Dynamic Registration. ILIAS and the tool exchange their configuration (URLs, keys, client id, the scopes and messages the tool uses); no personal data is part of it. ILIAS stores the tool as an own tool of the user who registered it, as if that user had entered it.
- When a tool supports Deep Linking, the user who creates an LTI object picks its content in the tool. The request describes the user as a launch does, under the same privacy settings, with the role of an instructor. Each resource link the tool sends back becomes an object with the title, description and URL the tool gave; they hold no personal data.
- If the provider or tool supports xAPI statements, refer to the PRIVACY.md file for xAPI. Typically, xAPI generates and transmits large amounts of behavioral data, often highly personalized. Proper privacy considerations must be taken into account when using xAPI.

### Data being stored by ILIAS as LTI Provider

ILIAS stores a user identification to match the external user with an ILIAS user. The personal data being stored depends on the settings made in the platform or consumer, and from this ILIAS installation it cannot be controlled how that personal data is dealt with.

If the “Global Role assigned to LTI Users” is set to “LTI User”, external users can only access the specified resource in ILIAS.

If the platform offers the Names and Role Provisioning Services, the launch of an instructor or administrator makes ILIAS read the members of the context at the platform. ILIAS creates an account for each active member that has none yet, with the user id, name and email the platform sends, and gives it the roles of the released object as a launch would. Members the platform reports as inactive or deleted lose the roles of the released object; their account stays. The accounts are created even for members who never launch ILIAS.

A platform can register ILIAS through Dynamic Registration with the URL given in Administration > LTI. ILIAS and the platform exchange their configuration (URLs, keys, client id, deployment id, the scopes and messages ILIAS uses); no personal data is part of it. ILIAS asks for the user id, name and email in launches; what the platform actually sends depends on its own settings.

If the platform requests Deep Linking, an instructor or administrator of the platform picks among the ILIAS objects released to that platform. Nobody is logged in to ILIAS and no account is created for it; ILIAS stores nothing about the user. The title, description and launch URL of the picked objects go back to the platform.

If the platform offers the Assignment and Grade Services or the LTI 1.1 Outcome Service, ILIAS sends it the Learning Progress and the score of the users that launched the object.

### Data being logged

The log of the component `lti` names the users of each launch and score by their ILIAS user id and by the user id the platform or tool knows them by. On the Debug level, it also holds the complete LTI messages ILIAS receives and sends, with the name, email and roles of the user as far as the privacy settings of the platform or tool let them through. Tokens, signatures, secrets and cookies are never logged. The Debug level is meant to look into a problem for a limited time.

### Data being presented

- As LTI Consumer, no personal data is displayed in ILIAS unless the Advanced Grading Service is activated for an LTI resource (LTI Advantage). In that case the grading process is documented in detail, including statuses such as:
  - “Initialized Grading Process for Learner”
  - “Grading Progress Pending for Learner”
  - and similar grading-related updates.

  The learner’s full name is displayed in ILIAS, and the data transfer from the LTI tool to ILIAS occurs according to the selected pseudonymization level.
- As LTI Provider, for Learning Progress data please consult the link above. The same applies to user data in Member Service and the like.
- For details on Learning Progress data, please refer to the link above.

### Data being deleted

As LTI Consumer, personal data is only stored in the tool if inappropriate pseudonymization levels were selected (see above). If personal data has been transferred to the tool, it can only be deleted within that tool.

As LTI Provider, for options for deleting personal data please consult the link above for User.

### Data being exported

No personal data can be exported.
