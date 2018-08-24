<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_map extends HFile{
   function HFile_map(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// MAP File
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

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array(".", " ", ",", "	", ":");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"BIT" => "1", 
			"BTW" => "1", 
			"BYT" => "1", 
			"CODE" => "1", 
			"D16" => "1", 
			"DATA" => "1", 
			"DT3" => "1", 
			"DT4" => "1", 
			"DT8" => "1", 
			"FAR" => "1", 
			"HDAT" => "1", 
			"INT" => "1", 
			"LDAT" => "1", 
			"NEA" => "1", 
			"PDAT" => "1", 
			"REG" => "1", 
			"TSK" => "1", 
			"WOR" => "1", 
			"BITA" => "2", 
			"BYTE" => "2", 
			"DWORD" => "2", 
			"IRAM" => "2", 
			"PAGE" => "2", 
			"PECA" => "2", 
			"SEGM" => "2", 
			"WORD" => "2", 
			"AT" => "3", 
			"COMM" => "3", 
			"GLOB" => "3", 
			"GUSK" => "3", 
			"PRIV" => "3", 
			"PUBL" => "3", 
			"SSTK" => "3", 
			"USTK" => "3", 
			"ABS" => "4", 
			"RAM" => "4", 
			"ROM" => "5", 
			"Reserved" => "5", 
			"?FI" => "6", 
			"?LI" => "6", 
			"?SY" => "6", 
			"EXT" => "6", 
			"GLB" => "6", 
			"LOC" => "6", 
			"PUB" => "6", 
			"CBITS" => "7", 
			"CBITWORDS" => "7", 
			"CFAR" => "7", 
			"CFARROM" => "7", 
			"CHUGE" => "7", 
			"CHUGEROM" => "7", 
			"CINITROM" => "7", 
			"CIRAM" => "7", 
			"CNEAR" => "7", 
			"CNEAR2" => "7", 
			"CPROGRAM" => "7", 
			"CROM" => "7", 
			"CSHUGE" => "7", 
			"CSHUGEROM" => "7", 
			"CSYSTEM" => "7", 
			"CUSTACK" => "7", 
			"C_INIT" => "7", 
			"CLIBRARY" => "8", 
			"RTLIBRARY" => "8", 
			"SHAREDCLIB" => "8", 
			"SHAREDRTLIB" => "8");

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
