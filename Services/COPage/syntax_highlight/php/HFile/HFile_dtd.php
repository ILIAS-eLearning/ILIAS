<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_dtd extends HFile{
   function HFile_dtd(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// DTD
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "@", "$", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "{", "}", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("<!--");
$this->blockcommentoff   	= array("-->");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"<!ATTLIST" => "1", 
			"<!DOCTYPE" => "1", 
			"<!ELEMENT" => "1", 
			"<!ENTITY" => "1", 
			"|" => "1", 
			">" => "1", 
			"(" => "1", 
			")" => "1", 
			"%;" => "2", 
			"#FIXED" => "3", 
			"#FIXED>" => "3", 
			"#IMPLIED" => "3", 
			"#IMPLIED>" => "3", 
			"#PCDATA" => "3", 
			"#REQUIRED" => "3", 
			"#REQUIRED>" => "3", 
			"CDATA" => "4", 
			"ENTITY" => "4", 
			"ENTITIES" => "4", 
			"ID" => "4", 
			"IDREF" => "4", 
			"IDREFS" => "4", 
			"NMTOKEN" => "4", 
			"NMTOKENS" => "4", 
			"NOTATION" => "4", 
			"EMPTY" => "5", 
			"EMPTY>" => "5");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
