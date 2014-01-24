<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_rexx extends HFile{
   function HFile_rexx(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// REXX
/*************************************/
// Flags

$this->nocase            	= "1";
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
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ADDRESS" => "1", 
			"ADDITIONAL" => "1", 
			"ANY" => "1", 
			"ARG" => "1", 
			"ARGUMENTS" => "1", 
			"ARRAY" => "1", 
			"BY" => "1", 
			"CALL" => "1", 
			"CASELESS" => "1", 
			"CONTINUE" => "1", 
			"CLASS" => "1", 
			"DESCRIPTION" => "1", 
			"DIGITS" => "1", 
			"DO" => "1", 
			"DROP" => "1", 
			"END" => "1", 
			"ENGINEERING" => "1", 
			"ERROR" => "1", 
			"EXIT" => "1", 
			"EXPOSE" => "1", 
			"ELSE" => "1", 
			"FAILURE" => "1", 
			"FOR" => "1", 
			"FOREVER" => "1", 
			"FORM" => "1", 
			"FORWARD" => "1", 
			"FUZZ" => "1", 
			"GUARD" => "1", 
			"HALT" => "1", 
			"IF" => "1", 
			"INTERPRET" => "1", 
			"ITERATE" => "1", 
			"LEAVE" => "1", 
			"LOWER" => "1", 
			"LOSTDIGITS" => "1", 
			"MESSAGE" => "1", 
			"NAME" => "1", 
			"NOP" => "1", 
			"NOMETHOD" => "1", 
			"NOSTRING" => "1", 
			"NOTREADY" => "1", 
			"NOVALUE" => "1", 
			"NUMERIC" => "1", 
			"ON" => "1", 
			"OFF" => "1", 
			"OTHERWISE" => "1", 
			"PARSE" => "1", 
			"PROCEDURE" => "1", 
			"PULL" => "1", 
			"PUSH" => "1", 
			"PROPAGATE" => "1", 
			"QUEUE" => "1", 
			"RAISE" => "1", 
			"REPLY" => "1", 
			"RETURN" => "1", 
			"RET" => "1", 
			"RC" => "1", 
			"SAY" => "1", 
			"SCIENTIFIC" => "1", 
			"SELECT" => "1", 
			"SIGL" => "1", 
			"SIGNAL" => "1", 
			"SOURCE" => "1", 
			"SYNTAX" => "1", 
			"THEN" => "1", 
			"TO" => "1", 
			"TRACE" => "1", 
			"UPPER" => "1", 
			"UNTIL" => "1", 
			"USE" => "1", 
			"USER" => "1", 
			"VERSION" => "1", 
			"WHEN" => "1", 
			"WHILE" => "1", 
			"WITH" => "1", 
			"ABBREV" => "2", 
			"ABS" => "2", 
			"APPEND" => "2", 
			"BEEP" => "2", 
			"BINARY" => "2", 
			"BITAND" => "2", 
			"BITOR" => "2", 
			"BITXOR" => "2", 
			"BOTH" => "2", 
			"B2X" => "2", 
			"CENTER" => "2", 
			"CHANGESTR" => "2", 
			"CHAR" => "2", 
			"CHARIN" => "2", 
			"CHAROUT" => "2", 
			"CHARS" => "2", 
			"COMPARE" => "2", 
			"CONDITION" => "2", 
			"COPIES" => "2", 
			"COUNTSTR" => "2", 
			"CLOSE" => "2", 
			"C2D" => "2", 
			"C2X" => "2", 
			"DATETIME" => "2", 
			"DATATYPE" => "2", 
			"DATE" => "2", 
			"DELSTR" => "2", 
			"DELWORD" => "2", 
			"DIRECTORY" => "2", 
			"D2C" => "2", 
			"D2X" => "2", 
			"ERRORTEXT" => "2", 
			"EXISTS" => "2", 
			"FILESPEC" => "2", 
			"FLUSH" => "2", 
			"FORMAT" => "2", 
			"HANDLE" => "2", 
			"INSERT" => "2", 
			"LASTPOS" => "2", 
			"LEFT" => "2", 
			"LENGTH" => "2", 
			"LINE" => "2", 
			"LINEIN" => "2", 
			"LINEOUT" => "2", 
			"LINES" => "2", 
			"MAX" => "2", 
			"MIN" => "2", 
			"NOBUFFER" => "2", 
			"OPEN" => "2", 
			"OVERLAY" => "2", 
			"POS" => "2", 
			"POSITION" => "2", 
			"QUEUED" => "2", 
			"QUERY" => "2", 
			"RANDOM" => "2", 
			"RECLENGTH" => "2", 
			"READ" => "2", 
			"REPLACE" => "2", 
			"REVERSE" => "2", 
			"RIGHT" => "2", 
			"SEEK" => "2", 
			"SIGN" => "2", 
			"SIZE" => "2", 
			"SHARED" => "2", 
			"SHAREREAD" => "2", 
			"SHAREWRITE" => "2", 
			"SOURCELINE" => "2", 
			"SPACE" => "2", 
			"STREAM" => "2", 
			"STREAMTYPE" => "2", 
			"STRIP" => "2", 
			"SUBWORD" => "2", 
			"SUBSTR" => "2", 
			"SYMBOL" => "2", 
			"SYS" => "2", 
			"TIME" => "2", 
			"TIMESTAMP" => "2", 
			"TRANSLATE" => "2", 
			"TRUNC" => "2", 
			"VAR" => "2", 
			"VALUE" => "2", 
			"VERIFY" => "2", 
			"WORD" => "2", 
			"WORDINDEX" => "2", 
			"WORDLENGTH" => "2", 
			"WORDPOS" => "2", 
			"WORDS" => "2", 
			"WRITE" => "2", 
			"XRANGE" => "2", 
			"X2B" => "2", 
			"X2C" => "2", 
			"X2D" => "2", 
			"PID" => "3", 
			"PPRIO" => "3", 
			"PTIME" => "3", 
			"RxFuncAdd" => "3", 
			"RxFuncDrop" => "3", 
			"RxFuncQuery" => "3", 
			"RxQueue" => "3", 
			"RxMessageBox" => "3", 
			"RxWinExec" => "3", 
			"SysAddRexxMacro" => "3", 
			"SysBootDrive" => "3", 
			"SysClearRexxMacroSpace" => "3", 
			"SysCloseEventSem" => "3", 
			"SysCloseMutexSem" => "3", 
			"SysCls" => "3", 
			"SysCreateEventSem" => "3", 
			"SysCreateMutexSem" => "3", 
			"SysCurPos" => "3", 
			"SysCurState" => "3", 
			"SysDriveInfo" => "3", 
			"SysDriveMap" => "3", 
			"SysDropFuncs" => "3", 
			"SysDropRexxMacro" => "3", 
			"SysDumpVariables" => "3", 
			"SysFileDelete" => "3", 
			"SysFileSearch" => "3", 
			"SysFileSystemType" => "3", 
			"SysGetFileDateTime" => "3", 
			"SysFileTree" => "3", 
			"SysGetKey" => "3", 
			"SysIni" => "3", 
			"SysLoadFuncs" => "3", 
			"SysLoadRexxMacroSpace" => "3", 
			"SysMkDir" => "3", 
			"SysOpenEventSem" => "3", 
			"SysOpenMutexSem" => "3", 
			"SysPostEventSem" => "3", 
			"SysPulseEventSem" => "3", 
			"SysQueryProcess" => "3", 
			"SysQueryRexxMacro" => "3", 
			"SysReleaseMutexSem" => "3", 
			"SysReorderRexxMacro" => "3", 
			"SysRequestMutexSem" => "3", 
			"SysResetEventSem" => "3", 
			"SysRmDir" => "3", 
			"SysSaveRexxMacroSpace" => "3", 
			"SysSearchPath" => "3", 
			"SysSetFileDateTime" => "3", 
			"SysSetPriority" => "3", 
			"SysSleep" => "3", 
			"SysStemCopy" => "3", 
			"SysStemDelete" => "3", 
			"SysStemInsert" => "3", 
			"SysStemSort" => "3", 
			"SysSwitchSession" => "3", 
			"SysSystemDirectory" => "3", 
			"SysTempFileName" => "3", 
			"SysTextScreenRead" => "3", 
			"SysTextScreenSize" => "3", 
			"SysUtilVersion" => "3", 
			"SysVolumeLabel" => "3", 
			"SysWaitEventSem" => "3", 
			"SysWaitNamedPipe" => "3", 
			"SysVersion" => "3", 
			"SysWinVer" => "3", 
			"TID" => "3", 
			"TPRIO" => "3", 
			"TTIME" => "3");

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
