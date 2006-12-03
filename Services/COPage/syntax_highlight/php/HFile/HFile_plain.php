<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_plain extends HFile{
   function HFile_plain(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// HTML
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "1";
$this->perl              	= "0";

// Colours

$this->colours        		= array();
$this->quotecolour       	= "";
$this->blockcommentcolour	= "";
$this->linecommentcolour 	= "";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array();
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array();
$this->blockcommenton    	= array();
$this->blockcommentoff   	= array();

// Keywords (keyword mapping to colour number)

$this->keywords          	= array();

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        

$this->linkscripts    	= array();
}


function donothing($keywordin)
{
	return $keywordin;
	
}

}?>
