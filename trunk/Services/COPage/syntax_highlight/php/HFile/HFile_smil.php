<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_smil extends HFile{
   function HFile_smil(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// SMIL
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
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("<!--");
$this->blockcommentoff   	= array("/-->");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"<a" => "1", 
			"</a>" => "1", 
			"<anchor" => "1", 
			"<anchor>" => "1", 
			"</anchor>" => "1", 
			"<animation>" => "1", 
			"</animation>" => "1", 
			"<audio" => "1", 
			"<audio>" => "1", 
			"</audio>" => "1", 
			"<body>" => "1", 
			"</body>" => "1", 
			"<head>" => "1", 
			"</head>" => "1", 
			"<img>" => "1", 
			"<layout" => "1", 
			"<layout>" => "1", 
			"</layout>" => "1", 
			"<meta" => "1", 
			"<par>" => "1", 
			"</par>" => "1", 
			"<ref>" => "1", 
			"</ref>" => "1", 
			"<region" => "1", 
			"<root-layout" => "1", 
			"<seq>" => "1", 
			"</seq>" => "1", 
			"<smil>" => "1", 
			"</smil>" => "1", 
			"<switch>" => "1", 
			"</switch>" => "1", 
			"<text" => "1", 
			"<text>" => "1", 
			"</text>" => "1", 
			"<textstream>" => "1", 
			"</textstream>" => "1", 
			"<video" => "1", 
			"<video>" => "1", 
			"</video>" => "1", 
			"//" => "1", 
			"/>" => "1", 
			"abstract=" => "2", 
			"alt=" => "2", 
			"anchor=" => "2", 
			"author=" => "2", 
			"background-color=" => "2", 
			"base=" => "2", 
			"begin=" => "2", 
			"clip-begin=" => "2", 
			"clip-end=" => "2", 
			"clock-val=" => "2", 
			"content=" => "2", 
			"coords=" => "2", 
			"copyright=" => "2", 
			"dur=" => "2", 
			"end=" => "2", 
			"endsync=" => "2", 
			"fill=" => "2", 
			"height=" => "2", 
			"href=" => "2", 
			"id=" => "2", 
			"longdesc=" => "2", 
			"name=" => "2", 
			"pics-label=" => "2", 
			"PICS-label=" => "2", 
			"region=" => "2", 
			"repeat=" => "2", 
			"show=" => "2", 
			"skip-content=" => "2", 
			"src=" => "2", 
			"system-bitrate=" => "2", 
			"system-captions=" => "2", 
			"system-language=" => "2", 
			"system-overdub-or-caption=" => "2", 
			"system-required=" => "2", 
			"system-screen-size=" => "2", 
			"system-screen-depth=" => "2", 
			"title=" => "2", 
			"type=" => "2", 
			"width=" => "2", 
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
