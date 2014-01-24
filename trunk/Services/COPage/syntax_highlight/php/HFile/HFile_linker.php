<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_linker extends HFile{
   function HFile_linker(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Linker File
/*************************************/
// Flags

$this->nocase            	= "1";
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
$this->delimiters        	= array("+", "?", "(", ")", ":", ";", "\\", " ", ",", "	", "-");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"HD" => "1", 
			"HEADER" => "1", 
			"LISTREGISTERS" => "1", 
			"LISTSYMBOLS" => "1", 
			"LRG" => "1", 
			"LSY" => "1", 
			"MA" => "1", 
			"MAP" => "1", 
			"NOHD" => "1", 
			"NOHEADER" => "1", 
			"NOLISTREGISTERS" => "1", 
			"NOLISTSYMBOLS" => "1", 
			"NOLRG" => "1", 
			"NOLSY" => "1", 
			"NOMA" => "1", 
			"NOMAP" => "1", 
			"NOPR" => "1", 
			"NOPRINT" => "1", 
			"PR" => "1", 
			"PRINT" => "1", 
			"AS" => "2", 
			"ASSIGN" => "2", 
			"CM" => "2", 
			"COMMENTS" => "2", 
			"DB" => "2", 
			"DEBUG" => "2", 
			"EC" => "2", 
			"EX" => "2", 
			"EXCEPT" => "2", 
			"EXTERNS" => "2", 
			"GL" => "2", 
			"GLOBALS" => "2", 
			"GR" => "2", 
			"GROUPS" => "2", 
			"IN" => "2", 
			"INTNRS" => "2", 
			"LC" => "2", 
			"LINES" => "2", 
			"LN" => "2", 
			"LOCALS" => "2", 
			"NOCM" => "2", 
			"NOCOMMENTS" => "2", 
			"NODB" => "2", 
			"NODEBUG" => "2", 
			"NOGL" => "2", 
			"NOGLOBALS" => "2", 
			"NOLC" => "2", 
			"NOLINES" => "2", 
			"NOLN" => "2", 
			"NOLOCALS" => "2", 
			"NOPB" => "2", 
			"NOPU" => "2", 
			"NOPUBLICS" => "2", 
			"NOPURGE" => "2", 
			"NOSB" => "2", 
			"NOSM" => "2", 
			"NOSYMB" => "2", 
			"OBJECTCONTROLS" => "2", 
			"OC" => "2", 
			"PB" => "2", 
			"PC" => "2", 
			"PRINTCONTROLS" => "2", 
			"PU" => "2", 
			"PUBLICS" => "2", 
			"PURGE" => "2", 
			"RENAMESYMBOLS" => "2", 
			"RS" => "2", 
			"SB" => "2", 
			"SC" => "2", 
			"SM" => "2", 
			"SYMB" => "2", 
			"SYMBOLCOLUMNS" => "2", 
			"DA" => "3", 
			"DATE" => "3", 
			"NOPA" => "3", 
			"NOPAGING" => "3", 
			"PA" => "3", 
			"PAGELENGTH" => "3", 
			"PAGEWIDTH" => "3", 
			"PAGING" => "3", 
			"PL" => "3", 
			"PW" => "3", 
			"TITLE" => "3", 
			"TT" => "3", 
			"AD" => "4", 
			"ADDRESSES" => "4", 
			"CL" => "4", 
			"CLASSES" => "4", 
			"FP" => "4", 
			"FPS" => "4", 
			"FPSTACKSIZE" => "4", 
			"HEAPSIZE" => "4", 
			"HP" => "4", 
			"HS" => "4", 
			"INTTBL" => "4", 
			"IR" => "4", 
			"IRAM" => "4", 
			"IRAMSIZE" => "4", 
			"IS" => "4", 
			"IT" => "4", 
			"LINEAR" => "4", 
			"LR" => "4", 
			"ME" => "4", 
			"MEMORY" => "4", 
			"NOIR" => "4", 
			"NOIRAM" => "4", 
			"NOSAL" => "4", 
			"NOSORTALIGN" => "4", 
			"NOVECINIT" => "4", 
			"NOVECTAB" => "4", 
			"NOVI" => "4", 
			"NOVT" => "4", 
			"OR" => "4", 
			"ORDER" => "4", 
			"OVERLAY" => "4", 
			"OVL" => "4", 
			"PECPTR" => "4", 
			"PP" => "4", 
			"RAM" => "4", 
			"RB" => "4", 
			"RBANK" => "4", 
			"RE" => "4", 
			"RESERVE" => "4", 
			"ROM" => "4", 
			"SAL" => "4", 
			"SE" => "4", 
			"SECSIZE" => "4", 
			"SECTIONS" => "4", 
			"SETNOSGDPP" => "4", 
			"SND" => "4", 
			"SORTALIGN" => "4", 
			"SS" => "4", 
			"SY" => "4", 
			"SYSSTACK" => "4", 
			"VECINIT" => "4", 
			"VECTAB" => "4", 
			"VI" => "4", 
			"VT" => "4", 
			"(" => "5", 
			")" => "5", 
			"+" => "5", 
			"," => "5", 
			"-" => "5", 
			"?" => "5", 
			"CA" => "6", 
			"CASE" => "6", 
			"CC" => "6", 
			"CHECKCLASSES" => "6", 
			"GENERAL" => "6", 
			"GLOBALSONLY" => "6", 
			"GN" => "6", 
			"GO" => "6", 
			"INT" => "6", 
			"INTERRUPT" => "6", 
			"LIBPATH" => "6", 
			"LINK" => "6", 
			"LNK" => "6", 
			"LOC" => "6", 
			"LOCATE" => "6", 
			"LP" => "6", 
			"MODPATH" => "6", 
			"MP" => "6", 
			"NA" => "6", 
			"NAME" => "6", 
			"NOCA" => "6", 
			"NOCASE" => "6", 
			"NOCC" => "6", 
			"NOCHECKCLASSES" => "6", 
			"NOST" => "6", 
			"NOSTRICTTASK" => "6", 
			"NOTY" => "6", 
			"NOTYPE" => "6", 
			"NOWA" => "6", 
			"NOWARNING" => "6", 
			"PO" => "6", 
			"PTOG" => "6", 
			"PUBLICSONLY" => "6", 
			"PUBTOGLB" => "6", 
			"ST" => "6", 
			"STRICTTASK" => "6", 
			"TY" => "6", 
			"TYPE" => "6", 
			"WA" => "6", 
			"WARNING" => "6", 
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
