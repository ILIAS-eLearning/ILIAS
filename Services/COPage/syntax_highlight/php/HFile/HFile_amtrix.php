<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_amtrix extends HFile{
   function HFile_amtrix(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// AMTrix
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
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ARGUMENT" => "1", 
			"AMTRIX_LOGID" => "1", 
			"AND" => "1", 
			"APPEND" => "1", 
			"ARG_LIST" => "1", 
			"ARG_OPT" => "1", 
			"ARGUMENTCOUNT" => "1", 
			"ARRAYSIZE" => "1", 
			"AS" => "1", 
			"ASSIGNMENT" => "1", 
			"BEGIN" => "1", 
			"BINARY" => "1", 
			"BIT_AND" => "1", 
			"BIT_NOT" => "1", 
			"BIT_OR" => "1", 
			"BIT_SHIFT" => "1", 
			"BIT_XOR" => "1", 
			"BLOCK" => "1", 
			"BOUNDED" => "1", 
			"BREAK" => "1", 
			"BY" => "1", 
			"CALL" => "1", 
			"CASE" => "1", 
			"CATCH" => "1", 
			"CENTER" => "1", 
			"CHAR" => "1", 
			"CHARSET" => "1", 
			"CLOSE" => "1", 
			"COMMENTS" => "1", 
			"COMMIT" => "1", 
			"COMPOSITE" => "1", 
			"CONDITIONAL" => "1", 
			"CONSTANT" => "1", 
			"CONSTANTS" => "1", 
			"CONTINUE" => "1", 
			"CONTROL" => "1", 
			"CONVERT" => "1", 
			"COPY" => "1", 
			"COUNT" => "1", 
			"CURRENTDATE" => "1", 
			"DATA" => "1", 
			"DATABASE" => "1", 
			"DATE" => "1", 
			"DEBUG" => "1", 
			"DECLARE" => "1", 
			"DELETE" => "1", 
			"DESTINATION" => "1", 
			"DIR_CLOSE" => "1", 
			"DIR_OPEN" => "1", 
			"DIR_READ" => "1", 
			"DIR_REWIND" => "1", 
			"EDI" => "1", 
			"EDI_CHARSET" => "1", 
			"EDI_READ_CHARSET" => "1", 
			"EDI_READ_INTERCHANGE" => "1", 
			"EDI_TRUNCATE" => "1", 
			"ELEMENT" => "1", 
			"ELSE" => "1", 
			"ERROR" => "1", 
			"EXEC" => "1", 
			"EXECUTE" => "1", 
			"EXIT" => "1", 
			"EXPORT" => "1", 
			"EXPRESSIONS" => "1", 
			"FILE" => "1", 
			"FLOAT" => "1", 
			"FOR" => "1", 
			"FORMAT" => "1", 
			"FROM" => "1", 
			"FROM_ISO8859" => "1", 
			"FUNCTION" => "1", 
			"GETOPT" => "1", 
			"GROUP" => "1", 
			"IF" => "1", 
			"IMPORT" => "1", 
			"INCLUDE" => "1", 
			"INPUT" => "1", 
			"INSERT" => "1", 
			"INTEGER" => "1", 
			"INTO" => "1", 
			"LEFT" => "1", 
			"LOCK" => "1", 
			"LOG" => "1", 
			"LOGID" => "1", 
			"LOOP" => "1", 
			"MANDATORY" => "1", 
			"MODULE" => "1", 
			"MOVE" => "1", 
			"NDEC" => "1", 
			"NOLOG" => "1", 
			"ON" => "1", 
			"OPEN" => "1", 
			"OPTDTA_READ" => "1", 
			"OPTDTA_WRITE" => "1", 
			"OPTIONAL" => "1", 
			"OR" => "1", 
			"ORDER" => "1", 
			"OTHERS" => "1", 
			"OUTPUT" => "1", 
			"PRAGMA" => "1", 
			"PRINT" => "1", 
			"RAW_CLOSE" => "1", 
			"RAW_FLUSH" => "1", 
			"RAW_OPEN" => "1", 
			"RAW_READ" => "1", 
			"RAW_SEEK" => "1", 
			"RAW_TELL" => "1", 
			"RAW_WRITE" => "1", 
			"READ" => "1", 
			"READTAG" => "1", 
			"RECEIVE" => "1", 
			"REGEXP" => "1", 
			"RELATION" => "1", 
			"RELEASE" => "1", 
			"REPEAT" => "1", 
			"RESERVED" => "1", 
			"RETURN" => "1", 
			"RIGHT" => "1", 
			"ROLLBACK" => "1", 
			"SCAN" => "1", 
			"SEGMENT" => "1", 
			"SELECT" => "1", 
			"SEND" => "1", 
			"SEQUENCE" => "1", 
			"SET" => "1", 
			"SLEEP" => "1", 
			"SOURCE" => "1", 
			"SOURCEFILE" => "1", 
			"SOURCELINE" => "1", 
			"SOURCEMODULE" => "1", 
			"SOURCEPROCEDURE" => "1", 
			"SPLIT" => "1", 
			"SQL" => "1", 
			"STATEMENT" => "1", 
			"STR_FIELD" => "1", 
			"STR_FIELDS" => "1", 
			"STR_LOWER" => "1", 
			"STR_UPPER" => "1", 
			"STRCNV" => "1", 
			"STRFIELD" => "1", 
			"STRFIELDS" => "1", 
			"STRING" => "1", 
			"STRLEN" => "1", 
			"STRMID" => "1", 
			"SUB" => "1", 
			"SWITCH" => "1", 
			"SYSTEM" => "1", 
			"TABLE" => "1", 
			"TEXT" => "1", 
			"THROW" => "1", 
			"TO" => "1", 
			"TO_ISO8859" => "1", 
			"TRUNC" => "1", 
			"TRUNCATE" => "1", 
			"TRY" => "1", 
			"TYPE" => "1", 
			"TYPES" => "1", 
			"UNBOUNDED" => "1", 
			"UNIQUE_ID" => "1", 
			"UNIQUE_NAME" => "1", 
			"UNLOCK" => "1", 
			"UPDATE" => "1", 
			"VALUES" => "1", 
			"VARIABLE" => "1", 
			"VARIABLES" => "1", 
			"WHILE" => "1", 
			"WHEN" => "1", 
			"WHERE" => "1", 
			"WORK" => "1", 
			"WRITETAG" => "1", 
			"SE_H�R" => "2", 
			"$error" => "3", 
			"$exec" => "3", 
			"$PGM_exception" => "3");

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
