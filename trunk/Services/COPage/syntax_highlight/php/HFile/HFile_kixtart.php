<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_kixtart extends HFile{
   function HFile_kixtart(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Kixtart 3.62
/*************************************/
// Flags

$this->nocase            	= "1";
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
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "-", "+", "=", "(", ")", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"(" => "1", 
			")" => "1", 
			"AT" => "1", 
			"BEEP" => "1", 
			"BIG" => "1", 
			"BOX" => "1", 
			"BREAK" => "1", 
			"CALL" => "1", 
			"CASE" => "1", 
			"CD" => "1", 
			"CLS" => "1", 
			"COLOR" => "1", 
			"COOKIE1" => "1", 
			"COPY" => "1", 
			"DEL" => "1", 
			"DIM" => "1", 
			"DISPLAY" => "1", 
			"DO" => "1", 
			"ELSE" => "1", 
			"ENDIF" => "1", 
			"ENDSELECT" => "1", 
			"EXIT" => "1", 
			"FLUSHKB" => "1", 
			"GET" => "1", 
			"GETS" => "1", 
			"GLOBAL" => "1", 
			"GO" => "1", 
			"GOSUB" => "1", 
			"GOTO" => "1", 
			"IF" => "1", 
			"LOOP" => "1", 
			"MD" => "1", 
			"OFF" => "1", 
			"ON" => "1", 
			"PASSWORD" => "1", 
			"PLAY" => "1", 
			"QUIT" => "1", 
			"RETURN" => "1", 
			"RD" => "1", 
			"RUN" => "1", 
			"SELECT" => "1", 
			"SET" => "1", 
			"SETL" => "1", 
			"SETM" => "1", 
			"SETTIME" => "1", 
			"SHELL" => "1", 
			"SLEEP" => "1", 
			"SMALL" => "1", 
			"UNTIL" => "1", 
			"USE" => "1", 
			"WHILE" => "1", 
			"ADDKEY" => "2", 
			"ADDPRINTERCONNECTION" => "2", 
			"ADDPROGRAMGROUP" => "2", 
			"ADDPROGRAMITEM" => "2", 
			"ASC" => "2", 
			"BACKUPEVENTLOG" => "2", 
			"CHR" => "2", 
			"CLEAREVENTLOG" => "2", 
			"CLOSE" => "2", 
			"COMPAREFILETIMES" => "2", 
			"DECTOHEX" => "2", 
			"DELKEY" => "2", 
			"DELPRINTERCONNECTION" => "2", 
			"DELPROGRAMGROUP" => "2", 
			"DELPROGRAMITEM" => "2", 
			"DELTREE" => "2", 
			"DELVALUE" => "2", 
			"DIR" => "2", 
			"ENUMGROUP" => "2", 
			"ENUMKEY" => "2", 
			"ENUMLOCALGROUP" => "2", 
			"ENUMVALUE" => "2", 
			"EXECUTE" => "2", 
			"EXIST" => "2", 
			"EXISTKEY" => "2", 
			"EXPANDENVIRONMENTVARS" => "2", 
			"GETDISKSPACE" => "2", 
			"GETFILEATTR" => "2", 
			"GETFILESIZE" => "2", 
			"GETFILETIME" => "2", 
			"GETFILEVERSION" => "2", 
			"INGROUP" => "2", 
			"INSTR" => "2", 
			"LCASE" => "2", 
			"LEN" => "2", 
			"LOADHIVE" => "2", 
			"LOADKEY" => "2", 
			"LOGEVENT" => "2", 
			"LOGOFF" => "2", 
			"LTRIM" => "2", 
			"MESSAGEBOX" => "2", 
			"OLECALLFUNC" => "2", 
			"OLECALLPROC" => "2", 
			"OLECREATEOBJECT" => "2", 
			"OLEENUMOBJECT" => "2", 
			"OLEGETOBJECT" => "2", 
			"OLEGETPROPERTY" => "2", 
			"OLEGETSUBOBJECT" => "2", 
			"OLEPUTPROPERTY" => "2", 
			"OLERELEASEOBJECT" => "2", 
			"OPEN" => "2", 
			"READLINE" => "2", 
			"READPROFILESTRING" => "2", 
			"READTYPE" => "2", 
			"READVALUE" => "2", 
			"REDIRECTOUTPUT" => "2", 
			"RND" => "2", 
			"RTRIM" => "2", 
			"SAVEKEY" => "2", 
			"SENDKEYS" => "2", 
			"SENDMESSAGE" => "2", 
			"SETACSII" => "2", 
			"SETCONSOLE" => "2", 
			"SETDEFAULTPRINTER" => "2", 
			"SETFILEATTR" => "2", 
			"SETFOCUS" => "2", 
			"SETWALLPAPER" => "2", 
			"SHOWPROGRAMGROUP" => "2", 
			"SHUTDOWN" => "2", 
			"SRND" => "2", 
			"SUBSTR" => "2", 
			"UCASE" => "2", 
			"UNLOADHIVE" => "2", 
			"VAL" => "2", 
			"WRITELINE" => "2", 
			"WRITEPROFILESTRING" => "2", 
			"WRITEVALUE" => "2");

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
