<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

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
define("IMAGEMAP_QUESTION_IDENTIFIER", "IMAGE MAP QUESTION");
define("JAVAAPPLET_QUESTION_IDENTIFIER", "JAVA APPLET QUESTION");
define("MATCHING_QUESTION_IDENTIFIER", "MATCHING QUESTION");
define("MULTIPLE_CHOICE_QUESTION_IDENTIFIER", "MULTIPLE CHOICE QUESTION");
define("SINGLE_CHOICE_QUESTION_IDENTIFIER", "SINGLE CHOICE QUESTION");
define("ORDERING_QUESTION_IDENTIFIER", "ORDERING QUESTION");
define("TEXT_QUESTION_IDENTIFIER", "TEXT QUESTION");
define("NUMERIC_QUESTION_IDENTIFIER", "NUMERIC QUESTION");
define("TEXTSUBSET_QUESTION_IDENTIFIER", "TEXTSUBSET QUESTION");

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
define ("MT_TERMS_PICTURES", 0);
define ("MT_TERMS_DEFINITIONS", 1);

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
define ("OQ_PICTURES", 0);
define ("OQ_TERMS", 1);

/**
* Test constants
*/
define("TEST_FIXED_SEQUENCE", 0);
define("TEST_POSTPONE", 1);

define("REPORT_AFTER_TEST", 1);
define("REPORT_ALWAYS", 2);
define("REPORT_AFTER_DATE", 3);

define("TYPE_ASSESSMENT", "1");
define("TYPE_SELF_ASSESSMENT", "2");
define("TYPE_ONLINE_TEST", "4");
define("TYPE_VARYING_RANDOMTEST", "5");

define("INVITATION_OFF",0);
define("INVITATION_ON",1);

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
define ("TYPE_XLS_PC", "latin1");
define ("TYPE_SPSS", "csv");

?>
