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
* tabs for calendar
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias
*/

$tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

$script_name = basename($_SERVER["SCRIPT_FILENAME"]);


// new appointment
$tpl->setCurrentBlock("tab");
$tpl->setVariable("TAB_TYPE",$script_name == "cal_edit_entry.php" ? "tabactive" : "tabinactive");
$tpl->setVariable("TAB_LINK","cal_edit_entry.php?ts=".$chosents);
$tpl->setVariable("TAB_TEXT",$lng->txt("new_appointment"));
$tpl->setVariable("TAB_TARGET","bottom");
$tpl->parseCurrentBlock();

// day
$tpl->setCurrentBlock("tab");
$tpl->setVariable("TAB_TYPE",$script_name == "cal_date.php" ? "tabactive" : "tabinactive");
$tpl->setVariable("TAB_LINK","cal_date.php?ts=".$chosents);
$tpl->setVariable("TAB_TEXT",$lng->txt("day"));
$tpl->setVariable("TAB_TARGET","bottom");
$tpl->parseCurrentBlock();

// week
$tpl->setCurrentBlock("tab");
$tpl->setVariable("TAB_TYPE",$script_name == "cal_week_overview.php" ? "tabactive" : "tabinactive");
$tpl->setVariable("TAB_LINK","cal_week_overview.php?ts=".$chosents);
$tpl->setVariable("TAB_TEXT",$lng->txt("week"));
$tpl->setVariable("TAB_TARGET","bottom");
$tpl->parseCurrentBlock();

// week
$tpl->setCurrentBlock("tab");
$tpl->setVariable("TAB_TYPE",$script_name == "cal_month_overview.php" ? "tabactive" : "tabinactive");
$tpl->setVariable("TAB_LINK","cal_month_overview.php?ts=".$chosents);
$tpl->setVariable("TAB_TEXT",$lng->txt("month"));
$tpl->setVariable("TAB_TARGET","bottom");
$tpl->parseCurrentBlock();

// appointment list
$tpl->setCurrentBlock("tab");
$tpl->setVariable("TAB_TYPE",$script_name == "cal_appointment_list.php" ? "tabactive" : "tabinactive");
$tpl->setVariable("TAB_LINK","cal_appointment_list.php?ts=".$todayts);
$tpl->setVariable("TAB_TEXT",$lng->txt("appointment_list"));
$tpl->setVariable("TAB_TARGET","bottom");
$tpl->parseCurrentBlock();

?>
