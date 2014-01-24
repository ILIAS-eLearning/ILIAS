<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_wml extends HFile{
   function HFile_wml(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// WML
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "@", "$", "%", "^", "*", "(", ")", "+", "=", "|", "\\", "{", "}", "\"", "'", "<", ">", " ", ",");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("<!--");
$this->blockcommentoff   	= array("-->");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"//" => "1", 
			"/>" => "1", 
			"<" => "1", 
			"<!DOCTYPE" => "1", 
			"<![CDATA[" => "1", 
			"<a" => "1", 
			"</a>" => "1", 
			"<anchor" => "1", 
			"<anchor>" => "1", 
			"</anchor>" => "1", 
			"<access>" => "1", 
			"</access>" => "1", 
			"<b>" => "1", 
			"</b>" => "1", 
			"<big>" => "1", 
			"</big>" => "1", 
			"<br>" => "1", 
			"<br/>" => "1", 
			"<card" => "1", 
			"</card>" => "1", 
			"<do" => "1", 
			"</do>" => "1", 
			"<em>" => "1", 
			"</em>" => "1", 
			"<go" => "1", 
			"<head>" => "1", 
			"</head>" => "1", 
			"<i>" => "1", 
			"</i>" => "1", 
			"<meta" => "1", 
			"<p>" => "1", 
			"<p" => "1", 
			"</p>" => "1", 
			"<prev" => "1", 
			"<prev/>" => "1", 
			"</prev>" => "1", 
			"<small>" => "1", 
			"</small>" => "1", 
			"<strong>" => "1", 
			"</strong>" => "1", 
			"<table" => "1", 
			"<table>" => "1", 
			"</table>" => "1", 
			"<template>" => "1", 
			"</template>" => "1", 
			"<td" => "1", 
			"<td>" => "1", 
			"</td>" => "1", 
			"<tr" => "1", 
			"<tr>" => "1", 
			"</tr>" => "1", 
			"<u>" => "1", 
			"</u>" => "1", 
			"<wml>" => "1", 
			"</wml>" => "1", 
			"<xml>" => "1", 
			"</xml>" => "1", 
			"<xmp>" => "1", 
			"</xmp>" => "1", 
			"<xptr" => "1", 
			"<xr>" => "1", 
			"<xr" => "1", 
			"</xr>" => "1", 
			"<xref" => "1", 
			"<xsl>" => "1", 
			"</xsl>" => "1", 
			">" => "1", 
			"]]>" => "1", 
			"<?php" => "1", 
			"<?phpxml" => "1", 
			"?>" => "1", 
			"accept-charset" => "2", 
			"align" => "2", 
			"alt" => "2", 
			"columns" => "2", 
			"content" => "2", 
			"domain" => "2", 
			"emptyok" => "2", 
			"format" => "2", 
			"forua" => "2", 
			"height" => "2", 
			"href" => "2", 
			"hspace" => "2", 
			"http-equiv" => "2", 
			"id" => "2", 
			"iname" => "2", 
			"ivalue" => "2", 
			"label" => "2", 
			"localsrc" => "2", 
			"maxlength" => "2", 
			"method" => "2", 
			"mode" => "2", 
			"multiple" => "2", 
			"name" => "2", 
			"newcontext" => "2", 
			"optional" => "2", 
			"ordered" => "2", 
			"path" => "2", 
			"scheme" => "2", 
			"sendreferer" => "2", 
			"size" => "2", 
			"src" => "2", 
			"tabindex" => "2", 
			"title" => "2", 
			"type" => "2", 
			"value" => "2", 
			"vspace" => "2", 
			"width" => "2", 
			"xml:lang" => "2", 
			"=" => "2", 
			"&amp;" => "4", 
			"&apos;" => "4", 
			"&gt;" => "4", 
			"&lt;" => "4", 
			"&nbsp;" => "4", 
			"&quot;" => "4", 
			"&shy;" => "4", 
			"onenterbackward=" => "5", 
			"onenterforward=" => "5", 
			"onpick=" => "5", 
			"ontimer=" => "5");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
