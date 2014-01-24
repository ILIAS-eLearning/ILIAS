<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_VerityTopics extends HFile{
   function HFile_VerityTopics(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Verity Topic File
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "<", ">", "\"", "'", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"<ACCRUE>" => "1", 
			"<ALL>" => "1", 
			"<AND>" => "1", 
			"<ANY>" => "1", 
			"<CONTAINS>" => "1", 
			"<ENDS>" => "1", 
			"<FILTER>" => "1", 
			"<FULLTEXT>" => "1", 
			"<IN>" => "1", 
			"<MATCHES>" => "1", 
			"<NEAR>" => "1", 
			"<OR>" => "1", 
			"<PARAGRAPH>" => "1", 
			"<PHRASE>" => "1", 
			"<PRODUCT>" => "1", 
			"<SENTENCE>" => "1", 
			"<SOUNDEX>" => "1", 
			"<STARTS>" => "1", 
			"<STEM>" => "1", 
			"<SUBSTRING>" => "1", 
			"<THESAURUS>" => "1", 
			"<WHERE>" => "1", 
			"<WILDCARD>" => "1", 
			"<WORD>" => "1", 
			"<YESNO>" => "1", 
			"<CASE>" => "2", 
			"<MANY>" => "2", 
			"<NOT>" => "2", 
			"<ORDER>" => "2", 
			"*" => "3", 
			"?" => "3", 
			"definition" => "4", 
			"wordtext" => "4", 
			"zonespec" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
