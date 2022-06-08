<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
const LIMIT_NO_LIMIT = 0;
const LIMIT_TIME_ONLY = 1;
const OUTPUT_HTML = 0;
const OUTPUT_JAVASCRIPT = 1;

/**
* Question identifier constants
*/
const CLOZE_TEST_IDENTIFIER = "CLOZE QUESTION";
const ERROR_TEXT_IDENTIFIER = "ERROR TEXT QUESTION";
const IMAGEMAP_QUESTION_IDENTIFIER = "IMAGE MAP QUESTION";
const JAVAAPPLET_QUESTION_IDENTIFIER = "JAVA APPLET QUESTION";
const FLASHAPPLET_QUESTION_IDENTIFIER = "FLASH APPLET QUESTION";
const MATCHING_QUESTION_IDENTIFIER = "MATCHING QUESTION";
const MULTIPLE_CHOICE_QUESTION_IDENTIFIER = "MULTIPLE CHOICE QUESTION";
const SINGLE_CHOICE_QUESTION_IDENTIFIER = "SINGLE CHOICE QUESTION";
const ORDERING_QUESTION_IDENTIFIER = "ORDERING QUESTION";
const ORDERING_HORIZONTAL_IDENTIFIER = "ORDERING HORIZONTAL";
const TEXT_QUESTION_IDENTIFIER = "TEXT QUESTION";
const FILE_UPLOAD_IDENTIFIER = "FILE UPLOAD QUESTION";
const NUMERIC_QUESTION_IDENTIFIER = "NUMERIC QUESTION";
const FORMULA_QUESTION_IDENTIFIER = "FORMULA QUESTION";
const TEXTSUBSET_QUESTION_IDENTIFIER = "TEXTSUBSET QUESTION";
const KPRIM_CHOICE_QUESTION_IDENTIFIER = 'KPRIM CHOICE QUESTION';
const LONG_MENU_QUESTION_IDENTIFIER = 'LONG MENU QUESTION';

/**
* Cloze question constants
*/

const CLOZE_TEXT = "0";
const CLOZE_SELECT = "1";
const CLOZE_NUMERIC = "2";

const TEXTGAP_RATING_CASEINSENSITIVE = "ci";
const TEXTGAP_RATING_CASESENSITIVE = "cs";
const TEXTGAP_RATING_LEVENSHTEIN1 = "l1";
const TEXTGAP_RATING_LEVENSHTEIN2 = "l2";
const TEXTGAP_RATING_LEVENSHTEIN3 = "l3";
const TEXTGAP_RATING_LEVENSHTEIN4 = "l4";
const TEXTGAP_RATING_LEVENSHTEIN5 = "l5";


/**
* Matching question constants
*/
const MT_TERMS_PICTURES = 0;
const MT_TERMS_DEFINITIONS = 1;

/**
* Multiple choice question constants
*/
const RESPONSE_SINGLE = "0";
const RESPONSE_MULTIPLE = "1";

const OUTPUT_ORDER = "0";
const OUTPUT_RANDOM = "1";

/**
* Ordering question constants
*/
const OQ_PICTURES = 0;
const OQ_TERMS = 1;
const OQ_NESTED_PICTURES = 2;
const OQ_NESTED_TERMS = 3;

/**
* Test constants
*/
const TEST_FIXED_SEQUENCE = 0;
const TEST_POSTPONE = 1;

const TYPE_ASSESSMENT = "1";
const TYPE_SELF_ASSESSMENT = "2";
const TYPE_ONLINE_TEST = "4";
const TYPE_VARYING_RANDOMTEST = "5";

const INVITATION_OFF = 0;
const INVITATION_ON = 1;

const COUNT_PARTIAL_SOLUTIONS = 0;
const COUNT_CORRECT_SOLUTIONS = 1;

const SCORE_CUT_QUESTION = 0;
const SCORE_CUT_TEST = 1;

const SCORE_LAST_PASS = 0;
const SCORE_BEST_PASS = 1;

/**
* Test evaluation constants
*/
const TYPE_XLS_PC = "latin1";
const TYPE_SPSS = "csv";
const EXCEL_BACKGROUND_COLOR = "C0C0C0";

/**
 * Redirect after finishing test constants
 */
const REDIRECT_NONE = 0;
const REDIRECT_ALWAYS = 1;
const REDIRECT_KIOSK = 2;
const REDIRECT_SEB = 3;

/**
 * PDF Purposes
 */
const PDF_USER_RESULT = 'UserResult';
const PDF_PRINT_VIEW_QUESTIONS = 'PrintViewOfQuestions';
