# LTI Privacy

> Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via the [ILIAS issue tracker](https://mantis.ilias.de) or submit a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

### General information

This component covers both sides of the LTI integration.

As **LTI Consumer**, ILIAS consumes learning content from external tools. Users can switch between these tools seamlessly without re-authentication, and the learning progress achieved in them can be transferred back to ILIAS. A tool can be another LMS, virtual classroom, plagiarism checker, or similar service. A list of supported tools is available at: [IMS Global LTI Platforms](https://www.imsglobal.org/all-learning-tools-interoperability-lti-platforms).

As **LTI Provider**, ILIAS provides learning content to external platforms and consumers. ILIAS acts as Provider in LTI 1.1 and as Tool in LTI Advantage. Users can switch from the other LMS to ILIAS without re-authentication, and the learning progress achieved in ILIAS can be transferred back to the platform.

An account with the “Create LTI Consumer” permission cannot add its own LTI Consumers, to prevent the uncontrolled transfer of personal data. Instead, it can only select from a predefined “white-listed” list of LTI tools that are available to all users. The management of this list is done in Administration > LTI.

Only accounts with the “Add Own LTI Provider Settings” permission can add individually configured LTI Consumers for specific tools. These are referred to as “Providers Defined by Users”. Accounts with the additional “Release Objects” permission can approve a User-Defined Provider as a Global Provider, thereby adding it to the white list. Both permissions are managed in the permission settings of Administration > LTI.

For improved privacy and enhanced data security, it is highly recommended to use LTI Advantage, as it offers superior protection of personal data compared to LTI 1.1, and to use pseudonymisation in the settings of the platform or consumer.

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
- For LTI Advantage tools, Advanced Grading Services can be activated.
- If the provider or tool supports xAPI statements, refer to the PRIVACY.md file for xAPI. Typically, xAPI generates and transmits large amounts of behavioral data, often highly personalized. Proper privacy considerations must be taken into account when using xAPI.

### Data being stored by ILIAS as LTI Provider

ILIAS stores a user identification to match the external user with an ILIAS user. The personal data being stored depends on the settings made in the platform or consumer, and from this ILIAS installation it cannot be controlled how that personal data is dealt with.

If the “Global Role assigned to LTI Users” is set to “LTI User”, external users can only access the specified resource in ILIAS.

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
