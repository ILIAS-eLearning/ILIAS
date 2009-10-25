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

$f = fopen("callback.txt", "a");
fwrite($f, "--- " . date(DATE_RFC822) . " --- \n");

$debug = true;

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
  //$ilUser->read();
  
  fwrite( $f, "Payment for user #" . $usr_id . " " . $ilUser->getFullname() . "\n.");
  
  $cart = new ilPaymentShoppingCart($ilUser);
  $sc = $cart->getShoppingCart(PAY_METHOD_EPAY);
  
  fwrite( $f, "Items in cart: " . count($sc) . "\n");   
  
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
    fwrite ($f, "User created in e-conomic.\n");
  }  
  
  $deb->createInvoice();  
  
  foreach ($sc as $i)
  {
    fwrite( $f, "cart item " . print_r($i, true) . "\n");
    $pod = ilPaymentObject::_getObjectData($i['pobject_id']);
    $bo  =& new ilPaymentBookings($ilUser->getId());
    
    if (!($bo->getPayedStatus()) && ($bo->getAccessStatus()))
    {    
      $bo->setPayed(1);
      $bo->setAccess(1);
      $bo->update();
    }
    if ( $i['typ'] == 'crs')
    {
      include_once './Modules/Course/classes/class.ilCourseParticipants.php';
      $deb->createInvoiceLine( 0, $product_name . " (" . $bo->getBookingId() . ")", 1, $amount );
      fwrite ($f, "invoice line: 0, " . $product_name . " " . $bo->getBookingId() );
      
      $obj_id = ilObject::_lookupObjId($pod["ref_id"]);    
      $cp = ilCourseParticipants::_getInstanceByObjId($obj_id); 
      $cp->add($usr_id, IL_CRS_MEMBER);
      $cp->sendNotification($cp->NOTIFY_ACCEPT_SUBSCRIBER, $usr_id);
    }
    else
    {
      fwrite($f, "Type error exptcted crs but got '" . $i['typ'] . "'");
    }
  }
    
  $invoice_number = $deb->bookInvoice();
  $attach = $deb->getInvoicePDF($invoice_number);
  $deb->saveInvoice($attach, false);
    
  $deb->sendInvoice($this->lng->txt('pay_order_paid_subject'), 
        $deb->getName() . ",\n" . $this->lng->txt('pays_erp_invoice_attached'), $ilUser->getEmail(), $attach, "faktura" );
  
  $cart->emptyShoppingCart();
}
catch (Exception $e)
{
  
  fwrite( $f, "EXCEPTION:\n" . $e->getMessage() . "\n");
  fwrite( $f, print_r($_REQUEST, true));
  //fwrite( $f, print_r($active, true));
  fclose($f);
  die($e->getMessage());
}
echo "Done.";
?>