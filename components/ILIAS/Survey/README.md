# Survey and Survey Question Pool

This part of the documentation deals with concepts and business rules, for technical documentation see [README-technical.md](./README-technical.md).


## General Concepts

* A **Survey Question Pool** enables authoring of questions. Questions of pools can be reused in Surveys.
* A **Question** has a certain **Question Type**. Current Types are: Singel Choice, Multiple Choice, Metric, Matrix and Test.
* A **Question Category** is a general answer option of a question.
* A **Variable** is an answer option of a concrete question (referencing a category and a question).
* A **Survey** contains a number of **Survey Questions** that are asked to participants.
* A **Question Block** groups multiple questions on one page. 
* A **Constraint** uses a relation and scale value (e.g. >1) to define a conditional presentation of questions.
* A **Question Constraint** imposes a constraint on a question of the survey.
* A **Survey Run** represents the current state of a user progress during a survey (access to pages, finished state)
* An **Answer** represents an answer given by a user during a Survey Run.
* An **Invitation** invites a user to participate in a survey by adding a task to his/her task list, see https://docu.ilias.de/goto_docu_wiki_wpage_6098_1357.html

## Editing Answer Options

- The plus sign adds a new entry after the row where the plus sign is clicked.
- If you click the plus sign, existing rows and their scale value will not be changed.
- Adding a new row will auto-enter the next free scale value.


## Question Answers

* If we do not save any answer and press "back to de Survey" or we leave this page without save. In the "svy_question" we have the records "title" and "questiontext" with NULL values and also "complete" and "tstamp" with value 0  (Look for services/cron which delete this rows).

## Codes

* If code usage is activated, every user (logged in or anonymous) must enter a code to participate.

## Survey Run

* **Suspend Behaviour**: Clicking suspend will leave the survey without saving the inputs of the current page. Resume will present the page left with empty input. JF decision: https://mantis.ilias.de/view.php?id=30766
* **Final Page**
  * The final page will contain a button named "Back to Repository", see https://mantis.ilias.de/view.php?id=14292
  * The button on the final survey page will lead to the container of the survey. Exception are 360° surveys, they will return to the info page, see https://mantis.ilias.de/view.php?id=14971
 
  
## Anonymous Access

* To give an external user (no ILIAS account) access to a standard survey, **read permission** to the survey (and all upper container) must be granted to the **Anonymous Role**. The Codes or Privacy settings are not relevant.
  * If an **anonymous user accesses via code** and suspends the survey. The user will be able to continue without entering the code, as long as the (anonymous) user session is valid. After the session has ended, the user needs to re-enter the code to be able to resume the survey.
  * If an **anonymous user accesses without code**, the use will **not get** a "Suspend" button. However as long as the (anonymous) user session is valid in the browser, the user may re-enter the survey and click on "Resume". After finishing the survey, a re-entering is not possible within the current (anonymous) user session. However a new anonymous user session will allow to perform the survey again.
    (Current issue: when the survey is set to "with names", a Suspend button will be shown. After suspending a "Start" instead of a "Resume" button is shown, even if given answers are store in the session. If the survey has been finished, the start button is still displayed, but an error message "You already finished" is shown on click. A new user session will not allow to re-enter the Survey.)

## 360 Mode

* 360° surveys do not allow to activate Codes on the top level. However **external raters** can be added to appraisees. These will get access codes assigned. External raters can access the survey **via code**, **no anonymous role permissions** or **public area configuration** are needed.
* 360° surveys do not allow to set privacy settings. 

## Result Presentation

* **Privacy** (with/without names): The setting only affects the result presentation. No user names, account names or emails will be shown if privacy is activated (without names).
* **Competences**
  * Results screen lists all profiles of the user. If other competences are used an additional dropdown "All competences of survey".
  * The "All competences of survey" view will show all competences that are assigned to the survey.
  * The competence results will only list entries related to the current survey. Also the gap analysis is based on these values. If users want to see data of other objects, they need to navigate e.g. to an upper course or the their global competence overview on the dashboard.
* **Print View**: The print process uses a modal to select available options, see https://docu.ilias.de/goto_docu_wiki_wpage_6994_1357.html
  **Standard Survey**

### Standard Survey Results Views

- **Overview**: This view lists all questions, numbers of user who answered/skipped and median/arithmetic means per answer.
- **Details**: This view will list detailed results per question including numbers of user who answered/skipped, most selected value, nr of selections, median/arithmetic means per answer. It will output the total number and percentage per answer option as table and bar chart.
- **Per Participant**: This view includes a table listing all answers of all participants, their working time for the survey and finished timestamp.

### 360° Survey Results Views

- **Competence Results**: Lists the competence results calculated per appraisee. This presents a calculated level for each competence that has related answered questions.
- **Overview**: Same as in standard survey, but aggregated per appraisee.
- **Details**: Same as in standard survey, but aggregated per appraisee.
- **Per Participant**: This view includes a table listing all answers of all raters, their working time for the survey and finished timestamp. The answers are listed without referencing the appraisees. All raters are listed as "Anonymous".

### Self Evaluation Results Views

- **Competence Results**: Lists the competence results calculated per participant. This presents a calculated level for each competence that has related answered questions.
- **Overview**: Same as in standard survey - no separation of participants.
- **Details**: Same as in standard survey - no separation of participants.
- **Per Participant**: Same as in standard survey.

### Individual Feebdack Results Views

- **Competence Results**: Lists the competence results calculated per participant. This presents a calculated level for each competence that has related answered questions.
- **Details**: Allows to select all appraisees. After selection the answers given by the raters for each question will be displayed (inkl. first/lastname of the raters).
