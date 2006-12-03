<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_realpix extends HFile{
   function HFile_realpix(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// RealPix
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
			"<b>" => "1", 
			"</b>" => "1", 
			"<br/>" => "1", 
			"<center>" => "1", 
			"</center>" => "1", 
			"<clear" => "1", 
			"<crossfade" => "1", 
			"<font" => "1", 
			"</font>" => "1", 
			"<fadein" => "1", 
			"<fadeout" => "1", 
			"<fill" => "1", 
			"<head" => "1", 
			"<hr" => "1", 
			"<i>" => "1", 
			"</i>" => "1", 
			"<image" => "1", 
			"<imfl>" => "1", 
			"</imfl>" => "1", 
			"<li>" => "1", 
			"</li>" => "1", 
			"<ol>" => "1", 
			"</ol>" => "1", 
			"<p>" => "1", 
			"</p>" => "1", 
			"<pos" => "1", 
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
			"<viewchange" => "1", 
			"<window>" => "1", 
			"</window>" => "1", 
			"<wipe>" => "1", 
			"//" => "1", 
			"/>" => "1", 
			"aspect=" => "2", 
			"author=" => "2", 
			"begin=" => "2", 
			"bgcolor=" => "2", 
			"bitrate=" => "2", 
			"charset=" => "2", 
			"color=" => "2", 
			"copyright=" => "2", 
			"crawlrate=" => "2", 
			"direction=" => "2", 
			"dsth=" => "2", 
			"dstw=" => "2", 
			"dstx=" => "2", 
			"dsty=" => "2", 
			"duration=" => "2", 
			"end=" => "2", 
			"face=" => "2", 
			"handle=" => "2", 
			"height=" => "2", 
			"href=" => "2", 
			"link=" => "2", 
			"loop=" => "2", 
			"maxfps=" => "2", 
			"name=" => "2", 
			"preroll=" => "2", 
			"scrollrate=" => "2", 
			"size=" => "2", 
			"srch=" => "2", 
			"srcw=" => "2", 
			"srcx=" => "2", 
			"srcy=" => "2", 
			"start=" => "2", 
			"target=" => "2", 
			"timeformat=" => "2", 
			"title=" => "2", 
			"type=" => "2", 
			"underline_hyperlinks=" => "2", 
			"url=" => "2", 
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
