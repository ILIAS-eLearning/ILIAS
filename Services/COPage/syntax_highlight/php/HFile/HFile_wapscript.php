<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_wapscript extends HFile{
   function HFile_wapscript(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// WAPScript
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

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			";" => "1", 
			"access" => "1", 
			"agent" => "1", 
			"block" => "1", 
			"break" => "1", 
			"case" => "1", 
			"catch" => "1", 
			"class" => "1", 
			"Console" => "1", 
			"const" => "1", 
			"continue" => "1", 
			"debugger" => "1", 
			"defaut" => "1", 
			"delete" => "1", 
			"Dialogs" => "1", 
			"div" => "1", 
			"do" => "1", 
			"domain" => "1", 
			"else" => "1", 
			"empty" => "1", 
			"enum" => "1", 
			"export" => "1", 
			"extends" => "1", 
			"expression" => "1", 
			"extern" => "1", 
			"finally" => "1", 
			"Float" => "1", 
			"for" => "1", 
			"function" => "1", 
			"header" => "1", 
			"http" => "1", 
			"if" => "1", 
			"import" => "1", 
			"in" => "1", 
			"Lang" => "1", 
			"lib" => "1", 
			"main" => "1", 
			"meta" => "1", 
			"name" => "1", 
			"new" => "1", 
			"null" => "1", 
			"path" => "1", 
			"private" => "1", 
			"public" => "1", 
			"return" => "1", 
			"sizeot" => "1", 
			"String" => "1", 
			"super" => "1", 
			"switch" => "1", 
			"throw" => "1", 
			"this" => "1", 
			"try" => "1", 
			"typeof" => "1", 
			"URL" => "1", 
			"use" => "1", 
			"var" => "1", 
			"varialbe" => "1", 
			"void" => "1", 
			"while" => "1", 
			"with" => "1", 
			"WMLBrowser" => "1", 
			"{" => "2", 
			"}" => "2", 
			"(" => "2", 
			")" => "2", 
			"'" => "3", 
			"\"" => "3", 
			"\\" => "3", 
			"abort" => "3", 
			"abs" => "3", 
			"alvert" => "3", 
			"cell" => "3", 
			"characterSet" => "3", 
			"charAt" => "3", 
			"compare" => "3", 
			"confirm" => "3", 
			"elementAt" => "3", 
			"elements" => "3", 
			"escapeString" => "3", 
			"exit" => "3", 
			"find" => "3", 
			"floor" => "3", 
			"format" => "3", 
			"getBase" => "3", 
			"getCurrentCard" => "3", 
			"getFragment" => "3", 
			"getHost" => "3", 
			"getParameters" => "3", 
			"getPath" => "3", 
			"getPort" => "3", 
			"getQuery" => "3", 
			"getReferer" => "3", 
			"getScheme" => "3", 
			"getVar" => "3", 
			"go" => "3", 
			"insertAt" => "3", 
			"int" => "3", 
			"isEmpty" => "3", 
			"isFloat" => "3", 
			"isInt" => "3", 
			"isValid" => "3", 
			"length" => "3", 
			"loadString" => "3", 
			"max" => "3", 
			"maxFloat" => "3", 
			"maxInt" => "3", 
			"min" => "3", 
			"minFloat" => "3", 
			"minInt" => "3", 
			"newContext" => "3", 
			"parseFloat" => "3", 
			"parseInt" => "3", 
			"pow" => "3", 
			"prev" => "3", 
			"print" => "3", 
			"println" => "3", 
			"prompt" => "3", 
			"random" => "3", 
			"refresh" => "3", 
			"removeAt" => "3", 
			"replace" => "3", 
			"replaceAt" => "3", 
			"resolve" => "3", 
			"round" => "3", 
			"seed" => "3", 
			"setVar" => "3", 
			"sqrt" => "3", 
			"squeeze" => "3", 
			"subString" => "3", 
			"toString" => "3", 
			"trim" => "3", 
			"unescapeString" => "3", 
			"+" => "4", 
			"-" => "4", 
			"=" => "4", 
			"*" => "4", 
			"//" => "4", 
			"/" => "4", 
			"%" => "4", 
			"&" => "4", 
			"<=" => "4", 
			"<" => "4", 
			">=" => "4", 
			">" => "4", 
			"!" => "4", 
			"|" => "4");

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
