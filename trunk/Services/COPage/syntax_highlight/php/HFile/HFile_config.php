<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_config extends HFile{
   function HFile_config(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Config Files
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("*", "(", ")", "-", "+", "=", "/", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"AGENCY" => "1", 
			"API_USERS" => "1", 
			"ABSFONT" => "1", 
			"ALERT" => "1", 
			"BUSINESS" => "1", 
			"CLIENT_USERS" => "1", 
			"CODEPAGE" => "1", 
			"DEBUG" => "1", 
			"DISABLE_TOOLBAR" => "1", 
			"EXECUTE" => "1", 
			"EIS" => "1", 
			"FILE" => "1", 
			"FILECODEPAGE" => "1", 
			"HOST" => "1", 
			"JOB" => "1", 
			"LOG_FILE" => "1", 
			"LP" => "1", 
			"LPPATH" => "1", 
			"LOCAL" => "1", 
			"LOADPAGEMSG" => "1", 
			"MEMORY" => "1", 
			"NT95INTERFACE" => "1", 
			"ROUTE_FILE" => "1", 
			"SERVER" => "1", 
			"STORE" => "1", 
			"SQL" => "1", 
			"SPX" => "1", 
			"SQLCODEPAGE" => "1", 
			"SCENARIO_SIZE" => "1", 
			"SPLASH" => "1", 
			"TIMEOUT" => "1", 
			"THREADS" => "1", 
			"TCP" => "1", 
			"TCPX" => "1", 
			"WORKERS" => "1", 
			"WEBSERVER" => "1", 
			"WEBPORT" => "1", 
			"WEBTIMEOUT" => "1", 
			"WEBCGI" => "1", 
			"WEBCLASSES" => "1", 
			"WEBICONS" => "1", 
			"WEB_USERS" => "1", 
			"WEBCODEPAGE" => "1", 
			"WEBSSL" => "1", 
			"sqlentry" => "1", 
			"sqlodbc" => "1");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
