<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_asms370 extends HFile{
   function HFile_asms370(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// S/370 Assembler
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

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array(":", ";", "\"", "'", ",", ".", "(", ")", " ");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("*");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"A" => "1", 
			"AD" => "1", 
			"ADR" => "1", 
			"AE" => "1", 
			"AER" => "1", 
			"AH" => "1", 
			"AL" => "1", 
			"ALR" => "1", 
			"AP" => "1", 
			"AR" => "1", 
			"AU" => "1", 
			"AUR" => "1", 
			"AW" => "1", 
			"AWR" => "1", 
			"B" => "1", 
			"BAKR" => "1", 
			"BAL" => "1", 
			"BALR" => "1", 
			"BAS" => "1", 
			"BASSM" => "1", 
			"BC" => "1", 
			"BCR" => "1", 
			"BCT" => "1", 
			"BCTR" => "1", 
			"BE" => "1", 
			"BH" => "1", 
			"BL" => "1", 
			"BM" => "1", 
			"BNE" => "1", 
			"BNH" => "1", 
			"BNL" => "1", 
			"BNM" => "1", 
			"BNO" => "1", 
			"BNP" => "1", 
			"BNZ" => "1", 
			"BO" => "1", 
			"BP" => "1", 
			"BR" => "1", 
			"BSM" => "1", 
			"BXH" => "1", 
			"BXLE" => "1", 
			"BZ" => "1", 
			"C" => "1", 
			"CALL" => "1", 
			"CD" => "1", 
			"CDR" => "1", 
			"CE" => "1", 
			"CER" => "1", 
			"CH" => "1", 
			"CL" => "1", 
			"CLC" => "1", 
			"CLI" => "1", 
			"CLR" => "1", 
			"CP" => "1", 
			"CR" => "1", 
			"CVB" => "1", 
			"CVD" => "1", 
			"D" => "1", 
			"DD" => "1", 
			"DDR" => "1", 
			"DE" => "1", 
			"DELETE" => "1", 
			"DER" => "1", 
			"DP" => "1", 
			"DR" => "1", 
			"DC" => "1", 
			"DS" => "1", 
			"ED" => "1", 
			"EDMK" => "1", 
			"EX" => "1", 
			"HDR" => "1", 
			"HER" => "1", 
			"HIO" => "1", 
			"IC" => "1", 
			"ICM" => "1", 
			"ISK" => "1", 
			"L" => "1", 
			"LA" => "1", 
			"LCDR" => "1", 
			"LCER" => "1", 
			"LCR" => "1", 
			"LD" => "1", 
			"LDR" => "1", 
			"LE" => "1", 
			"LER" => "1", 
			"LH" => "1", 
			"LINK" => "1", 
			"LM" => "1", 
			"LNDR" => "1", 
			"LNER" => "1", 
			"LNR" => "1", 
			"LNSW" => "1", 
			"LOAD" => "1", 
			"LR" => "1", 
			"LTDR" => "1", 
			"LTER" => "1", 
			"LTR" => "1", 
			"M" => "1", 
			"MD" => "1", 
			"MDR" => "1", 
			"ME" => "1", 
			"MER" => "1", 
			"MH" => "1", 
			"MP" => "1", 
			"MR" => "1", 
			"MVC" => "1", 
			"MVCL" => "1", 
			"MVCP" => "1", 
			"MVI" => "1", 
			"MVN" => "1", 
			"MVO" => "1", 
			"MVZ" => "1", 
			"N" => "1", 
			"NC" => "1", 
			"NI" => "1", 
			"NOP" => "1", 
			"NOPR" => "1", 
			"NR" => "1", 
			"O" => "1", 
			"OC" => "1", 
			"OI" => "1", 
			"OR" => "1", 
			"PACK" => "1", 
			"PC" => "1", 
			"RDD" => "1", 
			"S" => "1", 
			"SD" => "1", 
			"SDR" => "1", 
			"SE" => "1", 
			"SER" => "1", 
			"SH" => "1", 
			"SI" => "1", 
			"SIO" => "1", 
			"SLA" => "1", 
			"SLDA" => "1", 
			"SLDL" => "1", 
			"SLL" => "1", 
			"SLR" => "1", 
			"SP" => "1", 
			"SPM" => "1", 
			"SR" => "1", 
			"SRA" => "1", 
			"SRDA" => "1", 
			"SRDL" => "1", 
			"SRL" => "1", 
			"SSK" => "1", 
			"SSM" => "1", 
			"ST" => "1", 
			"STC" => "1", 
			"STD" => "1", 
			"STE" => "1", 
			"STH" => "1", 
			"STM" => "1", 
			"SU" => "1", 
			"SVC" => "1", 
			"SW" => "1", 
			"SWR" => "1", 
			"TCH" => "1", 
			"TIO" => "1", 
			"TM" => "1", 
			"TR" => "1", 
			"TRT" => "1", 
			"TS" => "1", 
			"UNPK" => "1", 
			"X" => "1", 
			"X1" => "1", 
			"XC" => "1", 
			"XR" => "1", 
			"ZAP" => "1", 
			"AMODE" => "2", 
			"CSECT" => "2", 
			"DROP" => "2", 
			"DSECT" => "2", 
			"EJECT" => "2", 
			"END" => "2", 
			"EQU" => "2", 
			"LTORG" => "2", 
			"ORG" => "2", 
			"PRINT" => "2", 
			"REQUATE" => "2", 
			"RMODE" => "2", 
			"SPACE" => "2", 
			"START" => "2", 
			"TITLE" => "2", 
			"USING" => "2", 
			"YREGS" => "2", 
			"R" => "3", 
			"R0" => "3", 
			"R1" => "3", 
			"R10" => "3", 
			"R11" => "3", 
			"R12" => "3", 
			"R13" => "3", 
			"R14" => "3", 
			"R2" => "3", 
			"R3" => "3", 
			"R4" => "3", 
			"R5" => "3", 
			"R6" => "3", 
			"R7" => "3", 
			"R8" => "3", 
			"R9" => "3");

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
