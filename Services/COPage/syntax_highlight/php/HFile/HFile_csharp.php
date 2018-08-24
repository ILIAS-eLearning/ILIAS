<?php

$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_csharp extends HFile{
   function HFile_csharp(){
     $this->HFile();
/*************************************/
// Beautifier Highlighting Configuration File 
// C#
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        		= array("blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"as" => "1", 
			"auto" => "1", 
			"base" => "1", 
			"break" => "1", 
			"case" => "1", 
			"catch" => "1", 
			"const" => "1", 
			"continue" => "1", 
			"default" => "1", 
			"do" => "1", 
			"else" => "1", 
			"event" => "1", 
			"explicit" => "1", 
			"extern" => "1", 
			"false" => "1", 
			"finally" => "1", 
			"fixed" => "1", 
			"for" => "1", 
			"foreach" => "1", 
			"goto" => "1", 
			"if" => "1", 
			"implicit" => "1", 
			"in" => "1", 
			"internal" => "1", 
			"lock" => "1", 
			"namespace" => "1", 
			"null" => "1", 
			"operator" => "1", 
			"out" => "1", 
			"override" => "1", 
			"params" => "1", 
			"private" => "1", 
			"protected" => "1", 
			"public" => "1", 
			"readonly" => "1", 
			"ref" => "1", 
			"return" => "1", 
			"sealed" => "1", 
			"stackalloc" => "1", 
			"static" => "1", 
			"switch" => "1", 
			"this" => "1", 
			"throw" => "1", 
			"true" => "1", 
			"try" => "1", 
			"unsafe" => "1", 
			"using" => "1", 
			"virtual" => "1", 
			"void" => "1", 
			"while" => "1", 
			"bool" => "2", 
			"byte" => "2", 
			"char" => "2", 
			"class" => "2", 
			"decimal" => "2", 
			"delegate" => "2", 
			"double" => "2", 
			"enum" => "2", 
			"float" => "2", 
			"int" => "2", 
			"interface" => "2", 
			"long" => "2", 
			"object" => "2", 
			"sbyte" => "2", 
			"short" => "2", 
			"string" => "2", 
			"struct" => "2", 
			"uint" => "2", 
			"ulong" => "2", 
			"ushort" => "2", 
			"#elif" => "3", 
			"#endif" => "3", 
			"#endregion" => "3", 
			"#else" => "3", 
			"#error" => "3", 
			"#define" => "3", 
			"#if" => "3", 
			"#line" => "3", 
			"#region" => "3", 
			"#undef" => "3", 
			"#warning" => "3", 
			"+" => "4", 
			"-" => "4", 
			"*" => "4", 
			"?" => "4", 
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
			":" => "4", 
			"checked" => "4", 
			"is" => "4", 
			"new" => "4", 
			"sizeof" => "4", 
			"typeof" => "4", 
			"unchecked" => "4");

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
