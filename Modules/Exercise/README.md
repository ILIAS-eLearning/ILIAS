# Exercise

* [API](#api)
* [General Documentation](#general-documentation)


# API

## Repository Objects as Assignments

#### Step 1 [WIP]

- Provide an implementation of `ilExAssignmentTypeInterface` under Exercise/AssignmentTypes/classes (this should be decentralized in the future)
- Provide an implenetation of ilExAssignmentTypeGUIInterface under Exercise/AssignmentTypes/GUI/classes

In both cases you need to add your classes to the factory methods in `ilExAssignmentTypes` and `ilExAssignmentTypesGUI`.

#### Step 2 [WIP]

In your repository object use...


# General Documentation

This section documents the general concepts and structures of the Exercise Module. These are internal implementations which SHOULD not be used outside of this module unless mentioned in the API section of this README.

* [Exercise](#exercise)
* [Assignment](#assignment)
* [Assignment Types](#assignment-types)
* [Member](#member)
* [Member Status](#member-status)
* [Team](#team)
* [Assignment Member State](#assignment-member-state)
* [Submission](#submission)
* [Peer Review](#peer-review)
* [Criteria Catalog](#criteria-catalog)
* [Calendar Appointments](#calendar-appointments)
* [Tasks](#tasks)
* [File System](#file-system)


## Exercise

## Assignment

## Assignment Types

## Member

## Team

### Business Rules

- Learners manage team screen will respect the privacy settings of the users (published profile).

## Assignment Member Status

Manages a number of properties that are attached to members during an assignment. The main source for this data are the "Submission and Grades" screens.

* DB Table: `exc_mem_ass_status`

* Note for tutors (`exc_mem_ass_status.notice`): A note for other tutors visible in the "Submission and Grades" view only.
* Returned Flag:
* Solved Flag:
* Grade (`exc_mem_ass_status.status`): "Not graded", "passed" or "failed".
* Status Time:
* Sent Flag:
* Sent Time:
* Feedback Flag:
* Feedback Time:
* Mark (`exc_mem_ass_status.mark`): A textual mark entered by the tutor and presented to the learner as part of the detailed assignment presentation.
* Evaluation Statement (`exc_mem_ass_status.u_comment`): Statement wich is entered by the tutor and presented to the learner as part of the detailed assignment presentation.

## Assignment Member State

* Code: `Modules/Exercise/AssMemberState`

Handles everything about the state (current phase) of a user in an assignment using assignment, individual deadline, user and team information.

* **Absolute Deadlines**: Assignments with absolute deadlines share the same starting time for submissions for all learners (**GS**). Submission end depends on the (absolute) deadline (**DL**) the optional grace period end date (**GPD**) and optional individual deadlines (**ID**). All these dates are absolute.
* **Relative Deadlines**: For assignments with relative deadlines the length of the submission is predefined (**RD**), but the start can be set by the learner individually (**IS**). These two values are used to calculate the end of the submission phase (**CaD**), which may further be modified by an individual (absolute) deadline by the tutor (**ID**).

* **GS**  **General Start**: As entered in settings. For absolute deadlines, this also starts the submission, for relative deadline this allows the user to start the submission period. (0 = immediately)
* **IS**  **Individual Start**: Timestamp when user hits "Start" button for an assignment using a relative deadline.
* **SS**  **Submission Start**: For absolute deadlines this is **GS**, for relative deadlines **IS**.
* **DL**  **Deadline**: The absolute Deadline (e.g. 5.12.2017) as set in settings.
* **RD**  **Relative Deadline**: The relative Deadline (e.g. 10 Days) as set in settings.
* **CaD** **Calculated Deadline**: **IS** + **RD**.
* **ID**  **Individual Deadline**: Individual deadline per learner as set by tutor in "Submissions and Grade" screen.
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

### Business Rules
- If submissions of participants are published, a participant can access submissions of others after **ED**. https://mantis.ilias.de/view.php?id=25606
- **ID** Presentation (Learner): **DL** and **ID** are both listed. No special byline is presented if submission is done after **DL**.
- **ID** Presentation (Tutor): Submission date and **ID** are both listed on the *Submissions and Grades* screen normally. No special byline is presented.
- **GP** Presentation (Learner): Only **DL** is presented (not **GPD**), but handing-in is still possible. Additionally a message states that submissions are marked as "late".
- **GP** Presentation (Tutor): Submission dates are marked as "late".


## Submission

## Peer Review

## Criteria Catalog

## Calendar Appointments

Calendar appointments are created for the **Official Deadline** and the **Peer Review Deadline**.

### Business Rules

- Calendar appointments are stored in an exercise calendar which is part of the aggregation of an upper course calendar: Appointmens will be visible if a user is member in an upper course (or group) of the exercise.

## Tasks

Tasks are created for handing-in and for providing a peer feedback..

### Business Rules

- Task for handing-in will only be visible during **Submission Period**.
- Task for peer-review will only be visible during **Peer Review Period**.

## File System

### Storage Data Directory

*Sample Solution*
- `ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/0/`
- sample solution file (with original name)

*Evaluation Feedback Files from Tutors*
- `ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/*USER_ID*/`
- evaluation/feedback files from tutors for learner *USER_ID*

*File Submissions*
- `ilExercise/X/exc_*EXC_ID*/subm_*ASS_ID*/*USER_ID*/*TIMESTAMP*_filename.pdf`
- file submissions (also blogs and porfilios, filename = obj_id)

*Peer Feedback Files*
- `ilExercise/X/exc_*EXC_ID*/peer_up_*ASS_ID*/*TAKER_ID*/*GIVER_ID*/*CRIT_ID*/`
- peer feedback file (original name)

*Multi-Feedback Zip File Structure*
- `ilExercise/X/exc_*EXC_ID*/mfb_up_*ASS_ID*/*UPLOADER_ID*/`
- multi-feedback zip file/structure from tutor *UPLOADER_ID*

*Download All Assignemtns Processing*
- `ilExercise/X/exc_*EXC_ID*/tmp_*ASS_ID*/`
- temp dir for "download all assignments" process (creates random subdir before starting)

### Web Data Directory

- `ilExercise/X/exc_*EXC_ID*/ass_*ASS_ID*/`
- directory holds all instruction files (with original names)


# Other Specs

## Use of TinyMCE Editor

Currently the text submissions support the use of the TinyMCE editor with full configuration in the administration. The instructions use a non-configurable version with minimal format support.