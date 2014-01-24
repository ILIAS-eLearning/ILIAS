<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_dosbatch extends HFile{
   function HFile_dosbatch(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// DOS Batch
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

$this->stringchars       	= array("\"");
$this->delimiters        	= array("~", "!", "^", "&", "(", ")", "+", "=", "|", "@", "{", "}", "[", "]", ";", "\"", "'", "<", ">", " ", ",", ".", "/", " ", " ", " ", " ");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("rem");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"@" => "1", 
			"%" => "1", 
			">" => "1", 
			"<" => "1", 
			"*" => "1", 
			"?" => "1", 
			"$e" => "1", 
			"[" => "1", 
			"call" => "1", 
			"do" => "1", 
			"echo" => "1", 
			"errorlevel" => "1", 
			"exit" => "1", 
			"exist" => "1", 
			"edit" => "1", 
			"edlin" => "1", 
			"for" => "1", 
			"goto" => "1", 
			"if" => "1", 
			"in" => "1", 
			"not" => "1", 
			"off" => "1", 
			"on" => "1", 
			"pause" => "1", 
			"prompt" => "1", 
			"path" => "1", 
			"qbasic" => "1", 
			"set" => "1", 
			"shift" => "1", 
			"attrib" => "2", 
			"append" => "2", 
			"backup" => "2", 
			"cd" => "2", 
			"choice" => "2", 
			"cls" => "2", 
			"copy" => "2", 
			"chdir" => "2", 
			"command" => "2", 
			"comp" => "2", 
			"chkdsk" => "2", 
			"del" => "2", 
			"dir" => "2", 
			"deltree" => "2", 
			"diskcopy" => "2", 
			"debug" => "2", 
			"diskcomp" => "2", 
			"doskey" => "2", 
			"expand" => "2", 
			"format" => "2", 
			"fc" => "2", 
			"fdisk" => "2", 
			"find" => "2", 
			"ftp" => "2", 
			"graphics" => "2", 
			"help" => "2", 
			"interlnk" => "2", 
			"intersvr" => "2", 
			"keyb" => "2", 
			"label" => "2", 
			"loadfix" => "2", 
			"mkdir" => "2", 
			"md" => "2", 
			"mode" => "2", 
			"msd" => "2", 
			"more" => "2", 
			"mem" => "2", 
			"move" => "2", 
			"msav" => "2", 
			"msbackup" => "2", 
			"nslfunc" => "2", 
			"print" => "2", 
			"rd" => "2", 
			"rmdir" => "2", 
			"replace" => "2", 
			"restore" => "2", 
			"sort" => "2", 
			"share" => "2", 
			"smartdrv" => "2", 
			"sys" => "2", 
			"scandisk" => "2", 
			"setver" => "2", 
			"subst" => "2", 
			"type" => "2", 
			"tree" => "2", 
			"undelete" => "2", 
			"unformat" => "2", 
			"ver" => "2", 
			"vol" => "2", 
			"vsafe" => "2", 
			"xcopy" => "2", 
			"date" => "3", 
			"time" => "3");

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
