<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_asm68Hc908 extends HFile{
   function HFile_asm68Hc908(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// 68HC908 ASM
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

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "^", "&", "(", ")", "-", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", "'", "\"", "<", ">", " ", "	", " ", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			",X" => "1", 
			"ADC" => "1", 
			"ADD" => "1", 
			"AIS" => "1", 
			"AIX" => "1", 
			"AND" => "1", 
			"ASL" => "1", 
			"ASLA" => "1", 
			"ASLX" => "1", 
			"ASR" => "1", 
			"ASRA" => "1", 
			"ASRX" => "1", 
			"BCC" => "1", 
			"BCLR" => "1", 
			"BCS" => "1", 
			"BEQ" => "1", 
			"BGE" => "1", 
			"BGT" => "1", 
			"BHCC" => "1", 
			"BHCS" => "1", 
			"BHI" => "1", 
			"BHS" => "1", 
			"BIH" => "1", 
			"BIL" => "1", 
			"BIT" => "1", 
			"BLE" => "1", 
			"BLO" => "1", 
			"BLS" => "1", 
			"BLT" => "1", 
			"BMC" => "1", 
			"BMI" => "1", 
			"BMS" => "1", 
			"BNE" => "1", 
			"BPL" => "1", 
			"BRA" => "1", 
			"BRCLR" => "1", 
			"BRN" => "1", 
			"BRSET" => "1", 
			"BSET" => "1", 
			"BSR" => "1", 
			"CBEQ" => "1", 
			"CBEQA" => "1", 
			"CBEQX" => "1", 
			"CLC" => "1", 
			"CLI" => "1", 
			"CLR" => "1", 
			"CLRA" => "1", 
			"CLRH" => "1", 
			"CLRX" => "1", 
			"CMP" => "1", 
			"COM" => "1", 
			"COMA" => "1", 
			"COMX" => "1", 
			"CPHX" => "1", 
			"CPX" => "1", 
			"DAA" => "1", 
			"DBNZ" => "1", 
			"DBNZA" => "1", 
			"DBNZXDEC" => "1", 
			"DECA" => "1", 
			"DECX" => "1", 
			"DIV" => "1", 
			"EOR" => "1", 
			"INC" => "1", 
			"INCA" => "1", 
			"INCX" => "1", 
			"JMP" => "1", 
			"JSR" => "1", 
			"LDA" => "1", 
			"LDHX" => "1", 
			"LDX" => "1", 
			"LSL" => "1", 
			"LSLA" => "1", 
			"LSLX" => "1", 
			"LSR" => "1", 
			"LSRA" => "1", 
			"LSRX" => "1", 
			"MOV" => "1", 
			"MUL" => "1", 
			"NEG" => "1", 
			"NOP" => "1", 
			"NSA" => "1", 
			"ORA" => "1", 
			"BeautifierA" => "1", 
			"BeautifierH" => "1", 
			"BeautifierX" => "1", 
			"PULA" => "1", 
			"PULH" => "1", 
			"PULX" => "1", 
			"ROL" => "1", 
			"ROLA" => "1", 
			"ROLX" => "1", 
			"ROR" => "1", 
			"RORA" => "1", 
			"RORX" => "1", 
			"RSP" => "1", 
			"RTI" => "1", 
			"RTS" => "1", 
			"SBC" => "1", 
			"SEC" => "1", 
			"SEI" => "1", 
			"STA" => "1", 
			"STHX" => "1", 
			"STOP" => "1", 
			"STX" => "1", 
			"SUB" => "1", 
			"SWI" => "1", 
			"TAP" => "1", 
			"TAX" => "1", 
			"TPA" => "1", 
			"TST" => "1", 
			"TSTA" => "1", 
			"TSX" => "1", 
			"TXA" => "1", 
			"TXS" => "1", 
			"ADCLK" => "2", 
			"ADR" => "2", 
			"ADSCR" => "2", 
			"BFCR" => "2", 
			"BRKH" => "2", 
			"BRKL" => "2", 
			"BSCR" => "2", 
			"CONFIG1" => "2", 
			"CONFIG2" => "2", 
			"DDRA" => "2", 
			"DDRB" => "2", 
			"DDRD" => "2", 
			"FLCR" => "2", 
			"FLSPR" => "2", 
			"FLTCR" => "2", 
			"INT1" => "2", 
			"INT2" => "2", 
			"INT3" => "2", 
			"INTKBIER" => "2", 
			"INTKBSR" => "2", 
			"INTSCR" => "2", 
			"KBIER" => "2", 
			"KBSCR" => "2", 
			"PDCR" => "2", 
			"PORTA" => "2", 
			"PORTB" => "2", 
			"PORTD" => "2", 
			"PTA" => "2", 
			"PTAUE" => "2", 
			"PTB" => "2", 
			"PTD" => "2", 
			"RSR" => "2", 
			"TCH0H" => "2", 
			"TCH0L" => "2", 
			"TCH1H" => "2", 
			"TCH1L" => "2", 
			"TCNTH" => "2", 
			"TCNTL" => "2", 
			"TMODH" => "2", 
			"TMODL" => "2", 
			"TSC" => "2", 
			"TSC0" => "2", 
			"TSC1" => "2", 
			"X" => "2", 
			"$if" => "3", 
			"$ifndef" => "3", 
			"$SET" => "3", 
			"$SETNOT" => "3", 
			"$endif" => "3", 
			"$include" => "3", 
			"$else" => "3", 
			"FCB" => "4", 
			"FCC" => "4", 
			"FDB" => "4", 
			"DB" => "4", 
			"DS" => "4", 
			"DW" => "4", 
			"EQU" => "4", 
			"ENDM" => "4", 
			"MACRO" => "4", 
			"ORG" => "4", 
			"SETDP" => "4", 
			"#" => "5", 
			"#$" => "5", 
			"#%" => "5");

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
