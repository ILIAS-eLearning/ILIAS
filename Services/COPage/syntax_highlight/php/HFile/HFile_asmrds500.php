<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_asmrds500 extends HFile{
   function HFile_asmrds500(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// Assembler RDS-500
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

$this->stringchars       	= array("\"", "'");
$this->delimiters        	= array("+", "-", ",", ".", "$", " ", "/", "[", "]", "=", ":", "*", "(", ")", "#", "%", ";");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("**");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ASMB" => "1", 
			"BLK" => "1", 
			"BLOW" => "1", 
			"BYTE" => "1", 
			"CHAD" => "1", 
			"DATA" => "1", 
			"DATE" => "1", 
			"DFLD" => "1", 
			"DFLH" => "1", 
			"DFLI" => "1", 
			"DFLM" => "1", 
			"DFLR" => "1", 
			"DO" => "1", 
			"DPI" => "1", 
			"DPR" => "1", 
			"EJCT" => "1", 
			"END" => "1", 
			"ENDC" => "1", 
			"ENDP" => "1", 
			"EQU" => "1", 
			"EXIT" => "1", 
			"INCLUDE" => "1", 
			"IS" => "1", 
			"LABL" => "1", 
			"LIBR" => "1", 
			"LIST" => "1", 
			"LOAD" => "1", 
			"LORG" => "1", 
			"NLST" => "1", 
			"NPRINT" => "1", 
			"NTRY" => "1", 
			"ORIG" => "1", 
			"PAGE" => "1", 
			"PRINT" => "1", 
			"PROC" => "1", 
			"PALS" => "1", 
			"RES" => "1", 
			"RQS" => "1", 
			"SKAD" => "1", 
			"SPAC" => "1", 
			"SPR" => "1", 
			"SUBR" => "1", 
			"TEXT" => "1", 
			"TRUE" => "1", 
			"X" => "1", 
			"HALT" => "2", 
			"ADD" => "3", 
			"AND" => "3", 
			"CMB" => "3", 
			"CMW" => "3", 
			"DSB" => "3", 
			"ENB" => "3", 
			"INR" => "3", 
			"JMP" => "3", 
			"JSX" => "3", 
			"LDB" => "3", 
			"LDW" => "3", 
			"LDX" => "3", 
			"ORI" => "3", 
			"ORE" => "3", 
			"SLA" => "3", 
			"SLC" => "3", 
			"SLL" => "3", 
			"SML" => "3", 
			"SMU" => "3", 
			"SRA" => "3", 
			"SRL" => "3", 
			"SRC" => "3", 
			"STB" => "3", 
			"STX" => "3", 
			"STW" => "3", 
			"SUB" => "3", 
			"SUS" => "3", 
			"A" => "4", 
			"D" => "4", 
			"L" => "4", 
			"R" => "4", 
			"U" => "4", 
			"=" => "4", 
			"+" => "4", 
			"-" => "4", 
			"$" => "4", 
			":" => "4", 
			"*" => "4", 
			"CAX" => "5", 
			"CEX" => "5", 
			"CLR" => "5", 
			"CMP" => "5", 
			"CXA" => "5", 
			"CXE" => "5", 
			"EEX" => "5", 
			"INV" => "5", 
			"MSK" => "5", 
			"NOP" => "5", 
			"SAM" => "5", 
			"SAO" => "5", 
			"SAP" => "5", 
			"SAZ" => "5", 
			"SEQ" => "5", 
			"SGM" => "5", 
			"SGR" => "5", 
			"SLE" => "5", 
			"SLM" => "5", 
			"SLS" => "5", 
			"SNE" => "5", 
			"SNO" => "5", 
			"SS1" => "5", 
			"SS2" => "5", 
			"SS3" => "5", 
			"SSE" => "5", 
			"SSO" => "5", 
			"SXE" => "5", 
			"SXM" => "5", 
			"SXP" => "5", 
			"UNM" => "5", 
			"XEE" => "5", 
			"CF" => "6", 
			"CFD" => "6", 
			"CLB" => "6", 
			"DIV" => "6", 
			"DIN" => "6", 
			"DOT" => "6", 
			"DXS" => "6", 
			"FAB" => "6", 
			"FABD" => "6", 
			"FCS" => "6", 
			"FCSD" => "6", 
			"FLT" => "6", 
			"FLTD" => "6", 
			"FIX" => "6", 
			"FIXD" => "6", 
			"FTR" => "6", 
			"FTRD" => "6", 
			"FXR" => "6", 
			"FXRD" => "6", 
			"FAD" => "6", 
			"FADD" => "6", 
			"FSB" => "6", 
			"FSBD" => "6", 
			"FM" => "6", 
			"FMD" => "6", 
			"FDV" => "6", 
			"FDVD" => "6", 
			"IXS" => "6", 
			"LF" => "6", 
			"LFD" => "6", 
			"LLB" => "6", 
			"LM" => "6", 
			"LPL" => "6", 
			"LPU" => "6", 
			"MPY" => "6", 
			"SF" => "6", 
			"SFD" => "6", 
			"SM" => "6", 
			"DCR" => "7", 
			"DRS" => "7", 
			"IVR" => "7", 
			"ICR" => "7", 
			"IRS" => "7", 
			"LDR" => "7", 
			"RRA" => "7", 
			"RRC" => "7", 
			"RRM" => "7", 
			"RRS" => "7", 
			"SMB" => "7", 
			"SRZ" => "7", 
			"SRP" => "7", 
			"SRN" => "7", 
			"STR" => "7", 
			"R0" => "8", 
			"R1" => "8", 
			"R2" => "8", 
			"R3" => "8", 
			"R4" => "8", 
			"R5" => "8", 
			"R6" => "8", 
			"R7" => "8", 
			"R8" => "8", 
			"R9" => "8");

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
