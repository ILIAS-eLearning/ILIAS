

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
* [Team](#team)
* [Assignment Member State](#assignment-member-state)
* [Submission](#submission)
* [Peer Review](#peer-review)
* [Criteria Catalog](#criteria-catalog)


## Exercise

## Assignment

## Assignment Types

## Member

## Team


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

## Submission

## Peer Review

## Criteria Catalog