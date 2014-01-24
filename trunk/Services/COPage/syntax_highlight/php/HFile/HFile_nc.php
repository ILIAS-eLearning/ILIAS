<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_nc extends HFile{
   function HFile_nc(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// NC Files
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("purple", "blue", "purple", "gray", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("A", "B", "C", "I", "J", "K", "P", "Q", "R", "U", "V", "W", "X", "Y", "Z", "(", " ", ")", "=", "-", "+", "*");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("$");
$this->blockcommenton    	= array("(");
$this->blockcommentoff   	= array(")");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"**" => "6", 
			"%" => "1", 
			":" => "1", 
			"O" => "1", 
			"H" => "1", 
			"N" => "1", 
			"M" => "2", 
			"G" => "3", 
			"D" => "5", 
			"F" => "5", 
			"S" => "5", 
			"T" => "5", 
			"E" => "6", 
			"L" => "6", 
			"-" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"6" => "donothing", 
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"5" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
