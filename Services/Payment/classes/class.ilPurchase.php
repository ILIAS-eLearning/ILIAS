<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilPurchase
* @author Jesper G�dvad <jesper@ilias.dk>
* @version $Id: class.ilPurchase.php 
* 
*/

require_once './include/inc.header.php';
require_once './Services/Payment/classes/class.ilPaymentObject.php';
require_once './Services/Payment/classes/class.ilPaymentBookings.php';
require_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
require_once './Services/User/classes/class.ilObjUser.php';
require_once './Services/Payment/classes/class.ilERP.php';


// TODO: rename class to ilPurchaseERP
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
      $bo = new ilPaymentBookings( $this->ilUser->getId());
      
      $ilias_tid = $this->ilUser->getId() . "_" . $tid;
      
      // psc_id, pobject_id, obj_id, typ, betrag_string        
      $bo->setTransaction($ilias_tid);
      $bo->setPobjectId( isset($i['pobject_id']) ? $i['pobject_id'] : 0 );
      $bo->setCustomerId( $this->ilUser->getId() );
      $bo->setVendorId( $pod['vendor_id'] );
      $bo->setPayMethod($this->paytype);
      $bo->setOrderDate(time());
     // $bo->setDuration($i['dauer']); // duration
     // $bo->setPrice(  $i['betrag'] ); // amount
      //$bo->setPrice( ilPaymentPrices::_getPriceString( $i['price_id'] ));
		$bo->setDuration($i['duration']);  
		$bo->setPrice($i['price_string']);			
      
      $bo->setDiscount(0);
      $bo->setVoucher('');
      $bo->setVatRate( $i['vat_rate'] );
      $bo->setVatUnit( $i['vat_unit'] );
            
      $bo->setTransactionExtern($tid);
     // $product_name = $i['buchungstext'];
      //$duration = $i['dauer'];
      //$amount = $i['betrag'];      
       	$product_name = $i['object_title'];
		$duration = $i['duration'];
		$amount = $i['price']; // -> ? $i['price_string']    
		
      include_once './Services/Payment/classes/class.ilPayMethods.php';
      $save_adr = (int) ilPaymethods::_EnabledSaveUserAddress($this->paytype) ? 1 : 0;
      //if($save_adr == 1)
      //{
        $bo->setStreet($this->ilUser->getStreet(), '');
        $bo->setPoBox('');//$this->ilUser->);
        $bo->setZipcode($this->ilUser->getZipcode());
        $bo->setCity($this->ilUser->getCity());
        $bo->setCountry($this->ilUser->getCountry());
      //}           
    
      $bo->setPayed(1);
      $bo->setAccess(1);
	  $bo->setAccessExtension($this->sc['extension']);
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