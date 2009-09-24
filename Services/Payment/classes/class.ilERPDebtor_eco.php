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
* @author Jesper Godvad <jesper@ilias.dk>
* @author Nicolai Lundgaard <nicolai@ilias.dk>
* 
* 
* @ingroup payment
*/


require_once './Services/Payment/classes/class.ilERPDebtor.php';
require_once './payment/classes/class.ilERP_eco.php';

class ilERPDebtor_eco extends ilERPDebtor
{
  
  
  private $set;
  private $erp;
  
  
  /**
  * Retrive or create Debtor
  */
  public function __construct( 
    $number, $name="", $email="", 
    $address="", $postalcode="", $city="", $country="", $phone="", $ean="",
    $dgh=1 )
  {
    
    $this->erp = new ilERP_eco();
    $this->set = $this->erp->getSettings();        
    
    parent::__construct( $number, $name, $email, $address, $postalcode, $city, $country, $phone, $ean);
    
    $this->dgh = $dgh;
  }
  
  private function assertConnected()
  {
    if (!$this->erp->connected()) $this->erp->connect(); else return true;    
    if (!$this->erp->connected()) return false; //ilUtil::sendError($this->erp->getLastError());
  }
  
  private function connectOrFail()
  {
    if ($this->erp->connected()) return true;
    else
    $this->erp->connect();
    if ($this->erp->connected()) return true;
    require_once './Services/Payment/exceptions/class.ilERPException.php';    
    Throw new ilERPException( $this->erp->getLastError() );
  }
  
  
  /**
  * Return handle to debtor or null if not exist
  */  
  public function getDebtorByNumber($number)
  {
    $this->assertConnected();  
    if ($number != 0) 
    {
      $this->handle = $this->erp->client->Debtor_FindByNumber(array('number' => $number))->Debtor_FindByNumberResult;
    }
    else
    $this->handle = null;
    
    if ($this->handle) return true; else return false;
    
    //return $this->handle;    
  }
    
  
  public function createDebtor($number)  
  {    
    assert($number != 0);
    $this->assertConnected();    
  
    $o = new stdClass();
    $o->Number = $number;
    $this->getDebtorGroup();
    $o->DebtorGroupHandle = $this->dgh;
    $o->Name = $this->name;
    $o->VatZone = "HomeCountry";
    $o->Email = $this->email;
    $o->Address = $this->address;
    $o->PostalCode = $this->postalcode;
    $o->City = $this->city;
    $o->Country = $this->country;
    $o->TelephoneAndFaxNumber = $this->phone;
    $o->IsAccessible = true;
    $o->Website = $this->website;
    
    $o->CurrencyHandle = new stdClass();
    $o->CurrencyHandle->Code = $this->set['code'];
    $o->TermOfPaymentHandle = new stdClass();
    $o->TermOfPaymentHandle->Id = $this->set['terms']; //3
    $o->LayoutHandle = new stdClass();
    $o->LayoutHandle->Id = $this->set['layout']; // 9  
    
    try
    {
      $this->handle = $this->erp->client->Debtor_CreateFromData(array('data' => $o))->Debtor_CreateFromDataResult;
    }
    catch (Exception $e)
    {
      die( "Report this bug to Mantis. " . $e->getMessage());
    }
  }
  
  public function getDebtorGroup()
  {
    $this->dgh = $this->erp->client->debtorGroup_FindByNumber(array('number' => 1))->DebtorGroup_FindByNumberResult;
  }
  
  public function setEAN($ean)
  {
    assert(strlen($ean)==13);
    $this->erp->client->Debtor_SetEan(array('debtorHandle' => $this->handle, 'value' => $ean));
  }
  
  /**
  * Create invoice
  */
  
  public function createInvoice($amount, $desc, $pcs=1.0)
  {
    $this->connectOrFail();    
    assert($this->set['product'] == 100);
    $debug = array();
    
    try
    {
    
      $ph = $this->erp->client->Product_FindByNumber(array('number' => $this->set['product']))->Product_FindByNumberResult;
      $debug[]="Found product " . print_r($this->handle, true);
      assert($this->handle);
      $cih = $this->erp->client->CurrentInvoice_Create(array('debtorHandle' => $this->handle))->CurrentInvoice_CreateResult;
      $debug[]="Found debtor";
      $cilh = $this->erp->client->CurrentInvoiceLine_Create(array('invoiceHandle' => $cih))->CurrentInvoiceLine_CreateResult;
      $debug[]="Invoice handle";

      $this->erp->client->CurrentInvoiceLine_SetProduct(array('currentInvoiceLineHandle' => $cilh, 'valueHandle' => $ph));
      $this->erp->client->CurrentInvoiceLine_SetDescription(array('currentInvoiceLineHandle' => $cilh, 'value' => $desc));
      $this->erp->client->CurrentInvoiceLine_SetQuantity(array('currentInvoiceLineHandle' => $cilh, 'value' => (float) $pcs));
      $this->erp->client->CurrentInvoiceLine_SetUnitNetPrice(array('currentInvoiceLineHandle' => $cilh, 'value' => $amount));

      $this->erp->client->CurrentInvoice_SetIsVatIncluded(array('currentInvoiceHandle' => $cih, 'value' => 0));
      $inv = $this->erp->client->CurrentInvoice_Book(array('currentInvoiceHandle' => $cih))->CurrentInvoice_BookResult;    
    }
    catch (Exception $e)
    {
      die("Exception<br/>" . implode('<br/>', $debug) . "<br/>" . $e->getMessage());      
    }

    return $inv;
    
  }
  
  public function getInvoicePDF($ih)
  {
    $this->assertConnected();
    
    $bytes = $this->erp->client->Invoice_GetPdf( array('invoiceHandle' => $ih))->Invoice_GetPdfResult;
    $content = chunk_split(base64_encode($bytes));
    return $content;
    
  }
  
  
  function bookUser($number, $name, $email, $address, $postalcode, $city, $country, $phone, $amount, $desc, $ean = null)
  {    
    $frommail = "noreply@inetworks.dk";
    $fromname = "inetworks";
    $subject = "test";
    $message = "test test";
    
    if ($erp->connect())
    {
    
      try
      {
        $this->dgh = $this->client->debtorGroup_FindByNumber(array('number' => 1))->DebtorGroup_FindByNumberResult;
        $d = new ilERPDebtor_eco($dgh, $number, $name, $email, $address, $postalcode, $city, $country, $phone, $ean);
        $ih = $d->createInvoice($this->product, $amount, $desc);
        $d->sendInvoice($ih, $frommail, $fromname, $subject, $message);
      }
      catch (Exception $e)
      {
        header('HTTP/1.1 500 Internal Server Error');
        die($e->getMessage());
      }
      
    }
    else ilUtil::sendFailure(":-( connect");    
  }
}
 
?>