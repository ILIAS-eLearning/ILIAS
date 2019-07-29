

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

* **Absolute Deadlines**: GS <-> DL - GPD - ID
* **Relative Deadlines**: GS - IS <-> CaD - ID

* **GS**  **General Start**: As entered in settings. For absolute deadlines, this also starts the submission, for relative deadline this allows the user to start the submission period. (0 = immediately)
* **IS**  **Individual Start**: TS when user hits "Start" button for an assignment using a relative deadline
* **SS**  **Submission Start**: For absolute deadlines this is General Start, for relative deadlines Individual Start
* **DL**  **Deadline**: absolute Deadline (e.g. 5.12.2017) as set in settings
* **RD**  **Relative Deadline**: relative Deadline (e.g. 10 Days) as set in settings
* **CaD** **Calculated Deadline**: Starting Timestamp + Relative Deadline
* **ID**  **Individual Deadline**: Set by tutor in "Submissions and Grade" screen
* **CoD** **Common Deadline**: Deadline or Calculated Deadlind used for "Ended on" or "Edit Until" presentation
* **OD**  **Official Deadline**: Max of (Deadline and Individual Deadline) or (Calculated Deadline and Individual Deadline)
* **ED**  **Effective Deadline**: Max of official deadline and grace period end da
* **GPD** **Grace Period End Date**: As being set in the settings of assignmet by tutor
* **GP**  **Grace Period**: Period between Official Deadline and Grace Period End Date.
* **SP**  **Submission Period**: From Submission Start (if not given immediately) to Max of (Official Deadline and Grace Period End Date)
* **LS**  **Late Submission Period**: Submissions being handed in during Grace Period
* **PS**  **Peer Review Start**: Max of (Official Deadline OF ALL USERS and Grace Period End Date)
* **PD**  **Peer Review Deadline**: As being set in the settings of assignmet by tutor
* **PP**  **Peer Review Period**: From Peer Feedback Start to Peer Feedback Deadline (may be infinite, if no deadline given)

### Business Rules
- If submissions of participants are published, a participant can access submissions of others after **ED**. https://mantis.ilias.de/view.php?id=25606

## Submission

## Peer Review

## Criteria Catalog