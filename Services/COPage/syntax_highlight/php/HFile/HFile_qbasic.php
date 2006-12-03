<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_qbasic extends HFile{
   function HFile_qbasic(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// QBasic
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

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
			"$DYNAMIC" => "1", 
			"$INCLUDE" => "1", 
			"$STATIC" => "1", 
			"BLOAD" => "1", 
			"BSAVE" => "1", 
			"BYVAL" => "1", 
			"CALL" => "1", 
			"CASE" => "1", 
			"CDECL" => "1", 
			"CHAIN" => "1", 
			"CHR$" => "1", 
			"CIRCLE" => "1", 
			"CLOSE" => "1", 
			"CLS" => "1", 
			"COLOR" => "1", 
			"COM" => "1", 
			"COMMAND" => "1", 
			"COMMAND$" => "1", 
			"COMMON" => "1", 
			"CONST" => "1", 
			"CTRL" => "1", 
			"DAT" => "1", 
			"DATA" => "1", 
			"DATE$" => "1", 
			"DECLARE" => "1", 
			"DEF" => "1", 
			"DEFINT" => "1", 
			"DEFtype" => "1", 
			"DGROUP" => "1", 
			"DIM" => "1", 
			"DOS" => "1", 
			"DOUBLE" => "1", 
			"DRAW" => "1", 
			"EGA" => "1", 
			"ELSE" => "1", 
			"ELSEIF" => "1", 
			"END" => "1", 
			"ENVIRON$" => "1", 
			"ERASE" => "1", 
			"ERDEV" => "1", 
			"ERDEV$" => "1", 
			"ERROR" => "1", 
			"EXIT" => "1", 
			"FIELD" => "1", 
			"FILEATTR" => "1", 
			"FOR" => "1", 
			"FUNCTION" => "1", 
			"GET" => "1", 
			"GOSUB" => "1", 
			"GOTO" => "1", 
			"IBM" => "1", 
			"INKEY$" => "1", 
			"INPUT" => "1", 
			"INPUT$" => "1", 
			"INSTR" => "1", 
			"INTEGER" => "1", 
			"INTERRUPT" => "1", 
			"IOCTL" => "1", 
			"IOCTL$" => "1", 
			"LBOUND" => "1", 
			"LEFT" => "1", 
			"LEN" => "1", 
			"LINE" => "1", 
			"LOCATE" => "1", 
			"LOCK" => "1", 
			"LONG" => "1", 
			"LOOP" => "1", 
			"LPRINT" => "1", 
			"LSET" => "1", 
			"MCGA" => "1", 
			"MID$" => "1", 
			"NAME" => "1", 
			"NEXT" => "1", 
			"NOT" => "1", 
			"OFF" => "1", 
			"OPEN" => "1", 
			"OPTION" => "1", 
			"OUTPUT" => "1", 
			"PALETTE" => "1", 
			"PEN" => "1", 
			"PLAY" => "1", 
			"PRINT" => "1", 
			"PSET" => "1", 
			"PUT" => "1", 
			"RANDOM" => "1", 
			"RANDOMIZE" => "1", 
			"READ" => "1", 
			"README" => "1", 
			"REDIM" => "1", 
			"RESTORE" => "1", 
			"RESUME" => "1", 
			"RETURN" => "1", 
			"RIGHT" => "1", 
			"RIGHT$" => "1", 
			"RSET" => "1", 
			"SCREEN" => "1", 
			"SEEK" => "1", 
			"SEG" => "1", 
			"SELECT" => "1", 
			"SETMEM" => "1", 
			"SHARED" => "1", 
			"SHIFT" => "1", 
			"SINGLE" => "1", 
			"STATIC" => "1", 
			"STEP" => "1", 
			"STICK" => "1", 
			"STOP" => "1", 
			"STRIG" => "1", 
			"STRING" => "1", 
			"STRING$" => "1", 
			"SUB" => "1", 
			"THEN" => "1", 
			"TIME$" => "1", 
			"TIMER" => "1", 
			"TYPE" => "1", 
			"UBOUND" => "1", 
			"UCASE$" => "1", 
			"UEVENT" => "1", 
			"UNLOCK" => "1", 
			"UNTIL" => "1", 
			"USING" => "1", 
			"VARPTR" => "1", 
			"VARPTR$" => "1", 
			"VARSEG" => "1", 
			"VGA" => "1", 
			"VIEW" => "1", 
			"WEND" => "1", 
			"WHILE" => "1", 
			"WIDTH" => "1", 
			"WINDOW" => "1", 
			"WRITE" => "1");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
