**Component** Documentation

* [Public Services](#public-services)
* [Internal Documentation](#internal-documentation)

# Public Services

This component does currently not offer any public services.

# PDF Generation

- If wkhtmltopdf is being used you must set the "Use print media type instead of screen" flag.
- wkhtmltopdf struggles with canvas rendering, see e.g. https://github.com/wkhtmltopdf/wkhtmltopdf/issues/1964

# Internal Documentation

This section documents the general concepts and structures of the Survey Module. These are internal implementations which SHOULD not be used outside of this module unless mentioned in the API section of this README.

* [Overview](#overview)
* [Survey Question Pool](#survey-question-pool)
* [Questions](#questions)
* [Question Type](#question-type)
* [Question Category](#question-category)
* [Phrases](#phrases)
* [Variables](#variables)
* [Survey](#survey)
* [Survey Questions](#survey-questions)
* [Question Block](#question-block)
* [Constraints](#constraints)
* [Question Constraint](#question-constraint)
* [Survey Run](#survey-run)
* [Answer](#answer)
* [Invitation](#invitation)

## Overview

* A **Survey Question Pool** enables authoring of questions. Questions of pools can be reused in Surveys.
* A **Question** has a certain **Question Type**. Current Types are: Singel Choice, Multiple Choice, Metric, Matrix and Test.
* A **Question Category** is a general answer option of a question.
* A **Phrase** is a predefined set of answer options, which can be re-used during question editing.
* A **Variable** is an answer option of a concrete question (referencing a category and a question).
* A **Survey** contains a number of **Survey Questions** that are asked to participants.
* A **Question Block** groups multiple questions on one page. 
* A **Constraint** uses a relation and scale value (e.g. >1) to define a conditional presentation of questions.
* A **Question Constraint** imposes a constraint on a question of the survey.
* A **Survey Run** represents the current state of a user progress during a survey (access to pages, finished state)
* An **Answer** represents an answer given by a user during a Survey Run.
* A **Invitation** invites a user to participate in a survey by adding a task to his/her task list.


## Survey Question Pool
* **Code**: `Modules/SurveyQuestinoPool`
* **DB Tables**: `svy_qpl`

### Properties

* **Property**: ... (`table.column`)

### Business Rules

...


## Questions
* **Code**: `Modules/SurveyQuestinoPool/Questions`
* **DB Tables**: `svy_question`

### Properties
* **Question Type**:  (`svy_question.questiontype_fi`)
* **Pool or Survey**: Object ID of parent Pool or Survey (`svy_question.obj_fi`)
* **Author**: Object ID of author user (`svy_question.owner_fi`), Author name (`svy_question.author`)
* **Title**
* **Description**
* **Obligatory**: Is question obligatory 1, 0 (`svy_question.obligatory`), note: table is used for both, questions in pools and surveys
* **Complete**: 1 or 0 depending if the user saved any data (`svy_question.complete`)
* : (`svy_question.original_id`)
* : (`svy_question.tstamp`)
* : (`svy_question.questiontext`)
* : (`svy_question.label`)


### Issues
* If we do not save any answer and press "back to de Survey" or we leave this page without save. In the "svy_question" we have the records "title" and "questiontext" with NULL values and also "complete" and "tstamp" with value 0  (Look for services/cron which delete this rows).

## Question Type
* **Code**: `Modules/SurveyQuestinoPool/Questions`
* **DB Tables**: `svy_qtype`

Can we get rid of legacy plugin architecture?
Can we get of the table completely?

### Single Choice Question
...

### Multiple Choice Question
...

### Metric Question
...

### Matrix Question
* **DB Tables**: `svy_matrix`, `svy_matrixrows`

### Text Question
...


## Question Category

A question category is an answer option of a question. There are predefined answer options, and custom options which are created during question editing. The categories hold the answer texts, but not the scale values which are stored in the Variables (`svy_variable`).

* **Code**: `Modules/SurveyQuestinoPool/Categories`
* **DB Tables**: `svy_category`

[WIP]
* Categories are reused by all questions, depending on the category title (answer option text)
* If a question is edited and a category title (answer option) already exists, it
  will be assigned to the question
* class ilSurveyCategory represents single category (but also holds "variable" data)
  * title ("answer option text")
  * other (if true, an additional text input field presented for free user input is presented)
  * neutral (true: this is a neutral answer, ...)
  * label ...
  * scale (<- this will be stored in "Variables")
* class SurveyCategories handles an array of ilSurveyCategory objects (for a question)
* problem: neither ilSurveyCategory nor SurveyCategories writes into table svy_category this is done by
  classes SurveySingleChoiceQuestion, SurveyQuesiton, SurveyMatrixQuestion and SurveyPhrases, ...
* table svy_category
  * title: answer text
  * defaultvalue: is set to "1" for categories predefined by the system? or for user defined phrases
  * neutral:
  * ..
  
## Phrases
* **Code**: `Modules/SurveyQuestinoPool/Phrases`
* **DB Tables**: `svy_phrase`,`svy_phrase_cat`

[WIP]
* Reusable sets of answer options (only for single choice and matrix questions) 
* table svy_phrase
    * title (of the answer option set/phrase)
    * defaultvalue...
* table svy_phrase_cat (Answer options of phrase)
  * phrase_fi -> phrase in svy_phrase
  * category_fi -> general answer option in svy_category
  * sequence: order of the options in the question presentation
  * other:
  * scale: always NULL?, editing provides only disabled input fields
* problem: why scale field when always NULL?


## Variables

Answer options for each question. Hold Scale Values. Texts are in Question Categories. Note: For matrix questions only entries "for one row" are in table `svy_variable`.

* **Code**: 
* **DB Tables**: `svy_variable`

### Properties
* **ID** (`svy_variable.variable_id`)
* **Question Category**: Reference to `svy_category` (`svy_variable.category_fi`)
* **Question**: Reference to `svy_question` (`svy_variable.question_fi`)
* **value1**: for metric q: min value
* **value2**: for metric q: max value
* **Sequence**: Order of the options in the question presentation (`svy_variable.sequence`)
* **Timestamp**: (`svy_variable.tstamp`)
* ??? (`svy_variable.other`)
* **Scale Value***: Positive or NULL. Here the scale have the real value entered, not scale -1. (`svy_variable.scale`)

### Issues  
* value1/value2 values seem to be redundant or belong to other tables (e.g. metric)


## Question Editing
* Question GUI classes 
  * Save process: save() -> saveForm() -> importEditFormValues() -> question object saveToDb()
  

## Survey
* **Code**: `Modules/Survey`
* **DB Tables**: `svy_svy`

### Properties

* **Survey ID**: Surveys have their own IDs (`svy_svy.survey_id`) and additionally reference the Object ID (`svy_svy.obj_fi`).
* ...
* **Privacy**: With Names (0): Names are presented in the Participants and Results view. Without Names/Anonymous (2): Instead of the names the term "Anonymous" is printed out in the Participants and Results view. (`svy_svy.anonymize`, see Property Access Codes)
* **List of Participants**: This suboption of Privacy/Anonymous is only available if activated in the Administration (List of Participants). This activates an additional Participants list with first-, lastname, login and status (finished) after the end date of the survey. Additionally a minimum number of participants can be set in the Administration (`svy_svy.anon_user_list`).
* **Access Codes**: Use Access Codes yes/no (`svy_svy.anonymize`: 0: With Names/No Codes, 1: Anonymous/Codes, 2: Anonymous/No Codes, 3: With Names/Codes). If activated, all users have to enter an access code when starting the survey.


[WIP]
* table svy_svy (general settings of the survey)
  * obj_fi: general object -> object_data
  * ...

## Survey Questions
* **Code**:
* **DB Tables**: `svy_svy_qst`,`svy_qst_oblig`

[WIP]
* table svy_svy_qst
  * survey_fi: survey -> svy_svy
  * question_fi: question -> svy_question
  * sequence: ordering of the questions in the survey (increments +1 normally through question blocks)
  * ...
* table svy_qst_oblig (compulsory (obligatory/mandatory) property)
  * survey_fi: survey -> svy_svy
  * question_fi: question -> svy_question
  * obligatory: 1 for true
* problem: Why is this not a simple property in svy_svy_qst?
  
## Question Block
* **Code**:
* **DB Tables**: `svy_qblk`, `svy_qblk_qst`

[WIP]
* table svy_qblk
  * title: block title
  * show_questiontext: enables to show/hide question texts for questions of the block
  * show_blocktitle: show/hide title of the block in presentation
* table svy_qblk_qst (Questions of a question block)
  * survey_fi: survey -> svy_svy
  * question_fi: question -> svy_question (could have pointed to svy_svy_qst instead, but does not do)
  * question_block_fi: block -> svy_qblk
  
## Constraints
* **Code**:
* **DB Tables**: `svy_relation`, `svy_constraint`

General Idea: Conditional presentation of questions or question blocks (pages) depending on former answers. Constraints can be defined using single choice, multiple choice and metric questions.

[WIP]
* table svy_relation (<, <=, =>, ..., fixed set of relations, should be moved from table to class constants)
  * longname, e.g. less
  * shortname, e.g. <
  * problem: why storing this in a table, its static?
* table svy_constraint
  * question_fi: "source" question -> svy_question
  * relation_fi: relation -> svy_relation
  * value: scale value - 1 !?
  * conjunction: or/and (but why on this level?)

## Question Constraint
* **Code**:
* **DB Tables**: `svy_qst_constraint`

If the constraint is met, show the question.

[WIP]
* table svy_qst_constraint 
  * survey_fi: survey -> svy_svy
  * question_fi: "target" question -> svy_question
  * constraint_fi: constraint definition -> svy_constraint
* problem: it seems that svy_constraint and svy_qst_constraint could be merged into one table

### Current "Business" Rules (weak, needs a better concept)

* The "targets" for constraints are always single questions.
* If a question is added to a single question page (no block), all constraints are removed (createQuestionBlock) from the single question.
* If a contraint is defined for a block (as a target), the constraint is assigned to all questions of the block (svy_qst_constraint).
* If a third question is added to a block svy_qst_constraint holds only entries for the first two questions.
* Constraint checking in ilSurveyExecutionGUI->outSurveyPage seems only to be done for the constraints of the first question of a block.
* The constraints table shows only the constraints of the first question of a question block.


## Survey Run
* **Code**:
* **DB Tables**: `svy_finished`,`svy_times`

[WIP]
* table svy_finished (progress of user)
  * finished_id: autoincrement and pk of this table
  * survey_fi: survey -> svy_svy
  * user_fi: user -> usr_data (and object_data)
  * anonymous_id:
  * state: 1 if finished? 0 otherwise?
  * lastpage:
  * appr_id:
* table svy_times (Access times to survey pages during a run. Back and forward navigation lead to multiple entries per run for a page)
  * finished_fi: survey run -> svy_finished
  * first_question: first question id of page/block -> svy_question (does not seem to point to svy_svy_qst)
  * entered_page: timestamp when page has been rendered
  * left_page: timestamp when answers of page have been saved
  
## Answer
* **Code**:
* **DB Tables**: `svy_answer`

[WIP]
* Given answers by user during test run
  - mc question answers may lead to multiple entries for one question in a run
  - matrix questions lead to muliple entries (each row gets a different rowvalue)
* table svy_answer
  * active_fi: survey run -> svy_finished
  * question_fi: question -> svy_question (does not point to svy_svy_qst)
  * value: scale value of corresponding "variable" - 1 (!)
    (metric question answers have the entered value stored, no "-1" !)
  * textanswer: Text answer
  * rowvalue: Matrix question row, starting with 0
  
## Invitation
* **Code**: `Modules/Survey/Participants`
* **DB Tables**: `svy_invitation`

### Properties
* **User**: (`svy_invitation.user_id`)
* **Survey**: (`svy_invitation.survey_id`)