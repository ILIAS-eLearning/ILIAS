<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_peoplesoftsqr extends HFile{
   function HFile_peoplesoftsqr(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// 
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
$this->delimiters        	= array("!", "(", ")");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"/L10" => "", 
			"\"SQR\"" => "", 
			"Line" => "", 
			"Comment" => "", 
			"=" => "", 
			"!" => "", 
			"Nocase" => "", 
			"Noquote" => "", 
			"File" => "", 
			"Extensions" => "", 
			"SQR" => "", 
			"SQC" => "", 
			"BEGIN-DOCUMENT" => "1", 
			"BEGIN-FOOTING" => "1", 
			"BEGIN-PROCEDURE" => "1", 
			"BEGIN-SQL" => "1", 
			"BEGIN-SELECT" => "1", 
			"BEGIN-REPORT" => "1", 
			"BEGIN-SETUP" => "1", 
			"BEGIN-HEADING" => "1", 
			"END-DOCUMENT" => "1", 
			"END-FOOTING" => "1", 
			"END-PROCEDURE" => "1", 
			"END-SQL" => "1", 
			"END-SELECT" => "1", 
			"END-REPORT" => "1", 
			"END-SETUP" => "1", 
			"END-HEADING" => "1", 
			"END-IF" => "1", 
			"END-WHILE" => "1", 
			"IF" => "1", 
			"WHILE" => "1", 
			"#DEBUG" => "2", 
			"#DEFINE" => "2", 
			"#ELSE" => "2", 
			"#END" => "2", 
			"#END-IF" => "2", 
			"#ENDIF" => "2", 
			"#IF" => "2", 
			"#IFDEF" => "2", 
			"#IFNDEF" => "2", 
			"#INCLUDE" => "2", 
			"ADD" => "2", 
			"ARRAY" => "2", 
			"ALTER" => "2", 
			"ASK" => "2", 
			"AND" => "2", 
			"ALTER-LOCAL" => "2", 
			"ALTER-PRINTER" => "2", 
			"ARRAY-ADD" => "2", 
			"ARRAY-DIVIDE" => "2", 
			"ARRAY-MULTIPLY" => "2", 
			"ARRAY-SUBTRACT" => "2", 
			"BREAK" => "2", 
			"BAR" => "2", 
			"BOX" => "2", 
			"BEGIN" => "2", 
			"BOTTOM-MARGIN" => "2", 
			"CALL" => "2", 
			"COMMIT" => "2", 
			"CLEAR" => "2", 
			"CONCAT" => "2", 
			"CLOSE" => "2", 
			"CONNECT" => "2", 
			"COLUMNS" => "2", 
			"CREATE" => "2", 
			"CHART" => "2", 
			"CODE" => "2", 
			"COLUMN" => "2", 
			"CLEAR-ARRAY" => "2", 
			"CREATE-ARRAY" => "2", 
			"CHAR" => "2", 
			"CHAR-WIDTH" => "2", 
			"COLOR" => "2", 
			"DIVIDE" => "2", 
			"DOCUMENT" => "2", 
			"DECLARE" => "2", 
			"DO" => "2", 
			"DISPLAY" => "2", 
			"DOLLAR" => "2", 
			"DIRECT" => "2", 
			"DEINIT" => "2", 
			"DECIMAL" => "2", 
			"DATE" => "2", 
			"DEBUG" => "2", 
			"DECLARE-CHART" => "2", 
			"DECLARE-IMAGE" => "2", 
			"DECLARE-LAYOUT" => "2", 
			"DECLARE-PRINTER" => "2", 
			"DECLARE-PROCEDURE" => "2", 
			"DECLARE-REPORT" => "2", 
			"DECLARE-VARIABLE" => "2", 
			"DATE-EDIT-MASK" => "2", 
			"DECIMAL-SEPARATOR" => "2", 
			"DATE-SEPARATOR" => "2", 
			"DISTINCT" => "2", 
			"DAY-OF-WEEK-CASE" => "2", 
			"DAY-OF-WEEK-FULL" => "2", 
			"DAY-OF-WEEK-SHORT" => "2", 
			"ELSE" => "2", 
			"ENCODE" => "2", 
			"EVALUATE" => "2", 
			"END-DECLARE" => "2", 
			"END-EVALUATE" => "2", 
			"END-PROGRAM" => "2", 
			"EXIT-SELECT" => "2", 
			"EXECUTE" => "2", 
			"EXIT" => "2", 
			"EXTRACT" => "2", 
			"END" => "2", 
			"FOOTING" => "2", 
			"FORMFEED" => "2", 
			"FIND" => "2", 
			"FONT" => "2", 
			"FROM" => "2", 
			"FONT-TYPE" => "2", 
			"FLOAT" => "2", 
			"FOR-REPORTS" => "2", 
			"FIELD" => "2", 
			"FILL" => "2", 
			"GET" => "2", 
			"GRAPHIC" => "2", 
			"GOTO" => "2", 
			"HORZ-LINE" => "2", 
			"HORZ" => "2", 
			"HEADING" => "2", 
			"IMAGE" => "2", 
			"INPUT" => "2", 
			"INIT" => "2", 
			"INPUT-DATE-EDIT-MASK" => "2", 
			"INTO" => "2", 
			"INTEGER" => "2", 
			"IMAGE-SIZE" => "2", 
			"INIT-STRING" => "2", 
			"LOOKUP" => "2", 
			"LET" => "2", 
			"LOWERCASE" => "2", 
			"LOAD" => "2", 
			"LAYOUT" => "2", 
			"LINE" => "2", 
			"LAST" => "2", 
			"LISTING" => "2", 
			"LAST-PAGE" => "2", 
			"LOWER" => "2", 
			"LOAD-LOOKUP" => "2", 
			"LOCALE" => "2", 
			"LOCAL" => "2", 
			"LOOPS" => "2", 
			"LEFT-MARGIN" => "2", 
			"LINE-WIDTH" => "2", 
			"LINE-HEIGHT" => "2", 
			"LANDSCAPE" => "2", 
			"MULTIPLY" => "2", 
			"MONEY" => "2", 
			"MOVE" => "2", 
			"MATCH" => "2", 
			"MONEY-SIGN" => "2", 
			"MONEY-SIGN-LOCATION" => "2", 
			"MONTHS-CASE" => "2", 
			"MONTHS-FULL" => "2", 
			"MONTHS-SHORT" => "2", 
			"MONEY-EDIT-MASK" => "2", 
			"MAX-COLUMNS" => "2", 
			"MAX-LINES" => "2", 
			"NEW-PAGE" => "2", 
			"NEW-REPORT" => "2", 
			"NEXT-COLUMN" => "2", 
			"NEWLINE" => "2", 
			"NEWPAGE" => "2", 
			"NEXT-LISTING" => "2", 
			"NOP" => "2", 
			"NUMBER" => "2", 
			"NUMBER-EDIT-MASK" => "2", 
			"NVL" => "2", 
			"NOWAIT" => "2", 
			"NAME" => "2", 
			"OPEN" => "2", 
			"ON-BREAK" => "2", 
			"ON-ERROR" => "2", 
			"ORIENTATION" => "2", 
			"PRINT" => "2", 
			"PRINTER" => "2", 
			"PROCEDURE" => "2", 
			"PROGRAM" => "2", 
			"PAGE" => "2", 
			"POSITION" => "2", 
			"PUT" => "2", 
			"PAGE-NUMBER" => "2", 
			"PRINT-BAR-CODE" => "2", 
			"PRINT-CHART" => "2", 
			"PRINT-DIRECT" => "2", 
			"PRINT-IMAGE" => "2", 
			"POINT-SIZE" => "2", 
			"PITCH" => "2", 
			"PAPER-SIZE" => "2", 
			"PAGE-DEPTH" => "2", 
			"REPORT" => "2", 
			"READ" => "2", 
			"ROLLBACK" => "2", 
			"ROUND" => "2", 
			"RIGHT-MARGIN" => "2", 
			"SELECT" => "2", 
			"SETUP" => "2", 
			"SUBTRACT" => "2", 
			"SQL" => "2", 
			"SYMBOL" => "2", 
			"SIZE" => "2", 
			"SHOW" => "2", 
			"STRING" => "2", 
			"STOP" => "2", 
			"SOURCE" => "2", 
			"STARTUP-FILE" => "2", 
			"TYPE" => "2", 
			"TO" => "2", 
			"THOUSAND-SEPARATOR" => "2", 
			"TIME-SEPARATOR" => "2", 
			"TIMES" => "2", 
			"TEXT" => "2", 
			"TOP-MARGIN" => "2", 
			"USE" => "2", 
			"UPPERCASE" => "2", 
			"USE-COLUMN" => "2", 
			"USE-PRINTER-TYPE" => "2", 
			"USE-PROCEDURE" => "2", 
			"USE-REPORT" => "2", 
			"USING" => "2", 
			"VERT" => "2", 
			"WRITE" => "2", 
			"WAIT" => "2", 
			"WITH" => "2", 
			"WHERE" => "2", 
			"DATEADD" => "3", 
			"DATEDIFF" => "3", 
			"DATENOW" => "3", 
			"DATETOSTR" => "3", 
			"DELETE" => "3", 
			"EDIT" => "3", 
			"EXISTS" => "3", 
			"INSTR" => "3", 
			"ISBLANK" => "3", 
			"ISNULL" => "3", 
			"LENGTH" => "3", 
			"LPAD" => "3", 
			"LTRIM" => "3", 
			"MOD" => "3", 
			"RENAME" => "3", 
			"RPAD" => "3", 
			"RTRIM" => "3", 
			"STRTODATE" => "3", 
			"SUBSTR" => "3", 
			"TO_CHAR" => "3", 
			"TO_NUMBER" => "3", 
			"TRANSLATE" => "3", 
			"UNSTRING" => "3", 
			"UPPER" => "3");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"" => "donothing", 
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
