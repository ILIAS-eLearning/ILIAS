<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_rmanshader extends HFile{
   function HFile_rmanshader(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Rman Shader
/*************************************/
// Flags

$this->nocase            	= "0";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array("{");
$this->unindent          	= array("}");

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("~", "!", "@", "%", "^", "&", "*", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", " ", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("//");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"__asm" => "1", 
			"__based" => "1", 
			"__cdecl" => "1", 
			"__export" => "1", 
			"__far" => "1", 
			"__fastcall" => "1", 
			"__fortran" => "1", 
			"__huge" => "1", 
			"__inline" => "1", 
			"__interrupt" => "1", 
			"__loadds" => "1", 
			"__near" => "1", 
			"__pascal" => "1", 
			"__saveregs" => "1", 
			"__segment" => "1", 
			"__segname" => "1", 
			"__self" => "1", 
			"#define" => "1", 
			"#elif" => "1", 
			"#else" => "1", 
			"#endif" => "1", 
			"#error" => "1", 
			"#if" => "1", 
			"#ifdef" => "1", 
			"#ifndef" => "1", 
			"#include" => "1", 
			"#line" => "1", 
			"#pragma" => "1", 
			"#undef" => "1", 
			"auto" => "1", 
			"break" => "1", 
			"case" => "1", 
			"char" => "1", 
			"color" => "1", 
			"const" => "1", 
			"continue" => "1", 
			"default" => "1", 
			"do" => "1", 
			"double" => "1", 
			"else" => "1", 
			"enum" => "1", 
			"extern" => "1", 
			"float" => "1", 
			"for" => "1", 
			"goto" => "1", 
			"if" => "1", 
			"int" => "1", 
			"long" => "1", 
			"normal" => "1", 
			"point" => "1", 
			"register" => "1", 
			"return" => "1", 
			"short" => "1", 
			"signed" => "1", 
			"sizeof" => "1", 
			"static" => "1", 
			"struct" => "1", 
			"switch" => "1", 
			"typedef" => "1", 
			"uniform" => "1", 
			"union" => "1", 
			"unsigned" => "1", 
			"varying" => "1", 
			"void" => "1", 
			"volatile" => "1", 
			"while" => "1", 
			"Ci" => "2", 
			"Cl" => "2", 
			"Cs" => "2", 
			"E" => "2", 
			"I" => "2", 
			"L" => "2", 
			"N" => "2", 
			"Ng" => "2", 
			"Oi" => "2", 
			"Ol" => "2", 
			"Os" => "2", 
			"P" => "2", 
			"alpha" => "2", 
			"class" => "2", 
			"dPdu" => "2", 
			"dPdv" => "2", 
			"delete" => "2", 
			"displacement" => "2", 
			"du" => "2", 
			"dv" => "2", 
			"friend" => "2", 
			"imager" => "2", 
			"inline" => "2", 
			"light" => "2", 
			"ncomps" => "2", 
			"new" => "2", 
			"null" => "2", 
			"operator" => "2", 
			"printf" => "2", 
			"private" => "2", 
			"protected" => "2", 
			"public" => "2", 
			"s" => "2", 
			"surface" => "2", 
			"t" => "2", 
			"this" => "2", 
			"time" => "2", 
			"transformation" => "2", 
			"try" => "2", 
			"u" => "2", 
			"v" => "2", 
			"version" => "2", 
			"virtual" => "2", 
			"volume" => "2", 
			"__multiple_inheritance" => "2", 
			"__single_inheritance" => "2", 
			"__virtual_inheritance" => "2", 
			"Du" => "3", 
			"Dv" => "3", 
			"abs" => "3", 
			"acos" => "3", 
			"asin" => "3", 
			"atan" => "3", 
			"ceil" => "3", 
			"clamp" => "3", 
			"cos" => "3", 
			"degrees" => "3", 
			"exp" => "3", 
			"filteredpulse" => "3", 
			"filteredpulsetrain" => "3", 
			"floor" => "3", 
			"max" => "3", 
			"min" => "3", 
			"mod" => "3", 
			"noise" => "3", 
			"pow" => "3", 
			"pulse" => "3", 
			"pulsetrain" => "3", 
			"random" => "3", 
			"round" => "3", 
			"sign" => "3", 
			"sin" => "3", 
			"smoothstep" => "3", 
			"snoise" => "3", 
			"snoisexy" => "3", 
			"sqr" => "3", 
			"sqrt" => "3", 
			"step" => "3", 
			"tan" => "3", 
			"vsnoise" => "3", 
			"area" => "4", 
			"calculatenormal" => "4", 
			"depth" => "4", 
			"distance" => "4", 
			"faceforward" => "4", 
			"fresnel" => "4", 
			"length" => "4", 
			"normalize" => "4", 
			"reflect" => "4", 
			"refract" => "4", 
			"setxcomp" => "4", 
			"setzcomp" => "4", 
			"transform" => "4", 
			"ycomp" => "4", 
			"comp" => "5", 
			"mix" => "5", 
			"setcomp" => "5", 
			"ambient" => "6", 
			"phong" => "6", 
			"specular" => "6", 
			"trace" => "6", 
			"bump" => "7", 
			"environment" => "7", 
			"shadow" => "7", 
			"texture" => "7", 
			"incident" => "8", 
			"opposite" => "8");

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
			"7" => "donothing", 
			"8" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
