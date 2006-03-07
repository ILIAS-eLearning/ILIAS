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

$script_name = basename($_SERVER["SCRIPT_NAME"]);


// new appointment
$inc_type = $script_name == "cal_edit_entry.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"cal_edit_entry.php?ts=".$chosents,$lng->txt("new_appointment"),
	ilFrameTargetInfo::_getFrame("MainContent"));

// day
$inc_type = $script_name == "cal_date.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"cal_date.php?ts=".$chosents,$lng->txt("day"),
	ilFrameTargetInfo::_getFrame("MainContent"));
// week
$inc_type = $script_name == "cal_week_overview.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"cal_week_overview.php?ts=.$chosents",$lng->txt("week"),
	ilFrameTargetInfo::_getFrame("MainContent"));

// month
$inc_type = $script_name == "cal_month_overview.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"cal_month_overview.php?ts=.$chosents",$lng->txt("month"),
	ilFrameTargetInfo::_getFrame("MainContent"));

// appointment list
$inc_type = $script_name == "cal_appointment_list.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"cal_appointment_list.php?ts=.$todays",$lng->txt("appointment_list"),
	ilFrameTargetInfo::_getFrame("MainContent"));
		  
for ( $i=0; $i<sizeof($inhalt1); $i++)
{
	if ($inhalt1[$i][1] != "")
	{	$tpl->setCurrentBlock("tab");
		$tpl->setVariable("TAB_TYPE",$inhalt1[$i][0]);
		$tpl->setVariable("TAB_LINK",$inhalt1[$i][1]);
		$tpl->setVariable("TAB_TEXT",$inhalt1[$i][2]);
		$tpl->setVariable("TAB_TARGET",$inhalt1[$i][3]);
		$tpl->parseCurrentBlock();
	}
}


?>
