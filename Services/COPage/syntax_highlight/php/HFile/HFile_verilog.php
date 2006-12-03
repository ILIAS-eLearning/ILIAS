<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_verilog extends HFile{
   function HFile_verilog(){
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

$this->colours        	= array("blue", "gray", "purple");
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
			"//" => "", 
			"Block" => "", 
			"On" => "", 
			"/*" => "", 
			"Off" => "", 
			"*/" => "", 
			"String" => "", 
			"Chars" => "", 
			"\"" => "", 
			"File" => "", 
			"Extensions" => "", 
			"V" => "", 
			"VMD" => "", 
			"always" => "1", 
			"and" => "1", 
			"assign" => "1", 
			"begin" => "1", 
			"buf" => "1", 
			"bufif0" => "1", 
			"bufif1" => "1", 
			"case" => "1", 
			"casex" => "1", 
			"casez" => "1", 
			"cmos" => "1", 
			"deassign" => "1", 
			"default" => "1", 
			"defparam" => "1", 
			"disable" => "1", 
			"edge" => "1", 
			"else" => "3", 
			"end" => "1", 
			"endcase" => "1", 
			"endmodule" => "1", 
			"endfunction" => "1", 
			"endprimitive" => "1", 
			"endspecify" => "1", 
			"endtable" => "1", 
			"endtask" => "1", 
			"event" => "1", 
			"for" => "1", 
			"force" => "1", 
			"forever" => "1", 
			"fork" => "1", 
			"function" => "1", 
			"highz0" => "1", 
			"highz1" => "1", 
			"if" => "1", 
			"initial" => "1", 
			"inout" => "1", 
			"input" => "1", 
			"integer" => "1", 
			"join" => "1", 
			"large" => "1", 
			"Library" => "1", 
			"macromodule" => "1", 
			"medium" => "1", 
			"module" => "1", 
			"nand" => "1", 
			"negedge" => "1", 
			"nmos" => "1", 
			"nor" => "1", 
			"not" => "1", 
			"notif0" => "1", 
			"notif1" => "1", 
			"or" => "1", 
			"output" => "1", 
			"parameter" => "1", 
			"pmos" => "1", 
			"posedge" => "1", 
			"primitive" => "1", 
			"pull0" => "1", 
			"pull1" => "1", 
			"pullup" => "1", 
			"pulldown" => "1", 
			"rcmos" => "1", 
			"reg" => "1", 
			"release" => "1", 
			"repeat" => "1", 
			"rnmos" => "1", 
			"rpmos" => "1", 
			"rtran" => "1", 
			"rtranif0" => "1", 
			"rtanif1" => "1", 
			"scalared" => "1", 
			"small" => "1", 
			"specify" => "1", 
			"specparam" => "1", 
			"strength" => "1", 
			"strong0" => "1", 
			"strong1" => "1", 
			"supply0" => "1", 
			"supply1" => "1", 
			"table" => "1", 
			"task" => "1", 
			"time" => "1", 
			"tran" => "1", 
			"tranif0" => "1", 
			"tranif1" => "1", 
			"tri1" => "1", 
			"tri0" => "1", 
			"triand" => "1", 
			"trior" => "1", 
			"trireg" => "1", 
			"vectored" => "1", 
			"wait" => "1", 
			"wand" => "1", 
			"weak0" => "1", 
			"weak1" => "1", 
			"while" => "1", 
			"wire" => "1", 
			"wor" => "1", 
			"xnor" => "1", 
			"xor" => "1", 
			"$bitstoreal" => "2", 
			"$countdrivers" => "2", 
			"$display" => "2", 
			"$fclose" => "2", 
			"$fdisplay" => "2", 
			"$finish" => "2", 
			"$fmonitor" => "2", 
			"$fopen" => "2", 
			"$fstrobe" => "2", 
			"$fwrite" => "2", 
			"$getpattern" => "2", 
			"$history" => "2", 
			"$hold" => "2", 
			"$incsave" => "2", 
			"$input" => "2", 
			"$itor" => "2", 
			"$key" => "2", 
			"$list" => "2", 
			"$log" => "2", 
			"$monitor" => "2", 
			"$monitoroff" => "2", 
			"$monitoron" => "2", 
			"$nokey" => "2", 
			"$nolog" => "2", 
			"$period" => "2", 
			"$printtimescale" => "2", 
			"$readmemb" => "2", 
			"$readmemh" => "2", 
			"$realtime" => "2", 
			"$realtobits" => "2", 
			"$recovery" => "2", 
			"$reset" => "2", 
			"$reset_count" => "2", 
			"$reset_value" => "2", 
			"$restart" => "2", 
			"$rtoi" => "2", 
			"$save" => "2", 
			"$scale" => "2", 
			"$scope" => "2", 
			"$setup" => "2", 
			"$setuphold" => "2", 
			"$showscopes" => "2", 
			"$showvariables" => "2", 
			"$showvars" => "2", 
			"$skew" => "2", 
			"$sreadmemb" => "2", 
			"$sreadmemh" => "2", 
			"$stime" => "2", 
			"$stop" => "2", 
			"$strobe" => "2", 
			"$time" => "2", 
			"$timeformat" => "2", 
			"$width" => "2", 
			"$write" => "2", 
			"`accelerate" => "3", 
			"`autoexepand_vectornets" => "3", 
			"`celldefine" => "3", 
			"`default_nettype" => "3", 
			"`define" => "3", 
			"`else" => "3", 
			"`endcelldefine" => "3", 
			"`endif" => "3", 
			"`endprotect" => "3", 
			"`endprotected" => "3", 
			"`expand_vectornets" => "3", 
			"`ifdef" => "3", 
			"`include" => "3", 
			"`noaccelerate" => "3", 
			"`noexpand_vectornets" => "3", 
			"`noremove_gatenames" => "3", 
			"`noremove_netnames" => "3", 
			"`nounconnected_drive" => "3", 
			"`protect" => "3", 
			"`protected" => "3", 
			"`remove_gatenames" => "3", 
			"`remove_netnames" => "3", 
			"`resetall" => "3", 
			"`timescale" => "3", 
			"`unconnected_drive" => "3", 
			"accelerate" => "3", 
			"autoexepand_vectornets" => "3", 
			"celldefine" => "3", 
			"default_nettype" => "3", 
			"define" => "3", 
			"endcelldefine" => "3", 
			"endif" => "3", 
			"endprotect" => "3", 
			"endprotected" => "3", 
			"expand_vectornets" => "3", 
			"ifdef" => "3", 
			"include" => "3", 
			"noaccelerate" => "3", 
			"noexpand_vectornets" => "3", 
			"noremove_gatenames" => "3", 
			"noremove_netnames" => "3", 
			"nounconnected_drive" => "3", 
			"protect" => "3", 
			"protected" => "3", 
			"remove_gatenames" => "3", 
			"remove_netnames" => "3", 
			"resetall" => "3", 
			"timescale" => "3", 
			"unconnected_drive" => "3", 
			"," => "3", 
			";" => "3", 
			"{" => "3", 
			"}" => "3", 
			"+" => "3", 
			"-" => "3", 
			"*" => "3", 
			"/" => "3", 
			"%" => "3", 
			">" => "3", 
			">=" => "3", 
			">>" => "3", 
			"<" => "3", 
			"<=" => "3", 
			"<<" => "3", 
			"!" => "3", 
			"!=" => "3", 
			"!==" => "3", 
			"&" => "3", 
			"&&" => "3", 
			"|" => "3", 
			"||" => "3", 
			"==" => "3", 
			"===" => "3", 
			"^" => "3", 
			"^~" => "3", 
			"~" => "3", 
			"~^" => "3", 
			"~&" => "3", 
			"~|" => "3", 
			"?" => "3", 
			":" => "3", 
			"#" => "3", 
			"@" => "3");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"" => "donothing", 
			"1" => "donothing", 
			"3" => "donothing", 
			"2" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
