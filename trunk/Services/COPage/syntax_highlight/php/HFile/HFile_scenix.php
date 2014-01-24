<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_scenix extends HFile{
   function HFile_scenix(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Scenix
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

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array(".", "	", ",", "!", "#", ">", "<", "%", "$", "+", "(", ")", "*", "-", "/", "@");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"add" => "1", 
			"addb" => "1", 
			"and" => "1", 
			"bank" => "1", 
			"call" => "1", 
			"cja" => "1", 
			"cjae" => "1", 
			"cjb" => "1", 
			"cjbe" => "1", 
			"cje" => "1", 
			"cjne" => "1", 
			"clc" => "1", 
			"clr" => "1", 
			"clrb" => "1", 
			"clz" => "1", 
			"dec" => "1", 
			"djnz" => "1", 
			"ijnz" => "1", 
			"inc" => "1", 
			"iread" => "1", 
			"jb" => "1", 
			"jc" => "1", 
			"jmp" => "1", 
			"jnb" => "1", 
			"jnc" => "1", 
			"jnz" => "1", 
			"jz" => "1", 
			"lcall" => "1", 
			"ljmp" => "1", 
			"lset" => "1", 
			"mov" => "1", 
			"movb" => "1", 
			"nop" => "1", 
			"not" => "1", 
			"or" => "1", 
			"page" => "1", 
			"ret" => "1", 
			"reti" => "1", 
			"retiw" => "1", 
			"retp" => "1", 
			"retw" => "1", 
			"rl" => "1", 
			"rr" => "1", 
			"setb" => "1", 
			"sleep" => "1", 
			"stc" => "1", 
			"stz" => "1", 
			"sub" => "1", 
			"subb" => "1", 
			"swap" => "1", 
			"test" => "1", 
			"xor" => "1", 
			"\"[0123456789aAbBcCdDeEfF]\"" => "2", 
			"fsr" => "3", 
			"ind" => "3", 
			"indf" => "3", 
			"m" => "3", 
			"option" => "3", 
			"pc" => "3", 
			"ra" => "3", 
			"rb" => "3", 
			"rc" => "3", 
			"rd" => "3", 
			"re" => "3", 
			"w" => "3", 
			"!" => "4", 
			"#" => "4", 
			"$" => "4", 
			"%" => "4", 
			"+" => "4", 
			"<" => "4", 
			"=" => "4", 
			">" => "4", 
			"@" => "4", 
			"banks1" => "4", 
			"banks2" => "4", 
			"banks3" => "4", 
			"banks8" => "4", 
			"device" => "4", 
			"ds" => "4", 
			"equ" => "4", 
			"freq" => "4", 
			"id" => "4", 
			"optionx" => "4", 
			"org" => "4", 
			"oschs" => "4", 
			"oschs1" => "4", 
			"oschs2" => "4", 
			"oschs3" => "4", 
			"oschs4" => "4", 
			"oschs5" => "4", 
			"oscin" => "4", 
			"oscxtmax" => "4", 
			"pages1" => "4", 
			"pages2" => "4", 
			"pages4" => "4", 
			"pins18" => "4", 
			"pins28" => "4", 
			"reset" => "4", 
			"stackx" => "4", 
			"stackx_optionx" => "4", 
			"sx18" => "4", 
			"sx28" => "4", 
			"sx28l" => "4", 
			"sx52" => "4", 
			"turbo" => "4", 
			"watchdog" => "4", 
			"csa" => "5", 
			"csae" => "5", 
			"csb" => "5", 
			"csbe" => "5", 
			"cse" => "5", 
			"csne" => "5", 
			"decsz" => "5", 
			"incsz" => "5", 
			"movsz" => "5", 
			"sb" => "5", 
			"sc" => "5", 
			"skip" => "5", 
			"snb" => "5", 
			"snc" => "5", 
			"snz" => "5", 
			"sz" => "5", 
			"else" => "6", 
			"endif" => "6", 
			"endm" => "6", 
			"endr" => "6", 
			"error" => "6", 
			"exitm" => "6", 
			"if" => "6", 
			"ifdef" => "6", 
			"ifndef" => "6", 
			"macro" => "6", 
			"rept" => "6");

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
