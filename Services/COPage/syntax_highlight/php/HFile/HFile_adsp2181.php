<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_adsp2181 extends HFile{
   function HFile_adsp2181(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// ADSP2181
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple", "gray");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			".adsp2181" => "1", 
			".const" => "1", 
			".endmod" => "1", 
			".endsys" => "1", 
			".entry" => "1", 
			".external" => "1", 
			".global" => "1", 
			".include" => "1", 
			".init" => "1", 
			".mmap0" => "1", 
			".module" => "1", 
			".seg" => "1", 
			".system" => "1", 
			".var" => "1", 
			"abs" => "1", 
			"circ" => "1", 
			"code" => "1", 
			"data" => "1", 
			"%" => "2", 
			"^" => "2", 
			"and" => "2", 
			"ashift" => "2", 
			"by" => "2", 
			"call" => "2", 
			"clrbit" => "2", 
			"dis" => "2", 
			"do" => "2", 
			"ena" => "2", 
			"exp" => "2", 
			"expadj" => "2", 
			"if" => "2", 
			"jump" => "2", 
			"lo" => "2", 
			"lshift" => "2", 
			"modify" => "2", 
			"none" => "2", 
			"nop" => "2", 
			"norm" => "2", 
			"of" => "2", 
			"or" => "2", 
			"pass" => "2", 
			"pop" => "2", 
			"push" => "2", 
			"reset" => "2", 
			"rnd" => "2", 
			"rti" => "2", 
			"rts" => "2", 
			"sat" => "2", 
			"sec_regset" => "2", 
			"setbit" => "2", 
			"ss" => "2", 
			"su" => "3", 
			"tglbit" => "2", 
			"toggle" => "2", 
			"toppcstack" => "2", 
			"tstbit" => "2", 
			"until" => "2", 
			"us" => "2", 
			"uu" => "2", 
			"xor" => "2", 
			"af" => "3", 
			"ar" => "3", 
			"astat" => "3", 
			"ax0" => "3", 
			"ax1" => "3", 
			"ay0" => "3", 
			"ay1" => "3", 
			"cntr" => "3", 
			"divq" => "3", 
			"divs" => "3", 
			"i0" => "3", 
			"i1" => "3", 
			"i2" => "3", 
			"i3" => "3", 
			"i4" => "3", 
			"i5" => "3", 
			"i6" => "3", 
			"i7" => "3", 
			"icntl" => "3", 
			"ifc" => "3", 
			"imask" => "3", 
			"l0" => "3", 
			"l1" => "3", 
			"l2" => "3", 
			"l3" => "3", 
			"l4" => "3", 
			"l5" => "3", 
			"l6" => "3", 
			"l7" => "3", 
			"loop" => "3", 
			"m0" => "3", 
			"m1" => "3", 
			"m2" => "3", 
			"m3" => "3", 
			"m4" => "3", 
			"m5" => "3", 
			"m6" => "3", 
			"m7" => "3", 
			"mf" => "3", 
			"mr" => "3", 
			"mr0" => "3", 
			"mr1" => "3", 
			"mr2" => "3", 
			"mstat" => "3", 
			"mx0" => "3", 
			"mx1" => "3", 
			"my0" => "3", 
			"my1" => "3", 
			"pc" => "3", 
			"sb" => "3", 
			"sesi" => "3", 
			"sr" => "3", 
			"sr0" => "3", 
			"sr1" => "3", 
			"sts" => "3", 
			"bm" => "4", 
			"dm" => "4", 
			"im" => "4", 
			"io" => "4", 
			"pm" => "4", 
			"ram" => "4", 
			"rom" => "4", 
			"ac" => "5", 
			"av" => "5", 
			"eq" => "5", 
			"ge" => "5", 
			"gt" => "5", 
			"le" => "5", 
			"lt" => "5", 
			"mv" => "5", 
			"ne" => "5", 
			"neg" => "5", 
			"not" => "5", 
			"pos" => "5", 
			"c" => "6", 
			"ce" => "6", 
			"fl0" => "6", 
			"fl1" => "6", 
			"fl2" => "6", 
			"flag_in" => "6", 
			"flag_out" => "6", 
			"m_mode" => "6", 
			"!" => "7", 
			"$" => "7", 
			"&" => "7", 
			"(" => "7", 
			")" => "7", 
			"*" => "7", 
			"+" => "7", 
			"," => "7", 
			"-" => "7", 
			"." => "7", 
			"//" => "7", 
			"/" => "7", 
			":" => "7", 
			";" => "7", 
			"<" => "7", 
			"=" => "7", 
			">" => "7", 
			"[" => "7", 
			"]" => "7", 
			"|" => "7");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing", 
			"7" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
