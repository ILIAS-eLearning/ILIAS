<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_lumonics extends HFile{
   function HFile_lumonics(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Lumonics Gcode
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("purple", "blue", "purple", "gray", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("B", "E", "I", "J", "K", "L", "P", "Q", "R", "U", "V", "W", "(", " ", ")", "=", "-", "+", "*");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("(");
$this->blockcommentoff   	= array(")");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"**" => "6", 
			"N" => "1", 
			"%" => "2", 
			":" => "2", 
			"O" => "2", 
			"H" => "2", 
			"F" => "2", 
			"S" => "2", 
			"T" => "2", 
			"G" => "3", 
			"M" => "3", 
			"A" => "4", 
			"C" => "4", 
			"D" => "4", 
			"X" => "5", 
			"Z" => "5", 
			"Y" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"6" => "donothing", 
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
