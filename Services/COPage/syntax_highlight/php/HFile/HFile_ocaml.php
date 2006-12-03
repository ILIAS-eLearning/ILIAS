<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_ocaml extends HFile{
   function HFile_ocaml(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// 
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array();
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"/L10" => "", 
			"Block" => "", 
			"Comment" => "", 
			"On" => "", 
			"=" => "", 
			"(*" => "", 
			"Off" => "", 
			"*)" => "", 
			"File" => "", 
			"Extensions" => "", 
			"ML" => "", 
			"MLI" => "", 
			"and" => "1", 
			"as" => "1", 
			"asr" => "1", 
			"begin" => "1", 
			"class" => "1", 
			"closed" => "1", 
			"constraint" => "1", 
			"do" => "1", 
			"done" => "1", 
			"downto" => "1", 
			"else" => "1", 
			"end" => "1", 
			"exception" => "1", 
			"external" => "1", 
			"failwith" => "1", 
			"false" => "1", 
			"flush" => "1", 
			"for" => "1", 
			"fun" => "1", 
			"function" => "1", 
			"functor" => "1", 
			"if" => "1", 
			"in" => "1", 
			"include" => "1", 
			"inherit" => "1", 
			"incr" => "1", 
			"land" => "1", 
			"let" => "1", 
			"lor" => "1", 
			"lsl" => "1", 
			"lsr" => "1", 
			"lxor" => "1", 
			"match" => "1", 
			"method" => "1", 
			"mod" => "1", 
			"module" => "1", 
			"mutable" => "1", 
			"new" => "1", 
			"not" => "1", 
			"of" => "1", 
			"open" => "1", 
			"option" => "1", 
			"or" => "1", 
			"parser" => "1", 
			"private" => "1", 
			"ref" => "1", 
			"rec" => "1", 
			"raise" => "1", 
			"regexp" => "1", 
			"sig" => "1", 
			"struct" => "1", 
			"stdout" => "1", 
			"stdin" => "1", 
			"stderr" => "1", 
			"then" => "1", 
			"to" => "1", 
			"true" => "1", 
			"try" => "1", 
			"type" => "1", 
			"val" => "1", 
			"virtual" => "1", 
			"when" => "1", 
			"while" => "1", 
			"with" => "1", 
			"Hashtbl" => "2", 
			"Array" => "2", 
			"Data" => "2", 
			"Util" => "2", 
			"Printf" => "2", 
			"Str" => "2", 
			"array" => "3", 
			"bool" => "3", 
			"dummy" => "3", 
			"float" => "3", 
			"int" => "3", 
			"list" => "3", 
			"string" => "3", 
			"unit" => "3");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"" => "donothing", 
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
