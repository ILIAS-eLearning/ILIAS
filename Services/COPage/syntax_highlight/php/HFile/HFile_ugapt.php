<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_ugapt extends HFile{
   function HFile_ugapt(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// UG/APT Source
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "brown", "blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("PPR");
$this->blockcommenton    	= array("$");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"CALL" => "1", 
			"CHOOSE" => "1", 
			"GPOS" => "1", 
			"IDENT" => "1", 
			"LOAD" => "1", 
			"MACRO" => "1", 
			"PARAM" => "1", 
			"TERMAC" => "1", 
			"TEXT" => "1", 
			"RAPID" => "2", 
			"AUXFUN" => "4", 
			"INSERT" => "4", 
			"IF" => "4", 
			"JUMP" => "4", 
			"MODE" => "4", 
			"PLABEL" => "4", 
			"POSTN" => "4", 
			"PREFUN" => "4", 
			"COPY" => "5", 
			"INDEX" => "5", 
			"OPSKIP" => "5", 
			"TRACUT" => "5", 
			"TRANS" => "5", 
			"APPEND" => "6", 
			"CREATE" => "6", 
			"DELIM" => "6", 
			"FILE" => "6", 
			"FETCH" => "6", 
			"FPRINT" => "6", 
			"FTERM" => "6", 
			"READ" => "6", 
			"RESET" => "6", 
			"WRITE" => "6", 
			"LOADTL" => "7", 
			"COOLNT" => "8", 
			"SPINDL" => "8");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing", 
			"7" => "donothing", 
			"8" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
