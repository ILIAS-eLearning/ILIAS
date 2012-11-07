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


class ilERPDebtor
{

  protected $number;
  protected $name = '';
  protected $email = '';
  protected $address = '';
  protected $postalcode = '';
  protected $city = '';
  protected $country = '';
  protected $phone = '';
  protected $ean;
  protected $website;
  protected $invoice_booked = false;
  protected $invoice_number = 0;
  
  protected $handle; // debtor
  protected $dgh;
  
  const website = "http://www.ilias.dk";
  
  const senderEmail = "noreply@ilias.dk";
  const senderName  = "ILIAS ERP";
  
  
  
  protected function __construct()
  {     
  }
  
  public function getInvoiceNumber()
  {
    return $this->invoice_number;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getEmail()
  {
    return $this->email;
  }
  
  /**
  * Get all information about a Debtor
  *
  * @return mixed array with Debtor info
  */  
  public function getAll() 
  {    
    $a['number'] = $this->number;
    $a['name'] = $this->name;
    $a['email'] = $this->email;
    $a['address'] = $this->address;
    $a['postalcode'] = $this->postalcode;
    $a['city'] = $this->city;
    $a['country'] = $this->country;
    $a['ean'] = $this->ean;
    $a['website'] = $this->website;
    $a['dgh'] = $this->dgh;
    $a['phone'] = $this->phone;
    return $a;
  }
  
   
  /**
  * Set information about a Debtor
  */
  public function setAll($values)
  {
    foreach ($values as $key => $value)
    $this->$key = $value;
  }
  
  
  
  
  
  public function saveInvoice($contens, $preview=true)
  {
    $file = ($preview) ? ilERP::getPreviewFile() : ilERP::getSaveDirectory() . $this->getInvoiceNumber(). ".pdf";
    $fp = @fopen( $file, 'w+' );
    if (!$fp)
    {
      throw new ilERPException("Cannot write " . $file);
    }    
    fwrite($fp, $contens);
    fclose($fp);  
  }

      
  public function sendInvoice($subject, $message, $to = null, $bytes, $fname = "faktura")
  {

    $content = chunk_split(base64_encode($bytes));

    if (!isset($to)) $to = $this->email;
    $filename = $fname . ".pdf";    
        
    $uid = md5(uniqid(time()));

    $header = "From: " . ilERPDebtor::senderName . " <". ilERPDebtor::senderEmail.">\r\n";
    $header .= "Reply-To: " . ilERPDebtor::senderEmail . "\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
    $header .= "This is a multi-part message in MIME format.\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-type:text/plain; charset=utf-8\r\n";
    $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $header .= $message."\r\n\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use diff. tyoes here
    $header .= "Content-Transfer-Encoding: base64\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
    $header .= $content."\r\n\r\n";
    $header .= "--".$uid."--";
    
    /*
    include_once './Services/Mail/classes/class.ilMimeMail.php';
    $mail = new ilMimeMail;
    $mail->autoCheck(true);
    $mail->Subject($subject);
    $mail->From(ilERPDebtor::senderName . " <". ilERPDebtor::senderEmail.">");
    $mail->ReplyTo(ilERPDebtor::senderEmail);
    $mail->To($to);
    $mail->Body("Mail body");
    $mail->Attach(
    
    $mail->Send();*/
    
    $restore_me = ini_get('display_errors');
    ini_set('display_errors', 0);
    mail($to, $subject, "", $header);
    ini_set('display_errors', $restore_me);
    
  }
  
  /**
  * Generate random values to test integration with ERP
  **/  
  public function setTestValues()
  {
    $fname = array("Jesper", "Nicolai", "Alex", "Stefan", "Helmut", "Elvis");
    $lname = array("Gødvad", "Lundgaard", "Killing", "Meyer", "Schottmüller", "Presly");
    $city  = array("Copenhagen", "Århus", "Collonge", "Bremen", "SecretPlace" );
    $country = array("Denmark", "Germany", "France", "Ümlaudia", "Graceland");
    $road = array(" Straße", " Road", "vej", " Boulevard");
    
    $this->number = rand(1000,1010);
    $this->name   = $fname[rand(0,5)] . " " . $lname[rand(0,5)];
    $this->email  = "noreply@ilias.dk";
    $this->address= "Ilias" . $road[rand(0,3)] ." " . rand(1,100);
    $this->postalcode = rand(2000,7000);
    $this->city = $city[rand(0,3)];
    $this->country = $country[rand(0,4)];
    $this->phone = "+" . rand(1,45) . " " . rand(100,999) . " " . rand(1000, 9999);
    $this->ean = 0;
    $this->website = ilERPDebtor::website;        
  }

}


?>