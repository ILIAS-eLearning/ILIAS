<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
*  Sry... need to commit this to test it *
*/



chdir(dirname(__FILE__));
chdir('../../..');

require_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_COOKIE["ilClientId"] = $_SERVER['argv'][3];
$_POST['username'] = $_SERVER['argv'][1];
$_POST['password'] = $_SERVER['argv'][2];

$_REQUEST

$pay_user = $_REQUEST['usr'];

include_once './include/inc.header.php';

include_once './payment/classes/class.ilPaymentObject.php';
include_once './payment/classes/class.ilPaymentBookings.php';

global $ilLog;
global $ilias;

require_once 'class.ilERP.php';

$active = ilERP::getActive();
$cls = "ilERPDebtor_" . $active['erp_short']; 
include_once './Services/Payment/classes/class.' . $cls. '.php';

    $f = fopen("callback.txt", "a");
    fwrite( $f, "Callback:");
    fwrite( $f, print_r($_REQUEST, true));
    fwrite( $f, print_r($active, true));
    
    $bo  =& new ilPaymentBookings($ilUser->getId());
    
    fclose($f);
?>