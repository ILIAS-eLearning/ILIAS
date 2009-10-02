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
  private $invH;
  
  /**
  * Retrive or create Debtor
  */
  public function __construct()  
  {
    
    $this->erp = ilERP_eco::_getInstance();
    $this->set = $this->erp->getSettings();        
    
    parent::__construct();
    
    //$this->dgh = $dgh;
  }
  
  public function getLastError()
  {
    return $this->erp->getLastError();
  }
  public function setError($something)
  { 
    $this->erp->setError($something);
  }
  public function error()
  {
    return $this->erp->error();
  }
  
  
  /*private function assertConnected()
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
  */
  
  /**
  * Return true if debtor found in e-conomic and set handle to that debtor
  * @return bool 
  */  
  public function getDebtorByNumber($number)
  {
    $this->handle = null;
    if ($this->erp->connect())
    {       
      $this->handle = $this->erp->client->Debtor_FindByNumber(array('number' => $number))->Debtor_FindByNumberResult;
      return true;
    }
    return false;
  }
  
  
  public function setDebtorGroup($number=1)
  {
    $this->dgh = $this->erp->client_debtorGroup_FindByNumber(
      array('number' => $number))->DebtorGroup_FindByNumberResult;
  }
  
  
  
  /**
  * Create a new debtor in e-conomic and set handle to that debtor
  * @return bool if creation was successfull
  */    
  public function createDebtor($number)  
  {
    //if (($number != 0) && (!$this->erp->error()))
    //{
      $cust = $this->getAll();
    
      $deb = new stdClass();      
      $deb->Number = $number;
      if (!isset($this->dgh)) $this->setDebtorGroup(); // cheating
      $deb->DebtorGroupHandle = $this->dgh;
      
      $deb->Name = $cust['name'];
      $deb->Email = $cust['email'];
      $deb->Address = $cust['address'];
      $deb->PostalCode = $cust['postalcode'];
      $deb->City = $cust['city'];
      $deb->Country = $cust['country'];
      $deb->TelephoneAndFaxNumber = $cust['phone'];
      $deb->Website = $this['website'];
            
      $deb->VatZone = "HomeCountry";
      $deb->IsAccessible = true;
      
      $deb->CurrencyHandle = new stdClass();
      $deb->CurrencyHandle->Code = $this->set['code'];
      
      $deb->TermOfPaymentHandle = new stdClass();
      $deb->TermofPaymentHandle->Id = $this->set['terms'];
      
      $deb->LayoutHandle = new stdClass();
      $deb->LayoutHandle->Id = $this->set['layout'];

    
      try
      {
        $this->handle = $this->erp->client->Debtor_CreateFromData(array('data' => $deb))->Debtor_CreateFromDataResult;
        
      }
      catch (Exception $e)
      {
        $this->setError("(e-conomic creating debtor) " . $e->getMessage());
        return false;
      }
      return true;
    //}
    //return false;
  }
  
  public function getDebtorGroup()
  {
    $this->dgh = $this->erp->client->debtorGroup_FindByNumber(array('number' => 1))->DebtorGroup_FindByNumberResult;
  }
  
  
  
  /**
  * Set EAN number on debtor. Return true if success
  */  
  public function setEAN($ean)
  {
    if (!strlen($ean)==13)    
    $this->setError("(cannot set EAN number. Must be 13 digits not '" . $ean .".");
    else      
    try 
    {
      $this->erp->client->Debtor_SetEan(array('debtorHandle' => $this->handle, 'value' => $ean));
      return true;
    }
    catch (Exception $e)
    {
      $this->setError("(setEan " . $ean . ") " . $e->getMessage());      
    }
    return false;    
  }
  
  /**
  * Create invoice
  */  
  public function createInvoice()
  {
      
    try
    {            
      $this->invH = $this->erp->client->CurrentInvoice_Create(array('debtorHandle' => $this->handle))->CurrentInvoice_CreateResult;
      
      $this->erp->client->CurrentInvoice_SetIsVatIncluded( 
        array('currentInvoiceHandle' => $this->invH, 'value' => 0));     
    }
    catch (Exception $e)
    {
      $this->erp->setError("(createInvoice) " . $e->getMessage());
    }
  }
  
  /**
  * Create a line on a invoice
  * Don't care about product currently
  *
  */
  public function createInvoiceLine( $product, $desc, $quantity, $unetprice )
  {
    if (!isset($this->invH)) $this->setError("(createInvoiceLine) InvoiceHandle not set.");
    else 
    try
    {
      $productH = $this->getProduct( $this->set['product'] );
      $lineH = $this->erp->client->CurrentInvoiceLine_Create(
        array('invoiceHandle' => $this->invH ))->CurrentInvoiceLine_CreateResult;
        
      $this->erp->client->CurrentInvoiceLine_SetProduct(
        array('currentInvoiceLineHandle' => $lineH, 'valueHandle' => $productH ));
      $this->erp->client->CurrentInvoiceLine_SetDescription(
        array('currentInvoiceLineHandle' => $lineH, 'value' => $desc ));
      $this->erp->client->CurrentInvoiceLine_SetQuantity(
        array('currentInvoiceLineHandle' => $lineH, 'value' => (float) $quantity ));
      $this->erp->client->CurrentInvoiceLine_UnitNetPrice(
        array('currentInvoiceLineHandle' => $lineH, 'value' => $unetprice )); 
    }
    catch (Exception $e)
    {
      $this->erp->setError("(createInvoiceLine) " . $e->getMessage());            
    }
  }
  
  /**
  * Finish the invoice.
  * Return a handle to it.
  */
  public function bookInvoice()
  {
    try
    {
      return $this->erp->client->CurrentInvoice_Book(
        array('currentInvoiceHandle' => $this->invH))->CurrentInvoice_BookResult();
    }
    catch (Exception $e)
    {
      $this->setError("(bookInvoice) " . $e->getMessage());
    }
  }
  
  private function getProduct($product = null)
  {
    $product = $this->set['product'];
    try
    {
      return $this->erp->client->Product_FindByNumber(array('number' => $product))->Product_FindByNumberResult();
    }
    catch (Exception $e)
    {
      $this->erp->setError("(getProduct) " . $e->getMessage());    
    }    
  }  
  
  
  
  public function getInvoiceNumber()
  {
  
  }
  
  public function getInvoicePDF($ih)
  {
    //$this->assertConnected();
    
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