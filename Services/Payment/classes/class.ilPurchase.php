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
* Class ilPurchase
* @author Jesper Gødvad <jesper@ilias.dk>
*
*/

require_once './include/inc.header.php';
require_once './payment/classes/class.ilPaymentObject.php';
require_once './payment/classes/class.ilPaymentBookings.php';
require_once './payment/classes/class.ilPaymentShoppingCart.php';
require_once './Services/User/classes/class.ilObjUser.php';
require_once './Services/Payment/classes/class.ilERP.php';



class ilPurchase
{
  private $deb;
  private $ilUser;
  private $active_erp;
  private $cart;
  private $sc;
  private $pay_type;
  
  public function __construct( $usr_id, $pay_type )
  {
    $this->ilUser = new ilObjUser($usr_id);    
    $this->pay_type = $pay_type;    
    $this->active_erp = ilERP::getActive();
    $this->erp_cls = "ilERPDebtor_" . $this->active_erp['erp_short']; 
    require_once './Services/Payment/classes/class.' . $this->erp_cls. '.php';
    
    $this->deb = new $this->erp_cls();
    $this->cart = new ilPaymentShoppingCart( $this->ilUser );
    $this->sc = $this->cart->getShoppingCart( $pay_type );
    
    if (! $this->sc) throw new ilERPException("EmptyCart");
  }
    

  
  private function getDebtor()
  {
    if (!$this->deb->getDebtorByNumber($this->ilUser->getId()))
    {
      $this->deb->setAll( array(
        'number' => $this->ilUser->getId(),
        'name' => $this->ilUser->getFullName(),
        'email' => $this->ilUser->email,
        'address' => $this->ilUser->street,
        'postalcode' => $this->ilUser->zipcode,
        'city' => $this->ilUser->city,
        'country' => $this->ilUser->country,
        'phone' => $this->ilUser->phone_mobile)
      );
      $this->deb->createDebtor($this->ilUser->getId());
    }
  }
  
  public function purchase($tid)
  {
    global $lng;
    $this->getDebtor();    
    $this->deb->createInvoice();
    $products = array();
    foreach ($this->sc as $i)
    {
      $pod = ilPaymentObject::_getObjectData($i['pobject_id']);
      $bo =& new ilPaymentBookings( $this->ilUser->getId());
      
      $ilias_tid = $this->ilUser->getId() . "_" . $tid;
      
      // psc_id, pobject_id, obj_id, typ, betrag_string        
      $bo->setTransaction($ilias_tid);
      $bo->setPobjectId( isset($i['pobject_id']) ? $i['pobject_id'] : 0 );
      $bo->setCustomerId( $this->ilUser->getId() );
      $bo->setVendorId( $pod['vendor_id'] );
      $bo->setPayMethod($this->paytype);
      $bo->setOrderDate(time());
      $bo->setDuration($i['dauer']); // duration
      $bo->setPrice(  $i['betrag'] ); // amount
      //$bo->setPrice( ilPaymentPrices::_getPriceString( $i['price_id'] ));
      $bo->setDiscount(0);
      $bo->setVoucher('');
      $bo->setVatRate( $i['vat_rate'] );
      $bo->setVatUnit( $i['vat_unit'] );
            
      $bo->setTransactionExtern($tid);
      $product_name = $i['buchungstext'];
      $duration = $i['dauer'];
      $amount = $i['betrag'];      
      
      include_once './payment/classes/class.ilPayMethods.php';
      $save_adr = (int) ilPaymethods::_enabled('save_user_adr_epay') ? 1 : 0;
      //if($save_adr == 1)
      //{
        $bo->setStreet($this->ilUser->getStreet(), '');
        $bo->setPoBox('');//$this->ilUser->);
        $bo->setZipcode($this->ilUser->getZipcode());
        $bo->setCity($this->ilUser->getCity);
        $bo->setCountry($this->ilUser->getCountry());
      //}           
    
      $bo->setPayed(1);
      $bo->setAccess(1);
      $boid = $bo->add();
      //$bo->update();
      
      if ( $i['typ'] == 'crs')
      {
        include_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $this->deb->createInvoiceLine( 0, $product_name . " (" . $duration. ")", 1, $amount );      
        $products[] = $product_name;
        $obj_id = ilObject::_lookupObjId($pod["ref_id"]);    
        $cp = ilCourseParticipants::_getInstanceByObjId($obj_id); 
        $cp->add($this->ilUser->getId(), IL_CRS_MEMBER);
        $cp->sendNotification($cp->NOTIFY_ACCEPT_SUBSCRIBER, $this->ilUser->getId());
      }
    }   
    $inv = $this->deb->bookInvoice();
    $invoice_number = $this->deb->getInvoiceNumber();

    $attach = $this->deb->getInvoicePDF($inv);
    $this->deb->saveInvoice($attach, false);
    $lng->loadLanguageModule('payment');
    $this->deb->sendInvoice($lng->txt('pay_order_paid_subject'), 
          $this->ilUser->getFullName() . ",\n" . 
            str_replace( '%products%', implode(", ", $products), $lng->txt('pay_order_paid_body')) , 
          $this->ilUser->getEmail(), 
          $attach, $lng->txt('pays_invoice') ."-" . $invoice_number
    );
    $this->cart->emptyShoppingCart();   
  }
}  
?>