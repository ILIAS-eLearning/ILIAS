<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_batch extends HFile{
   function HFile_batch(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Batch Files
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

$this->stringchars       	= array("\"", ";");
$this->delimiters        	= array("#", "$", "(", ")", "+", ",", ".", "/", ":", ";", "<", " ", "=", "	", ">", "\\");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("rem");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"do" => "1", 
			"else" => "1", 
			"end" => "1", 
			"errorlevel" => "1", 
			"exist" => "1", 
			"exit" => "1", 
			"for" => "1", 
			"goto" => "1", 
			"if" => "1", 
			"not" => "1", 
			"pause" => "1", 
			"return" => "1", 
			"say" => "1", 
			"select" => "1", 
			"then" => "1", 
			"when" => "1", 
			"ansi" => "2", 
			"append" => "2", 
			"assign" => "2", 
			"attrib" => "2", 
			"autofail" => "2", 
			"backup" => "2", 
			"basedev" => "2", 
			"boot" => "2", 
			"break" => "2", 
			"buffers" => "2", 
			"cache" => "2", 
			"call" => "2", 
			"cd" => "2", 
			"chcp" => "2", 
			"chdir" => "2", 
			"chkdsk" => "2", 
			"choice" => "2", 
			"cls" => "2", 
			"cmd" => "2", 
			"codepage" => "2", 
			"command" => "2", 
			"comp" => "2", 
			"copy" => "2", 
			"country" => "2", 
			"date" => "2", 
			"ddinstal" => "2", 
			"debug" => "2", 
			"del" => "2", 
			"detach" => "2", 
			"device" => "2", 
			"devicehigh" => "2", 
			"devinfo" => "2", 
			"dir" => "2", 
			"diskcoache" => "2", 
			"diskcomp" => "2", 
			"diskcopy" => "2", 
			"doskey" => "2", 
			"dpath" => "2", 
			"dumpprocess" => "2", 
			"eautil" => "2", 
			"endlocal" => "2", 
			"erase" => "2", 
			"exit_vdm" => "2", 
			"extproc" => "2", 
			"fcbs" => "2", 
			"fdisk" => "2", 
			"fdiskpm" => "2", 
			"files" => "2", 
			"find" => "2", 
			"format" => "2", 
			"fsaccess" => "2", 
			"fsfilter" => "2", 
			"graftabl" => "2", 
			"iopl" => "2", 
			"join" => "2", 
			"keyb" => "2", 
			"keys" => "2", 
			"label" => "2", 
			"lastdrive" => "2", 
			"libpath" => "2", 
			"lh" => "2", 
			"loadhigh" => "2", 
			"makeini" => "2", 
			"maxwait" => "2", 
			"md" => "2", 
			"mem" => "2", 
			"memman" => "2", 
			"mkdir" => "2", 
			"mode" => "2", 
			"move" => "2", 
			"net" => "2", 
			"patch" => "2", 
			"path" => "2", 
			"pauseonerror" => "2", 
			"picview" => "2", 
			"pmrexx" => "2", 
			"print" => "2", 
			"printmonbufsize" => "2", 
			"priority" => "2", 
			"priority_disk_io" => "2", 
			"prompt" => "2", 
			"protectonly" => "2", 
			"protshell" => "2", 
			"pstat" => "2", 
			"rd" => "2", 
			"recover" => "2", 
			"reipl" => "2", 
			"ren" => "2", 
			"rename" => "2", 
			"replace" => "2", 
			"restore" => "2", 
			"rmdir" => "2", 
			"rmsize" => "2", 
			"run" => "2", 
			"set" => "2", 
			"setboot" => "2", 
			"setlocal" => "2", 
			"shell" => "2", 
			"shift" => "2", 
			"sort" => "2", 
			"spool" => "2", 
			"start" => "2", 
			"subst" => "2", 
			"suppresspopups" => "2", 
			"swappath" => "2", 
			"syslevel" => "2", 
			"syslog" => "2", 
			"threads" => "2", 
			"time" => "2", 
			"timeslice" => "2", 
			"trace" => "2", 
			"tracebuf" => "2", 
			"tracefmt" => "2", 
			"trapdump" => "2", 
			"tree" => "2", 
			"type" => "2", 
			"undelete" => "2", 
			"unpack" => "2", 
			"use" => "2", 
			"ver" => "2", 
			"verify" => "2", 
			"view" => "2", 
			"vmdisk" => "2", 
			"vol" => "2", 
			"xcopy" => "2", 
			"xcopy32" => "2", 
			"xdfcopy" => "2", 
			"@echo" => "3", 
			"echo" => "3", 
			"off" => "3", 
			"on" => "3", 
			";" => "4", 
			"#" => "5", 
			"$" => "5", 
			"(" => "5", 
			")" => "5", 
			"+" => "5", 
			"," => "5", 
			"." => "5", 
			"//" => "5", 
			"/" => "5", 
			":" => "5", 
			"<" => "5", 
			"=" => "5", 
			">" => "5", 
			"\\" => "5");

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
