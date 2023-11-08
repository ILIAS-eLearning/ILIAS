<?php exit; ?>


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

We can put as scale values everything without restrictions. Strings, numbers and symbols are allowed.
After pressing the save button we get the success message "Modifications saved."
But in the database table "svy_variable" the column scale has "NULL".

- Problem:

If we go to edit this answer we can see that the javascript filled the Scale with the first number available in the
scale values. Therefore we have NULL in the DB and one new number in the edit form.

- Observation:

Everytime one answer is added, the javascript clones exactly the same row above. Therefore we have to delete the text
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

Here we are passing to the template, the scale -1. Therefore all the radio buttons, checkboxes will have the scale value as scale -1
Also if we need store 0 values this if statement is not valid.

    $template->setVariable("VALUE_SC", ($cat->scale) ? ($cat->scale - 1) : $i);

functions affected:

- getParsedAnswers
- getWorkingForm  (horizontal,vertical and combobox options)


## DATABASE TABLES




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



##Tables documentation.


* table svy_question  (What is the difference between tstamp and created?)
    * question_id: sequence value -> svy_question_seq (PK)
    * questiontype_fi: question type -> svy_qtype
    * obj_fi: survey or survey pool object -> object_data (MUL)
    * owner_fi: author user -> usr_data and object_data (MUL)
    * title: question text (MUL)
    * description:
    * author:
    * obligatory:
    * complete: 1 or 0 depending if the user saved any data
    * created:
    * original_id:
    * tstamp:
    * questiontext:
    * label:
    INSERT: Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php - saveToDb
    UPDATE: Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php - saveToDb
    DELETE: Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php - delete
    UPDATE: Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php - delete
    UPDATE: Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php - _changeOriginalId
    INSERT: Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php - createNewQuestion
    UPDATE: Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php - saveCompletionStatus
    UPDATE: Modules/Survey/classes/class.ilObjSurvey.php - setObligatoryStates
    UPDATE: Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php - setObligatoryStates

* table svy_category
    * category_id: sequence value -> svy_category_seq (PK)
    * title: answer text
    * defaultvalue: is set to "1" for categories predefined by the system? or for user defined phrases
    * owner_fi: (MUL)
    * neutral:
    * tstamp:


* table svy_variable
    * variable_id: sequence value -> svy_variable_seq (PK)
    * category_fi: general answer option -> svy_category (MUL)
    * question_fi: question -> svy_question (MUL)
    * value1: for metric q: min value
    * value2: for metric q: max value
    * sequence: order of the options in the question presentation
    * other:
    * scale: scale value (positives or NULL. Here the scale have the real value entered, not scale -1 )

* table svy_phrase
    * phrase_id: sequence value -> svy_phrase_seq (PK)
    * title: phrase text
    * defaultvalue:
    * owner_fi: (MUL)
    * tstamp:


* table svy_phrase_cat
    * phrase_category_id: sequence value -> svy_phrase_cat_seq (PK)
    * phrase_fi -> phrase in svy_phrase (MUL)
    * category_fi -> general answer option in svy_category (MUL)
    * sequence: order of the options in the question presentation
    * other:
    * scale: always NULL?, editing provides only disabled input fields


* table svy_qblk
    * questionblock_id: sequence value -> sv_qblk_seq (PK)
    * title: block title
    * show_questiontext: enables to show/hide question texts for questions of the block
    * owner_fi: (MUL)
    * tstamp:
    * show_blocktitle: show/hide title of the block in presentation

* table svy_qblk_qst
    * qblk_qst_id: sequence value -> sv_gblk_qst_seq (PK)
    * survey_fi: survey -> svy_svy
    * question_fi: question -> svy_question (could have pointed to svy_svy_qst instead, but does not do)
    * question_block_fi: block -> svy_qblk

* table svy_relation (fixed set of relations, should be moved from table to class constants)
    * relation_id: sequence value -> sv_relation_seq (PK)
    * longname: e.g. less
    * shortname: e.g. <
    * tstamp:

* table svy_constraint
    * constraint_id: sequence value -> svy_constraint_seq (PK)
    * question_fi: "source" question -> svy_question (MUL)
    * relation_fi: relation -> svy_relation (MUL)
    * value: scale value - 1 !?
    * conjunction: or/and (but why on this level?)

* table svy_qst_constraint
    * question_constraint_id: sequence value -> svy_qst_constraint_seq (PK)
    * survey_fi: survey -> svy_svy (MUL)
    * question_fi: "target" question -> svy_question (MUL)
    * constraint_fi: constraint definition -> svy_constraint (MUL)

* table svy_finished
    * finished_id: sequence value -> svy_finished_seq (PK)
    * survey_fi: survey -> svy_svy (MUL)
    * user_fi: user -> usr_data (and object_data)(MUL)
    * anonymous_id: (MUL)
    * state: 1 if finished? 0 otherwise?
    * lastpage:
    * appr_id:

*table svy_material
    * material_id: sequence value -> svy_material_seq (PK)
    * question_fi: question -> svy_question (MUL)
    * internal_link:
    * import_id:
    * material_title:
    * tstamp:
    * text_material:
    * external_link:
    * file material:
    * material_type:

* table svy_qst_metric
    * question_fi: question -> sv_question (PK)
    * subtype: default 3

* table svy_times
    * id: sequence value -> svy_times_seq (PK)
    * finished_fi: survey run -> svy_finished (MUL)
    * first_question: first question id of page/block -> svy_question (does not seem to point to svy_svy_qst)
    * entered_page: timestamp when page has been rendered
    * left_page: timestamp when answers of page have been saved

* table svy_answer
    * answer_id: sequence value -> svy_answer_seq (PK)
    * active_fi: survey run -> svy_finished
    * question_fi: question -> svy_question (does not point to svy_svy_qst)
    * value: scale value of corresponding "variable" -1?
    * textanswer:
    * rowvalue:
    * tstamp:

* table svy_qst_sc
    * question_fi: question -> svy_question (PK)
    * orientation: horizontal, vertial or combobox

* table svy_qst_mc
    * question_fi: question -> svy_question (PK)
    * orientation: horizontal, vertial
    * nr_min_answers:
    * nr_max_answers:

* table svy_qst_matrix
    * question_fi: question -> svy_question (PK)
    * subtype
    * column_separators
    * row_separators
    * neutral_column_separator
    * column_placeholders
    * legend
    * singleline_row_caption
    * repeat_column_header
    * column_header_position
    * random_rows
    * column_order
    * column_images
    * row_images
    * bipolar_adjective1
    * bipolar_adjective2
    * layout
    * tstamp

*table svy_qst_matrix_rows
    * id_svy_qst_matrixrows: sequence value -> svy_qst_matrix_rows_seq (PK)
    * title
    * sequence
    * question_fi: question -> svy_question (MUL)
    * other
    * label

* table svy_qpl
    * id_questionpool: sequence value -> svy_qpl_seq (PK)
    * obj_fi: (MUL)
    * isonline:
    * tstamp

* table svy_inv_usr
    * invited_user_id: sequence value -> svy_inv_usr_seq (PK)
    * survey_fi: survey -> svy_svy (MUL)
    * user_fi: user -> usr_data (MUL)
    * tstamp

* table svy_qst_text
    * question_fi: question -> svy_question (PK)
    * maxchars:
    * width:
    * height:

* table svy_anonymous:
    * anonymous_id: sequence value -> svy_anonymous_seq (PK)
    * survey_key:
    * survey_fi: survey -> svy_svy
    * user_key:
    * tstamp:
    * externaldata:
    * sent:
