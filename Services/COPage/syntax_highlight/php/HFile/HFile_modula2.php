<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_modula2 extends HFile{
   function HFile_modula2(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Modula2/2k-02-6-nr
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "purple", "gray", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("ARRAY", "BEGIN", "CONST", "DO", "ELSE", "ELSIF", "FOR", "FROM", "IF", "IMPORT", "LOOP", "PROCEDURE", "RECORD", "REPEAT", "TYPE", "VAR", "WHILE", "WITH");
$this->unindent          	= array("ELSE", "ELSIF", "END", "UNTIL");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", " ", ",", ".", "?", "/");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("(*");
$this->blockcommentoff   	= array("*)");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ABS" => "1", 
			"AND" => "1", 
			"ADDRESS" => "1", 
			"ARRAY" => "1", 
			"BEGIN" => "1", 
			"BITSET" => "1", 
			"BY" => "1", 
			"CASE" => "1", 
			"CONST" => "1", 
			"DEALLOCATE" => "6", 
			"DEFINITION" => "1", 
			"DO" => "1", 
			"ELSE" => "1", 
			"ELSIF" => "1", 
			"END" => "1", 
			"EXCL" => "1", 
			"EXIT" => "1", 
			"FOR" => "1", 
			"FOREIGN" => "1", 
			"FROM" => "1", 
			"HIGH" => "1", 
			"IF" => "1", 
			"IMPLEMENTATION" => "1", 
			"IMPORT" => "1", 
			"IN" => "1", 
			"INC" => "1", 
			"INCL" => "1", 
			"LOOP" => "1", 
			"MAX" => "1", 
			"MIN" => "1", 
			"MODULE" => "1", 
			"NOT" => "1", 
			"OF" => "1", 
			"OR" => "1", 
			"POINTER" => "1", 
			"PROCEDURE" => "1", 
			"RECORD" => "1", 
			"REPEAT" => "1", 
			"RETURN" => "1", 
			"SET" => "1", 
			"STEP" => "1", 
			"THEN" => "1", 
			"TO" => "1", 
			"TYPE" => "1", 
			"UNTIL" => "1", 
			"VAR" => "1", 
			"WHILE" => "1", 
			"WITH" => "1", 
			"^" => "2", 
			"BOOLEAN" => "2", 
			"BYTE" => "2", 
			"CARDINAL" => "2", 
			"CHAR" => "2", 
			"FLOAT" => "2", 
			"INTEGER" => "2", 
			"IntSet" => "2", 
			"NIL" => "2", 
			"REAL" => "2", 
			"=" => "3", 
			":" => "3", 
			"<=" => "3", 
			">=" => "3", 
			"#" => "3", 
			"+" => "3", 
			"-" => "3", 
			"*" => "3", 
			"//" => "3", 
			"/" => "3", 
			"DIV" => "3", 
			"MOD" => "3", 
			"[" => "4", 
			"]" => "4", 
			"FALSE" => "5", 
			"TRUE" => "5", 
			"ALLOCATE" => "6", 
			"CHR" => "6", 
			"DEC" => "6", 
			"DISPOSE" => "6", 
			"NEW" => "6", 
			"ORD" => "6", 
			"SIZE" => "6", 
			"SYSTEM" => "6", 
			"TRUNC" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"6" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
