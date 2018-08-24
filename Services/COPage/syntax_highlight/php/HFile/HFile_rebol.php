<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_rebol extends HFile{
   function HFile_rebol(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// REBOL
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

$this->indent            	= array("[");
$this->unindent          	= array("]");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "@", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"about" => "1", 
			"absolute" => "1", 
			"add" => "1", 
			"alias" => "1", 
			"all" => "1", 
			"and" => "1", 
			"any" => "1", 
			"append" => "1", 
			"arccosine" => "1", 
			"arcsine" => "1", 
			"arctangent" => "1", 
			"array" => "1", 
			"ask" => "1", 
			"at" => "1", 
			"back" => "1", 
			"break" => "1", 
			"bind" => "1", 
			"catch" => "1", 
			"change" => "1", 
			"change-dir" => "1", 
			"charset" => "1", 
			"checksum" => "1", 
			"clear" => "1", 
			"close" => "1", 
			"comment" => "1", 
			"complement" => "1", 
			"compress" => "1", 
			"confirm" => "1", 
			"copy" => "1", 
			"cosine" => "1", 
			"debase" => "1", 
			"decompress" => "1", 
			"dehex" => "1", 
			"delete" => "1", 
			"detab" => "1", 
			"disarm" => "1", 
			"divide" => "1", 
			"do" => "1", 
			"echo" => "1", 
			"either" => "1", 
			"enbase" => "1", 
			"entab" => "1", 
			"exit" => "1", 
			"exp" => "1", 
			"false" => "1", 
			"fifth" => "1", 
			"find" => "1", 
			"first" => "1", 
			"for" => "1", 
			"forall" => "1", 
			"foreach" => "1", 
			"form" => "1", 
			"forskip" => "1", 
			"fourth" => "1", 
			"func" => "1", 
			"function" => "1", 
			"get" => "1", 
			"halt" => "1", 
			"head" => "1", 
			"help" => "1", 
			"if" => "1", 
			"in" => "1", 
			"input" => "1", 
			"insert" => "1", 
			"intersect" => "1", 
			"join" => "1", 
			"last" => "1", 
			"less" => "1", 
			"third" => "1", 
			"throw" => "1", 
			"to-binary" => "1", 
			"to-bitset" => "1", 
			"to-block" => "1", 
			"to-char" => "1", 
			"to-date" => "1", 
			"to-decimal" => "1", 
			"to-email" => "1", 
			"to-file" => "1", 
			"to-get-word" => "1", 
			"to-hash" => "1", 
			"to-hex" => "1", 
			"to-idate" => "1", 
			"to-integer" => "1", 
			"to-issue" => "1", 
			"to-list" => "1", 
			"to-lit-word" => "1", 
			"to-logic" => "1", 
			"to-money" => "1", 
			"to-none" => "1", 
			"to-paren" => "1", 
			"to-refinement" => "1", 
			"to-set-path" => "1", 
			"to-set-word" => "1", 
			"to-string" => "1", 
			"to-tag" => "1", 
			"to-time" => "1", 
			"to-tuple" => "1", 
			"to-url" => "1", 
			"to-word" => "1", 
			"trace" => "1", 
			"trim" => "1", 
			"true" => "1", 
			"try" => "1", 
			"union" => "1", 
			"unset" => "1", 
			"until" => "1", 
			"update" => "1", 
			"uppercase" => "1", 
			"usage" => "1", 
			"use" => "1", 
			"wait" => "1", 
			"what" => "1", 
			"what-dir" => "1", 
			"while" => "1", 
			"write" => "1", 
			"xor" => "1", 
			"yes" => "1", 
			"any-block!" => "2", 
			"any-function!" => "2", 
			"any-string!" => "2", 
			"any-type!" => "2", 
			"any-word!" => "2", 
			"binary!" => "2", 
			"bitset!" => "2", 
			"block!" => "2", 
			"char!" => "2", 
			"datatype!" => "2", 
			"date!" => "2", 
			"decimal!" => "2", 
			"email!" => "2", 
			"error!" => "2", 
			"file!" => "2", 
			"function!" => "2", 
			"get-word!" => "2", 
			"hash!" => "2", 
			"integer!" => "2", 
			"issue!" => "2", 
			"time!" => "2", 
			"tuple!" => "2", 
			"url!" => "2", 
			"word!" => "2", 
			"any-block?" => "3", 
			"any-function?" => "3", 
			"any-string?" => "3", 
			"any-type?" => "3", 
			"any-word?" => "3", 
			"binary?" => "3", 
			"bitset?" => "3", 
			"block?" => "3", 
			"char?" => "3", 
			"datatype?" => "3", 
			"date?" => "3", 
			"decimal?" => "3", 
			"dir?" => "3", 
			"email?" => "3", 
			"empty?" => "3", 
			"equal?" => "3", 
			"error?" => "3", 
			"even?" => "3", 
			"exists?" => "3", 
			"file?" => "3", 
			"found?" => "3", 
			"function?" => "3", 
			"get-word?" => "3", 
			"greater?" => "3", 
			"greater-or-equal?" => "3", 
			"hash?" => "3", 
			"head?" => "3", 
			"index?" => "3", 
			"info?" => "3", 
			"input?" => "3", 
			"integer?" => "3", 
			"issue?" => "3", 
			"length?" => "3", 
			"time?" => "3", 
			"tuple?" => "3", 
			"type?" => "3", 
			"unset?" => "3", 
			"url?" => "3", 
			"value?" => "3", 
			"word?" => "3", 
			"zero?" => "3");

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
