<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_awk extends HFile{
   function HFile_awk(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// AWK Script
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
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"atan2" => "1", 
			"break" => "1", 
			"BEGIN" => "1", 
			"close" => "1", 
			"continue" => "1", 
			"cos" => "1", 
			"delete" => "1", 
			"do" => "1", 
			"else" => "1", 
			"exp" => "1", 
			"exit" => "1", 
			"END" => "1", 
			"for" => "1", 
			"function" => "1", 
			"getline" => "1", 
			"gsub" => "1", 
			"if" => "1", 
			"index" => "1", 
			"int" => "1", 
			"length" => "1", 
			"local" => "1", 
			"log" => "1", 
			"match" => "1", 
			"next" => "1", 
			"print" => "1", 
			"printf" => "1", 
			"rand" => "1", 
			"return" => "1", 
			"sin" => "1", 
			"split" => "1", 
			"sprintf" => "1", 
			"sqrt" => "1", 
			"srand" => "1", 
			"sub" => "1", 
			"substr" => "1", 
			"system" => "1", 
			"tolower" => "1", 
			"toupper" => "1", 
			"while" => "1", 
			"ARGC" => "2", 
			"ARGV" => "2", 
			"CONVFMT" => "2", 
			"ENVIRON" => "2", 
			"FILENAME" => "2", 
			"FNR" => "2", 
			"FS" => "2", 
			"NF" => "2", 
			"NR" => "2", 
			"OFMT" => "2", 
			"OFS" => "2", 
			"ORS" => "2", 
			"RLENGTH" => "2", 
			"RS" => "2", 
			"RSTART" => "2", 
			"SUBSEP" => "2", 
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
			"$" => "4", 
			"*" => "4");

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
