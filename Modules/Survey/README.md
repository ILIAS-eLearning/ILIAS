* [API](#api)
* [General Documentation](#general-documentation)


# API

Currently the survey does not provide any API for other components.

# General Documentation

This section documents the general concepts and structures of the Survey Module. These are internal implementations which SHOULD not be used outside of this module unless mentioned in the API section of this README.

* [Survey](#exercise)
* [Assignment](#assignment)
* [Assignment Types](#assignment-types)
* [Member](#member)
* [Team](#team)
* [Assignment Member State](#assignment-member-state)
* [Submission](#submission)
* [Peer Review](#peer-review)
* [Criteria Catalog](#criteria-catalog)


# Survey Question Pool (Modules/SurveyQuestionPool)
* Main question editing/storing
* Storing of phrases

## Question Types
* stored in table svy_qtype
* Can we get rid of legacy plugin architecture?
* Can we get of the table completely?

## Questions
* Code: `SurveyQuestionPool/Exercise/AssMemberState`

* class SurveyQuestion (parent for specialized question classes)
* table svy_question (general question properties)
  * questiontype_fi: question type -> svy_qtype
  * obj_fi: survey or survey pool object -> object_data
  * owner_fi: author user -> usr_data and object_data
  * complete: 1 or 0 depending if the user saved any data
  * original_id: 
  * ...
* Problem: If we do not save any answer and press "back to de Survey" or we leave this page without save.
  In the "svy_question" we have the records "title" and "questiontext" with NULL values and also "complete" and
  "tstamp" with value 0  (Look for services/cron which delete this rows)

  

### Single Choice Question
...

### Multiple Choice Question
...

### Metric Question
...

### Matrix Question
...

### Text Question
...


## Question "Categories" (General Answer Options)
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

## "Variables" (Answer Options of Concrete Questions)
* Answer options for each question
* Hold Scale Values
* table svy_variable
  * category_fi: general answer option -> svy_category
  * question_fi: question -> svy_question
  * value1: for metric q: min value
  * value2: for metric q: max value
  * sequence: order of the options in the question presentation
  * other:
  * scale: scale value (positives or NULL. Here the scale have the real value entered, not scale -1 )
* problem: value1/value2 values seem to be redundant or belong to other tables (e.g. metric)


## "Phrases"
* Reusable sets of answer options (only for single choice and matrix questions) 
* table svy_phrase
  * title (of the answer option set/phrase)
  * defaultvalue...
  
## Phrase Categories (Answer options of phrase)
* table svy_phrase_cat
  * phrase_fi -> phrase in svy_phrase
  * category_fi -> general answer option in svy_category
  * sequence: order of the options in the question presentation
  * other:
  * scale: always NULL?, editing provides only disabled input fields
* problem: why scale field when always NULL?


## Question Editing
* Question GUI classes 
  * Save process: save() -> saveForm() -> importEditFormValues() -> question object saveToDb()
  

# Survey (Modules/Survey)
* Represents Survey Repository Object
* Manages question of survey
* Stores given answers
* ...

## Survey
* table svy_svy (general settings of the survey)
  * obj_fi: general object -> object_data
  * ...

## Questions in a survey
* table svy_svy_qst
  * survey_fi: survey -> svy_svy
  * question_fi: question -> svy_question
  * sequence: ordering of the questions in the survey (increments +1 normally through question blocks)
  * ...
  
## Compulsory questions in a survey
* Stores compulsory (obligatory/mandatory) state of each question in a survey
* problem: Why is this not a simple property in svy_svy_qst?
* table svy_qst_oblig
  * survey_fi: survey -> svy_svy
  * question_fi: question -> svy_question
  * obligatory: 1 for true
  
## Question Block / Page
* All questions of a block will be presented on one page
* table svy_qblk
  * title: block title
  * show_questiontext: enables to show/hide question texts for questions of the block
  * show_blocktitle: show/hide title of the block in presentation

## Question Block Questions
* Questions of a question block
* table svy_qblk_qst
  * survey_fi: survey -> svy_svy
  * question_fi: question -> svy_question (could have pointed to svy_svy_qst instead, but does not do)
  * question_block_fi: block -> svy_qblk
  
## Rules/Routing
* General Idea: Conditional presentation of questions or question blocks (pages) depending on former answers

## Rule Relation
* <, <=, =>, ...
* table svy_relation (fixed set of relations, should be moved from table to class constants)
  * longname, e.g. less
  * shortname, e.g. <
* problem: why storing this in a table, its static?

## Rule / Condition Definition
* Rules can be defined using single choice, multiple choice and metric questions
* table svy_constraint
  * question_fi: "source" question -> svy_question
  * relation_fi: relation -> svy_relation
  * value: scale value - 1 !?
  * conjunction: or/and (but why on this level?)

## Rules / Condition used on a Question
* "If condition is met, show the question"
* table svy_qst_constraint 
  * survey_fi: survey -> svy_svy
  * question_fi: "target" question -> svy_question
  * constraint_fi: constraint definition -> svy_constraint
* problem: it seems that svy_constraint and svy_qst_constraint could be merged into one table

## Survey Run ("Finished")
* Stores progress of user working through a survey
* table svy_finished
  * finished_id: autoincrement and pk of this table
  * survey_fi: survey -> svy_svy
  * user_fi: user -> usr_data (and object_data)
  * anonymous_id:
  * state: 1 if finished? 0 otherwise?
  * lastpage:
  * appr_id:
  
## Survey Run Access Times
* Access times to survey pages during a run. Back and forward navigation lead to multiple entries per run for a page
* table svy_times
  * finished_fi: survey run -> svy_finished
  * first_question: first question id of page/block -> svy_question (does not seem to point to svy_svy_qst)
  * entered_page: timestamp when page has been rendered
  * left_page: timestamp when answers of page have been saved
  
## Given Answers
* Given answers by user during test run, mc question answers may lead to multiple entries for one question in a run
* table svy_answer
  * active_fi: survey run -> svy_finished
  * question_fi: question -> svy_question (does not point to svy_svy_qst)
  * value: scale value of corresponding "variable" -1?
  * textanswer: 
  * rowvalue: