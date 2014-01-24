<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");

class HFile_ada95 extends HFile
{

function HFile_ada95()
{
$this->HFile();	
     
/*************************************/
// Beautifier Highlighting Configuration File 
// Ada95
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        		= array("blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array();
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("--");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"abort" => "1", 
			"abs" => "1", 
			"abstract" => "1", 
			"accept" => "1", 
			"access" => "1", 
			"aliased" => "1", 
			"all" => "1", 
			"and" => "1", 
			"array" => "1", 
			"at" => "1", 
			"begin" => "1", 
			"body" => "1", 
			"case" => "1", 
			"constant" => "1", 
			"declare" => "1", 
			"delay" => "1", 
			"delta" => "1", 
			"digits" => "1", 
			"do" => "1", 
			"else" => "1", 
			"elsif" => "1", 
			"end" => "1", 
			"entry" => "1", 
			"exception" => "1", 
			"exit" => "1", 
			"for" => "1", 
			"function" => "1", 
			"goto" => "1", 
			"generic" => "1", 
			"if" => "1", 
			"in" => "1", 
			"is" => "1", 
			"limited" => "1", 
			"loop" => "1", 
			"mod" => "1", 
			"new" => "1", 
			"not" => "1", 
			"null" => "1", 
			"of" => "1", 
			"or" => "1", 
			"others" => "1", 
			"out" => "1", 
			"package" => "1", 
			"pragma" => "1", 
			"private" => "1", 
			"procedure" => "1", 
			"protected" => "1", 
			"raise" => "1", 
			"range" => "1", 
			"record" => "1", 
			"rem" => "1", 
			"renames" => "1", 
			"requeue" => "1", 
			"return" => "1", 
			"reverse" => "1", 
			"select" => "1", 
			"separate" => "1", 
			"subtype" => "1", 
			"tagged" => "1", 
			"task" => "1", 
			"terminate" => "1", 
			"then" => "1", 
			"type" => "1", 
			"until" => "1", 
			"use" => "1", 
			"when" => "1", 
			"while" => "1", 
			"with" => "1", 
			"xor" => "1", 
			"boolean" => "2", 
			"integer" => "2", 
			"false" => "2", 
			"float" => "2", 
			"natural" => "2", 
			"positive" => "2", 
			"real" => "2", 
			"true" => "2", 
			"vector" => "2", 
			".." => "3");

// Special extensions

// Each category can specify a PHP function that takes in the function name, and returns an alternative.
// This is great for doing links to manuals, etc.

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
