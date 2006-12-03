<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_pearl extends HFile{
   function HFile_pearl(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Pearl
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

$this->linecommenton     	= array("!");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ACTIVATE" => "1", 
			"AFTER" => "1", 
			"ALL" => "1", 
			"ALPHIC" => "1", 
			"ALT" => "1", 
			"AT" => "1", 
			"BASIC" => "1", 
			"BEGIN" => "1", 
			"BIT" => "1", 
			"BOLT" => "1", 
			"BY" => "1", 
			"CALL" => "1", 
			"CASE" => "1", 
			"CHARACTER" => "1", 
			"CHAR" => "1", 
			"CLOCK" => "1", 
			"CLOSE" => "1", 
			"CONT" => "1", 
			"CONTINUE" => "1", 
			"CONTROL" => "1", 
			"CONVERT" => "1", 
			"CREATE" => "1", 
			"CREATED" => "1", 
			"CYCLE" => "1", 
			"DATION" => "1", 
			"DECLARE" => "1", 
			"DCL" => "1", 
			"DELETE" => "1", 
			"DIM" => "1", 
			"DIRECT" => "1", 
			"DISABLE" => "1", 
			"DURATION" => "1", 
			"DUR" => "1", 
			"DURING" => "1", 
			"ELSE" => "1", 
			"ENABLE" => "1", 
			"ENTER" => "1", 
			"ENTRY" => "1", 
			"EVERY" => "1", 
			"EXIT" => "1", 
			"FIN" => "1", 
			"FIXED" => "1", 
			"FLOAT" => "1", 
			"FOR" => "1", 
			"FORBACK" => "1", 
			"FORMAT" => "1", 
			"FORWARD" => "1", 
			"FREE" => "1", 
			"FROM" => "1", 
			"GET" => "1", 
			"GLOBAL" => "1", 
			"GOTO" => "1", 
			"HRS" => "1", 
			"IDENTIACAL" => "1", 
			"IDENT" => "1", 
			"IF" => "1", 
			"IN" => "1", 
			"INDUCE" => "1", 
			"INITIAL" => "1", 
			"INIT" => "1", 
			"INLINE" => "1", 
			"INOUT" => "1", 
			"INTERRUPT" => "1", 
			"IRPT" => "1", 
			"INTFAC" => "1", 
			"INV" => "1", 
			"IS" => "1", 
			"ISNT" => "1", 
			"LEAVE" => "1", 
			"LENGTH" => "1", 
			"MATCH" => "1", 
			"MAX" => "1", 
			"MIN" => "1", 
			"NIL" => "1", 
			"NOCYCL" => "1", 
			"NOMATCH" => "1", 
			"NOSTREAM" => "1", 
			"ON" => "1", 
			"ONEOF" => "1", 
			"OPEN" => "1", 
			"OPERATOR" => "1", 
			"OUT" => "1", 
			"PRECEDENCE" => "1", 
			"PRESET" => "1", 
			"PREVENT" => "1", 
			"PRIORITY" => "1", 
			"PUT" => "1", 
			"READ" => "1", 
			"REENT" => "1", 
			"REF" => "1", 
			"RELEASE" => "1", 
			"REPEAT" => "1", 
			"REQUEST" => "1", 
			"RESERVE" => "1", 
			"RESIDENT" => "1", 
			"RESUME" => "1", 
			"RETURN" => "1", 
			"RETURNS" => "1", 
			"SEC" => "1", 
			"SEMA" => "1", 
			"SEND" => "1", 
			"SIGNAL" => "1", 
			"SPECIFY" => "1", 
			"SPC" => "1", 
			"STREAM" => "1", 
			"STRUCT" => "1", 
			"SUSPEND" => "1", 
			"SYS" => "1", 
			"SYSTEM" => "1", 
			"TAKE" => "1", 
			"TERMINATE" => "1", 
			"TFU" => "1", 
			"THEN" => "1", 
			"TO" => "1", 
			"TRIGGER" => "1", 
			"TYPE" => "1", 
			"UNTIL" => "1", 
			"UPON" => "1", 
			"USING" => "1", 
			"WHEN" => "1", 
			"WHILE" => "1", 
			"WRITE" => "1", 
			"A" => "2", 
			"ABS" => "2", 
			"ADV" => "2", 
			"AND" => "2", 
			"ANY" => "2", 
			"B" => "2", 
			"B1" => "2", 
			"B2" => "2", 
			"B3" => "2", 
			"B4" => "2", 
			"CAN" => "2", 
			"CAT" => "2", 
			"COL" => "2", 
			"COS" => "2", 
			"CSHIFT" => "2", 
			"D" => "2", 
			"DATE" => "2", 
			"E" => "2", 
			"ENTIER" => "2", 
			"EQ" => "2", 
			"EXOR" => "2", 
			"EXP" => "2", 
			"F" => "2", 
			"FIT" => "2", 
			"GE" => "2", 
			"GT" => "2", 
			"IDF" => "2", 
			"LE" => "2", 
			"LINE" => "2", 
			"LIST" => "2", 
			"LN" => "2", 
			"LT" => "2", 
			"LWB" => "2", 
			"NE" => "2", 
			"NEW" => "2", 
			"NOT" => "2", 
			"NOW" => "2", 
			"OLD" => "2", 
			"OR" => "2", 
			"PAGE" => "2", 
			"POS" => "2", 
			"PRM" => "2", 
			"R" => "2", 
			"REM" => "2", 
			"ROUND" => "2", 
			"RST" => "2", 
			"S" => "2", 
			"SIGN" => "2", 
			"SIN" => "2", 
			"SKIP" => "2", 
			"SOP" => "2", 
			"SQRT" => "2", 
			"T" => "2", 
			"TAN" => "2", 
			"TANH" => "2", 
			"TOBIT" => "2", 
			"END" => "3", 
			"MODEND" => "3", 
			"MODULE" => "3", 
			"PROBLEM" => "3", 
			"PROCEDURE" => "3", 
			"PRIO" => "3", 
			"PROC" => "3", 
			"TASK" => "3");

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
