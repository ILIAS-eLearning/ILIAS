<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_sacs4 extends HFile{
   function HFile_sacs4(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// SACS IV
/*************************************/
// Flags

$this->nocase            	= "1";
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

$this->stringchars       	= array("'", "\'");
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "(", ")", "+", "=", "\\", "/", "|", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", "*", " ", " ", " ", " ", " ", " ", " ", ",", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("!");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"AMOD" => "1", 
			"DEAD" => "1", 
			"END" => "1", 
			"GRUP" => "1", 
			"HYDRO" => "1", 
			"JOINT" => "1", 
			"LDCASE" => "1", 
			"LDCOMB" => "1", 
			"LDOPT" => "1", 
			"LOAD" => "1", 
			"LOADCN" => "1", 
			"MEMBER" => "1", 
			"OPTIONS" => "1", 
			"PGRUP" => "1", 
			"PLATE" => "1", 
			"PSTIF" => "1", 
			"REDESIGN" => "1", 
			"SECT" => "1", 
			"SECS" => "1", 
			"SHELL" => "1", 
			"SOLID" => "1", 
			"UCPART" => "1", 
			"CONC" => "2", 
			"ELASTI" => "2", 
			"F" => "2", 
			"JOIN" => "2", 
			"MOMT" => "2", 
			"MN" => "2", 
			"MT" => "2", 
			"N" => "2", 
			"OFFSET" => "2", 
			"OFFSETS" => "2", 
			"PERSET" => "2", 
			"PRI" => "2", 
			"SPC" => "2", 
			"SPG" => "2", 
			"STB" => "2", 
			"STC" => "2", 
			"STM" => "2", 
			"STT" => "2", 
			"TEE" => "2", 
			"TEMP" => "2", 
			"THICK" => "2", 
			"TUB" => "2", 
			"UNIF" => "2", 
			"WF" => "2", 
			"X" => "2", 
			"Y" => "2", 
			"Z" => "2");

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
