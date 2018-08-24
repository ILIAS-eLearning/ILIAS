<?php

$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_turbopascal extends HFile{
   function HFile_turbopascal(){
     $this->HFile();
     
/*************************************/
// Beautifier Highlighting Configuration File 
// Turbo Pascal
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("begin", "type");
$this->unindent          	= array("end");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "|", "\\", "/", "{", "}", "[", "]", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("{");
$this->blockcommentoff   	= array("}");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"begin" => "1", 
			"const" => "1", 
			"else" => "1", 
			"end" => "1", 
			"function" => "1", 
			"goto" => "1", 
			"if" => "1", 
			"label" => "1", 
			"procedure" => "1", 
			"program" => "1", 
			"readln" => "1", 
			"repeat" => "1", 
			"uses" => "1", 
			"var" => "1", 
			"write" => "1", 
			"writeln" => "1", 
			"and" => "2", 
			"array" => "2", 
			"asm" => "2", 
			"case" => "2", 
			"destructor" => "2", 
			"div" => "2", 
			"do" => "2", 
			"downto" => "2", 
			"exports" => "2", 
			"file" => "2", 
			"for" => "2", 
			"implementation" => "2", 
			"in" => "2", 
			":=" => "3", 
			"inhertited" => "3", 
			"inline" => "3", 
			"interface" => "3", 
			"library" => "3", 
			"mod" => "3", 
			"nil" => "3", 
			"not" => "3", 
			"object" => "3", 
			"of" => "3", 
			"or" => "3", 
			";" => "4", 
			"+" => "4", 
			"-" => "4", 
			"=" => "4", 
			"//" => "4", 
			"/" => "4", 
			"%" => "4", 
			"&" => "4", 
			">" => "4", 
			"<" => "4", 
			"^" => "4", 
			"!" => "4", 
			"|" => "4", 
			"packed" => "5", 
			"private" => "5", 
			"public" => "5", 
			"record" => "5", 
			"set" => "5", 
			"shl" => "5", 
			"shr" => "5", 
			"string" => "5", 
			"then" => "5", 
			"to" => "5", 
			"type" => "5", 
			"unit" => "5", 
			"until" => "5", 
			"with" => "5", 
			"xor" => "5");

// Special extensions


// Each category can specify a PHP function that returns an altered
// version of the keyword.





$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing");
}



function donothing($keywordin)
{
	return $keywordin;
}

}
?>
