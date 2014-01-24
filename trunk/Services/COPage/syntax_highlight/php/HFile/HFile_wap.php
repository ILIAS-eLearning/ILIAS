<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_wap extends HFile{
   function HFile_wap(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// WAP
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
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("<!--");
$this->blockcommentoff   	= array("-->");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"=" => "1", 
			"<" => "1", 
			">" => "1", 
			"<a>" => "1", 
			"<a" => "1", 
			"<access>" => "1", 
			"<access" => "1", 
			"<anchor>" => "1", 
			"<anchor" => "1", 
			"<b>" => "1", 
			"<big>" => "1", 
			"<br/>" => "1", 
			"<card>" => "1", 
			"<card" => "1", 
			"<do>" => "1", 
			"<do" => "1", 
			"<em>" => "1", 
			"<fieldset>" => "1", 
			"<fieldset" => "1", 
			"<go" => "1", 
			"<head>" => "1", 
			"<i>" => "1", 
			"<img" => "1", 
			"<input" => "1", 
			"<meta" => "1", 
			"<noop/>" => "1", 
			"<option>" => "1", 
			"<option" => "1", 
			"<optgroup>" => "1", 
			"<optgroup" => "1", 
			"<onevent" => "1", 
			"<p>" => "1", 
			"<p" => "1", 
			"<prev>" => "1", 
			"<prev/>" => "1", 
			"<postfield>" => "1", 
			"<postfield" => "1", 
			"<refresh>" => "1", 
			"<select" => "1", 
			"<setvar" => "1", 
			"<small>" => "1", 
			"<strong>" => "1", 
			"<table>" => "1", 
			"<table" => "1", 
			"<td>" => "1", 
			"<td" => "1", 
			"<template>" => "1", 
			"<template" => "1", 
			"<tr>" => "1", 
			"<tr" => "1", 
			"<timer>" => "1", 
			"<timer" => "1", 
			"<u>" => "1", 
			"<wml>" => "1", 
			"</a>" => "2", 
			"</access>" => "2", 
			"</anchor>" => "2", 
			"</b>" => "2", 
			"</big>" => "2", 
			"</card>" => "2", 
			"</do>" => "2", 
			"</em>" => "2", 
			"</fieldset>" => "2", 
			"</go>" => "2", 
			"</head>" => "2", 
			"</i>" => "2", 
			"</img>" => "2", 
			"</input>" => "2", 
			"</meta>" => "2", 
			"</option>" => "2", 
			"</optgroup>" => "2", 
			"</onevent>" => "2", 
			"</p>" => "2", 
			"</prev>" => "2", 
			"</postfield>" => "2", 
			"</refresh>" => "2", 
			"</select>" => "2", 
			"</setvar>" => "2", 
			"</small>" => "2", 
			"</strong>" => "2", 
			"</table>" => "2", 
			"</td>" => "2", 
			"</template>" => "2", 
			"</tr>" => "2", 
			"</timer>" => "2", 
			"</u>" => "2", 
			"</wml>" => "2", 
			"'" => "3", 
			"\"" => "3", 
			"accept-charset=" => "3", 
			"align=" => "3", 
			"alt=" => "3", 
			"columns=" => "3", 
			"content=" => "3", 
			"domain=" => "3", 
			"emptyok=" => "3", 
			"format=" => "3", 
			"forua=" => "3", 
			"height=" => "3", 
			"href=" => "3", 
			"hspace=" => "3", 
			"http-equiv=" => "3", 
			"id=" => "3", 
			"iname=" => "3", 
			"ivalue=" => "3", 
			"label=" => "3", 
			"localsrc=" => "3", 
			"maxlength=" => "3", 
			"method=" => "3", 
			"mode=" => "3", 
			"multiple=" => "3", 
			"name=" => "3", 
			"nextcontext=" => "3", 
			"onpick=" => "3", 
			"optional=" => "3", 
			"ontimer=" => "3", 
			"ordered=" => "3", 
			"path=" => "3", 
			"sendreferer=" => "3", 
			"src=" => "3", 
			"title=" => "3", 
			"type=" => "3", 
			"value=" => "3", 
			"vspace=" => "3", 
			"width=" => "3", 
			"xml:lang=" => "3", 
			"accept" => "4", 
			"action" => "4", 
			"bottom" => "4", 
			"Cache-Control" => "4", 
			"center" => "4", 
			"delete" => "4", 
			"false" => "4", 
			"get" => "4", 
			"left" => "4", 
			"max-age" => "4", 
			"nowrap" => "4", 
			"onenterforward" => "4", 
			"onenterbackward" => "4", 
			"onpick" => "4", 
			"ontimer" => "4", 
			"options" => "4", 
			"passwd" => "4", 
			"password" => "4", 
			"prevpost" => "4", 
			"right" => "4", 
			"text" => "4", 
			"timer" => "4", 
			"true" => "4", 
			"wrap" => "4");

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
