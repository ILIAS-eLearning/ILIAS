# Survey Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## Integrated Services

- The Survey component employs the following services, please consult the respective privacy.mds
    - The [Metadata](../Metadata/PRIVACY.md) component contains two branches: LOM and custom metdata. The LOM offers storing person dates like author. Custom metadata do contain user-created metadata sets which may contain personal data, which must be individually checked in the global administration.)
    - The **ILIASObject** component stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [AccessControl](../AccessControl/PRIVACY.md)
    - [InfoScreen](../InfoScreen/PRIVACY.md)
    - [Skill](../Skill/PRIVACY.md)
    - The **Conditions** component controls preconditions for repository objects. The survey implements a "Finished" condition.

## Configuration

**Global**

The following global survey settings are accessible under **Administration** > **Repository and Objects** > **Survey**

- **Access Codes** Presentation: If activated, access codes in anonymous surveys (setting "Without Names" in survey results privacy settings), which have with access code activated setting "Authentication by Access Code"), will present the access codes in the results. If deactivated the term "Anonymous" will be displayed.
- **List of Participants** for anonymous surveys: This will add a setting **Results** > **Privacy** > **Without Names/Anonymous Survey** > **List of Participants** to the survey settings screen. See survey configuration for further details. If enabled, a minimum number of participants can be set, before the list will appear.

**Survey**

The following settings in surveys configure privacy related actions.

- **Authentication by Access Codes** : Users need to provide an access code when starting the survey, either as input or as part of a personalised link for the survey. This allows participation in 360° for external raters without ILIAS login or participation of other surveys in the ILIAS public section. Please note that access codes do not provide any additional level of anonymisation. If users are logged in and access a survey with code, the code will reference their user id internally.
- **Seperate E-Mail for Each Finished Survey**: Sends a mail to the configured recipients each time a participant finishes. The mail includes the given answers and, unless the results are anonymised, the participant's first name, last name and login. For 360° surveys it also includes the appraisee's name.
- **Remind Users to Participate**: Participation reminders for participants that did not finish the survey yet.
- **Participants can View Own Answers**: A separate presentation view of given answers is activated after finishing a survey.
- **Results** > **Privacy** > **Without Names/Anonymous Survey**: Will remove names from results screen. Please note that internally ILIAS will still assign user IDs to survey passes to keep track of their participant status and store their answers.
- **Results** > **Privacy** > **Without Names/Anonymous Survey** > **List of Participants** : This setting is only available if **List of Participants** for anonymous surveys is activated in the global survey administration. If activated the participants will be listed, if the minimum number has been reached (see global configuration) and the end date of the survey is reached. Participants will be listed with **Firstname**, **Lastname**, **Login** and their finished status.

**Standard Survey**

The following settings in standard surveys configure privacy related actions.

- **Participants' Access to Results**
  - **Participants Cannot Access Results**: Only users having the **Survey Results** permission will have access to the survey results views.
  - **All Registered Users can Access the Results**: Users having the **Read** permission will have access to the surveys results **Overview** and **Details** views, see [README.md](./README.md) chapter **Results Presentation**. However this option will not grant access to the **Per Participant** results view.
  - **All Survey Participants can Access the Results**: All users having **Read** permission that at least started the survey will have access to the **Overview** and **Details** views.

**360° Survey**

The following settings in 360° surveys configure privacy related actions.

- **Appraisees Select Own Raters**: This allows appraisees to add internal or external users (per e-mail) as raters for them.
- **Open Feedback**: This allows users having **Read** permission to add themselves as appraisee to the survey.
- **Self-Evaluation**: This allows users to rate themselves.

- **Access To Results For Appraisees**
    - **No access to Results**: Only users having the **Survey Results** permission will have access to the survey results views.
    - **Access to Feedback of Own Raters**: The result views **Competences**, **Overview** and **Details** are accessible for appraisees, but will only show the data related to the answers given by their own raters. This option will not grant access to the **Per Participant** results view.
    - **All Feedbacks**: The result views **Competences**, **Overview** and **Details** are accessible for appraisees. They will be able to select all other appraisees as well to see their data. This option will not grant access to the **Per Participant** results view.

