<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_logic extends HFile{
   function HFile_logic(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// LOGIC
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

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", "	", ",", ".", "?", "/", "	");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array(";");
$this->blockcommentoff   	= array(";");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"BOOLEAN-EQUATIONS" => "1", 
			"end" => "1", 
			"flow-table" => "1", 
			"function-table" => "1", 
			"IDENTIFICATION" => "1", 
			"pal" => "1", 
			"pins" => "1", 
			"RUN-CONTROL" => "1", 
			"special-functions" => "1", 
			"STATE-ASSIGNMENT" => "1", 
			"X-NAMES" => "1", 
			"Y-NAMES" => "1", 
			"*" => "2", 
			"binary" => "3", 
			"listing" => "3", 
			"PROGFORMAT" => "3", 
			"Type" => "3", 
			"EQUATIONS" => "4", 
			"Relevant" => "4", 
			"FUSE" => "4", 
			"GA22V10" => "4", 
			"JEDEC" => "4", 
			"PinOut" => "4");

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
