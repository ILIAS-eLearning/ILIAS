<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Assessment constants
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTest
*/

/**
* General question constants
*/
define("LIMIT_NO_LIMIT", 0);
define("LIMIT_TIME_ONLY", 1);
define("OUTPUT_HTML", 0);
define("OUTPUT_JAVASCRIPT", 1);

/**
* Question identifier constants
*/
define("CLOZE_TEST_IDENTIFIER", "CLOZE QUESTION");
define("ERROR_TEXT_IDENTIFIER", "ERROR TEXT QUESTION");
define("IMAGEMAP_QUESTION_IDENTIFIER", "IMAGE MAP QUESTION");
define("JAVAAPPLET_QUESTION_IDENTIFIER", "JAVA APPLET QUESTION");
define("FLASHAPPLET_QUESTION_IDENTIFIER", "FLASH APPLET QUESTION");
define("MATCHING_QUESTION_IDENTIFIER", "MATCHING QUESTION");
define("MULTIPLE_CHOICE_QUESTION_IDENTIFIER", "MULTIPLE CHOICE QUESTION");
define("SINGLE_CHOICE_QUESTION_IDENTIFIER", "SINGLE CHOICE QUESTION");
define("ORDERING_QUESTION_IDENTIFIER", "ORDERING QUESTION");
define("ORDERING_HORIZONTAL_IDENTIFIER", "ORDERING HORIZONTAL");
define("TEXT_QUESTION_IDENTIFIER", "TEXT QUESTION");
define("FILE_UPLOAD_IDENTIFIER", "FILE UPLOAD QUESTION");
define("NUMERIC_QUESTION_IDENTIFIER", "NUMERIC QUESTION");
define("FORMULA_QUESTION_IDENTIFIER", "FORMULA QUESTION");
define("TEXTSUBSET_QUESTION_IDENTIFIER", "TEXTSUBSET QUESTION");
define('KPRIM_CHOICE_QUESTION_IDENTIFIER', 'KPRIM CHOICE QUESTION');
define('LONG_MENU_QUESTION_IDENTIFIER', 'LONG MENU QUESTION');

/**
* Cloze question constants
*/

define("CLOZE_TEXT", "0");
define("CLOZE_SELECT", "1");
define("CLOZE_NUMERIC", "2");

define("TEXTGAP_RATING_CASEINSENSITIVE", "ci");
define("TEXTGAP_RATING_CASESENSITIVE", "cs");
define("TEXTGAP_RATING_LEVENSHTEIN1", "l1");
define("TEXTGAP_RATING_LEVENSHTEIN2", "l2");
define("TEXTGAP_RATING_LEVENSHTEIN3", "l3");
define("TEXTGAP_RATING_LEVENSHTEIN4", "l4");
define("TEXTGAP_RATING_LEVENSHTEIN5", "l5");


/**
* Matching question constants
*/
define("MT_TERMS_PICTURES", 0);
define("MT_TERMS_DEFINITIONS", 1);

/**
* Multiple choice question constants
*/
define("RESPONSE_SINGLE", "0");
define("RESPONSE_MULTIPLE", "1");

define("OUTPUT_ORDER", "0");
define("OUTPUT_RANDOM", "1");

/**
* Ordering question constants
*/
define("OQ_PICTURES", 0);
define("OQ_TERMS", 1);
define("OQ_NESTED_PICTURES", 2);
define("OQ_NESTED_TERMS", 3);

/**
* Test constants
*/
define("TEST_FIXED_SEQUENCE", 0);
define("TEST_POSTPONE", 1);

define("TYPE_ASSESSMENT", "1");
define("TYPE_SELF_ASSESSMENT", "2");
define("TYPE_ONLINE_TEST", "4");
define("TYPE_VARYING_RANDOMTEST", "5");

define("INVITATION_OFF", 0);
define("INVITATION_ON", 1);

define("COUNT_PARTIAL_SOLUTIONS", 0);
define("COUNT_CORRECT_SOLUTIONS", 1);

define("SCORE_ZERO_POINTS_WHEN_UNANSWERED", 0);
define("SCORE_STANDARD_SCORE_SYSTEM", 1);

define("SCORE_CUT_QUESTION", 0);
define("SCORE_CUT_TEST", 1);

define("SCORE_LAST_PASS", 0);
define("SCORE_BEST_PASS", 1);

/**
* Test evaluation constants
*/
define("TYPE_XLS_PC", "latin1");
define("TYPE_SPSS", "csv");
define("EXCEL_BACKGROUND_COLOR", "C0C0C0");

/**
 * Redirect after finishing test constants
 */
define("REDIRECT_NONE", 0);
define("REDIRECT_ALWAYS", 1);
define("REDIRECT_KIOSK", 2);
define("REDIRECT_SEB", 3);

/**
 * PDF Purposes
 */
define('PDF_USER_RESULT', 'UserResult');
define('PDF_PRINT_VIEW_QUESTIONS', 'PrintViewOfQuestions');
