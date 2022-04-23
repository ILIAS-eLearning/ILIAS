## TestQuestionPool Privacy
Insert caveat: *This documentation does not warrant completeness or correctness. 
Please report any missing or wrong information using the ILIAS issue tracker.* 

### Note:
The module TestQuestionPool and the module Test are still tied together in most intricate ways. The primary 
component of concern in regards to privacy related evaluations is the Test. As the lines between these components
are blurred - which makes them subject for refactoring, too - it is advised to never look at only one of the components
but always at both.

## Data being stored

- **Hint Tracking of Questions by User**:
  Keyed via the "active_id", the information about users who have received "hints" during answering a question, is tracked.
  This information is revealed in solution views in the Test module to the users themselves as well as users with administrative permissions on the objects and those users who have permission to work on manual scoring and corrections.
  The data is required for keeping track of and adjusting scoring in question-usage.

- **Authorship of Questions**:
  Authors of questions are stored in the TestQuestionPool as reference to the users id. 
  The data is required for copyright purposes as well as to enable communication with the author.

- **Ownership of Questions**:
  Owners of questions are stored in the TestQuestionPool as reference to the users id. 
  The data is required to manage detailed access and permissions on usage and editing of the question.
  
- The TestQuestions Pool component employs the following services, please consult the respective privacy.mds: [Skill](../../Services/Skill/PRIVACY.md), [Metadata](../../Services/MetaData/Privacy.md), [AccessControl](../../Services/AccessControl/PRIVACY.md)


## Data being presented 
- **Authorship of Questions**: 
The authors of questions are revealed in editing forms of questions as well as tabular 
overviews to accounts with the edit permission to the test question pool object.
- **Ownership of Questions**:
The owners of questions are revealed in editing forms of questions as well as tabular 
- overviews to accounts with the edit permission to the test question pool object.

## Data being deleted 
- **Hint Tracking of Questions by User**:
    The storage of this information is tied to the use of the question in tests and will be deleted together with data
    pertaining test-participation in the object the data were they were gathered.
- **Authorship of Questions**: 
The storage of this information is tied to the lifecycle of the question it is attached
to and so the deletion happens in the removal of a question by a user account with edit permissions to the question 
pool the questions resides in.
- **Ownership of Questions**:
The storage of this information is tied to the lifecycle of the question it is attached
  to and so the deletion happens in the removal of a question by a user account with edit permissions to the question
  pool the questions resides in.


## Data being exported 
- **Hint Tracking of Questions by User**:
  The hint tracking data are included in exports of the test object where the information was gathered. The information
is made available in result detail exports and archive-exports for long-term storage and can be triggered by accounts
with edit permissions on the test object.
- **Authorship of Questions**:
  Authorship of questions is exported with the questions. In the test question pool, this is the case when questions or
the pool as a whole is exported by account with edit permissions on the test question pool object.
In the test object, questions can be exported by accounts with edit permission in the context of the test.
- **Ownership of Questions**:
 Ownership of questions is exported with the questions. In the test question pool, this is the case when questions or
  the pool as a whole is exported by account with edit permissions on the test question pool object.
  In the test object, questions can be exported by accounts with edit permission in the context of the test.
