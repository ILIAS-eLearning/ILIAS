<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_verify extends HFile{
   function HFile_verify(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Verilog
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

$this->indent            	= array("begin");
$this->unindent          	= array("end");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"begin" => "1", 
			"case" => "1", 
			"else" => "1", 
			"end" => "1", 
			"endcase" => "1", 
			"for" => "1", 
			"if" => "1", 
			"join" => "1", 
			"memory" => "1", 
			"negedge" => "1", 
			"posedge" => "1", 
			"pullup" => "1", 
			"pulldown" => "1", 
			"while" => "1", 
			"`define" => "2", 
			"`include" => "2", 
			"`timescale" => "2", 
			"`ifdef" => "2", 
			"`else" => "2", 
			"`endif" => "2", 
			"\'b" => "2", 
			"\'d" => "2", 
			"\'h" => "2", 
			"$display" => "2", 
			"$monitor" => "2", 
			"$fopen" => "2", 
			"$fclose" => "2", 
			"$fdisplay" => "2", 
			"$dumfile" => "2", 
			"$dumpvars" => "2", 
			"$finish" => "2", 
			"$stop" => "2", 
			"$setup" => "2", 
			"$hold" => "2", 
			"$readmemh" => "2", 
			"deassign" => "2", 
			"endfunction" => "2", 
			"endmodule" => "2", 
			"endspecify" => "2", 
			"endtask" => "2", 
			"fork" => "2", 
			"function" => "2", 
			"initial" => "2", 
			"module" => "2", 
			"reg" => "2", 
			"repeat" => "2", 
			"specify" => "2", 
			"task" => "2", 
			"wait" => "2", 
			"wire" => "2", 
			"+" => "3", 
			"-" => "3", 
			"*" => "3", 
			"//" => "3", 
			"/" => "3", 
			":" => "3", 
			"=" => "3", 
			"~" => "3", 
			"%" => "3", 
			"&" => "3", 
			">" => "3", 
			"<" => "3", 
			"^" => "3", 
			"!" => "3", 
			"|" => "3", 
			"always" => "3", 
			"assign" => "3", 
			"input" => "3", 
			"inout" => "3", 
			"or" => "3", 
			"output" => "3");

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
