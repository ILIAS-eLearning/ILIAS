<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_autolisp extends HFile{
   function HFile_autolisp(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// AutoLISP
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("(");
$this->unindent          	= array(")");

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("(", ")", "\"");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"cond" => "1", 
			"foreach" => "1", 
			"if" => "1", 
			"progn" => "1", 
			"repeat" => "1", 
			"while" => "1", 
			"*error*" => "2", 
			"alert" => "2", 
			"exit" => "2", 
			"nil" => "2", 
			"NIL" => "2", 
			"pi" => "2", 
			"quit" => "2", 
			"setq" => "2", 
			"setvar" => "2", 
			"t" => "2", 
			"T" => "2", 
			"*" => "3", 
			"+" => "3", 
			"-" => "3", 
			"//" => "3", 
			"/" => "3", 
			"/=" => "3", 
			"1+" => "3", 
			"1-" => "3", 
			"<" => "3", 
			"<=" => "3", 
			"=" => "3", 
			">" => "3", 
			">=" => "3", 
			"abs" => "3", 
			"and" => "3", 
			"angle" => "3", 
			"angtof" => "3", 
			"angtos" => "3", 
			"atan" => "3", 
			"atof" => "3", 
			"atoi" => "3", 
			"boundp" => "3", 
			"cos" => "3", 
			"distance" => "3", 
			"eq" => "3", 
			"equal" => "3", 
			"exp" => "3", 
			"expt" => "3", 
			"fix" => "3", 
			"float" => "3", 
			"log" => "3", 
			"logand" => "3", 
			"logior" => "3", 
			"lsh" => "3", 
			"max" => "3", 
			"min" => "3", 
			"minusp" => "3", 
			"not" => "3", 
			"null" => "3", 
			"or" => "3", 
			"sqrt" => "3", 
			"zerop" => "3", 
			"~" => "3", 
			"append" => "4", 
			"caddr" => "4", 
			"cadr" => "4", 
			"cal" => "4", 
			"car" => "4", 
			"cdddr" => "4", 
			"cdr" => "4", 
			"cons" => "4", 
			"length" => "4", 
			"list" => "4", 
			"listp" => "4", 
			"member" => "4", 
			"nth" => "4", 
			"reverse" => "4", 
			"subst" => "4", 
			"acad_colordlg" => "5", 
			"acad_helpdlg" => "5", 
			"acad_strlsort" => "5", 
			"action_tile" => "5", 
			"add_list" => "5", 
			"ads" => "5", 
			"alloc" => "5", 
			"apply" => "5", 
			"arx" => "5", 
			"arxload" => "5", 
			"ascii" => "5", 
			"atom" => "5", 
			"atoms-family" => "5", 
			"autoarxload" => "5", 
			"autoload" => "5", 
			"autoxload" => "5", 
			"boole" => "5", 
			"client_data_tile" => "5", 
			"close" => "5", 
			"command" => "5", 
			"cvunit" => "5", 
			"dictadd" => "5", 
			"dictnext" => "5", 
			"dictremove" => "5", 
			"dictrename" => "5", 
			"dictsearch" => "5", 
			"dimxtile" => "5", 
			"dimytile" => "5", 
			"done_dialog" => "5", 
			"end_image" => "5", 
			"end_list" => "5", 
			"eval" => "5", 
			"expand" => "5", 
			"fill_image" => "5", 
			"findfile" => "5", 
			"gc" => "5", 
			"gcd" => "5", 
			"get_attr" => "5", 
			"get_tile" => "5", 
			"getangle" => "5", 
			"getcfg" => "5", 
			"getcorner" => "5", 
			"getdist" => "5", 
			"getenv" => "5", 
			"getfiled" => "5", 
			"getint" => "5", 
			"getkword" => "5", 
			"getorient" => "5", 
			"getpoint" => "5", 
			"getreal" => "5", 
			"getstring" => "5", 
			"getvar" => "5", 
			"graphscr" => "5", 
			"grclear" => "5", 
			"grdraw" => "5", 
			"grread" => "5", 
			"grtext" => "5", 
			"grvecs" => "5", 
			"handent" => "5", 
			"help" => "5", 
			"initget" => "5", 
			"inters" => "5", 
			"lambda" => "5", 
			"last" => "5", 
			"load" => "5", 
			"load_dialog" => "5", 
			"mapcar" => "5", 
			"mem" => "5", 
			"menucmd" => "5", 
			"menugroup" => "5", 
			"mode_tile" => "5", 
			"namedobjdict" => "5", 
			"new_dialog" => "5", 
			"numberp" => "5", 
			"open" => "5", 
			"osnap" => "5", 
			"pause" => "5", 
			"polar" => "5", 
			"prin1" => "5", 
			"princ" => "5", 
			"print" => "5", 
			"prompt" => "5", 
			"quote" => "5", 
			"read" => "5", 
			"read-char" => "5", 
			"read-line" => "5", 
			"redraw" => "5", 
			"regapp" => "5", 
			"rem" => "5", 
			"rtos" => "5", 
			"set" => "5", 
			"set_tile" => "5", 
			"setcfg" => "5", 
			"setfunhelp" => "5", 
			"setview" => "5", 
			"sin" => "5", 
			"slide_image" => "5", 
			"snvalid" => "5", 
			"start_app" => "5", 
			"start_dialog" => "5", 
			"start_image" => "5", 
			"start_list" => "5", 
			"tablet" => "5", 
			"term_dialog" => "5", 
			"terpri" => "5", 
			"textbox" => "5", 
			"textpage" => "5", 
			"textscr" => "5", 
			"trace" => "5", 
			"trans" => "5", 
			"type" => "5", 
			"unload_dialog" => "5", 
			"untrace" => "5", 
			"vector_image" => "5", 
			"ver" => "5", 
			"vmon" => "5", 
			"vports" => "5", 
			"wcmatch" => "5", 
			"write-char" => "5", 
			"write-line" => "5", 
			"xdroom" => "5", 
			"xdsize" => "5", 
			"xload" => "5", 
			"xunload" => "5", 
			"assoc" => "6", 
			"chr" => "6", 
			"distof" => "6", 
			"entdel" => "6", 
			"entget" => "6", 
			"entlast" => "6", 
			"entmake" => "6", 
			"entmakex" => "6", 
			"entmod" => "6", 
			"entnext" => "6", 
			"entsel" => "6", 
			"entupd" => "6", 
			"itoa" => "6", 
			"nentsel" => "6", 
			"nentselp" => "6", 
			"ssadd" => "6", 
			"ssdel" => "6", 
			"ssget" => "6", 
			"ssgetfirst" => "6", 
			"sslength" => "6", 
			"ssmemb" => "6", 
			"ssname" => "6", 
			"ssnamex" => "6", 
			"sssetfirst" => "6", 
			"strcase" => "6", 
			"strcat" => "6", 
			"strlen" => "6", 
			"substr" => "6", 
			"tblnext" => "6", 
			"tblobjname" => "6", 
			"tblsearch" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
