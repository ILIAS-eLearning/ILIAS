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
* editor view
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";

$tpl = new ilTemplate("tpl.lo_edit_infoptions.html", false, false);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("information_abbr")."/".$lng->txt("options"));

$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_UID", $lng->txt("uid"));
$tpl->setVariable("TXT_AUTHORS", $lng->txt("authors"));
$tpl->setVariable("TXT_MEMBERS", $lng->txt("members"));
$tpl->setVariable("TXT_PUBLISHING_ORGANISATION", $lng->txt("publishing_organisation"));
$tpl->setVariable("TXT_LANGUAGE", $lng->txt("language"));
$tpl->setVariable("TXT_SUMMARY", $lng->txt("summary"));
$tpl->setVariable("TXT_KEYWORDS", $lng->txt("keywords"));
$tpl->setVariable("TXT_LO_CAT", $lng->txt("lo_categories"));
$tpl->setVariable("TXT_LEVEL", $lng->txt("level"));
$tpl->setVariable("TXT_STATUS", $lng->txt("status"));
$tpl->setVariable("TXT_PUB_DATE", $lng->txt("publication_date"));
$tpl->setVariable("TXT_PUB", $lng->txt("publication"));
$tpl->setVariable("TXT_LASTCHANGE", $lng->txt("last_change"));

$tpl->setVariable("TXT_FUNCTIONS", $lng->txt("functions"));
$tpl->setVariable("TXT_CHANGE_METADATA", $lng->txt("change_metadata"));
$tpl->setVariable("TXT_PRESENTATION_OPTIONS", $lng->txt("presentation_options"));
$tpl->setVariable("TXT_CHANGE_LO_INFO", $lng->txt("change_lo_info"));
$tpl->setVariable("TXT_ANNOUNCE_CHANGES", $lng->txt("announce_changes"));
$tpl->setVariable("TXT_ASSIGN_LO_FORUM", $lng->txt("assign_lo_forum"));

include "./include/inc.lo_buttons.php";

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>
