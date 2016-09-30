<?php exit; ?>

# Survey Question Pool (Modules/SurveyQuestionPool)
* Main question editing/storing
* Storing of phrases

## Question Types
* stored in table svy_qtype

## Questions
* class SurveyQuestion
* table svy_question (general question properties)
  * questiontype_fi: question type -> svy_qtype
  * obj_fi: survey or survey pool object -> object_data
  

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
  * title: answer text
  * defaultvalue: is set to "1" for categories predefined by the system? or for user defined phrases
  * neutral:

## "Variables" (Answer Options of Concrete Questions)
* Answer options for each question
* Hold Scale Values
* table svy_variable
  * category_fi: general answer option -> svy_category
  * question_fi: question -> svy_question
  * value1:
  * value2:
  * sequence (order of the options in the question presentation)
  * other:
  * scale: scale value

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
  
## Rule Relation
* <, <=, =>, ...
* table svy_relation (fixed set of relations, should be moved from table to class constants)
  * longname, e.g. less
  * shortname, e.g. <

## Rule / Condition Definition
* Rules can be defined using single choice, multiple choice and metric questions
* table svy_constraint
  * question_fi: "source" question -> svy_question
  * relation_fi: relation -> svy_relation
  * value: scale value - 1 !?
  * conjunction: or/and (but why on this level?)

## Rules / Condition used on a Question
* "If condition is met, show the question"
* table svy_qst_constraint (it seems that svy_constraint and svy_qst_constraint could be merged into one table)
  * survey_fi: survey -> svy_svy
  * question_fi: "target" question -> svy_question
  * constraint_fi: constraint definition -> svy_constraint

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
  * question_fi: question -> svy_question (does not point to svy_svy_qst)
  * value: scale value of corresponding "variable" -1?
  * textanswer: 
  * rowvalue:



## Current behaviour without patch.

### scales doesn't allow 0 values. And this scales are saved with the scale value - 1 in the table "svy_answer"

#### Web workflow
**As admin:**

Repository home -> create new survey, create new page with any of this "Question type":
- Multiple Choice Question (Single Response)
- Multiple Choice Question (Multiple Response)
- Matrix Question

**In the answers section:**

- Problem:

At this point, if we don not save any answer and press "back to de Survey" or leave this page without save.
In the "svy_question" we have the records "title" and "questiontext" with NULL values and also "complete" and
"tstamp" with value 0  (Look for services/cron which delete this rows)

- Problem:

We can put as scale values everything without restrictions. Strings, numbers and symbols are allowed.
After press the save button we get the success message "Modifications saved."
But in the database table "svy_variable" the column scale has "NULL".

- Problem:

If we go to edit this answer we can see that the javascript filled the Scale with the first number available in the
scale values. Therefore we have NULL in the DB and one new number in the edit form.

- Problem:

Everytime one answer is added the javascript clone exactly the same row above. Therefore we have to delete the text
and scale before write.

- Observation:

Can't store the value "0"

- Observation:

Editing the question answers. If we delete one answer with "-" button. In the table "svy_category" this answer remains available.
this answer is perfectly deleted in "svy_variable" table.

    select * from svy_question;
    +-------------+-----------------+--------+----------+-------------------+-------------+-----------+------------+----------+------------+-------------+------------+---------------------+-------+
    | question_id | questiontype_fi | obj_fi | owner_fi | title             | description | author    | obligatory | complete | created    | original_id | tstamp     | questiontext        | label |
    +-------------+-----------------+--------+----------+-------------------+-------------+-----------+------------+----------+------------+-------------+------------+---------------------+-------+
    |          50 |               2 |    277 |        6 | My first question | NULL        | root user | 1          | 1        | 1475171583 |        NULL | 1475174823 | This is my question | NULL  |
    |          51 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475172649 |        NULL |          0 | NULL                | NULL  |
    |          52 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475174096 |        NULL |          0 | NULL                | NULL  |
    |          53 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475174194 |        NULL |          0 | NULL                | NULL  |
    |          54 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475174292 |        NULL |          0 | NULL                | NULL  |
    |          55 |               2 |    277 |        6 | NULL              | NULL        | root user | 1          | 0        | 1475175261 |        NULL |          0 | NULL                | NULL  |
    +-------------+-----------------+--------+----------+-------------------+-------------+-----------+------------+----------+------------+-------------+------------+---------------------+-------+
    6 rows in set (0.00 sec)


**In the Questions page (Drag and drop section)**

Only the GUI files are affected:
- Modules/SurveyQuestionPool/classes/class.SurveySingleChoiceQuestionGUI.php
- Modules/SurveyQuestionPool/classes/class.SurveyMultipleChoiceQuestionGUI.php
- Modules/SurveyQuestionPool/classes/class.SurveyMatrixQuestionGUI.php

Not affected:
- Modules/SurveyQuestionPool/classes/class.SurveySingleChoiceQuestion.php
- Modules/SurveyQuestionPool/classes/class.SurveyMultipleChoiceQuestion.php
- Modules/SurveyQuestionPool/classes/class.SurveyMatrixQuestion.php

Here que can create pages, add from pool etc...

- Problem/Observation:

Here we are passing to the template the scale -1. Therefore all the radiobuttons, checkboxes will have the scale value as scale -1
Also if we need store 0 values this if statement is not valid.

    $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);

functions affected:

- getParsedAnswers
- getWorkingForm  (horizontal,vertical and combobox options)


## POSSIBLE CONFLICTS
In this Services I have seen "svy_variable"
- Services/Database/test/Implementations/data
- Services/Database/test/Implementations/data
- Services/LoadTest/data/usr_1000


## DATABASE TABLES

#### svy_question

Stores questions

Special columns:
- "complete" 1 or 0 depending if the user saved any data.

#### svy_qtype

Stores the question types: SingleChoice / MultipleChoice / Matrix / Metric

#### svy_variable

Stores the answers available.

Special columns:
- "sequence" determines the position in the form.
- "scale" scale value (positives or NULL. Here the scale have the real value entered, not scale -1 )
- "value1" ??? it seems to be the same as sequence but starting by 1 instead of 0 (tested deleting and adding answers)
- "value2" ??? always null?

#### svy_category

Stores all the answers saved by the user, even if this answers were deleted at any moment.

####svy_answer

Stores the answers, those that the user chooses when he takes the survey.


## EXAMPLE: Table comparation

For the "question_fi" 50

We have 2 scales, 11 and 22:

    select * from svy_variable;
    +-------------+-------------+-------------+--------+--------+----------+------------+-------+-------+
    | variable_id | category_fi | question_fi | value1 | value2 | sequence | tstamp     | other | scale |
    +-------------+-------------+-------------+--------+--------+----------+------------+-------+-------+
    |         291 |         123 |          50 |      2 |   NULL |        1 | 1475174823 |     0 |    22 |
    |         290 |         122 |          50 |      1 |   NULL |        0 | 1475174823 |     0 |    11 |
    +-------------+-------------+-------------+--------+--------+----------+------------+-------+-------+
    2 rows in set (0.00 sec)

User answered but the scale is saved as value 10: (scale -1)

    select * from svy_answer;
    +-----------+-----------+-------------+-------+------------+----------+------------+
    | answer_id | active_fi | question_fi | value | textanswer | rowvalue | tstamp     |
    +-----------+-----------+-------------+-------+------------+----------+------------+
    |        54 |         3 |          50 |    10 | NULL       |        0 | 1475178660 |
    +-----------+-----------+-------------+-------+------------+----------+------------+
    1 row in set (0.00 sec)

