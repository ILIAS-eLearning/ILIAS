<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_directives extends HFile{
   function HFile_directives(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Directives file
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("\"");
$this->delimiters        	= array("\"", ",", "	", ".", " ", "?", ";");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ACTIONS" => "1", 
			"ALL" => "1", 
			"DELETE" => "1", 
			"END_TO" => "1", 
			"EXPORT" => "1", 
			"EXPORT_REF" => "1", 
			"GROUP" => "1", 
			"NO_EXPORT" => "1", 
			"OBJECT" => "1", 
			"STOP_IF" => "1", 
			"THROUGH" => "1", 
			"TO" => "1", 
			"UNIQUE_FIELD" => "1", 
			"UNIQUE_RELATION" => "1", 
			"WHERE" => "1", 
			"=" => "2", 
			"::" => "2", 
			";" => "2", 
			"," => "2");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
