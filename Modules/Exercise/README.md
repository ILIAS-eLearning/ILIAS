# Exercise

This part of the documentation deals with concepts and business rules, for technical documentation see [README-technical.md](./README-technical.md).


* [Exercise](#exercise)
* [Assignment](#assignment)
* [Team](#team)
* [Assignment Member State](#assignment-member-state)
* [Calendar Appointments](#calendar-appointments)
* [Tasks](#tasks)
* [Use of TinyMCE Editor](#use-of-tinymce-editor)

## Exercise

### Submission Notification

- The setting "Notification on Submissions" is a personal setting for the user saving the settings form. If enabled the user is treated as a tutor by other notifications/reminder features, see [weak tutor in ROADMAP](./ROADMAP.md).

### Submission and Grades

- The presented columns are specified in this feature wiki entry: https://docu.ilias.de/goto_docu_wiki_wpage_4250_1357.html


## Assignment

- If the **Remind Tutors to Grade** is activated, the recipients of the reminders are all users that activated the "Notification on Submissions" option in the exercise settings, see [weak tutor in ROADMAP](./ROADMAP.md).

## Team

- Learners manage team screen will respect the privacy settings of the users (published profile).

## Assignment Phases

Depending on the assignment configuration, participants and tutors can perform different actions. The following business rules define these phases and their possible actions.

* **Absolute Deadlines**: Assignments with absolute deadlines share the same starting time for submissions for all learners (**GS**). Submission end depends on the (absolute) deadline (**DL**) the optional grace period end date (**GPD**) and optional individual deadlines (**ID**). All these dates are absolute.
* **Relative Deadlines**: For assignments with relative deadlines the length of the submission is predefined (**RD**), but the start can be set by the learner individually (**IS**). These two values are used to calculate the end of the submission phase (**CaD**), which may further be modified by an individual (absolute) deadline by the tutor (**ID**).

* **GS**  **General Start**: As entered in settings. For absolute deadlines, this also starts the submission, for relative deadline this allows the user to start the submission period. (0 = immediately)
* **IS**  **Individual Start**: Timestamp when user hits "Start" button for an assignment using a relative deadline.
* **SS**  **Submission Start**: For absolute deadlines this is **GS**, for relative deadlines **IS**.
* **DL**  **Deadline**: The absolute Deadline (e.g. 5.12.2017) as set in settings.
* **RD**  **Relative Deadline**: The relative Deadline (e.g. 10 Days) as set in settings.
* **CaD** **Calculated Deadline**: **IS** + **RD**.
* **ID**  **Individual Deadline**: Individual deadline per learner as set by tutor in "Submissions and Grade" screen. It can only be set if either a concrete **DL** or **CaD** is given. It can't be set, if the assignment is a team assignment and the user does not have a team yet. It can't be set if **PP** has started and peer review groups have been builded.
* **CoD** **Common Deadline**: **DL** (absolute deadline) or **CaD** (relative deadline) used for "Ended on" or "Edit Until" presentation.
* **OD**  **Official Deadline**: Maximum of [**DL**, **ID**] (absolute deadline) or [**CaD**, **ID**] (relative deadline).
* **GPD** **Grace Period End Date**: As being set in the settings of assignmet by tutor.
* **ED**  **Effective Deadline**: Maximum of [**OD**, **GPD**].
* **GP**  **Grace Period**: Period between **OD** and **GPD**.
* **SP**  **Submission Period**: From **SS** (if not given immediately) to Maximum of [**OD**, **GPD**].
* **LS**  **Late Submission Period**: Submissions being handed in during **GP**.
* **PS**  **Peer Review Start**: Maximum of [**OD** OF ALL USERS, **GPD**].
* **PD**  **Peer Review Deadline**: As being set in the settings of assignmet by tutor.
* **PP**  **Peer Review Period**: From **PS** to **PD** (may be infinite, if no deadline given).

- If submissions of participants are published, a participant can access submissions of others after **ED**. https://mantis.ilias.de/view.php?id=25606
- **ID** Presentation (Learner): **DL** and **ID** are both listed. No special byline is presented if submission is done after **DL**.
- **ID** Presentation (Tutor): Submission date and **ID** are both listed on the *Submissions and Grades* screen normally. No special byline is presented.
- **GP** Presentation (Learner): Only **DL** is presented (not **GPD**), but handing-in is still possible. Additionally a message states that submissions are marked as "late".
- **GP** Presentation (Tutor): Submission dates are marked as "late".

## Calendar Appointments

Calendar appointments are created for the **Official Deadline** and the **Peer Review Deadline**.

- Calendar appointments are stored in an exercise calendar which is part of the aggregation of an upper course calendar: Appointmens will be visible if a user is member in an upper course (or group) of the exercise.

## Tasks

Tasks are created for handing-in and for providing a peer feedback..

- Task for handing-in will only be visible during **Submission Period**. Since ILIAS 8 these tasks will not be presented anymore, if the user has submitted anything.
- Task for peer-review will only be visible during **Peer Review Period**.


## Use of TinyMCE Editor

Currently the text submissions support the use of the TinyMCE editor with full configuration in the administration. The instructions use a non-configurable version with minimal format support.