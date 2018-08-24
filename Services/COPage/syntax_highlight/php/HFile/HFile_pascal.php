<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_pascal extends HFile{
   function HFile_pascal(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Pascal
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

$this->indent            	= array("Begin");
$this->unindent          	= array("End");

// String characters and delimiters

$this->stringchars       	= array("'");
$this->delimiters        	= array("@", "^", "*", "(", ")", "-", "+", "=", "/", "[", "]", ":", ";", "'", "<", ">", " ", ",", "	", ".");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("{");
$this->blockcommentoff   	= array("}");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"absolute" => "1", 
			"array" => "1", 
			"assembler" => "1", 
			"const" => "1", 
			"constructor" => "1", 
			"destructor" => "1", 
			"export" => "1", 
			"exports" => "1", 
			"external" => "1", 
			"far" => "1", 
			"file" => "1", 
			"forward" => "1", 
			"function" => "1", 
			"implementation" => "1", 
			"index" => "1", 
			"inherited" => "1", 
			"inline" => "1", 
			"interface" => "1", 
			"interrupt" => "1", 
			"library" => "1", 
			"near" => "1", 
			"nil" => "1", 
			"object" => "1", 
			"of" => "1", 
			"packed" => "1", 
			"private" => "1", 
			"procedure" => "1", 
			"program" => "1", 
			"public" => "1", 
			"record" => "1", 
			"resident" => "1", 
			"set" => "1", 
			"string" => "1", 
			"type" => "1", 
			"unit" => "1", 
			"uses" => "1", 
			"var" => "1", 
			"virtual" => "1", 
			"asm" => "2", 
			"begin" => "2", 
			"case" => "2", 
			"do" => "2", 
			"downto" => "2", 
			"else" => "2", 
			"end" => "2", 
			"for" => "2", 
			"goto" => "2", 
			"if" => "2", 
			"label" => "2", 
			"repeat" => "2", 
			"then" => "2", 
			"to" => "2", 
			"until" => "2", 
			"while" => "2", 
			"with" => "2", 
			"and" => "3", 
			"div" => "3", 
			"in" => "3", 
			"mod" => "3", 
			"not" => "3", 
			"or" => "3", 
			"shl" => "3", 
			"shr" => "3", 
			"xor" => "3", 
			"+" => "3", 
			"-" => "3", 
			"*" => "3", 
			":" => "3", 
			"=" => "3", 
			"/" => "3", 
			">" => "3", 
			"<" => "3", 
			"^" => "3");

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
