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

DB Table: `exc_data`

* Object ID (`exc_data.obj_id`)
* Instruction (`exc_data.instruction`): Deprecated
* Time Stamp (`exc_data.time_stamp`): Deprecated
* Pass Mode (`exc_data.time_stamp`):
* Pass Nr (`exc_data.pass_nr`):
* Mandatory Random Nr (`exc_data.nr_mandatory_random`):
* Show Submissions (`exc_data.show_submissions`):
* Completed by Submission (`exc_data.compl_by_submission`): If set, assignments are completed on submission, otherwise tutors set the completion status.
* Certificate Visibility (`exc_data.certificate_visibility`):
* Tutor Feedback (`exc_data.tfeedback`):


## Assignment

## Assignment Types

## Member

## Team


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

## Submission

## Peer Review

## Criteria Catalog

## File System

### Storage Data Directory

*Sample Solution*
- `ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/0/`
- sample solution file (with original name)

*Evaluation Feedback Files from Tutors*
- `ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/*USER_ID*|t*TEAM_ID*/`
- evaluation/feedback files from tutors for learner *USER_ID* or team *TEAM_ID* (note the leading "t" above)

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
