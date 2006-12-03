<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_spin extends HFile{
   function HFile_spin(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Spin
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "@", "%", "^", "&", "*", "(", ")", "|", "\\", "/", "{", "}", "[", "]", ";", "\"", "'", " ", ",", "	", ".");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"bit" => "1", 
			"bool" => "1", 
			"byte" => "1", 
			"chan" => "1", 
			"int" => "1", 
			"local" => "1", 
			"mtype" => "1", 
			"short" => "1", 
			"typedef" => "1", 
			"unsigned" => "1", 
			"assert" => "2", 
			"init" => "2", 
			"priority" => "2", 
			"proctype" => "2", 
			"provided" => "2", 
			"active" => "3", 
			"break" => "3", 
			"do" => "3", 
			"else" => "3", 
			"empty" => "3", 
			"enabled" => "3", 
			"eval" => "3", 
			"fi" => "3", 
			"full" => "3", 
			"goto" => "3", 
			"if" => "3", 
			"inline" => "3", 
			"len" => "3", 
			"nempty" => "3", 
			"nfull" => "3", 
			"od" => "3", 
			"of" => "3", 
			"printf" => "3", 
			"run" => "3", 
			"skip" => "3", 
			"timeout" => "3", 
			"xr" => "3", 
			"xs" => "3", 
			"?" => "4", 
			"??" => "4", 
			"!" => "4", 
			"!!" => "4", 
			"@" => "4", 
			"!=" => "5", 
			"+" => "5", 
			"-" => "5", 
			"->" => "5", 
			"::" => "5", 
			"<" => "5", 
			"<=" => "5", 
			"==" => "5", 
			">" => "5", 
			">=" => "5", 
			"unless" => "5", 
			"_" => "6", 
			"_last" => "6", 
			"_pid" => "6", 
			"cond_expr" => "6", 
			"false" => "6", 
			"np_" => "6", 
			"pc_value" => "6", 
			"STDIN" => "6", 
			"true" => "6", 
			"accept" => "7", 
			"end" => "7", 
			"progress" => "7", 
			"atomic" => "8", 
			"d_step" => "8", 
			"hidden" => "8", 
			"ltl" => "8", 
			"never" => "8", 
			"notrace" => "8", 
			"trace" => "8", 
			"show" => "8");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing", 
			"7" => "donothing", 
			"8" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
