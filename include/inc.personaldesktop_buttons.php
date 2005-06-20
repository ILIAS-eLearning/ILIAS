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
$inc_type = $script_name == "usr_personaldesktop.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type,"usr_personaldesktop.php",$lng->txt("overview"),"bottom","usr_pdesktop_menu.php?cmd=highest_level","left");

// user profile
$inc_type = $script_name == "usr_profile.php" ? "tabactive" : "tabinactive";
$inhalt1[] = array($inc_type ,"usr_profile.php",$lng->txt("personal_profile"),"bottom","usr_pdesktop_menu.php?cmd=highest_level","left");

if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
{
	// user calendar
	if ($ilias->getSetting("enable_calendar"))
	{
		$inc_type = ($script_name == "dateplaner.php")
			? "tabactive"
			: "tabinactive";
		$inhalt1[] = array($inc_type,"dateplaner.php",$lng->txt("calendar"),"bottom","usr_pdesktop_menu.php?cmd=highest_level","left");
	}

/*	// user agreement
	$inc_type = $script_name == "usr_agreement.php" ? "tabactive" : "tabinactive";
	$inhalt1[] = array($inc_type,"usr_agreement.php",$lng->txt("usr_agreement"),"bottom","usr_pdesktop_menu.php?cmd=highest_level","left");
*/
	// user bookmarks
	$inc_type = ($script_name == "usr_bookmarks.php")
		? "tabactive"
		: "tabinactive";
	$inhalt1[] = array($inc_type,"usr_bookmarks.php",$lng->txt("bookmarks"),"bottom","usr_pdesktop_menu.php?cmd=highest_level","left");

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
	$inhalt1[] = array('tabinactive',"./payment/payment.php",$lng->txt('paya_shopping_cart'),'bottom');
}	
if(ilPaymentVendors::_isVendor($ilias->account->getId()) or
   ilPaymentTrustees::_hasAccess($ilias->account->getId()))
{
	$lng->loadLanguageModule('payment');
	$inhalt1[] = array('tabinactive',"./payment/payment_admin.php",$lng->txt('paya_header'),'bottom');
}



include_once "./tracking/classes/class.ilUserTracking.php";
/*
$tracking = new ilUserTracking();
$lm = $tracking->searchTitle($_SESSION["AccountId"]);
if((count($lm) > 0) and (DEVMODE))
{
	$inc_type = ($script_name == "tracking.php")
		? "tabactive"
		: "tabinactive";
	$inhalt1[] = array($inc_type,"tracking.php",$lng->txt("usertracking"),"bottom","usr_pdesktop_menu.php?cmd=highest_level","left");
}*/

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
