<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_asm21xx extends HFile{
   function HFile_asm21xx(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// 21xx Assembly
/*************************************/
// Flags

$this->nocase            	= "1";
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

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("{");
$this->blockcommentoff   	= array("}");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"#define" => "1", 
			"#else" => "1", 
			"#endif" => "1", 
			"#if" => "1", 
			"#ifdef" => "1", 
			"#ifndef" => "1", 
			"#include" => "1", 
			"#undef" => "1", 
			"<" => "1", 
			">" => "1", 
			".const" => "2", 
			".endmod" => "2", 
			".external" => "2", 
			".global" => "2", 
			".include" => "2", 
			".init" => "2", 
			".module" => "2", 
			".var" => "2", 
			"abs" => "3", 
			"and" => "3", 
			"ashift" => "3", 
			"by" => "3", 
			"call" => "3", 
			"clrbit" => "3", 
			"dis" => "3", 
			"divq" => "3", 
			"divs" => "3", 
			"dm" => "3", 
			"do" => "3", 
			"ena" => "3", 
			"exp" => "3", 
			"expadj" => "3", 
			"hi" => "3", 
			"hix" => "3", 
			"idle" => "3", 
			"if" => "3", 
			"io" => "3", 
			"jump" => "3", 
			"lo" => "3", 
			"lshift" => "3", 
			"modify" => "3", 
			"neg" => "3", 
			"nop" => "3", 
			"norm" => "3", 
			"not" => "3", 
			"or" => "3", 
			"pass" => "3", 
			"pm" => "3", 
			"pop" => "3", 
			"pos" => "3", 
			"push" => "3", 
			"reset" => "3", 
			"rnd" => "3", 
			"rti" => "3", 
			"rts" => "3", 
			"sat" => "3", 
			"set" => "3", 
			"setbit" => "3", 
			"ss" => "3", 
			"su" => "3", 
			"tglbit" => "3", 
			"toggle" => "3", 
			"tstbit" => "3", 
			"until" => "3", 
			"us" => "3", 
			"uu" => "3", 
			"xor" => "3", 
			"af" => "4", 
			"ar" => "4", 
			"ax0" => "4", 
			"ax1" => "4", 
			"ay0" => "4", 
			"ay1" => "4", 
			"mf" => "4", 
			"mr" => "4", 
			"mr0" => "4", 
			"mr1" => "4", 
			"mr2" => "4", 
			"mx0" => "4", 
			"mx1" => "4", 
			"my0" => "4", 
			"my1" => "4", 
			"none" => "4", 
			"se" => "4", 
			"si" => "4", 
			"sr" => "4", 
			"sr0" => "4", 
			"sr1" => "4", 
			"i0" => "5", 
			"i1" => "5", 
			"i2" => "5", 
			"i3" => "5", 
			"i4" => "5", 
			"i5" => "5", 
			"i6" => "5", 
			"i7" => "5", 
			"l0" => "5", 
			"l1" => "5", 
			"l2" => "5", 
			"l3" => "5", 
			"l4" => "5", 
			"l5" => "5", 
			"l6" => "5", 
			"l7" => "5", 
			"m0" => "5", 
			"m1" => "5", 
			"m2" => "5", 
			"m3" => "5", 
			"m4" => "5", 
			"m5" => "5", 
			"m6" => "5", 
			"m7" => "5", 
			"ac" => "6", 
			"astat" => "6", 
			"av" => "6", 
			"ar_set" => "6", 
			"av_latch" => "6", 
			"bit_rev" => "6", 
			"c" => "6", 
			"ce" => "6", 
			"cntr" => "6", 
			"eq" => "6", 
			"flag_in" => "6", 
			"flag_out" => "6", 
			"fl0" => "6", 
			"fl1" => "6", 
			"fl2" => "6", 
			"ge" => "6", 
			"g_mode" => "6", 
			"gt" => "6", 
			"icntl" => "6", 
			"ifc" => "6", 
			"imask" => "6", 
			"ints" => "6", 
			"le" => "6", 
			"loop" => "6", 
			"lt" => "6", 
			"m_mode" => "6", 
			"mstat" => "6", 
			"mv" => "6", 
			"ne" => "6", 
			"owrcntr" => "6", 
			"pc" => "6", 
			"px" => "6", 
			"rx0" => "6", 
			"rx1" => "6", 
			"sb" => "6", 
			"sec_reg" => "6", 
			"sstat" => "6", 
			"sts" => "6", 
			"toppcstack" => "6", 
			"tx0" => "6", 
			"tx1" => "6", 
			"timer" => "6", 
			"%" => "7", 
			"(" => "7", 
			")" => "7", 
			"*" => "7", 
			"+" => "7", 
			"-" => "7", 
			"//" => "7", 
			"/" => "7", 
			"=" => "7", 
			"^" => "7");

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
