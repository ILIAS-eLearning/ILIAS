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
* buttons for personaldesktop
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias
*/

$tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

$script_name = basename($_SERVER["SCRIPT_NAME"]);

$command = $_GET["cmd"] ? $_GET["cmd"] : "";

if (ereg("whois",$command) or $script_name == "profile.php")
{
	$who_is_online = true;
}


// personal desktop home
$inc_type = (strtolower($_GET["baseClass"]) == "ilpersonaldesktopgui" &&
	(strtolower($_GET["cmdClass"]) == "ilpersonaldesktopgui" ||
	$_GET["cmdClass"] == ""))
	? "tabactive"
	: "tabinactive";
$inhalt1[] = array($inc_type, "ilias.php?baseClass=ilPersonalDesktopGUI", $lng->txt("overview"),
	ilFrameTargetInfo::_getFrame("MainContent"),"usr_pdesktop_menu.php?cmd=highest_level","left");

// workaround for calendar, normally this include file should not be used anymore
$inc_type = $script_name == "usr_profile.php"
	? "tabactive"
	: "tabinactive";
$inhalt1[] = array($inc_type , "ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToProfile",$lng->txt("personal_profile"));

// news
$inc_type = "tabinactive";
$inhalt1[] = array($inc_type,"ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToNews",$lng->txt("news"),
	ilFrameTargetInfo::_getFrame("MainContent"),"usr_pdesktop_menu.php?cmd=highest_level","left");

if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
{
	// user calendar
	if ($ilias->getSetting("enable_calendar"))
	{
		$inc_type = ($script_name == "dateplaner.php")
			? "tabactive"
			: "tabinactive";
		$inhalt1[] = array($inc_type,"dateplaner.php",$lng->txt("calendar"),
			ilFrameTargetInfo::_getFrame("MainContent"),"usr_pdesktop_menu.php?cmd=highest_level","left");
	}

/*	// user agreement
	$inc_type = $script_name == "usr_agreement.php" ? "tabactive" : "tabinactive";
	//$inhalt1[] = array($inc_type,"usr_agreement.php",$lng->txt("usr_agreement"),"bottom","usr_pdesktop_menu.php?cmd=highest_level","left");
*/
	// private notes
	$inc_type = "tabinactive";
	$inhalt1[] = array($inc_type,"ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToNotes",$lng->txt("private_notes"),
		ilFrameTargetInfo::_getFrame("MainContent"),"usr_pdesktop_menu.php?cmd=highest_level","left");

	// user bookmarks
	$inc_type = ($script_name == "usr_bookmarks.php")
		? "tabactive"
		: "tabinactive";
	$inhalt1[] = array($inc_type,"ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToBookmarks",$lng->txt("bookmarks"),
		ilFrameTargetInfo::_getFrame("MainContent"),"usr_pdesktop_menu.php?cmd=highest_level","left");


	include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
	if (ilObjUserTracking::_enabledLearningProgress())
	{
		// learning progress
		$inc_type = "tabinactive";
		$inhalt1[] = array($inc_type,"ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToLP",$lng->txt("learning_progress"),
					   ilFrameTargetInfo::_getFrame("MainContent")
					   ,"usr_pdesktop_menu.php?cmd=highest_level","left");
	}
}


include_once "./payment/classes/class.ilPaymentVendors.php";
include_once "./payment/classes/class.ilPaymentTrustees.php";
include_once "./payment/classes/class.ilPaymentShoppingCart.php";
include_once "./payment/classes/class.ilPaymentBookings.php";

global $ilias;

if(ilPaymentShoppingCart::_hasEntries($ilias->account->getId()) or
   ilPaymentBookings::_getCountBookingsByCustomer($ilias->account->getId()))
									  
{
	$lng->loadLanguageModule('payment');

	$inhalt1[] = array('tabinactive',"./payment.php",$lng->txt('paya_shopping_cart'),
		ilFrameTargetInfo::_getFrame("MainContent"));
}	
if(ilPaymentVendors::_isVendor($ilias->account->getId()) or
   ilPaymentTrustees::_hasAccess($ilias->account->getId()))
{
	$lng->loadLanguageModule('payment');

	$inhalt1[] = array('tabinactive',"./payment.php?view=payment_admin",$lng->txt('paya_header'),
		ilFrameTargetInfo::_getFrame("MainContent"));
}

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

$tpl->setCurrentBlock("tabs");
$tpl->parseCurrentBlock();

//$tpl->setVariable("TABS", "KK");
?>
