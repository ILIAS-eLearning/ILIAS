<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_ldif extends HFile{
   function HFile_ldif(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// LDIF for Netscape Directory Server
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("\"", ",", ".", ":", ";", "{", " ", "}", "=", "\"");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"aci" => "1", 
			"audio" => "1", 
			"binary" => "1", 
			"businesscategory" => "1", 
			"carlicense" => "1", 
			"changetype" => "1", 
			"cn" => "1", 
			"commonname" => "1", 
			"departmentNumber" => "1", 
			"description" => "1", 
			"dn" => "1", 
			"employeenumber" => "1", 
			"employeeType" => "1", 
			"facsimileTelephoneNumber" => "1", 
			"givenname" => "1", 
			"homePhone" => "1", 
			"homePostalAddress" => "1", 
			"initials" => "1", 
			"internationalIsdnNumber" => "1", 
			"jpegPhoto" => "1", 
			"l" => "1", 
			"labeledURI" => "1", 
			"locality" => "1", 
			"mail" => "1", 
			"mailalternateaddress" => "1", 
			"maildeliveryoption" => "1", 
			"mailhost" => "1", 
			"manager" => "1", 
			"mobile" => "1", 
			"objectclass" => "1", 
			"pager" => "1", 
			"photo" => "1", 
			"postOfficeBox" => "1", 
			"preferredDeliveryMethod" => "1", 
			"preferredLanguage" => "1", 
			"physicaldeliveryofficename" => "1", 
			"postaladdress" => "1", 
			"postalcode" => "1", 
			"registeredAddress" => "1", 
			"roleOccupant" => "1", 
			"roomnumber" => "1", 
			"secretary" => "1", 
			"seeAlso" => "1", 
			"sn" => "1", 
			"st" => "1", 
			"street" => "1", 
			"surname" => "1", 
			"telephonenumber" => "1", 
			"title" => "1", 
			"uid" => "1", 
			"userpassword" => "1", 
			"userCertificate" => "1", 
			"userSMIMECertificate" => "1", 
			"x500UniqueIdentifier" => "1", 
			"add" => "2", 
			"c" => "2", 
			"dc" => "2", 
			"delete" => "2", 
			"modify" => "2", 
			"o" => "2", 
			"ou" => "2", 
			"crypt" => "4", 
			"inetOrgPerson" => "4", 
			"mailRecipient" => "4", 
			"nsLicenseUser" => "4", 
			"organizationalPerson" => "4", 
			"organizationalRole" => "4", 
			"residentialPerson" => "4", 
			"person" => "4", 
			"SHA" => "4", 
			"top" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
