<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_rtf extends HFile{
   function HFile_rtf(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Rich Text Format
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("#", "$", "%", "&", "(", ")", "+", ",", "-", ".", " ", "/", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", ":", ";", "<", "=", ">", "[", "\\", "]", "^", "_", "{", "|", "}", "~", "`");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"\\ansi" => "1", 
			"\\author" => "1", 
			"\\b" => "1", 
			"\\company" => "1", 
			"\\deff" => "1", 
			"\\f" => "1", 
			"\\fmodern" => "1", 
			"\\fnil" => "1", 
			"\\fonttbl" => "1", 
			"\\froman" => "1", 
			"\\fswiss" => "1", 
			"\\ftech" => "1", 
			"\\i" => "1", 
			"\\info" => "1", 
			"\\operator" => "1", 
			"\\par" => "1", 
			"\\pard" => "1", 
			"\\qc" => "1", 
			"\\qj" => "1", 
			"\\ql" => "1", 
			"\\qr" => "1", 
			"\\rtf" => "1", 
			"\\title" => "1", 
			"\\ul" => "1", 
			"\\*" => "2", 
			"\\fs24" => "3", 
			"\\margl" => "3", 
			"\\margr" => "3", 
			"\\sa" => "3", 
			"\\sb" => "3");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
