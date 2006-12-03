<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_realtext extends HFile{
   function HFile_realtext(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// RealText
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

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("<!--");
$this->blockcommentoff   	= array("/-->");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"<a" => "1", 
			"</a>" => "1", 
			"<a>" => "1", 
			"<b>" => "1", 
			"</b>" => "1", 
			"<br/>" => "1", 
			"<center>" => "1", 
			"</center>" => "1", 
			"<clear/>" => "1", 
			"<font>" => "1", 
			"<font" => "1", 
			"</font>" => "1", 
			"<hr/>" => "1", 
			"<i>" => "1", 
			"</i>" => "1", 
			"<li>" => "1", 
			"</li>" => "1", 
			"<ol>" => "1", 
			"</ol>" => "1", 
			"<p>" => "1", 
			"</p>" => "1", 
			"<pos/>" => "1", 
			"<pre>" => "1", 
			"</pre>" => "1", 
			"<required>" => "1", 
			"</required>" => "1", 
			"<time" => "1", 
			"<tl>" => "1", 
			"</tl>" => "1", 
			"<tu>" => "1", 
			"</tu>" => "1", 
			"<u>" => "1", 
			"</u>" => "1", 
			"<ul>" => "1", 
			"</ul>" => "1", 
			"<window>" => "1", 
			"<window" => "1", 
			"</window>" => "1", 
			"//" => "1", 
			"/>" => "1", 
			"attribute=" => "2", 
			"begin=" => "2", 
			"bgcolor=" => "2", 
			"color=" => "2", 
			"crawlrate=" => "2", 
			"duration=" => "2", 
			"end=" => "2", 
			"height=" => "2", 
			"href=" => "2", 
			"scrollrate=" => "2", 
			"size=" => "2", 
			"target=" => "2", 
			"type=" => "2", 
			"width=" => "2", 
			"wordwrap=" => "2", 
			"x=" => "2", 
			"y=" => "2", 
			"=" => "2");

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
