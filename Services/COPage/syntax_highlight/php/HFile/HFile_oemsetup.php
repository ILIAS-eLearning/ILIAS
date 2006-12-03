<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_oemsetup extends HFile{
   function HFile_oemsetup(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Oemsetup Script
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

$this->indent            	= array("ifstr", "ifstr(i)", "ifint", "ifcontains");
$this->unindent          	= array("endif");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "+", "=", "|", "\\", "/", "{", "}", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"\"ifcontains(i)\"" => "", 
			"addfiletodeletelist" => "1", 
			"closeregkey" => "1", 
			"createregkey" => "1", 
			"debug-output" => "1", 
			"debug_output" => "1", 
			"deleteregkey" => "1", 
			"deleteregtree" => "1", 
			"deleteregvalue" => "1", 
			"else-ifcontains" => "1", 
			"else-ifcontains(i)" => "1", 
			"else-ifint" => "1", 
			"else-ifstr" => "1", 
			"else-ifstr(i)" => "1", 
			"endforlistdo" => "1", 
			"endif" => "1", 
			"endwait" => "1", 
			"enumregkey" => "1", 
			"enumregvalue" => "1", 
			"flushinf" => "1", 
			"flushregkey" => "1", 
			"forlistdo" => "1", 
			"freelibrary" => "1", 
			"getdriveinpath" => "1", 
			"getregvalue" => "1", 
			"getsystemdate" => "1", 
			"goto" => "1", 
			"ifcontains" => "1", 
			"ifcontaints(i)" => "1", 
			"ifint" => "1", 
			"ifstr" => "1", 
			"ifstr(i)" => "1", 
			"libraryprocedure" => "1", 
			"loadlibrary" => "1", 
			"openregkey" => "1", 
			"querylistsize" => "1", 
			"set" => "1", 
			"set-add" => "1", 
			"set-and" => "1", 
			"set-dectohex" => "1", 
			"set-div" => "1", 
			"set-hextodec" => "1", 
			"set-mul" => "1", 
			"set-or" => "1", 
			"set-sub" => "1", 
			"set-subst" => "1", 
			"setregvalue" => "1", 
			"sleep" => "1", 
			"split-string" => "1", 
			"startwait" => "1", 
			"addsectionfilestocopylist" => "2", 
			"addsectionkeyfiletocopylist" => "2", 
			"addnthsectionfiletocopylist" => "2", 
			"copyfilesincopylist" => "2", 
			"clearcopylist" => "2", 
			"createdir" => "2", 
			"detect" => "2", 
			"exit" => "2", 
			"install" => "2", 
			"read-syms" => "2", 
			"removedir" => "2", 
			"return" => "2", 
			"shell" => "2", 
			"[]" => "3", 
			"$)" => "3", 
			"+" => "4", 
			"=" => "4", 
			"//" => "4", 
			"/" => "4", 
			"%" => "4", 
			"&" => "4", 
			">" => "4", 
			"<" => "4", 
			"^" => "4", 
			"!" => "4", 
			"|" => "4", 
			"NO_ERROR" => "5", 
			"STATUS_SUCCESSFUL" => "5", 
			"STATUS_FAILED" => "5", 
			"STATUS_USERCANCEL" => "5", 
			"STATUS_REBIND" => "5", 
			"STATUS_REBOOT" => "5", 
			"STATUS_NO_EFFECT" => "5");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"" => "donothing", 
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
