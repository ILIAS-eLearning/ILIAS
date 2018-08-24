<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_6809 extends HFile{
   function HFile_6809(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// 
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

$this->stringchars       	= array("\"");
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "_", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"#$" => "1", 
			"#%" => "1", 
			"&(" => "1", 
			"ABX" => "2", 
			"ADCA" => "2", 
			"ADCB" => "2", 
			"ADDA" => "2", 
			"ADDB" => "2", 
			"ADDD" => "2", 
			"ANDA" => "2", 
			"ANDB" => "2", 
			"ANDCC" => "2", 
			"ASLA" => "2", 
			"ASLB" => "2", 
			"ASL" => "2", 
			"ASRA" => "2", 
			"ASR" => "2", 
			"BCC" => "2", 
			"BCS" => "2", 
			"BEQ" => "2", 
			"BGE" => "2", 
			"BGT" => "2", 
			"BHI" => "2", 
			"BHS" => "2", 
			"BLE" => "2", 
			"BLO" => "2", 
			"BLS" => "2", 
			"BLT" => "2", 
			"BMI" => "2", 
			"BNE" => "2", 
			"BPL" => "2", 
			"BRA" => "2", 
			"BRN" => "2", 
			"BSR" => "2", 
			"BVC" => "2", 
			"BVS" => "2", 
			"BITA" => "2", 
			"BITB" => "2", 
			"CLRA" => "2", 
			"CLRB" => "2", 
			"CLR" => "2", 
			"CMPA" => "2", 
			"CMPB" => "2", 
			"CMPD" => "2", 
			"CMPS" => "2", 
			"CMPU" => "2", 
			"CMPX" => "2", 
			"CMPY" => "2", 
			"COMA" => "2", 
			"COMB" => "2", 
			"COM" => "2", 
			"CWAI" => "2", 
			"DAA" => "2", 
			"DECA" => "2", 
			"DECB" => "2", 
			"DEC" => "2", 
			"EORA" => "2", 
			"EORB" => "2", 
			"EXG" => "2", 
			"INCA" => "2", 
			"INCB" => "2", 
			"INC" => "2", 
			"JMP" => "2", 
			"JSR" => "2", 
			"LDA" => "2", 
			"LDB" => "2", 
			"LDD" => "2", 
			"LDS" => "2", 
			"LDU" => "2", 
			"LDX" => "2", 
			"LDY" => "2", 
			"LEAS" => "2", 
			"LEAU" => "2", 
			"LEAX" => "2", 
			"LEAY" => "2", 
			"LSLA" => "2", 
			"LSLB" => "2", 
			"LSL" => "2", 
			"LSRA" => "2", 
			"LSRB" => "2", 
			"LSR" => "2", 
			"LBCC" => "2", 
			"LBCS" => "2", 
			"LBEQ" => "2", 
			"LBGE" => "2", 
			"LBGT" => "2", 
			"LBHI" => "2", 
			"LBHS" => "2", 
			"LBLE" => "2", 
			"LBLO" => "2", 
			"LBLS" => "2", 
			"LBLT" => "2", 
			"LBMI" => "2", 
			"LBNE" => "2", 
			"LBPL" => "2", 
			"LBRA" => "2", 
			"LBRN" => "2", 
			"LBSR" => "2", 
			"LBVC" => "2", 
			"LBVS" => "2", 
			"MUL" => "2", 
			"NEGA" => "2", 
			"NEGB" => "2", 
			"NEG" => "2", 
			"NOP" => "2", 
			"ORA" => "2", 
			"ORB" => "2", 
			"ORCC" => "2", 
			"BeautifierS" => "2", 
			"BeautifierU" => "2", 
			"PULS" => "2", 
			"PULU" => "2", 
			"ROLA" => "2", 
			"ROLB" => "2", 
			"ROL" => "2", 
			"RORA" => "2", 
			"RORB" => "2", 
			"ROR" => "2", 
			"RTI" => "2", 
			"RTS" => "2", 
			"SBCA" => "2", 
			"SBCB" => "2", 
			"SEX" => "2", 
			"STA" => "2", 
			"STB" => "2", 
			"STD" => "2", 
			"STS" => "2", 
			"STU" => "2", 
			"STX" => "2", 
			"STY" => "2", 
			"SUBA" => "2", 
			"SUBB" => "2", 
			"SUBD" => "2", 
			"SWI" => "2", 
			"SWI2" => "2", 
			"SWI3" => "2", 
			"SYNC" => "2", 
			"TFR" => "2", 
			"TSTA" => "2", 
			"TSTB" => "2", 
			"TST" => "2", 
			"FCB" => "3", 
			"FDB" => "3", 
			"FCC" => "3", 
			"RMB" => "3", 
			"END" => "4", 
			"LIB" => "4", 
			"EQU" => "5", 
			"ENDM" => "5", 
			"MACRO" => "5", 
			"ORG" => "5", 
			"SETDP" => "5");

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
