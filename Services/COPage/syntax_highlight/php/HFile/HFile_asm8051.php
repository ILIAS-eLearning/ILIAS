<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_asm8051 extends HFile{
   function HFile_asm8051(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// 8051 Assembly
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array("'");
$this->delimiters        	= array("#", "@", "$", "+", "-", "(", ")", "[", "]", ":", ";", "\"", "'", "<", ">", " ", ",", "	", ".", "?");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array(";");
$this->blockcommenton    	= array("/*");
$this->blockcommentoff   	= array("*/");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"ASEG" => "1", 
			"BSEG" => "1", 
			"COMMON" => "1", 
			"CSEG" => "1", 
			"DB" => "1", 
			"DBIT" => "1", 
			"DS" => "1", 
			"DSEG" => "1", 
			"DW" => "1", 
			"END" => "1", 
			"ENDIF" => "1", 
			"ENDMOD" => "1", 
			"ELSE" => "1", 
			"EQU" => "1", 
			"EXTERN" => "1", 
			"EXTRN" => "1", 
			"HIGH" => "1", 
			"ISEG" => "1", 
			"LOW" => "1", 
			"LSTPAG" => "1", 
			"MODULE" => "1", 
			"NAME" => "1", 
			"ORG" => "1", 
			"PAGE" => "1", 
			"PAGSIZ" => "1", 
			"PUBLIC" => "1", 
			"RSEG" => "1", 
			"SEGMENT" => "1", 
			"SET" => "1", 
			"TITEL" => "1", 
			"TITL" => "1", 
			"USING" => "1", 
			"XSEG" => "1", 
			"ACALL" => "2", 
			"ADD" => "2", 
			"ADDC" => "2", 
			"AJMP" => "2", 
			"ANL" => "2", 
			"CALL" => "2", 
			"CJNE" => "2", 
			"CLR" => "2", 
			"CPL" => "2", 
			"DA" => "2", 
			"DEC" => "2", 
			"DIV" => "2", 
			"DJNZ" => "2", 
			"INC" => "2", 
			"JB" => "2", 
			"JBC" => "2", 
			"JC" => "2", 
			"JMP" => "2", 
			"JNC" => "2", 
			"JNB" => "2", 
			"JNZ" => "2", 
			"JZ" => "2", 
			"LCALL" => "2", 
			"LJMP" => "2", 
			"MOV" => "2", 
			"MOVC" => "2", 
			"MOVX" => "2", 
			"MUL" => "2", 
			"NOP" => "2", 
			"ORL" => "2", 
			"POP" => "2", 
			"PUSH" => "2", 
			"RET" => "2", 
			"RETI" => "2", 
			"RL" => "2", 
			"RLC" => "2", 
			"RR" => "2", 
			"RRC" => "2", 
			"SETB" => "2", 
			"SJMP" => "2", 
			"SUBB" => "2", 
			"SWAP" => "2", 
			"XCH" => "2", 
			"XRL" => "2", 
			"A" => "3", 
			"AB" => "3", 
			"ACC" => "3", 
			"B" => "3", 
			"C" => "3", 
			"DPH" => "3", 
			"DPL" => "3", 
			"DPTR" => "3", 
			"R0" => "3", 
			"R1" => "3", 
			"R2" => "3", 
			"R3" => "3", 
			"R4" => "3", 
			"R5" => "3", 
			"R6" => "3", 
			"R7" => "3", 
			"SP" => "3", 
			"PSW" => "3", 
			"+" => "4", 
			"-" => "4", 
			"=" => "4", 
			"@" => "4", 
			"#" => "4", 
			"$" => "4", 
			"[" => "4", 
			"]" => "4");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
