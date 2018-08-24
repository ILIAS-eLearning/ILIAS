<?php

$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_mumps extends HFile{
   function HFile_mumps(){
     $this->HFile();

/*************************************/
// Beautifier Highlighting Configuration File 
// Mumps
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

$this->stringchars       	= array("\"");
$this->delimiters        	= array("+", "&", "_", "/", "=", "*", "*", ">", "#", "@", "\\", "<", "-", "*", ".", "!", "?", " ", "[", "]", "\"", "	", ",", ".", "?", "(", ")", "|", ":", ";");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"B" => "1", 
			"C" => "1", 
			"D" => "1", 
			"E" => "1", 
			"F" => "1", 
			"G" => "1", 
			"H" => "1", 
			"I" => "1", 
			"J" => "1", 
			"K" => "1", 
			"L" => "1", 
			"M" => "1", 
			"N" => "1", 
			"O" => "1", 
			"Q" => "1", 
			"R" => "1", 
			"S" => "1", 
			"TC" => "1", 
			"TRE" => "1", 
			"TRO" => "1", 
			"TS" => "1", 
			"U" => "1", 
			"V" => "1", 
			"W" => "1", 
			"X" => "1", 
			"ZA" => "1", 
			"ZD" => "1", 
			"ZF" => "1", 
			"ZG" => "1", 
			"ZHOROLOG" => "1", 
			"ZI" => "1", 
			"ZL" => "1", 
			"ZM" => "1", 
			"ZN" => "1", 
			"ZP" => "1", 
			"ZQ" => "1", 
			"ZR" => "1", 
			"ZS" => "1", 
			"ZSY" => "1", 
			"ZT" => "1", 
			"ZU" => "1", 
			"ZW" => "1", 
			"$$" => "2", 
			"$A" => "2", 
			"$C" => "2", 
			"$D" => "2", 
			"$E" => "2", 
			"$F" => "2", 
			"$FN" => "2", 
			"$G" => "2", 
			"$L" => "2", 
			"$NA" => "2", 
			"$N" => "2", 
			"$O" => "2", 
			"$P" => "2", 
			"$Q" => "2", 
			"$R" => "2", 
			"$RE" => "2", 
			"$S" => "2", 
			"$T" => "2", 
			"$TR" => "2", 
			"$V" => "2", 
			"$ZB" => "2", 
			"$ZBN" => "2", 
			"$ZCR" => "2", 
			"$ZD" => "2", 
			"$ZDE" => "2", 
			"$ZH" => "2", 
			"$ZN" => "2", 
			"$ZO" => "2", 
			"$ZOS" => "2", 
			"$ZP" => "2", 
			"$ZS" => "2", 
			"$ZSE" => "2", 
			"$ZU" => "2", 
			"$ZV" => "2", 
			"$H" => "3", 
			"$I" => "3", 
			"$IO" => "3", 
			"$J" => "3", 
			"$K" => "3", 
			"$SY" => "3", 
			"$TL" => "3", 
			"$X" => "3", 
			"$Y" => "3", 
			"$ZA" => "3", 
			"$ZC" => "3", 
			"$ZE" => "3", 
			"$ZL" => "3", 
			"$ZR" => "3", 
			"$ZT" => "3", 
			"^$DEVICE" => "4", 
			"^$GLOBAL" => "4", 
			"^$JOB" => "4", 
			"^$LOCK" => "4", 
			"^$ROUTINE" => "4", 
			"=" => "5", 
			"+" => "5", 
			"-" => "5", 
			"*" => "5", 
			"_" => "5", 
			"\\" => "5", 
			"/" => "5", 
			":" => "5", 
			"." => "5", 
			"," => "5", 
			"[" => "5", 
			"]" => "5", 
			"'" => "5", 
			"<" => "5", 
			">" => "5", 
			"@" => "5", 
			"#" => "5", 
			"^" => "6", 
			"^(" => "6", 
			"^;" => "6", 
			"^," => "6", 
			"(" => "6", 
			")" => "6", 
			"APPEND" => "7", 
			"NEWVERSION" => "7", 
			"OVERWRITE" => "7");

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

}

?>
