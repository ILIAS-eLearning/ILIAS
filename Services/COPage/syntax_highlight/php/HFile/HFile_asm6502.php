<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_asm6502 extends HFile{
   function HFile_asm6502(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// ASM for 6502
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("$", "%", "+", "(", ")", ";", "\"", "'", " ", ",", "	", "#");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("");
$this->blockcommentoff   	= array("");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ADC" => "1", 
			"AND" => "1", 
			"ASL" => "1", 
			"BCC" => "1", 
			"BCS" => "1", 
			"BEQ" => "1", 
			"BIT" => "1", 
			"BMI" => "1", 
			"BNE" => "1", 
			"BPL" => "1", 
			"BRA" => "1", 
			"BRK" => "1", 
			"BVC" => "1", 
			"BVS" => "1", 
			"CLC" => "1", 
			"CLD" => "1", 
			"CLI" => "1", 
			"CLV" => "1", 
			"CMP" => "1", 
			"CPX" => "1", 
			"CPY" => "1", 
			"DEC" => "1", 
			"DEX" => "1", 
			"DEY" => "1", 
			"EOR" => "1", 
			"INC" => "1", 
			"INX" => "1", 
			"INY" => "1", 
			"JMP" => "1", 
			"JSR" => "1", 
			"LDA" => "1", 
			"LDX" => "1", 
			"LDY" => "1", 
			"LSR" => "1", 
			"NOP" => "1", 
			"ORA" => "1", 
			"PHA" => "1", 
			"PHP" => "1", 
			"PHX" => "1", 
			"PHY" => "1", 
			"PLA" => "1", 
			"PLP" => "1", 
			"PLX" => "1", 
			"PLY" => "1", 
			"ROL" => "1", 
			"ROR" => "1", 
			"RTI" => "1", 
			"RTS" => "1", 
			"SBC" => "1", 
			"SEC" => "1", 
			"SED" => "1", 
			"SEI" => "1", 
			"STA" => "1", 
			"STX" => "1", 
			"STY" => "1", 
			"STZ" => "1", 
			"TAX" => "1", 
			"TAY" => "1", 
			"TSX" => "1", 
			"TXA" => "1", 
			"TXS" => "1", 
			"TYA" => "1", 
			"A" => "2", 
			"X" => "2", 
			"Y" => "2", 
			"C" => "3", 
			"#" => "5", 
			"$" => "5", 
			"%" => "5", 
			"(" => "5", 
			")" => "5", 
			"+" => "5", 
			"," => "5", 
			";" => "5", 
			"ABSOLUTE" => "6", 
			"BYTE" => "6", 
			"DB" => "6", 
			"DRIVES" => "6", 
			"DS" => "6", 
			"DW" => "6", 
			"END" => "6", 
			"EQU" => "6", 
			"LIST" => "6", 
			"MOD32" => "6", 
			"OFF" => "6", 
			"ON" => "6", 
			"ORG" => "6", 
			"TITLE" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
