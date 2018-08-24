<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_asmz80 extends HFile{
   function HFile_asmz80(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Z80 Assembler
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "brown", "blue");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "#", "!", "$", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"adc" => "1", 
			"add" => "1", 
			"and" => "1", 
			"bit" => "1", 
			"call" => "1", 
			"ccf" => "1", 
			"cp" => "1", 
			"cpd" => "1", 
			"cpdr" => "1", 
			"cpi" => "1", 
			"cpir" => "1", 
			"cpl" => "1", 
			"daa" => "1", 
			"dec" => "1", 
			"di" => "1", 
			"djnz" => "1", 
			"ei" => "1", 
			"ex" => "1", 
			"exx" => "1", 
			"halt" => "1", 
			"im" => "1", 
			"in" => "1", 
			"inc" => "1", 
			"ind" => "1", 
			"indr" => "1", 
			"ini" => "1", 
			"inir" => "1", 
			"jp" => "1", 
			"jr" => "1", 
			"ld" => "1", 
			"ldd" => "1", 
			"lddr" => "1", 
			"ldi" => "1", 
			"ldir" => "1", 
			"neg" => "1", 
			"nop" => "1", 
			"or" => "1", 
			"otdr" => "1", 
			"otir" => "1", 
			"out" => "1", 
			"outd" => "1", 
			"outi" => "1", 
			"pop" => "1", 
			"push" => "1", 
			"res" => "1", 
			"ret" => "1", 
			"reti" => "1", 
			"retn" => "1", 
			"rl" => "1", 
			"rla" => "1", 
			"rlc" => "1", 
			"rlca" => "1", 
			"rld" => "1", 
			"rr" => "1", 
			"rra" => "1", 
			"rrc" => "1", 
			"rrca" => "1", 
			"rrd" => "1", 
			"rst" => "1", 
			"sbc" => "1", 
			"scf" => "1", 
			"set" => "1", 
			"sla" => "1", 
			"sll" => "1", 
			"sra" => "1", 
			"srl" => "1", 
			"sub" => "1", 
			"xor" => "1", 
			"binary" => "2", 
			"defb" => "2", 
			"defc" => "2", 
			"defgroup" => "2", 
			"define" => "2", 
			"defl" => "2", 
			"defm" => "2", 
			"defs" => "2", 
			"defvars" => "2", 
			"defw" => "2", 
			"else" => "2", 
			"endif" => "2", 
			"if" => "2", 
			"include" => "2", 
			"lib" => "2", 
			"lstoff" => "2", 
			"lston" => "2", 
			"module" => "2", 
			"org" => "2", 
			"xdef" => "2", 
			"xlib" => "2", 
			"xref" => "2", 
			"$" => "4", 
			"." => "4", 
			"+" => "4", 
			"-" => "4", 
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
			"A" => "5", 
			"AF" => "5", 
			"AF\'" => "5", 
			"B" => "5", 
			"BC" => "5", 
			"C" => "5", 
			"D" => "5", 
			"DE" => "5", 
			"E" => "5", 
			"F" => "5", 
			"H" => "5", 
			"HL" => "5", 
			"IX" => "5", 
			"IY" => "5", 
			"IXL" => "5", 
			"IXH" => "5", 
			"IYL" => "5", 
			"IYH" => "5", 
			"L" => "5", 
			"M" => "5", 
			"NZ" => "5", 
			"NC" => "5", 
			"P" => "5", 
			"PE" => "5", 
			"PO" => "5", 
			"Z" => "5");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
