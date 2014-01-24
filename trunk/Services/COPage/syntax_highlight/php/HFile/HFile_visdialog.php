<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_visdialog extends HFile{
   function HFile_visdialog(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Visual Dialog Script
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

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "@", "[", "]", ":", ";", "\"", "'", "<", ">", " ", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("REM");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"@" => "1", 
			"ALT" => "1", 
			"ASC" => "1", 
			"ASK" => "1", 
			"CHR" => "1", 
			"CLICK" => "1", 
			"COUNT" => "1", 
			"CR" => "1", 
			"CTRL" => "1", 
			"CURDIR" => "1", 
			"DATETIME" => "1", 
			"DDEITEM" => "1", 
			"DIFF" => "1", 
			"DIRDLG" => "1", 
			"DIV" => "1", 
			"DLGTEXT" => "1", 
			"ENV" => "1", 
			"EQUAL" => "1", 
			"ESC" => "1", 
			"EVENT" => "1", 
			"EXT" => "1", 
			"FILEDLG" => "1", 
			"FORMAT" => "1", 
			"GREATER" => "1", 
			"HEX" => "1", 
			"INDEX" => "1", 
			"INIREAD" => "1", 
			"INPUT" => "1", 
			"ITEM" => "1", 
			"KEY" => "1", 
			"LEN" => "1", 
			"LOWER" => "1", 
			"MATCH" => "1", 
			"MCI" => "1", 
			"MSGBOX" => "1", 
			"NAME" => "1", 
			"NEXT" => "1", 
			"NOT" => "1", 
			"NULL" => "1", 
			"NUMERIC" => "1", 
			"OK" => "1", 
			"PATH" => "1", 
			"POS" => "1", 
			"PRED" => "1", 
			"PROD" => "1", 
			"QUERY" => "1", 
			"REGREAD" => "1", 
			"RETCODE" => "1", 
			"SENDMSG" => "1", 
			"SHIFT" => "1", 
			"SHORTNAME" => "1", 
			"STRDEL" => "1", 
			"STRINS" => "1", 
			"SUBSTR" => "1", 
			"SUCC" => "1", 
			"SUM" => "1", 
			"SYSINFO" => "1", 
			"TAB" => "1", 
			"TRIM" => "1", 
			"UPPER" => "1", 
			"VERINFO" => "1", 
			"VOLINFO" => "1", 
			"WINACTIVE" => "1", 
			"WINATPOINT" => "1", 
			"WINCLASS" => "1", 
			"WINDIR" => "1", 
			"WINEXISTS" => "1", 
			"WINPOS" => "1", 
			"WINTEXT" => "1", 
			"ZERO" => "1", 
			"BEEP" => "2", 
			"CLIPBOARD" => "2", 
			"DDE" => "2", 
			"DIALOG" => "2", 
			"DIRECTORY" => "2", 
			"EXIT" => "2", 
			"EXITWIN" => "2", 
			"EXTERNAL" => "2", 
			"ELSE" => "2", 
			"END" => "2", 
			"GOSUB" => "2", 
			"GOTO" => "2", 
			"FILE" => "2", 
			"IF" => "2", 
			"INFO" => "2", 
			"INIFILE" => "2", 
			"LINK" => "2", 
			"LIST" => "2", 
			"OPTION" => "2", 
			"PARSE" => "2", 
			"PLAY" => "2", 
			"REGISTRY" => "2", 
			"REPEAT" => "2", 
			"RUN" => "2", 
			"RUNH" => "2", 
			"RUNM" => "2", 
			"RUNZ" => "2", 
			"SHELL" => "2", 
			"STOP" => "2", 
			"TITLE" => "2", 
			"UNTIL" => "2", 
			"WAIT" => "2", 
			"WARN" => "2", 
			"WINDOW" => "2", 
			"WINHELP" => "2", 
			"%" => "3", 
			"%1" => "3", 
			"%2" => "3", 
			"%3" => "3", 
			"%4" => "3", 
			"%5" => "3", 
			"%6" => "3", 
			"%7" => "3", 
			"%8" => "3", 
			"%9" => "3", 
			"%0" => "3", 
			"%10" => "3", 
			"%A" => "3", 
			"%B" => "3", 
			"%C" => "3", 
			"%D" => "3", 
			"%E" => "3", 
			"%F" => "3", 
			"%G" => "3", 
			"%H" => "3", 
			"%I" => "3", 
			"%J" => "3", 
			"%K" => "3", 
			"%L" => "3", 
			"%M" => "3", 
			"%N" => "3", 
			"%O" => "3", 
			"%P" => "3", 
			"%Q" => "3", 
			"%R" => "3", 
			"%S" => "3", 
			"%T" => "3", 
			"%U" => "3", 
			"%V" => "3", 
			"%W" => "3", 
			"%X" => "3", 
			"%Y" => "3", 
			"%Z" => "3", 
			"APPEND" => "4", 
			"ADD" => "4", 
			"ASSIGN" => "4", 
			"ACTIVATE" => "4", 
			"CLEAR" => "4", 
			"CLEARSEL" => "4", 
			"CLOSE" => "4", 
			"CREATE" => "4", 
			"CURSOR" => "4", 
			"CHANGE" => "4", 
			"COPY" => "4", 
			"DISABLE" => "4", 
			"DELETE" => "4", 
			"DROPFILES" => "4", 
			"EXECUTE" => "4", 
			"ENABLE" => "4", 
			"ERRORTRAP" => "4", 
			"FOCUS" => "4", 
			"FILELIST" => "4", 
			"FIELDSEP" => "4", 
			"FILENAMES" => "4", 
			"HIDE" => "4", 
			"INSERT" => "4", 
			"ICONIZE" => "4", 
			"LOADFILE" => "4", 
			"LOADTEXT" => "4", 
			"MAXIMIZE" => "4", 
			"NORMAL" => "4", 
			"OPEN" => "4", 
			"ONTOP" => "4", 
			"POKE" => "4", 
			"POPUP" => "4", 
			"POWEROFF" => "4", 
			"PASTE" => "4", 
			"PUT" => "4", 
			"PRIORITY" => "4", 
			"POSITION" => "4", 
			"REBOOT" => "4", 
			"RENAME" => "4", 
			"REGKEYS" => "4", 
			"REGVALS" => "4", 
			"REGBUF" => "4", 
			"SET" => "4", 
			"SHOW" => "4", 
			"SHUTDOWN" => "4", 
			"SETDATE" => "4", 
			"SETATTR" => "4", 
			"SEEK" => "4", 
			"SAVEFILE" => "4", 
			"SCALE" => "4", 
			"SKDELAY" => "4", 
			"SLEEPTIME" => "4", 
			"SEND" => "4", 
			"SETTEXT" => "4", 
			"TERMINATE" => "4", 
			"WRITE" => "4", 
			"WINLIST" => "4", 
			",WAIT" => "4", 
			"BITMAP" => "5", 
			"BUTTON" => "5", 
			"CHECK" => "5", 
			"COMBO" => "5", 
			"DLGTYPE" => "5", 
			"EDIT" => "5", 
			"PROGRESS" => "5", 
			"RADIO" => "5", 
			"STATUS" => "5", 
			"STYLE" => "5", 
			"TASKICON" => "5", 
			"TEXT" => "5");

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
