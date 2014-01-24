<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_asmavr extends HFile{
   function HFile_asmavr(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// AVR assembler
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

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "_", "=", "|", "\\", "/", "{", "}", "	", "[", "]", ":", "\"", "'", "<", ">", " ", ",", "?", "/");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"add" => "1", 
			"adc" => "1", 
			"adiw" => "1", 
			"and" => "1", 
			"andi" => "1", 
			"cbr" => "1", 
			"clr" => "1", 
			"com" => "1", 
			"cp" => "1", 
			"cpc" => "1", 
			"cpi" => "1", 
			"dec" => "1", 
			"eor" => "1", 
			"fmul" => "1", 
			"fmuls" => "1", 
			"fmulsu" => "1", 
			"inc" => "1", 
			"mul" => "1", 
			"muls" => "1", 
			"mulsu" => "1", 
			"neg" => "1", 
			"or" => "1", 
			"ori" => "1", 
			"sub" => "1", 
			"subi" => "1", 
			"sbc" => "1", 
			"sbci" => "1", 
			"sbiw" => "1", 
			"sbr" => "1", 
			"ser" => "1", 
			"tst" => "1", 
			"brbs" => "2", 
			"brbc" => "2", 
			"breq" => "2", 
			"brne" => "2", 
			"brcs" => "2", 
			"brcc" => "2", 
			"brsh" => "2", 
			"brlo" => "2", 
			"brmi" => "2", 
			"brpl" => "2", 
			"brge" => "2", 
			"brlt" => "2", 
			"brhs" => "2", 
			"brhc" => "2", 
			"brts" => "2", 
			"brtc" => "2", 
			"brvs" => "2", 
			"brvc" => "2", 
			"brie" => "2", 
			"brid" => "2", 
			"call" => "2", 
			"cpse" => "2", 
			"eicall" => "2", 
			"eijmp" => "2", 
			"ijmp" => "2", 
			"icall" => "2", 
			"jmp" => "2", 
			"rjmp" => "2", 
			"rcall" => "2", 
			"ret" => "2", 
			"reti" => "2", 
			"sbrc" => "2", 
			"sbrs" => "2", 
			"sbic" => "2", 
			"sbis" => "2", 
			"elpm" => "3", 
			"espm" => "3", 
			"in" => "3", 
			"ldi" => "3", 
			"lds" => "3", 
			"ld" => "3", 
			"ldd" => "3", 
			"lpm" => "3", 
			"mov" => "3", 
			"movw" => "3", 
			"out" => "3", 
			"push" => "3", 
			"pop" => "3", 
			"st" => "3", 
			"sts" => "3", 
			"std" => "3", 
			"spm" => "3", 
			"x" => "3", 
			"x+" => "3", 
			"y" => "3", 
			"y+" => "3", 
			"y+q" => "3", 
			"z" => "3", 
			"z+" => "3", 
			"z+q" => "3", 
			"-x" => "3", 
			"-y" => "3", 
			"-z" => "3", 
			"asr" => "4", 
			"cbi" => "4", 
			"clc" => "4", 
			"cln" => "4", 
			"clz" => "4", 
			"cli" => "4", 
			"cls" => "4", 
			"clv" => "4", 
			"clt" => "4", 
			"clh" => "4", 
			"lsl" => "4", 
			"lsr" => "4", 
			"nop" => "4", 
			"ror" => "4", 
			"rol" => "4", 
			"sbi" => "4", 
			"sec" => "4", 
			"sen" => "4", 
			"sez" => "4", 
			"sei" => "4", 
			"ses" => "4", 
			"sev" => "4", 
			"set" => "4", 
			"seh" => "4", 
			"swap" => "4", 
			"sleep" => "4", 
			"bst" => "4", 
			"bld" => "4", 
			"bset" => "4", 
			"bclr" => "4", 
			"wdr" => "4", 
			".org" => "5", 
			".equ" => "5", 
			".include" => "5", 
			".macro" => "5", 
			".endmacro" => "5", 
			".set" => "5", 
			".byte" => "5", 
			".cseg" => "5", 
			".db" => "5", 
			".def" => "5", 
			".device" => "5", 
			".dseg" => "5", 
			".dw" => "5", 
			".eseg" => "5", 
			".exit" => "5", 
			".list" => "5", 
			".nolist" => "5", 
			".listmac" => "5");

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
