<?php exit; ?>

===================================
LMPresentation
===================================

Typical Control Flow

- executeCommand()
  - layout()
    - determineLayout()
      - getCurrentPageId()
    - ilMainMenu
    - ilTOC
    - ilPage
    - ...



===================================
LP in Learning Modules
===================================

Services/COPage/js/ilCOPageQuestionHandler.js
-> processAnswer
-> ilPageObjectGUI->processAnswer()
-> ilPageQuestionProcessor::saveQuestionAnswer(...)
   - ilPageQuestionProcessor::calculatePoints(...)
   - saves into table page_qst_answer (qst_id, user_id, try, passed, points)
     (<- table holds last answer/passed status of each user/question)

Modules/LearningModule/classes/class.ilLMPresentation.php
-> lmAccess
   - saves last access to an lm in lo_access
     (<- table holds one entry per user/lm ref id)

Table lo_access
- timestamp,usr_id,lm_id,obj_id,lm_title (lm_id is REF ID!)
- written in ilLMPresentation->lmAccess() (should be refactored in sep. class)
- read and used in ilLMPresentation->trackChapterAccess() (should be refactored in sep. class)
  - trackChapterAccess() is called in ilLMPresentation->executeCommand() right before lmAccess()
  - trackChapterAccess needs "last timestamp" to calculate "time spent"
- read in ilObjContetnObjectAccess::_getLastAccessedPage and ilObjContetnOBjectAccess::_preloadData

Ideas
=====
- use lo_access (-- ref based)
  - remove lm_title
  - primary key: usr_id, obj_id (keep lm_id and timestamp, use replace)
  -> trackChapterAccess must get "max(timestamp) record)
  -> ilObjContetnObjectAccess preloader must be rewritten
- introduce new table for user page access
  - copg_access (usr_id, ts, pg_id, parent_type) using replace
- use lm_read_event


Problems
========
- lo_access is ref id based, page_qst_answer not