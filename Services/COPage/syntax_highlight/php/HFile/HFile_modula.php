<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_modula extends HFile{
   function HFile_modula(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Modula3
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple");
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

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("(*");
$this->blockcommentoff   	= array("*)");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"AND" => "1", 
			"AS" => "1", 
			"ANY" => "1", 
			"ARRAY" => "1", 
			"BEGIN" => "1", 
			"BITS" => "1", 
			"BRANDED" => "1", 
			"BY" => "1", 
			"CASE" => "1", 
			"CONST" => "1", 
			"DIV" => "1", 
			"DO" => "1", 
			"END" => "1", 
			"EVAL" => "1", 
			"EXCEPT" => "1", 
			"EXCEPTION" => "1", 
			"EXIT" => "1", 
			"EXPORTS" => "1", 
			"ELSIF" => "1", 
			"ELSE" => "1", 
			"FROM" => "1", 
			"FINALLY" => "1", 
			"FOR" => "1", 
			"GENERIC" => "1", 
			"IF" => "1", 
			"IMPORT" => "1", 
			"IN" => "1", 
			"INTERFACE" => "1", 
			"LOCK" => "1", 
			"LOOP" => "1", 
			"METHODS" => "1", 
			"MOD" => "1", 
			"MODULE" => "1", 
			"NOT" => "1", 
			"OBJECT" => "1", 
			"OF" => "1", 
			"OR" => "1", 
			"OVERRIDES" => "1", 
			"PROCEDURE" => "1", 
			"REPEAT" => "1", 
			"RETURN" => "1", 
			"REVEAL" => "1", 
			"RAISE" => "1", 
			"RAISES" => "1", 
			"READONLY" => "1", 
			"RECORD" => "1", 
			"REF" => "1", 
			"ROOT" => "1", 
			"SET" => "1", 
			"THEN" => "1", 
			"TO" => "1", 
			"TRY" => "1", 
			"TYPE" => "1", 
			"TYPECASE" => "1", 
			"UNTIL" => "1", 
			"UNTRACED" => "1", 
			"UNSAFE" => "1", 
			"VALUE" => "1", 
			"VAR" => "1", 
			"WHILE" => "1", 
			"WITH" => "1", 
			"ABS" => "2", 
			"ADDRESS" => "2", 
			"ADR" => "2", 
			"ADRSIZE" => "2", 
			"BITSIZE" => "2", 
			"BOOLEAN" => "2", 
			"BYTESIZE" => "2", 
			"CARDINAL" => "2", 
			"CEILING" => "2", 
			"CHAR" => "2", 
			"DEC" => "2", 
			"DISPOSE" => "2", 
			"EXTENDED" => "2", 
			"FALSE" => "2", 
			"FIRST" => "2", 
			"FLOAT" => "2", 
			"FLOOR" => "2", 
			"INC" => "2", 
			"INTEGER" => "2", 
			"ISTYPE" => "2", 
			"LAST" => "2", 
			"LONGREAL" => "2", 
			"LOOPHOLE" => "2", 
			"MAX" => "2", 
			"MIN" => "2", 
			"MUTEX" => "2", 
			"NARROW" => "2", 
			"NEW" => "2", 
			"NIL" => "2", 
			"NULL" => "2", 
			"NUMBER" => "2", 
			"ORD" => "2", 
			"REAL" => "2", 
			"REFANY" => "2", 
			"ROUND" => "2", 
			"SUBARRAY" => "2", 
			"TEXT" => "2", 
			"TRUE" => "2", 
			"TRUNC" => "2", 
			"TYPECODE" => "2", 
			"VAL" => "2");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
