# Exercise Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Exercise component employs the following services, please consult the respective privacy.mds
    - The **Learning Progress** service manages data on access time specifically last time, number of accesses and the progress status specifically in progress, completed for each user accessing the object.
    - The **Metadata** service contains two branches: LOM and custom metdata. The LOM offers storing person dates like author. Custom metadata do contain user-created metadata sets which may contain personal data, which must be individually checked in the global administration.)
    - The **Object** service stores the account which created the
      object as it's owner and creation and update timestamps for the
      object.
    - [AccessControl](../../Services/AccessControl/PRIVACY.md)
    - [Info Screen Service](../../Services/InfoScreen/PRIVACY.md)
    - Dedicated assignment types allow to submit exported ILIAS objects as zip files. These objects are **Portfolios**, **Blogs**, **Wikis**.
    - Evaluation statements and notifications can be sent using the **Mail** service.

## Configuration

**Global**

  - The exercise module does not provide any global configuration.

**Exercise**

  - **Publish Submissions after Deadline**: This setting controls, if submissions are revealed to other learners.
  - **E-Mail Notification on Submissions**: This settings controls, if notification e-mails (including account and timestamp information) are being sent to tutors.
  - **Evaluation by Tutor**: This sections configures how evaluation information is sent to the learner.

**Assignment**

  - **Peer-Feedback**: This settings controls whether learner can rate/evaluate submission of other learners. The setting **Personalized Peer-Feedback** controls if full names are presented to the peers.

## Data being stored

**Exercise**
- Exercises with a **Pass Mode** set to **Random Selection** will store the random assignemnts for each learner using **user ID** and **assignment ID**.
- The overall status of a participant is stored including **user ID** and **grading status**.

**Assignments**
- The status of a participant for each assignment is stored including **user ID**, **submission status**, **submission timestamp**, **tutor feedback**, **feedback timestamp**, **grading status**, **mark**, **internal note of tutors**
- A tutor may assign an individual submission deadline to users. This stores the **user ID** together with the **deadline timestamp**.
- Assignments with a **relative deadline** will store the **starting timestamp** together with the **user ID** for each learner who started the assignment.

**Team Assignments**
- Some assignemnt types are team assignments. For each team member the **team ID** and the **user ID** is stored.
- A team log stores **actions** on teams (create, add/remove member, file submission, file deletion) including the **user ID** of the user who performed the action, the **timestamp** and the **user** who was added/removed.

**Submissions**
- Each submission stores the **submitted file or text**, **user ID**, **timestamp**
- Late submissions are tagged with a **late flag**.
- **Blog**, **Portfolio** and **Wiki submissions** store the XML and HTML export version of these objects as a zip package.
- If a tutor accesses a Blog, Portfolio or Wiki submission directly, a **timestamp** for the **latest unpackaging** is stored to prevent multiple redundant subsequent unzip actions. However this timestamp is not attached to the tutor user ID, but to the submission itself. _Reason_: Reduce server load.
- For each tutor a **timestamp of the latest download** of a submission is stored. This allows a tutor to download only new submissions in subsequent downloads.

**Peer Reviews**
- Each pair of a review **giver and taker** is stored with **user IDs**.
- The rating, review text or review file are stored together with a **timestamp** when the peer review has been created/updated.

## Data being presented

**Learner Presentation** (Read Permission)
- **personal submission**, incl. **date/time**
- **team submissions**
- team members incl. the **account name/login**
- **first and last** name will only be presented, if the other learner has set the **profile** to **public**
- **team log** data including action, performing user, timestamp and user being added/removed. _Reason_: This data should help team members to self organise their teams.
- **evaluation statements**, **marks** and **gradings** from tutors
- **submission of other learners** including **name** and **timestamp** of last submission (see Exercise configuration)
- **Submissions** that should be peer reviewed, **optionally** including the **name** (see Assignment configuration)
- **Received peer feebdack**, **optionally** including the **name** (see Assignment configuration)

**Tutor Presentation** (Edit Submission and Grades Permission)
- All **participating learners** incl. **first and last name**, user **image** (if public), **grading**, **mark**, 
- All learner **submissions** incl. **timestamp** of submission
- **Individual Deadlines** of learners
- **Tutor notes** attached to learners
- **Evaluation** texts and timestamp of evaluation
- Given and received **peer feedback** of each learner (incl. **names of feedback giver and taker**)
- **Team assignments** and **team logs**

## Data being deleted

- If a **user is removed from an exercise** by a tutor
  - all submission files of the user are deleted
  - all team assignments of the user are deleted
- If a **user deletes a submission file**
  - the file is deleted
  - timestamp information on the submission is deleted


## Data being exported

- XML Exports of Exercises do not contain any personal data.
- Learners can download their submissions as long as they have access to the exercise assignments.
