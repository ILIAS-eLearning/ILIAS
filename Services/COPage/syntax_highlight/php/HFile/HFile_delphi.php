<?php

$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_delphi extends HFile{
   function HFile_delphi(){
     $this->HFile();

/*************************************/
// Beautifier Highlighting Configuration File 
// Delphi 4
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

$this->indent            	= array("begin", "repeat", "asm", "type");
$this->unindent          	= array("end", "until");

// String characters and delimiters

$this->stringchars       	= array("'");
$this->delimiters        	= array("#", "$", "&", "'", "(", ")", "*", "+", ",", "-", ".", "/", ";", "<", ">", "@", "[", "]", "^", "{", "}", " ", "	");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("{");
$this->blockcommentoff   	= array("}");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"array" => "1", 
			"asm" => "1", 
			"begin" => "1", 
			"case" => "1", 
			"class" => "1", 
			"const" => "1", 
			"constructor" => "1", 
			"destructor" => "1", 
			"dispinterface" => "1", 
			"do" => "1", 
			"downto" => "1", 
			"else" => "1", 
			"end" => "1", 
			"except" => "1", 
			"exports" => "1", 
			"file" => "1", 
			"finalization" => "1", 
			"finally" => "1", 
			"for" => "1", 
			"function" => "1", 
			"goto" => "1", 
			"if" => "1", 
			"implementation" => "1", 
			"inherited" => "1", 
			"initialization" => "1", 
			"inline" => "1", 
			"interface" => "1", 
			"label" => "1", 
			"library" => "1", 
			"nil" => "1", 
			"object" => "1", 
			"of" => "1", 
			"out" => "1", 
			"packed" => "1", 
			"procedure" => "1", 
			"program" => "1", 
			"property" => "1", 
			"raise" => "1", 
			"record" => "1", 
			"repeat" => "1", 
			"resourcestring" => "1", 
			"set" => "1", 
			"string" => "1", 
			"then" => "1", 
			"threadvar" => "1", 
			"to" => "1", 
			"try" => "1", 
			"type" => "1", 
			"unit" => "1", 
			"until" => "1", 
			"uses" => "1", 
			"var" => "1", 
			"while" => "1", 
			"with" => "1", 
			"absolute" => "2", 
			"abstract" => "2", 
			"assembler" => "2", 
			"automated" => "2", 
			"cdecl" => "2", 
			"contains" => "2", 
			"default" => "2", 
			"dispid" => "2", 
			"dynamic" => "2", 
			"export" => "2", 
			"external" => "2", 
			"far" => "2", 
			"forward" => "2", 
			"implements" => "2", 
			"index" => "2", 
			"message" => "2", 
			"name" => "2", 
			"near" => "2", 
			"nodefault" => "2", 
			"overload" => "2", 
			"override" => "2", 
			"package" => "2", 
			"pascal" => "2", 
			"private" => "2", 
			"protected" => "2", 
			"public" => "2", 
			"published" => "2", 
			"read" => "2", 
			"readonly" => "2", 
			"register" => "2", 
			"reintroduce" => "2", 
			"requires" => "2", 
			"resident" => "2", 
			"safecall" => "2", 
			"stdcall" => "2", 
			"stored" => "2", 
			"virtual" => "2", 
			"write" => "2", 
			"writeonly" => "2", 
			"*" => "3", 
			"+" => "3", 
			"-" => "3", 
			"//" => "3", 
			"/" => "3", 
			"<" => "3", 
			"<=" => "3", 
			"<>" => "3", 
			"=" => "3", 
			">" => "3", 
			">=" => "3", 
			"@" => "3", 
			"and" => "3", 
			"as" => "3", 
			"div" => "3", 
			"in" => "3", 
			"is" => "3", 
			"mod" => "3", 
			"not" => "3", 
			"or" => "3", 
			"shl" => "3", 
			"shr" => "3", 
			"xor" => "3", 
			"#" => "4", 
			"$" => "4", 
			"&" => "4", 
			"(" => "4", 
			"(." => "4", 
			")" => "4", 
			"," => "4", 
			"." => "4", 
			".)" => "4", 
			".." => "4", 
			":" => "4", 
			":=" => "4", 
			";" => "4", 
			"[" => "4", 
			"]" => "4", 
			"^" => "4");

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

}

?>
