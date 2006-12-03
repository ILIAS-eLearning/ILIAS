<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_systempolicies extends HFile{
   function HFile_systempolicies(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Systempolicies
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "$", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", ":", ";", "\"", "'", "<", ">", " ", ",", ".", "?", "/", "	");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"MACHINE" => "1", 
			"USER" => "1", 
			"[STRINGS]" => "2", 
			"CATEGORY" => "2", 
			"CLASS" => "2", 
			"END" => "2", 
			"PART" => "2", 
			"POLICY" => "2", 
			"ACTIONLISTOFF" => "3", 
			"ACTIONLISTON" => "3", 
			"CHECKBOX" => "3", 
			"COMBOBOX" => "3", 
			"DELETE" => "3", 
			"DISABLED" => "3", 
			"DROPDOWNLIST" => "3", 
			"EDITTEXT" => "3", 
			"ENABLED" => "3", 
			"KEYNAME" => "3", 
			"LISTBOX" => "3", 
			"NAME" => "3", 
			"NUMERIC" => "3", 
			"TEXT" => "3", 
			"VALUE" => "3", 
			"VALUENAME" => "3", 
			"VALUEOFF" => "3", 
			"VALUEON" => "3", 
			"ACTIONLIST" => "4", 
			"ADDITIVE" => "4", 
			"DEFAULT" => "4", 
			"DEFCHECKED" => "4", 
			"EXPANDABLETEXT" => "4", 
			"EXPLICITVALUE" => "4", 
			"ITEMLIST" => "4", 
			"MAX" => "4", 
			"MAXLEN" => "4", 
			"MIN" => "4", 
			"REQUIRED" => "4", 
			"SPIN" => "4", 
			"SUGGESTIONS" => "4", 
			"TXTCONVERT" => "4", 
			"VALUEPREFIX" => "4", 
			"#ENDIF" => "6", 
			"#IF" => "6", 
			"NOSORT" => "6", 
			"VERSION" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"6" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
