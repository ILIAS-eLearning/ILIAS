# LTI Consumer Privacy

> Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via the [ILIAS issue tracker](https://mantis.ilias.de) or submit a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

### General information

This component allows to consume learning content from external tools into ILIAS. Users can switch between these tools seamlessly without re-authentication. Additionally, learning progress achieved in these external tools can be transferred back to ILIAS.
A tool can be another LMS, virtual classroom, plagiarism checker, or similar service. A list of supported tools is available at: [IMS Global LTI Platforms](https://www.imsglobal.org/all-learning-tools-interoperability-lti-platforms).

An account with the “Create LTI Consumer” permission cannot add its own LTI Consumers to prevent the uncontrolled transfer of personal data. Instead, it can only select from a predefined “white-listed” list of LTI tools that are available to all users. When adding an LTI Consumer to the repository, such an account can only choose from this white list. The management of this list is done in the administration section under:
Administration > Extending ILIAS > LTI > ILIAS as LTI Consumer > Global Providers/Tools for all Users.

Only accounts with the “Add Own LTI Provider Settings” permission can add individually configured LTI Consumers for specific tools. These are referred to as “Providers Defined by Users”. When such accounts add an LTI consumer to the repository, they are offered Option 2 and Option 3. The “Add Own LTI Provider Settings” permission is managed under:
Administration > Extending ILIAS > LTI > Permissions.

Accounts with the additional “Release Objects” permission can approve a User-Defined Provider as a Global Provider, thereby adding it to the white list. The “Release Objects” permission is managed under: Administration > Extending ILIAS > LTI > Permissions.

For improved privacy and enhanced data security, it is highly recommended to use LTI 1.3, as it offers superior protection of personal data compared to previous versions.

### Integrated Components

The LTI component employs the following services, please consult the respective privacy.md files:
- LearningProgress
- User
- [xAPI](../../ILIAS/CmiXapi/PRIVACY.md)

### Data being stored

For each Tool, you must specify whether the “Provider supports Outcome Service” setting is enabled. This option can be found under:
Administration > Extending ILIAS > LTI > ILIAS as LTI Consumer. If the Outcome Service is activated, a Default Mastery Score is set as the threshold for completing the Tool resource. This default value can be adjusted individually for each LTI Consumer in the Repository. The Mastery Score is used to determine the Learning Progress of the User.

In Administration > Extending ILIAS > LTI > ILIAS as LTI Consumer, the privacy-related settings can be configured.
- The “User Identification” setting determines which User information is transmitted to the tool. Options include User-ID, Email Address, or a Hash Value. It is highly advisable to hash "User Identification" data or use a format like:
Random-ID@ILIAS-Platform-ID.ilias to enhance privacy.
- To ensure that course or group administrator accounts are properly identified and granted the appropriate permissions by the LTI Provider, the “Instructor E-Mail” is transmitted.
- In the “User Name” setting, you can define which user information is included in the data sent to the LTI Provider. It is recommended to select “No one” to maximize privacy.
- If accounts learning with an LTI resource are supposed to identify the account teaching them, the "Instructor Name" must be transmitted to the LTI Provider. 
- It is advised to keep “Send User Picture” deactivated, as enabling this option will transmit profile pictures to the LTI Provider.
- A privacy statement warning can be displayed in the Info Tab by enabling the “External Provider” option.
- If “Provider supports Outcome Service” is activated, Learning Progress can be enabled in the Settings of the LTI Consumer object in the repository.
- For LTI 1.3 Tools, Advanced Grading Services can be activated.
- If the Provider/Tool supports xAPI statements, refer to the privacy.md file for xAPI. Typically, xAPI generates and transmits large amounts of behavioral data, often highly personalized. Proper privacy considerations must be taken into account when using xAPI.

### Data being presented
- No personal data is displayed in ILIAS unless the Advanced Grading Service is activated for an LTI Resource (1.3).
- For details on Learning Progress data, please refer to the link above.
- If the Advanced Grading Service is activated in an LTI Resource (1.3), the grading process is documented in detail. This includes statuses such as:
  - “Initialized Grading Process for Learner”
  - “Grading Progress Pending for Learner”
  - and similar grading-related updates.

  The learner’s full name is displayed in ILIAS, and the data transfer from the LTI Tool to ILIAS occurs according to the selected pseudonymization level.

### Data being deleted
Personal data is only stored in the tool if inappropriate pseudonymization levels were selected (see above). If personal data has been transferred to the tool, it can only be deleted within that tool.

### Data being exported
No personal data can be exported.