**Self Evaluation**

The following settings in self evaluation surveys configure privacy related actions.

- **Participants' Access to Results**
  - **No access to Results**: Only users having the **Survey Results** permission will have access to the survey results views.
  - **Access to Own Self-Evaluations**: The result views **Competences**, **Overview** and **Details** are accessible for participants, but will only show the data related to the answers given by themselves. This option will not grant access to the **Per Participant** results view.
  - **Access to Self-Evaluations of All Participants**: The result views **Competences**, **Overview** and **Details** are accessible for participants. The **Overview** and **Details** will show the data of all users. This option will not grant access to the **Per Participant** results view.

**Individual Feedback**

The following settings in individual feedback surveys configure privacy related actions.

- **Appraisees Select Own Raters**: This allows appraisees to add internal or external users (per e-mail) as raters for them.
- **Open Feedback**: This allows users having **Read** permission to add themselves as appraisee to the survey.

- **Access To Results For Appraisees**
  - **No access to Results**: Only users having the **Survey Results** permission will have access to the survey results views.
  - **Access to Feedback of Own Raters**: The result views **Competences** and **Details** are accessible for participants. Only data related to themselves will be presented. They will see the first/lastnames of the raters attached to their answers.


## Data being stored

- **Survey Runs**: Each time a user starts a survey, ILIAS will store the user ID, the survey ID, the access code (if being used), the state (finished) and the appraisee id (if type is 360°).
- **Answer Times**: For each survey page, ILIAS will store the access time (page presented) and the leave time (answer being saved) to calculate the working time together with the run ID.
- **Given Answer**: For each answered question ILIAS will store the run ID, question ID, together with the given answer (scale value or text answer).
- **Invitation**: If users are invited to a survey, the survey ID and the user ID will be stored.
- **Access Codes**: For each access code, ILIAS stores the survey ID, the code, its creation timestamp and whether an invitation mail was sent. If a registered user uses a code, a pseudonymous key derived from the user ID is stored with the code. For external recipients, ILIAS additionally stores the e-mail address, first name and last name provided for the invitation.
- **360° Survey**: For 360° surveys, ILIAS stores the user IDs of appraisees and raters, their relationship, a reference to an external rater's access code where applicable, whether an appraisee has closed the feedback process, and whether a rater notification was sent.

## Data being presented

- As long as users work through a survey, the can see their own answers.
- Runs, given answers and working time are presented on survey results screens. Please see chapter **Results Presentation** in the [README.md](./README.md) to see a list of results views. Please see the **Configuration** chapter on this screen to see how configuration controls the access to the different views.
- The access-code administration displays the access code, personalised survey URL, creation date, usage and mail status. For external recipients it also displays their e-mail address, first name and last name.
- Completion notifications include the participant's answers and, in 360° surveys, the appraisee's name. Access-code invitations include a personalised survey URL containing the access code and can include recipient data in the message.

## Data being deleted

- **Deleting participant data**: The action to delete all participant data removes survey runs, given answers, answer times and invitations. Access codes are not removed by this action.
- **Deleting selected results**: Deleting individual participant results removes the corresponding survey run, given answers, answer times and, for registered users, the invitation and related learning-progress data.
- **Deleting access codes**: Access codes can be deleted individually. All access codes, including external recipient data, are deleted when the survey object is deleted.
- **360° Survey Relationships**: Appraisee and rater relationships are deleted when the respective appraisee or rater is removed. Deleting participant data or the survey object does not delete these relationship records.
- **Deleting the survey object**: Deleting a survey object deletes its participant data and access codes. No automatic retention period is configured by the Survey component.

## Data being exported

- XML Exports of Surveys do not contain any personal data.
- Results screens provide Spreadsheet exports of the presented data.
- **Access Code Export**: The access-code administration provides a CSV export. For external recipients it includes the access code, personalised survey URL containing the code, e-mail address, first name, last name, creation date, and sent and usage status.
