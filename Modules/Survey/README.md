# Survey Question Pool (Modules/SurveyQuestionPool)
* Main question editing/storing
* Storing of phrases

## Question Types
* stored in table svy_qtype

## Questions
* class SurveyQuestion
* table svy_question: general question properties
  * questiontype_fi -> question type in svy_qtype
  * obj_fi -> survey or survey pool in object_data
  

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
  * other ...
  * neutral
  * label ...
  * scale (<- this will be stored in "Variables"
* class SurveyCategories handles an array of ilSurveyCategory objects (for a question)
* neither ilSurveyCategory nor SurveyCategories writes into table svy_category this is done by
  classes SurveySingleChoiceQuestion, SurveyQuesiton, SurveyMatrixQuestion and SurveyPhrases
* table svy_category
  * title
  * defaultvalue (is set to "1" for categories predefined by the system? or phrases)
  * neutral

## "Variables" (Answer Options of Concrete Questions)
* Answer options for each question
* Hold Scale Values
* table svy_variable
  * category_fi -> general answer option
  * question_fi -> question
  * value1 ...
  * value2 ...
  * sequence (order of the options in the question presentation)
  * other ...
  * scale (scale value)

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

## Question Editing
* Question GUI classes 
  * Save process: save() -> saveForm() -> importEditFormValues() -> question object saveToDb()
  
# Survey (Modules/Survey)
* Represents Survey Repository Object
* Manages question of survey
* Stores given answers

## Survey
* table svy_svy (general settings of the survey)
  * ...

## Questions in a survey
* table svy_svy_qst
  * survey_fi: survey -> svy_svy
  * question_fi: question -> svy_question
  * sequence: ordering of the questions in the survey (increments +1 normally through question blocks)
  
## Compulsory questions in a survey
* Stores compulsory (obligatory/mandatory) state of each question in a survey
* Why is this not a simple property in svy_svy_qst?
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
  
## Rule Relation Definition

## Rule / Condition Definition

## Rules / Condition use on a Question

## Survey Run ("Finished")
* Stores progress of user working through a survey
* table svy_finished
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
  * question_fi: question -> svy_question (does not seem to point to svy_svy_qst)
  * value: scale value of corresponding "variable" -1?
  * textanswer: 
  * rowvalue: 
  