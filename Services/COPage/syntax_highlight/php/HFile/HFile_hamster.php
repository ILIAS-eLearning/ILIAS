<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_hamster extends HFile{
   function HFile_hamster(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Hamster Scripts
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

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("\"");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ras.dial" => "1", 
			"ras.hangup" => "1", 
			"setlogin" => "1", 
			"msgbox" => "2", 
			"quit" => "2", 
			"restart" => "2", 
			"start.wait" => "2", 
			"start.nowait" => "2", 
			"wait.delay" => "2", 
			"wait.idle" => "2", 
			"wait.until" => "2", 
			"fetchmail" => "3", 
			"mail.pull" => "3", 
			"news.post" => "3", 
			"news.pull" => "3", 
			"news.purge" => "3", 
			"news.rebuildhistory" => "3", 
			"sendmail" => "3");

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
