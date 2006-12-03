<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_nt_cmd extends HFile{
   function HFile_nt_cmd(){
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
$this->delimiters        	= array();
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"/L10" => "", 
			"Line" => "", 
			"Comment" => "", 
			"=" => "", 
			"REM" => "", 
			"Nocase" => "", 
			"File" => "", 
			"Extensions" => "", 
			"CMD" => "", 
			"BAT" => "", 
			"append" => "1", 
			"arp" => "1", 
			"assoc" => "1", 
			"at" => "1", 
			"attrib" => "1", 
			"backup" => "1", 
			"break" => "1", 
			"buffers" => "1", 
			"COPY" => "1", 
			"cacls" => "1", 
			"chcp" => "1", 
			"chdir" => "1", 
			"chkdsk" => "1", 
			"cls" => "1", 
			"cmd" => "1", 
			"codepage" => "1", 
			"color" => "1", 
			"comp" => "1", 
			"compact" => "1", 
			"convert" => "1", 
			"copy" => "1", 
			"country" => "1", 
			"DEL" => "1", 
			"date" => "1", 
			"debug" => "1", 
			"device" => "1", 
			"devicehigh" => "1", 
			"devinfo" => "1", 
			"dir" => "1", 
			"diskcomp" => "1", 
			"diskcopy" => "1", 
			"diskperf" => "1", 
			"dos" => "1", 
			"doskey" => "1", 
			"dosonly" => "1", 
			"driveparm" => "1", 
			"edit" => "1", 
			"edlin" => "1", 
			"erase" => "1", 
			"exe2bin" => "1", 
			"exit" => "1", 
			"expand" => "1", 
			"fastopen" => "1", 
			"fc" => "1", 
			"fcbs" => "1", 
			"files" => "1", 
			"find" => "1", 
			"findstr" => "1", 
			"finger" => "1", 
			"forcedos" => "1", 
			"format" => "1", 
			"ftp" => "1", 
			"ftype" => "1", 
			"graftabl" => "1", 
			"graphics" => "1", 
			"help" => "1", 
			"hostname" => "1", 
			"install" => "1", 
			"ipconfig" => "1", 
			"ipxroute" => "1", 
			"keyb" => "1", 
			"label" => "1", 
			"lastdrive" => "1", 
			"libpath" => "1", 
			"loadfix" => "1", 
			"loadhigh" => "1", 
			"lh" => "1", 
			"lpq" => "1", 
			"lpr" => "1", 
			"mem" => "1", 
			"mkdir" => "1", 
			"md" => "1", 
			"mode" => "1", 
			"more" => "1", 
			"move" => "1", 
			"nbtstat" => "1", 
			"netstat" => "1", 
			"net" => "1", 
			"nlsfunc" => "1", 
			"nslookup" => "1", 
			"prompt" => "1", 
			"path" => "1", 
			"pax" => "1", 
			"ping" => "1", 
			"pentnt" => "1", 
			"popd" => "1", 
			"portuas" => "1", 
			"print" => "1", 
			"protshell" => "1", 
			"pushd" => "1", 
			"qbasic" => "1", 
			"rcp" => "1", 
			"recover" => "1", 
			"rename" => "1", 
			"ren" => "1", 
			"replace" => "1", 
			"restore" => "1", 
			"rexec" => "1", 
			"rmdir" => "1", 
			"rd" => "1", 
			"route" => "1", 
			"rsh" => "1", 
			"set" => "1", 
			"setver" => "1", 
			"share" => "1", 
			"shell" => "1", 
			"sort" => "1", 
			"stacks" => "1", 
			"start" => "1", 
			"subst" => "1", 
			"TYPE" => "1", 
			"tftp" => "1", 
			"time" => "1", 
			"title" => "1", 
			"tracert" => "1", 
			"tree" => "1", 
			"ver" => "1", 
			"verify" => "1", 
			"vol" => "1", 
			"XCOPY" => "1", 
			"call" => "2", 
			"EXIST" => "2", 
			"echo" => "2", 
			"endlocal" => "2", 
			"for" => "2", 
			"GOTO" => "2", 
			"IF" => "2", 
			"NOT" => "2", 
			"pause" => "2", 
			"rem" => "2", 
			"setlocal" => "2", 
			"shift" => "2", 
			"awk" => "3", 
			"arj" => "3", 
			"cat" => "3", 
			"compress" => "3", 
			"CRCNT" => "3", 
			"datetime" => "3", 
			"DEFNCOPY" => "3", 
			"dequote" => "3", 
			"DIFF" => "3", 
			"DOAT" => "3", 
			"ERR" => "3", 
			"exitcode" => "3", 
			"filesize" => "3", 
			"FINDSTR" => "3", 
			"gawk" => "3", 
			"getdrves" => "3", 
			"getunc" => "3", 
			"GLOBAL" => "3", 
			"grep" => "3", 
			"head" => "3", 
			"IFSIZE" => "3", 
			"ls" => "3", 
			"mv" => "3", 
			"perl" => "3", 
			"PKUNZIP" => "3", 
			"PKZIP" => "3", 
			"REGFIX" => "3", 
			"sawk" => "3", 
			"sed" => "3", 
			"split" => "3", 
			"tail" => "3", 
			"TEE" => "3", 
			"tr" => "3", 
			"TRANS" => "3", 
			"uniq" => "3", 
			"unzip" => "3", 
			"unarj" => "3", 
			"VALIDATE" => "3", 
			"wc" => "3", 
			"XFRM" => "3", 
			"ZIP2EXE" => "3", 
			"zip" => "3");

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
