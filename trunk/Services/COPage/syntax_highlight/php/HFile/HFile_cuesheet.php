<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_cuesheet extends HFile{
   function HFile_cuesheet(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Cue Sheets
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
$this->delimiters        	= array("!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "[", "]", ";", "\"", "'", "<", ">", " ", ",", "	", ".");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"CATALOG" => "1", 
			"FILE" => "1", 
			"TRACK" => "2", 
			"FLAGS" => "3", 
			"INDEX" => "3", 
			"ISRC" => "3", 
			"POSTGAP" => "3", 
			"PREGAP" => "3", 
			"REM" => "3", 
			"4CH" => "4", 
			"AIFF" => "4", 
			"AUDIO" => "4", 
			"BINARY" => "4", 
			"CDG" => "4", 
			"DCP" => "4", 
			"MODE1/2048" => "4", 
			"MODE1/2352" => "4", 
			"MODE2/2336" => "4", 
			"MODE2/2352" => "4", 
			"MOTOROLA" => "4", 
			"PRE" => "4", 
			"WAVE" => "4");

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
