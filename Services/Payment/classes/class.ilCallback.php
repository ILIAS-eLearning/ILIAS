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

$debug = true;
$file = null;

chdir(dirname(__FILE__));
chdir('../../..');

require_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

function wlog($txt)
{
  global $file;
  global $debug;
  if ($debug)
  fwrite($file, $txt);
}

function opnLog()
{
  global $file;
  $file = fopen("callback.txt", "a");
  wlog( "--- " . date(DATE_RFC822) . " --- \n");
}

if ($debug) opnLog();

$usr_id = $_REQUEST['ilUser'];

try
{
  include_once './include/inc.header.php';

  include_once './payment/classes/class.ilPaymentObject.php';
  include_once './payment/classes/class.ilPaymentBookings.php';
  require_once './payment/classes/class.ilPaymentShoppingCart.php';
  require_once './Services/User/classes/class.ilObjUser.php';

  //global $ilLog;
  global $ilias;

  require_once './Services/Payment/classes/class.ilERP.php';
  $active = ilERP::getActive();
  $cls = "ilERPDebtor_" . $active['erp_short']; 
  include_once './Services/Payment/classes/class.' . $cls. '.php';

  $ilUser = new ilObjUser($usr_id);
  
  wlog("Payment for user #" . $usr_id . " " . $ilUser->getFullname() . "\n");
  
  $cart = new ilPaymentShoppingCart($ilUser);
  $sc = $cart->getShoppingCart(PAY_METHOD_EPAY);
  
  wlog("Items in cart: " . count($sc) . "\n");   
  
  $deb = new $cls();

  if (!$deb->getDebtorByNumber($usr_id))
  {
    $deb->setAll( array(
      'number' => $usr_id,
      'name' => $ilUser->getFullName(),
      'email' => $ilUser->email,
      'address' => $ilUser->street,
      'postalcode' => $ilUser->zipcode,
      'city' => $ilUser->city,
      'country' => $ilUser->country,
      'phone' => $ilUser->phone_mobile)
    );
    $deb->createDebtor($usr_id);
    wlog("User created in e-conomic.\n");
  }
  else wlog("Existing e-conomic Debtor.\n");  
  
  $deb->createInvoice();  
  $products = array();
  foreach ($sc as $i)
  {    
    $pod = ilPaymentObject::_getObjectData($i['pobject_id']);
    $bo  =& new ilPaymentBookings($ilUser->getId());
    
    $product_name = $i['buchungstext'];
    $duration = $i['dauer'];
    $amount = $i['betrag'];
    
    // psc_id, pobject_id, obj_id, typ, betrag_string
    
    if (!($bo->getPayedStatus()) && ($bo->getAccessStatus()))
    {    
      $bo->setPayed(1);
      $bo->setAccess(1);
      $bo->update();
    }
    if ( $i['typ'] == 'crs')
    {
      include_once './Modules/Course/classes/class.ilCourseParticipants.php';
      $deb->createInvoiceLine( 0, $product_name . " (" . $duration. ")", 1, $amount );      
      $products[] = $product_name;
      $obj_id = ilObject::_lookupObjId($pod["ref_id"]);    
      $cp = ilCourseParticipants::_getInstanceByObjId($obj_id); 
      $cp->add($usr_id, IL_CRS_MEMBER);
      $cp->sendNotification($cp->NOTIFY_ACCEPT_SUBSCRIBER, $usr_id);
    }
    else
    {
      wlog("Type error exptcted crs but got '" . $i['typ'] . "'");
    }
  }
    
  $inv = $deb->bookInvoice();
  $invoice_number = $deb->getInvoiceNumber();
  //wlog("Invoice is #" . $invoice_number ."\n" );
  $attach = $deb->getInvoicePDF($inv);
  $deb->saveInvoice($attach, false);
  //wlog("Invoice is saved.\n");
  /*$deb->sendInvoice("Your invoice " . $invoice_number,
      $ilUser->getFullName() . ", \nYour invoice is attached this mail.",
      $ilUser->getEmail(),
      $attach,
      "Invoice-" . $invoice_number
  );*/
  $lng->loadLanguageModule('payment');
  $deb->sendInvoice($lng->txt('pay_order_paid_subject'), 
        $ilUser->getFullName() . ",\n" . 
          str_replace( '%products%', implode(",", $products), $lng->txt('pay_order_paid_body')) , 
        $ilUser->getEmail(), 
        $attach, $lng->txt('pays_invoice') ."-" . $invoice_number
  );
  wlog("Sent invoice " . $invoice_number . " with " . $produtcs . "\n");
  $cart->emptyShoppingCart();
}
catch (Exception $e)
{  
  wlog("EXCEPTION:\n" . $e->getMessage() . "\n");
  echo $e->getMessage();
}
echo "Done.";
if ($debug) fclose($file);
?>