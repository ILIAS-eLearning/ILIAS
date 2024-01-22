# Survey Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Survey component employs the following services, please consult the respective privacy.mds
    - The **Metadata** service contains two branches: LOM and custom metdata. The LOM offers storing person dates like author. Custom metadata do contain user-created metadata sets which may contain personal data, which must be individually checked in the global administration.)
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [AccessControl](../../Services/AccessControl/PRIVACY.md)
    - [Info Screen Service](../../Services/InfoScreen/PRIVACY.md)
    - The **Conditions** service controls preconditions for repository objects. The survey implements a "Finished" condition.

## Configuration

**Global**

The following global survey settings are accessible under **Administration** > **Repository and Objects** > **Survey**

- **Access Codes** Presentation: If activated, access codes in anonymous surveys (setting "Without Names" in survey results privacy settings), which have with access code activated setting "Authentication by Access Code"), will present the access codes in the results. If deactivated the term "Anonymous" will be displayed.
- **List of Participants** for anonymous surveys: This will add a setting **Results** > **Privacy** > **Without Names/Anonymous Survey** > **List of Participants** to the survey settings screen. See survey configuration for further details. If enabled, a minimum number of participants can be set, before the list will appear.

**Survey**

The following settings in surveys configure privacy related actions.

- **Authentication by Access Codes** : Users need to provide an access code when starting the survey, either as input or as part of a personalised link for the survey. This allows participation in 360° for external raters without ILIAS login or participation of other surveys in the ILIAS public section. Please note that access codes do not provide any additional level of anonymisation. If users are logged in and access a survey with code, the code will reference their user id internally.
- **Seperate E-Mail for Each Finished Survey**: Sends mails each time a participants finishes to survey admins including the given answers and first/lastname/login, if activated.
- **Remind Users to Participate**: Participation reminders for participants that did not finish the survey yet.
- **Results** > **Privacy** > **Without Names/Anonymous Survey**: Will remove names from results screen. Please note that internally ILIAS will still assign user IDs to survey passes to keep track of their participant status and store their answers.
- **Results** > **Privacy** > **Without Names/Anonymous Survey** > **List of Participants** : This setting is only available if **List of Participants** for anonymous surveys is activated in the global survey administration. If activated the participants will be listed, if the minimum number has been reached (see global configuration) and the end date of the survey is reached. Participants will be listed with **Firstname**, **Lastname**, **Login** and their finished status.

**Standard Survey**

The following settings in standard surveys configure privacy related actions.

- **Participants' Access to Results**
  - **Participants Cannot Access Results**: Only users having the **Survey Results** permission will have access to the survey results views.
  - **All Registered Users can Access the Results**: Users having the **Read** permission will have access to the surveys results **Overview** and **Details** views, see [README.md](./README.md) chapter **Results Presentation**. However this option will not grant access to the **Per Participant** results view.
  - **All Survey Participants can Access the Results**: All users having **Read** permission that at least started the survey will have access to the **Overview** and **Details** views.

**360° Survey**

The following settings in standard surveys configure privacy related actions.

- **Appraisees Select Own Raters**: This allows appraisees to add internal or external users (per e-mail) as raters for them.
- **Open Feedback**: This allows users having **Read** permission to add themselves as appraisee to the survey.
- **Self-Evaluation**: This allows users to rate themselves.

- **Access To Results For Appraisees**
    - **No access to Results**: Only users having the **Survey Results** permission will have access to the survey results views.
    - **Access to Feedback of Own Raters**:
    - **All Feedbacks**:

## Data being stored

- **Survey Runs**: Each time a user starts a survey, ILIAS will store the user ID, the survey ID, the access code (if being used), the state (finished) and the appraisee id (if type is 360°).
- **Answer Times**: For each survey page, ILIAS will store the access time (page presented) and the leave time (answer being saved) to calculate the working time together with the run ID.
- **Given Answer**: For each answered question ILIAS will store the run ID, question ID, together with the given answer (scale value or text answer).
- **Invitation**: If users are invited to a survey, the survey ID and the user ID will be stored.

## Data being presented

- As long as users work through a survey, the can see their own answers.
- Runs, given answers and working time are presented on survey results screens. Please see chapter **Results Presentation** in the [README.md](./README.md) to see a list of results views. Please see the **Configuration** chapter on this screen to see how configuration controls the access to the different views.

## Data being deleted


## Data being exported

- XML Exports of Surveys do not contain any personal data.
- Results screens provide Spreadsheet exports of the presented data.