<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_msgbuilder extends HFile{
   function HFile_msgbuilder(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// MessageBuilder 4edi
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", "	", ",", ".", "?", "/", "	");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"AND" => "1", 
			"BREAK" => "1", 
			"BEGIN" => "1", 
			"BY" => "1", 
			"CATCH" => "1", 
			"CASE" => "1", 
			"CONTINUE" => "1", 
			"CALL" => "1", 
			"DO" => "1", 
			"ELSE" => "1", 
			"EXIT" => "1", 
			"EXEC" => "1", 
			"FOR" => "1", 
			"IF" => "1", 
			"LOOP" => "1", 
			"OR" => "1", 
			"RETURN" => "1", 
			"REPEAT" => "1", 
			"SWITCH" => "1", 
			"TRY" => "1", 
			"THROW" => "1", 
			"TO" => "1", 
			"WHILE" => "1", 
			"WHEN" => "1", 
			"WHERE" => "1", 
			"WORK" => "1", 
			"APPEND" => "2", 
			"AS" => "2", 
			"ASSIGNMENT" => "2", 
			"BYTE" => "2", 
			"boolean" => "2", 
			"binary" => "2", 
			"BIT" => "2", 
			"BOUNDED" => "2", 
			"CHAR" => "2", 
			"CONSTANT" => "2", 
			"CENTER" => "2", 
			"CHARSET" => "2", 
			"COMMENTS" => "2", 
			"COMPOSITE" => "2", 
			"CONDITIONAL" => "2", 
			"CONSTANTS" => "2", 
			"CONTROL" => "2", 
			"DEFAULT" => "2", 
			"double" => "2", 
			"DECLARE" => "2", 
			"DATA" => "2", 
			"DATABASE" => "2", 
			"DESTINATION" => "2", 
			"EDI" => "2", 
			"EDI_CHARSET" => "2", 
			"ELEMENT" => "2", 
			"ERROR" => "2", 
			"EXECUTE" => "2", 
			"EXPORT" => "2", 
			"FALSE" => "2", 
			"float" => "2", 
			"FUNCTION" => "2", 
			"FILE" => "2", 
			"FORMAT" => "2", 
			"FROM_ISO8859" => "2", 
			"FROM" => "2", 
			"GROUP" => "2", 
			"IMPORT" => "2", 
			"INTEGER" => "2", 
			"INTO" => "2", 
			"INCLUDE" => "2", 
			"IN" => "2", 
			"INOUT" => "2", 
			"INPUT" => "2", 
			"INSERT" => "2", 
			"LOGID" => "2", 
			"LOCK" => "2", 
			"MANDATORY" => "2", 
			"OUT" => "2", 
			"ON" => "2", 
			"OPTIONAL" => "2", 
			"ORDER" => "2", 
			"OTHERS" => "2", 
			"OUTPUT" => "2", 
			"RELATION" => "2", 
			"RELEASE" => "2", 
			"RESERVED" => "2", 
			"ROLLBACK" => "2", 
			"SHORT" => "2", 
			"static" => "2", 
			"SUB" => "2", 
			"STRING" => "2", 
			"STATEMENT" => "2", 
			"SEGMENT" => "2", 
			"SELECT" => "2", 
			"SEQUENCE" => "2", 
			"SET" => "2", 
			"SOURCE" => "2", 
			"SQL" => "2", 
			"TRUE" => "2", 
			"TABLE" => "2", 
			"TEXT" => "2", 
			"TO_ISO8859" => "2", 
			"TYPE" => "2", 
			"TYPES" => "2", 
			"UNBOUNDED" => "2", 
			"UNLOCK" => "2", 
			"UPDATE" => "2", 
			"VOID" => "2", 
			"VALUES" => "2", 
			"VARIABLE" => "2", 
			"VARIABLES" => "2", 
			"$error" => "2", 
			"$exec" => "2", 
			"$PGM_exception" => "2", 
			"ARRAYSIZE" => "3", 
			"BIT_AND" => "3", 
			"BIT_NOT" => "3", 
			"BIT_OR" => "3", 
			"BIT_SHIFT" => "3", 
			"BIT_XOR" => "3", 
			"CLOSE" => "3", 
			"COMMIT" => "3", 
			"CONVERT" => "3", 
			"COPY" => "3", 
			"COUNT" => "3", 
			"CURRENTDATE" => "3", 
			"DATE" => "3", 
			"DEBUG" => "3", 
			"DELETE" => "3", 
			"EDI_READ_CHARSET" => "3", 
			"EDI_READ_INTERCHANGE" => "3", 
			"EDI_TRUNCATE" => "3", 
			"EXPRESSIONS" => "3", 
			"LOG" => "3", 
			"LEFT" => "3", 
			"REGEXP" => "3", 
			"READ" => "3", 
			"RIGHT" => "3", 
			"MOVE" => "3", 
			"NDEC" => "3", 
			"NOLOG" => "3", 
			"OPEN" => "3", 
			"PRINT" => "3", 
			"PRINTERR" => "3", 
			"PRAGMA" => "3", 
			"SLEEP" => "3", 
			"STRMID" => "3", 
			"STRLEN" => "3", 
			"STRFIELD" => "3", 
			"STRFIELDS" => "3", 
			"STR_FIELD" => "3", 
			"STR_FIELDS" => "3", 
			"STR_LOWER" => "3", 
			"STR_UPPER" => "3", 
			"STRCNV" => "3", 
			"SOURCEFILE" => "3", 
			"SOURCELINE" => "3", 
			"SOURCEMODULE" => "3", 
			"SOURCEPROCEDURE" => "3", 
			"SPLIT" => "3", 
			"SYSTEM" => "3", 
			"UNIQUE_ID" => "3", 
			"UNIQUE_NAME" => "3", 
			"AMTRIX_LOGID" => "4", 
			"ARGUMENT" => "4", 
			"ARG_LIST" => "4", 
			"ARG_OPT" => "4", 
			"ARGUMENTCOUNT" => "4", 
			"DIR_CLOSE" => "4", 
			"DIR_OPEN" => "4", 
			"DIR_READ" => "4", 
			"DIR_REWIND" => "4", 
			"GETOPT" => "4", 
			"OPTDTA_READ" => "4", 
			"OPTDTA_WRITE" => "4", 
			"RAW_CLOSE" => "4", 
			"RAW_FLUSH" => "4", 
			"RAW_OPEN" => "4", 
			"RAW_READ" => "4", 
			"RAW_SEEK" => "4", 
			"RAW_TELL" => "4", 
			"RAW_WRITE" => "4", 
			"RECEIVE" => "4", 
			"READTAG" => "4", 
			"TRUNCATE" => "4", 
			"TRUNC" => "4", 
			"SEND" => "4", 
			"SCAN" => "4", 
			"WRITETAG" => "4", 
			"ARG" => "5", 
			"EXTERNAL" => "5", 
			"MODULE" => "5", 
			"PGM" => "5", 
			"PUBLIC" => "5", 
			"TRANSFER" => "5", 
			"USE" => "5");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
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
