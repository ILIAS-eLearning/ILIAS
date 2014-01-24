<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_vospl1 extends HFile{
   function HFile_vospl1(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// VOS PL/1
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"aligned" => "1", 
			"allocate" => "1", 
			"auto" => "1", 
			"based" => "1", 
			"begin" => "1", 
			"bin" => "1", 
			"binary" => "1", 
			"case" => "1", 
			"call" => "1", 
			"char" => "1", 
			"char_varying" => "1", 
			"character" => "1", 
			"close" => "1", 
			"const" => "1", 
			"continue" => "1", 
			"defined" => "1", 
			"declare" => "1", 
			"dcl" => "1", 
			"default" => "1", 
			"delete" => "1", 
			"do" => "1", 
			"double" => "1", 
			"else" => "1", 
			"end" => "1", 
			"entry" => "1", 
			"enum" => "1", 
			"extern" => "1", 
			"ext" => "1", 
			"fixed" => "1", 
			"float" => "1", 
			"for" => "1", 
			"format" => "1", 
			"free" => "1", 
			"get" => "1", 
			"goto" => "1", 
			"if" => "1", 
			"int" => "1", 
			"like" => "1", 
			"long" => "1", 
			"on" => "1", 
			"open" => "1", 
			"pointer" => "1", 
			"ptr" => "1", 
			"proc" => "1", 
			"procedure" => "1", 
			"put" => "1", 
			"read" => "1", 
			"register" => "1", 
			"return" => "1", 
			"returns" => "1", 
			"revert" => "1", 
			"rewrite" => "1", 
			"short" => "1", 
			"signal" => "1", 
			"signed" => "1", 
			"sizeof" => "1", 
			"static" => "1", 
			"stop" => "1", 
			"struct" => "1", 
			"switch" => "1", 
			"then" => "1", 
			"typedef" => "1", 
			"union" => "1", 
			"unsigned" => "1", 
			"var" => "1", 
			"void" => "1", 
			"volatile" => "1", 
			"while" => "1", 
			"write" => "1", 
			"$if" => "1", 
			"$endif" => "1", 
			"%include" => "2", 
			"%list" => "2", 
			"%nolist" => "2", 
			"%options" => "2", 
			"%page" => "2", 
			"%replace" => "2", 
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
			"|" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
