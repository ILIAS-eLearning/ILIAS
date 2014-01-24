<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_millg extends HFile{
   function HFile_millg(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Mill G Code
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"G40" => "1", 
			"G41" => "1", 
			"G42" => "1", 
			"G68" => "1", 
			"G69" => "1", 
			"G70" => "1", 
			"G71" => "1", 
			"G90" => "1", 
			"G91" => "1", 
			"G92" => "1", 
			"G93" => "1", 
			"G94" => "1", 
			"G99" => "1", 
			"G00" => "2", 
			"G04" => "2", 
			"G01" => "3", 
			"G02" => "4", 
			"G03" => "4", 
			"G17" => "5", 
			"G18" => "5", 
			"G19" => "5", 
			"M00" => "6", 
			"M01" => "6", 
			"M02" => "6", 
			"M03" => "6", 
			"M04" => "6", 
			"M05" => "6", 
			"M06" => "6", 
			"M07" => "6", 
			"M08" => "6", 
			"M09" => "6", 
			"M13" => "6", 
			"M14" => "6", 
			"M19" => "6", 
			"M26" => "6", 
			"M60" => "6", 
			"G80" => "7", 
			"G81" => "7", 
			"G82" => "7", 
			"G83" => "7", 
			"G84" => "7");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing", 
			"7" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
