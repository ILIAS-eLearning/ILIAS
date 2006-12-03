<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_winrex extends HFile{
   function HFile_winrex(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// WinRexx
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("do");
$this->unindent          	= array("end");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", ".", "?", "/", " ", " ", " ", " ");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ABBREV" => "1", 
			"ABS" => "1", 
			"ADDRESS" => "1", 
			"ARG" => "1", 
			"BITAND" => "1", 
			"BITOR" => "1", 
			"BITXOR" => "1", 
			"B2X" => "1", 
			"CENTER" => "1", 
			"CHARIN" => "1", 
			"CHAROUT" => "1", 
			"CHARS" => "1", 
			"COMPARE" => "1", 
			"CONDITION" => "1", 
			"COPIES" => "1", 
			"C2D" => "1", 
			"C2X" => "1", 
			"DATATYPE" => "1", 
			"DATE" => "1", 
			"DELSTR" => "1", 
			"DELWORD" => "1", 
			"DIGITS" => "1", 
			"D2C" => "1", 
			"D2X" => "1", 
			"ERRORTEXT" => "1", 
			"FORM" => "1", 
			"FORMAT" => "1", 
			"FUZZ" => "1", 
			"filespec" => "1", 
			"INSERT" => "1", 
			"LASTPOS" => "1", 
			"LEFT" => "1", 
			"LENGTH" => "1", 
			"LINEIN" => "1", 
			"LINEOUT" => "1", 
			"LINES" => "1", 
			"MAX" => "1", 
			"MIN" => "1", 
			"nop" => "1", 
			"OVERLAY" => "1", 
			"POS" => "1", 
			"QUEUED" => "1", 
			"RANDOM" => "1", 
			"REVERSE" => "1", 
			"RIGHT" => "1", 
			"SIGN" => "1", 
			"SOURCELINE" => "1", 
			"SPACE" => "1", 
			"STREAM" => "1", 
			"STRIP" => "1", 
			"SUBSTR" => "1", 
			"SUBWORD" => "1", 
			"SYMBOL" => "1", 
			"TIME" => "1", 
			"TRACE" => "1", 
			"TRANSLATE" => "1", 
			"TRUNC" => "1", 
			"VALUE" => "1", 
			"VERIFY" => "1", 
			"WORD" => "1", 
			"WORDINDEX" => "1", 
			"WORDLENGTH" => "1", 
			"WORDPOS" => "1", 
			"WORDS" => "1", 
			"XRANGE" => "1", 
			"X2B" => "1", 
			"X2C" => "1", 
			"X2D" => "1", 
			"say" => "2", 
			"signal" => "2", 
			"call" => "3", 
			"do" => "3", 
			"end" => "3", 
			"exit" => "3", 
			"if" => "3", 
			"otherwise" => "3", 
			"select" => "3", 
			"then" => "3", 
			"when" => "3");

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
