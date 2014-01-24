<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_alphascript extends HFile{
   function HFile_alphascript(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// AlphaScript
/*************************************/
// Flags

$this->nocase            	= "0";
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
$this->delimiters        	= array("(", ")");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("'''");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"@command" => "1", 
			"@date" => "1", 
			"@fileout" => "1", 
			"@listout" => "1", 
			"@regout" => "1", 
			"@scriptfile" => "1", 
			"@textout" => "1", 
			"@time" => "1", 
			"@user" => "1", 
			"@ver" => "1", 
			"!quit" => "2", 
			"!#BEGIN" => "3", 
			"!#END" => "3", 
			"!about" => "4", 
			"!clearallvars" => "4", 
			"!copy" => "4", 
			"!del" => "4", 
			"!delay" => "4", 
			"!else" => "4", 
			"!email" => "4", 
			"!emptyfile" => "4", 
			"!exe" => "4", 
			"!exist" => "4", 
			"!fileread" => "4", 
			"!goto" => "4", 
			"!if" => "4", 
			"!input" => "4", 
			"!iscancel" => "4", 
			"!isfault" => "4", 
			"!isnotfound" => "4", 
			"!isok" => "4", 
			"!let" => "4", 
			"!list" => "4", 
			"!listadd" => "4", 
			"!listclear" => "4", 
			"!log" => "4", 
			"!msg" => "4", 
			"!multitask" => "4", 
			"!openfolder" => "4", 
			"!regcreate" => "4", 
			"!regread" => "4", 
			"!regwrite" => "4", 
			"!skeys" => "4", 
			"!then" => "4", 
			"!winactive" => "4", 
			"!winkill" => "4", 
			"!winwait" => "4", 
			"!_closelogwindow" => "4", 
			"!_openlogwindow" => "4", 
			"!_pauselogwindow" => "4");

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
