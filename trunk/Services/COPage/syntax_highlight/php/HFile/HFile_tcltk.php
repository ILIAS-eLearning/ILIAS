<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_tcltk extends HFile{
   function HFile_tcltk(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Tcl/tk
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("#");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"after" => "1", 
			"append" => "1", 
			"array" => "1", 
			"auto_execok" => "1", 
			"auto_load" => "1", 
			"auto_mkindex" => "1", 
			"auto_reset" => "1", 
			"bgerror" => "1", 
			"break" => "1", 
			"case" => "1", 
			"catch" => "1", 
			"cd" => "1", 
			"clock" => "1", 
			"close" => "1", 
			"concat" => "1", 
			"continue" => "1", 
			"eof" => "1", 
			"error" => "1", 
			"eval" => "1", 
			"exec" => "1", 
			"exit" => "1", 
			"expr" => "1", 
			"else" => "1", 
			"elseif" => "1", 
			"fblocked" => "1", 
			"fconfigure" => "1", 
			"file" => "1", 
			"fileevent" => "1", 
			"flush" => "1", 
			"for" => "1", 
			"foreach" => "1", 
			"format" => "1", 
			"gets" => "1", 
			"glob" => "1", 
			"global" => "1", 
			"history" => "1", 
			"if" => "1", 
			"incr" => "1", 
			"info" => "1", 
			"interp" => "1", 
			"join" => "1", 
			"lappend" => "1", 
			"lindex" => "1", 
			"linsert" => "1", 
			"list" => "1", 
			"llength" => "1", 
			"load" => "1", 
			"lrange" => "1", 
			"lreplace" => "1", 
			"lsearch" => "1", 
			"lsort" => "1", 
			"open" => "1", 
			"package" => "1", 
			"parray" => "1", 
			"pid" => "1", 
			"proc" => "1", 
			"puts" => "1", 
			"pwd" => "1", 
			"read" => "1", 
			"regexp" => "1", 
			"regsub" => "1", 
			"rename" => "1", 
			"return" => "1", 
			"scan" => "1", 
			"seek" => "1", 
			"set" => "1", 
			"socket" => "1", 
			"source" => "1", 
			"split" => "1", 
			"string" => "1", 
			"subst" => "1", 
			"switch" => "1", 
			"tell" => "1", 
			"time" => "1", 
			"trace" => "1", 
			"tcl_endOfWord" => "1", 
			"tcl_startOfNextWord" => "1", 
			"tcl_startOfPreviousWord" => "1", 
			"tcl_wordBreakAfter" => "1", 
			"tcl_wordBreakBefore" => "1", 
			"unknown" => "1", 
			"unset" => "1", 
			"update" => "1", 
			"uplevel" => "1", 
			"upvar" => "1", 
			"vwait" => "1", 
			"while" => "1", 
			"auto_execs" => "2", 
			"auto_index" => "2", 
			"auto_noexec" => "2", 
			"auto_noload" => "2", 
			"auto_path" => "2", 
			"env" => "2", 
			"ErrorCode" => "2", 
			"ErrorInfo" => "2", 
			"tcl_library" => "2", 
			"tcl_patchLevel" => "2", 
			"tcl_pkgPath" => "2", 
			"tcl_platform" => "2", 
			"tcl_precision" => "2", 
			"tcl_rcFileName" => "2", 
			"tcl_rcRsrcName" => "2", 
			"tcl_version" => "2", 
			"tcl_nonwordchars" => "2", 
			"tcl_wordchars" => "2", 
			"unknown_active" => "2", 
			"bell" => "3", 
			"bind" => "3", 
			"bindtags" => "3", 
			"bitmap" => "3", 
			"button" => "3", 
			"canvas" => "3", 
			"checkbutton" => "3", 
			"clipboard" => "3", 
			"destroy" => "3", 
			"entry" => "3", 
			"event" => "3", 
			"focus" => "3", 
			"frame" => "3", 
			"grab" => "3", 
			"grid" => "3", 
			"image" => "3", 
			"label" => "3", 
			"listbox" => "3", 
			"lower" => "3", 
			"menu" => "3", 
			"menubutton" => "3", 
			"message" => "3", 
			"option" => "3", 
			"pack" => "3", 
			"photo" => "3", 
			"place" => "3", 
			"radiobutton" => "3", 
			"raise" => "3", 
			"scale" => "3", 
			"scrollbar" => "3", 
			"selection" => "3", 
			"send" => "3", 
			"text" => "3", 
			"tk" => "3", 
			"tk_bindForTraversal" => "3", 
			"tk_bisque" => "3", 
			"tk_chooseColor" => "3", 
			"tk_dialog" => "3", 
			"tk_focusFollowsMouse" => "3", 
			"tk_focusNext" => "3", 
			"tk_focusPrev" => "3", 
			"tk_getOpenFile" => "3", 
			"tk_getSaveFile" => "3", 
			"tk_menuBar" => "3", 
			"tk_messageBox" => "3", 
			"tk_optionMenu" => "3", 
			"tk_popup" => "3", 
			"tk_setPalette" => "3", 
			"tkerror" => "3", 
			"tkvars" => "3", 
			"tkwait" => "3", 
			"toplevel" => "3", 
			"winfo" => "3", 
			"wm" => "3", 
			"**" => "4", 
			"$" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
