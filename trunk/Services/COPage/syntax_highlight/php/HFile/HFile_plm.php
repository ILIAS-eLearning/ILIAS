<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_plm extends HFile{
   function HFile_plm(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// PLM
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "$", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"@" => "1", 
			"BLOCKINPUT" => "1", 
			"BLOCKINWORD" => "1", 
			"BLOCKOUTPUT" => "1", 
			"BLOCKOUTWORD" => "1", 
			"CALL" => "1", 
			"CASE" => "1", 
			"CMPB" => "1", 
			"CMPW" => "1", 
			"DECLARE" => "1", 
			"DISABLE" => "1", 
			"DO" => "1", 
			"ELSE" => "1", 
			"ENABLE" => "1", 
			"END" => "1", 
			"EXTERNAL" => "1", 
			"GOTO" => "1", 
			"HALT" => "1", 
			"IF" => "1", 
			"INPUT" => "1", 
			"INTERRUPT" => "1", 
			"INWORD" => "1", 
			"OUTPUT" => "1", 
			"OUTWORD" => "1", 
			"PROCEDURE" => "1", 
			"PUBLIC" => "1", 
			"REENTRANT" => "1", 
			"RETURN" => "1", 
			"SIZE" => "1", 
			"STRUCTURE" => "1", 
			"THEN" => "1", 
			"WHILE" => "1", 
			"." => "2", 
			"*" => "2", 
			"+" => "2", 
			"-" => "2", 
			"=" => "2", 
			"//" => "2", 
			"/" => "2", 
			"%" => "2", 
			"&" => "2", 
			">" => "2", 
			"<" => "2", 
			"^" => "2", 
			"!" => "2", 
			"|" => "2", 
			"ABS" => "2", 
			"AND" => "2", 
			"CARRY" => "2", 
			"DEC" => "2", 
			"FINDB" => "2", 
			"FINDRB" => "2", 
			"FINDRW" => "2", 
			"FINDW" => "2", 
			"FLAGS" => "2", 
			"IABS" => "2", 
			"LAST" => "2", 
			"LENGTH" => "2", 
			"MINUS" => "2", 
			"MOVB" => "2", 
			"MOVE" => "2", 
			"MOVRB" => "2", 
			"MOVRW" => "2", 
			"MOVW" => "2", 
			"NOT" => "2", 
			"OR" => "2", 
			"PARITY" => "2", 
			"PLUS" => "2", 
			"ROL" => "2", 
			"ROR" => "2", 
			"SAL" => "2", 
			"SAR" => "2", 
			"SCL" => "2", 
			"SCR" => "2", 
			"SETB" => "2", 
			"SETW" => "2", 
			"SHL" => "2", 
			"SHR" => "2", 
			"SIGN" => "2", 
			"SKIPB" => "2", 
			"SKIPRB" => "2", 
			"SKIPRW" => "2", 
			"SKIPW" => "2", 
			"TIME" => "2", 
			"XLAT" => "2", 
			"XOR" => "2", 
			"ZERO" => "2", 
			"BYTE" => "3", 
			"DWORD" => "3", 
			"DOUBLE" => "3", 
			"FLOAT" => "3", 
			"FIX" => "3", 
			"HIGH" => "3", 
			"INTEGER" => "3", 
			"INT" => "3", 
			"LOW" => "3", 
			"POINTER" => "3", 
			"REAL" => "3", 
			"SIGNED" => "3", 
			"UNSIGNED" => "3", 
			"WORD" => "3", 
			"$" => "4", 
			"COMPACT" => "4", 
			"CODE" => "4", 
			"COND" => "4", 
			"DEBUG" => "4", 
			"EJECT" => "4", 
			"INTVECTOR" => "4", 
			"INCLUDE" => "4", 
			"LARGE" => "4", 
			"LEFTMARGIN" => "4", 
			"LIST" => "4", 
			"MOD86" => "4", 
			"MOD186" => "4", 
			"MEDIUM" => "4", 
			"NODEBUG" => "4", 
			"NONINTVECTOR" => "4", 
			"NOOBJECT" => "4", 
			"NOPAGING" => "4", 
			"NOPRINT" => "4", 
			"NOSYMBOLS" => "4", 
			"NOTYPE" => "4", 
			"NOXREF" => "4", 
			"NOCODE" => "4", 
			"NOCOND" => "4", 
			"NOLIST" => "4", 
			"NOOVERFLOW" => "4", 
			"OBJECT" => "4", 
			"OPTIMIZE" => "4", 
			"OVERFLOW" => "4", 
			"PAGING" => "4", 
			"PAGELENGTH" => "4", 
			"PAGEWIDTH" => "4", 
			"PRINT" => "4", 
			"RAM" => "4", 
			"ROM" => "4", 
			"RESTORE" => "4", 
			"RESET" => "4", 
			"SMALL" => "4", 
			"SYMBOLS" => "4", 
			"SAVE" => "4", 
			"SET" => "4", 
			"SUBTITLE" => "4", 
			"TITLE" => "4", 
			"TYPE" => "4", 
			"XREF" => "4");

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

}?>
