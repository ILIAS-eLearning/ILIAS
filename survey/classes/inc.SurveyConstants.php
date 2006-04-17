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
* Survey constants
* 
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
*/

/**
* Survey question import/export identifiers
*/
define("METRIC_QUESTION_IDENTIFIER", "Metric Question");
define("NOMINAL_QUESTION_IDENTIFIER", "Nominal Question");
define("ORDINAL_QUESTION_IDENTIFIER", "Ordinal Question");
define("TEXT_QUESTION_IDENTIFIER", "Text Question");

/**
* Survey object constants
*/
define("STATUS_OFFLINE", 0);
define("STATUS_ONLINE", 1);

define("EVALUATION_ACCESS_OFF", 0);
define("EVALUATION_ACCESS_ALL", 1);
define("EVALUATION_ACCESS_PARTICIPANTS", 2);

define("INVITATION_OFF", 0);
define("INVITATION_ON", 1);

define("MODE_UNLIMITED", 0);
define("MODE_PREDEFINED_USERS", 1);

define("SURVEY_START_ALLOWED", 0);
define("SURVEY_START_START_DATE_NOT_REACHED", 1);
define("SURVEY_START_END_DATE_REACHED", 2);
define("SURVEY_START_OFFLINE", 3);

define("ANONYMIZE_OFF", 0);
define("ANONYMIZE_ON", 1);
define("ANONYMIZE_FREEACCESS", 2);

define("QUESTIONTITLES_HIDDEN", 0);
define("QUESTIONTITLES_VISIBLE", 1);

/**
* File export constants
*/
define("TYPE_XLS", "latin1");
define("TYPE_SPSS", "csv");
define("TYPE_PRINT", "prnt");

/**
* Search constants
*/
define("CONCAT_AND", 0);
define("CONCAT_OR", 1);

?>
