<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_bkshell extends HFile{
   function HFile_bkshell(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Bourne & Korn Shell
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "brown", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "%", "^", "&", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"bg" => "1", 
			"break" => "1", 
			"cd" => "1", 
			"continue" => "1", 
			"echo" => "1", 
			"eval" => "1", 
			"exec" => "1", 
			"exit" => "1", 
			"export" => "1", 
			"fg" => "1", 
			"function" => "1", 
			"getopts" => "1", 
			"hash" => "1", 
			"jobs" => "1", 
			"kill" => "1", 
			"newgrp" => "1", 
			"pwd" => "1", 
			"read" => "1", 
			"readonly" => "1", 
			"return" => "1", 
			"select" => "1", 
			"set" => "1", 
			"shift" => "1", 
			"stop" => "1", 
			"stty" => "1", 
			"suspend" => "1", 
			"test" => "1", 
			"times" => "1", 
			"trap" => "1", 
			"type" => "1", 
			"ulimit" => "1", 
			"umask" => "1", 
			"unset" => "1", 
			"wait" => "1", 
			"CDPATH" => "2", 
			"EDITOR" => "2", 
			"HOME" => "2", 
			"IFS" => "2", 
			"LANG" => "2", 
			"MAIL" => "2", 
			"MAILCHECK" => "2", 
			"MAILPATH" => "2", 
			"OLDPWD" => "2", 
			"PATH" => "2", 
			"PPID" => "2", 
			"PS1" => "2", 
			"PS2" => "2", 
			"REPLY" => "2", 
			"SHACCT" => "2", 
			"SHELL" => "2", 
			"TERM" => "2", 
			"case" => "3", 
			"do" => "3", 
			"done" => "3", 
			"elif" => "3", 
			"else" => "3", 
			"esac" => "3", 
			"fi" => "3", 
			"for" => "3", 
			"if" => "3", 
			"then" => "3", 
			"while" => "3", 
			"+" => "4", 
			"-" => "4", 
			"=" => "4", 
			"//" => "4", 
			"/" => "4", 
			"%" => "4", 
			"&" => "4", 
			"^" => "4", 
			"!" => "4", 
			"|" => "4", 
			">>" => "8", 
			">&" => "8", 
			"<<" => "8", 
			"<&" => "8", 
			"<" => "8", 
			">" => "8", 
			"$#" => "6", 
			"$-" => "6", 
			"$?" => "6", 
			"$!" => "6", 
			"$*" => "6", 
			"$@" => "6", 
			"$$" => "6", 
			"$0" => "6", 
			"$1" => "6", 
			"$2" => "6", 
			"$3" => "6", 
			"$4" => "6", 
			"$5" => "6", 
			"$6" => "6", 
			"$7" => "6", 
			"$8" => "6", 
			"$9" => "6", 
			"-a" => "7", 
			"-o" => "7", 
			"-eq" => "7", 
			"-ne" => "7", 
			"-le" => "7", 
			"-lt" => "7", 
			"-ge" => "7", 
			"-gt" => "7", 
			"-b" => "7", 
			"-c" => "7", 
			"-d" => "7", 
			"-f" => "7", 
			"-g" => "7", 
			"-k" => "7", 
			"-l" => "7", 
			"-p" => "7", 
			"-r" => "7", 
			"-s" => "7", 
			"-S" => "7", 
			"-t" => "7", 
			"-u" => "7", 
			"-w" => "7", 
			"-x" => "7", 
			"-n" => "7", 
			"-z" => "7");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"8" => "donothing", 
			"6" => "donothing", 
			"7" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
