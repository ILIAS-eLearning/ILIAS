<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_uniface6 extends HFile{
   function HFile_uniface6(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Uniface V6
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array(")");
$this->unindent          	= array("else", "end", "endif", "endwhile");

// String characters and delimiters

$this->stringchars       	= array("\"");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"/Marker" => "", 
			"Characters" => "", 
			"\"$$\"" => "", 
			"addmonths" => "1", 
			"apexit" => "1", 
			"askmess" => "1", 
			"blockdata" => "1", 
			"break" => "1", 
			"by" => "1", 
			"call" => "1", 
			"clear" => "1", 
			"close" => "1", 
			"clrmess" => "1", 
			"commit" => "1", 
			"compare" => "1", 
			"compute" => "1", 
			"creocc" => "1", 
			"debug" => "1", 
			"delete" => "1", 
			"delitem" => "1", 
			"discard" => "1", 
			"display" => "1", 
			"done" => "1", 
			"edit" => "1", 
			"eject" => "1", 
			"else" => "1", 
			"end" => "1", 
			"endif" => "1", 
			"endwhile" => "1", 
			"entry" => "1", 
			"erase" => "1", 
			"exit" => "1", 
			"field_syntax" => "1", 
			"field_video" => "1", 
			"filebox" => "1", 
			"filedump" => "1", 
			"file_dump" => "1", 
			"fileload" => "1", 
			"file_load" => "1", 
			"from" => "1", 
			"getitem" => "1", 
			"getlistitems" => "1", 
			"goto" => "1", 
			"help" => "1", 
			"if" => "1", 
			"length" => "1", 
			"lock" => "1", 
			"lookup" => "1", 
			"lowercase" => "1", 
			"macro" => "1", 
			"message" => "1", 
			"nodebug" => "1", 
			"numgen" => "1", 
			"numset" => "1", 
			"open" => "1", 
			"order" => "1", 
			"perform" => "1", 
			"pragma" => "1", 
			"previous" => "1", 
			"print" => "1", 
			"print_break" => "1", 
			"pulldown" => "1", 
			"putitem" => "1", 
			"putlistitems" => "1", 
			"putmess" => "1", 
			"read" => "1", 
			"refresh" => "1", 
			"release" => "1", 
			"reload" => "1", 
			"remocc" => "1", 
			"repeat" => "1", 
			"reset" => "1", 
			"retrieve" => "1", 
			"return" => "1", 
			"rollback" => "1", 
			"run" => "1", 
			"scan" => "1", 
			"selectdb" => "1", 
			"set" => "1", 
			"setocc" => "1", 
			"skip" => "1", 
			"sort" => "1", 
			"spawn" => "1", 
			"sql" => "1", 
			"store" => "1", 
			"to" => "1", 
			"u_where" => "1", 
			"until" => "1", 
			"uppercase" => "1", 
			"using" => "1", 
			"where" => "1", 
			"while" => "1", 
			"write" => "1", 
			"append" => "2", 
			"ask" => "2", 
			"complete" => "2", 
			"desc" => "2", 
			"dump" => "2", 
			"e" => "2", 
			"error" => "2", 
			"field" => "2", 
			"global" => "2", 
			"hint" => "2", 
			"id" => "2", 
			"image" => "2", 
			"info" => "2", 
			"init" => "2", 
			"list" => "2", 
			"load" => "2", 
			"local" => "2", 
			"menu" => "2", 
			"mod" => "2", 
			"net" => "2", 
			"next" => "2", 
			"nobeep" => "2", 
			"noborder" => "2", 
			"nolock" => "2", 
			"noterm" => "2", 
			"nowander" => "2", 
			"o" => "2", 
			"occ" => "2", 
			"query" => "2", 
			"raw" => "2", 
			"save" => "2", 
			"truncate" => "2", 
			"warning" => "2", 
			"x" => "2", 
			":a" => "2", 
			":ascending" => "2", 
			":d" => "2", 
			":descending" => "2", 
			"$applname" => "3", 
			"$batch" => "3", 
			"$char" => "3", 
			"$check" => "3", 
			"$clock" => "3", 
			"$curline" => "3", 
			"$curocc" => "3", 
			"$currhits" => "3", 
			"$curword" => "3", 
			"$date" => "3", 
			"$datim" => "3", 
			"$dberror" => "3", 
			"$dbocc" => "3", 
			"$direction" => "3", 
			"$disable" => "3", 
			"$display" => "3", 
			"$empty" => "3", 
			"$entname" => "3", 
			"$error" => "3", 
			"$fieldcheck" => "3", 
			"$fieldendmod" => "3", 
			"$fieldmod" => "3", 
			"$fieldname" => "3", 
			"$fieldprofile" => "3", 
			"$fieldproperties" => "3", 
			"$fieldvalrep" => "3", 
			"$format" => "3", 
			"$formdb" => "3", 
			"$formdbmod" => "3", 
			"$formmod" => "3", 
			"$formname" => "3", 
			"$formtitle" => "3", 
			"$framedepth" => "3", 
			"$gui" => "3", 
			"$hide" => "3", 
			"$hits" => "3", 
			"$ioprint" => "3", 
			"$keyboard" => "3", 
			"$language" => "3", 
			"$lines" => "3", 
			"$next" => "3", 
			"$number" => "3", 
			"$occcheck" => "3", 
			"$occdel" => "3", 
			"$occdepth" => "3", 
			"$occmod" => "3", 
			"$oprsys" => "3", 
			"$page" => "3", 
			"$password" => "3", 
			"$previous" => "3", 
			"$printing" => "3", 
			"$prompt" => "3", 
			"$properties" => "3", 
			"$putmess" => "3", 
			"$relation" => "3", 
			"$result" => "3", 
			"$rettype" => "3", 
			"$selblk" => "3", 
			"$status" => "3", 
			"$storetype" => "3", 
			"$syntax" => "3", 
			"$text" => "3", 
			"$time" => "3", 
			"$totdbocc" => "3", 
			"$totlines" => "3", 
			"$totocc" => "3", 
			"$user" => "3", 
			"$valrep" => "3", 
			"$variation" => "3", 
			"!" => "5", 
			"$" => "5", 
			"%" => "5", 
			"&" => "5", 
			"(" => "5", 
			")" => "5", 
			"*" => "5", 
			"+" => "5", 
			"," => "5", 
			"-" => "5", 
			"." => "5", 
			"/" => "5", 
			":" => "5", 
			";" => "5", 
			"<" => "5", 
			"=" => "5", 
			">" => "5", 
			"[" => "5", 
			"\\" => "5", 
			"]" => "5", 
			"^" => "5", 
			"|" => "5", 
			"$$" => "5");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"" => "donothing", 
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"5" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
