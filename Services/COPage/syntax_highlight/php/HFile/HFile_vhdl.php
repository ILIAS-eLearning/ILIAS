<?php
$BEAUT_PATH = realpath(".")."/Services/COPage/syntax_highlight/php";
if (!isset ($BEAUT_PATH)) return;
require_once("$BEAUT_PATH/Beautifier/HFile.php");
  class HFile_vhdl extends HFile{
   function HFile_vhdl(){
     $this->HFile();	
/*************************************/
// Beautifier Highlighting Configuration File 
// VHDL
/*************************************/
// Flags

$this->nocase            	= "1";
$this->notrim            	= "0";
$this->perl              	= "0";

// Colours

$this->colours        	= array("blue", "purple", "gray", "brown", "blue", "purple");
$this->quotecolour       	= "blue";
$this->blockcommentcolour	= "green";
$this->linecommentcolour 	= "green";

// Indent Strings

$this->indent            	= array();
$this->unindent          	= array();

// String characters and delimiters

$this->stringchars       	= array();
$this->delimiters        	= array("~", "!", "@", "$", "%", "^", "&", "*", "(", ")", "+", "=", "|", "\\", "/", "{", "}", "[", "]", ":", ";", "\"", "<", ">", " ", "	", ",", ".", "?", "/", "	");
$this->escchar           	= "";

// Comment settings

$this->linecommenton     	= array("--");
$this->blockcommenton    	= array("--");
$this->blockcommentoff   	= array("--");

// Keywords (keyword mapping to colour number)

$this->keywords          	= array(
			"abs" => "1", 
			"access" => "1", 
			"after" => "1", 
			"alias" => "1", 
			"all" => "1", 
			"and" => "1", 
			"architecture" => "1", 
			"array" => "1", 
			"assert" => "1", 
			"attribute" => "1", 
			"begin" => "1", 
			"block" => "1", 
			"body" => "1", 
			"buffer" => "1", 
			"bus" => "1", 
			"case" => "1", 
			"component" => "1", 
			"configuration" => "1", 
			"constant" => "1", 
			"disconnect" => "1", 
			"downto" => "1", 
			"else" => "1", 
			"elsif" => "1", 
			"end" => "1", 
			"entity" => "1", 
			"exit" => "1", 
			"file" => "1", 
			"for" => "1", 
			"function" => "1", 
			"generate" => "1", 
			"generic" => "1", 
			"group" => "1", 
			"guarded" => "1", 
			"if" => "1", 
			"impure" => "1", 
			"in" => "1", 
			"inertial" => "1", 
			"inout" => "1", 
			"is" => "1", 
			"label" => "1", 
			"library" => "1", 
			"linkage" => "1", 
			"literal" => "1", 
			"loop" => "1", 
			"map" => "1", 
			"mod" => "1", 
			"nand" => "1", 
			"new" => "1", 
			"next" => "1", 
			"nor" => "1", 
			"not" => "1", 
			"null" => "1", 
			"of" => "1", 
			"on" => "1", 
			"open" => "1", 
			"or" => "1", 
			"others" => "1", 
			"out" => "1", 
			"package" => "1", 
			"port" => "1", 
			"postponed" => "1", 
			"procedure" => "1", 
			"process" => "1", 
			"pure" => "1", 
			"range" => "1", 
			"record" => "1", 
			"register" => "1", 
			"reject" => "1", 
			"rem" => "1", 
			"report" => "1", 
			"return" => "1", 
			"rol" => "1", 
			"ror" => "1", 
			"select" => "1", 
			"severity" => "1", 
			"signal" => "1", 
			"shared" => "1", 
			"sla" => "1", 
			"sll" => "1", 
			"sra" => "1", 
			"srl" => "1", 
			"subtype" => "1", 
			"then" => "1", 
			"to" => "1", 
			"transport" => "1", 
			"type" => "1", 
			"unaffected" => "1", 
			"units" => "1", 
			"until" => "1", 
			"use" => "1", 
			"variable" => "1", 
			"wait" => "1", 
			"when" => "1", 
			"while" => "1", 
			"with" => "1", 
			"xnor" => "1", 
			"xor" => "1", 
			"bit" => "2", 
			"bit_vector" => "2", 
			"boolean" => "2", 
			"integer" => "2", 
			"real" => "2", 
			"std_logic" => "2", 
			"std_logic_vector" => "2", 
			"=" => "3", 
			"<" => "3", 
			">" => "3", 
			":" => "3", 
			"\'event" => "4", 
			"\'right" => "4", 
			"ActivPullUp" => "5", 
			"AndN" => "5", 
			"And2FF" => "5", 
			"AndNFF" => "5", 
			"Cnt1Bit" => "5", 
			"CntNBit" => "5", 
			"CntNBitDown" => "5", 
			"CntNBitMod" => "5", 
			"CntNBitOe" => "5", 
			"CntNBitSLd" => "5", 
			"CntNBitSR" => "5", 
			"CntNBitUpDown" => "5", 
			"CompNBit" => "5", 
			"CompNBitFF" => "5", 
			"DiffH2LWithFF" => "5", 
			"DiffL2HWithFF" => "5", 
			"Dff1" => "5", 
			"Dff1NegClk" => "5", 
			"Dffn" => "5", 
			"Encode4to5" => "5", 
			"Mux1of2" => "5", 
			"Mux1of8" => "5", 
			"Mux1Vof2V" => "5", 
			"Mux1Vof3V" => "5", 
			"Mux1Vof4V" => "5", 
			"PreScale1Bit" => "5", 
			"PreScale1BitAR" => "5", 
			"PreScale1BitARNegClk" => "5", 
			"PreScaleNBit" => "5", 
			"PreScaleNBitAR" => "5", 
			"Reg1Bit" => "5", 
			"Reg1BitAR" => "5", 
			"Reg1BitR" => "5", 
			"RegNBit" => "5", 
			"RegNBitAR" => "5", 
			"RSFFAsync" => "5", 
			"RSFFsync" => "5", 
			"RsSynchronizer" => "5", 
			"ShiftP2SRegNBitAR" => "5", 
			"ShiftRegNBitAR" => "5", 
			"ShiftS2SRegNBit" => "5", 
			"SRFFsync" => "5", 
			"SyncAndDiffL2HWithFF" => "5", 
			"SyncAndDiffH2LWithFF" => "5", 
			"SyncAndDiffL2HWithFFAndFg" => "5", 
			"SyncAndDiffH2LWithFFAndFg" => "5", 
			"SyncAndDiffLL2HHWithFF" => "5", 
			"SyncAndDiffHH2LLWithFF" => "5", 
			"SyncAndDiffLL2HHWithFFAndFg" => "5", 
			"SyncAndDiffHH2LLWithFFAndFg" => "5", 
			"ActivPullUp_arch" => "6", 
			"AndN_arch" => "6", 
			"And2FF_arch" => "6", 
			"AndNFF_arch" => "6", 
			"Cnt1Bit_arch" => "6", 
			"CntNBit_arch" => "6", 
			"CntNBitDown_arch" => "6", 
			"CntNBitMod_arch" => "6", 
			"CntNBitOe_arch" => "6", 
			"CntNBitSLd_arch" => "6", 
			"CntNBitSR_arch" => "6", 
			"CntNBitUpDown_arch" => "6", 
			"CompNBit_arch" => "6", 
			"CompNBitFF_arch" => "6", 
			"DiffH2LWithFF_arch" => "6", 
			"DiffL2HWithFF_arch" => "6", 
			"Dff1_arch" => "6", 
			"Dff1NegClk_arch" => "6", 
			"Dffn_arch" => "6", 
			"Encode4to5_arch" => "6", 
			"Mux1of2_arch" => "6", 
			"Mux1of8_arch" => "6", 
			"Mux1Vof2V_arch" => "6", 
			"Mux1Vof3V_arch" => "6", 
			"Mux1Vof4V_arch" => "6", 
			"PreScale1Bit_arch" => "6", 
			"PreScale1BitAR_arch" => "6", 
			"PreScale1BitARNegClk_arch" => "6", 
			"PreScaleNBit_arch" => "6", 
			"PreScaleNBitAR_arch" => "6", 
			"Reg1Bit_arch" => "6", 
			"Reg1BitAR_arch" => "6", 
			"Reg1BitR_arch" => "6", 
			"RegNBit_arch" => "6", 
			"RegNBitAR_arch" => "6", 
			"RSFFAsync_arch" => "6", 
			"RSFFsync_arch" => "6", 
			"RsSynchronizer_arch" => "6", 
			"ShiftP2SRegNBitAR_arch" => "6", 
			"ShiftRegNBitAR_arch" => "6", 
			"ShiftS2SRegNBit_arch" => "6", 
			"SRFFsync_arch" => "6", 
			"SyncAndDiffL2HWithFF_arch" => "6", 
			"SyncAndDiffH2LWithFF_arch" => "6", 
			"SyncAndDiffL2HWithFFAndFg_arch" => "6", 
			"SyncAndDiffH2LWithFFAndFg_arch" => "6", 
			"SyncAndDiffLL2HHWithFF_arch" => "6", 
			"SyncAndDiffHH2LLWithFF_arch" => "6", 
			"SyncAndDiffLL2HHWithFFAndFg_arch" => "6", 
			"SyncAndDiffHH2LLWithFFAndFg_arch" => "6");

// Special extensions

// Each category can specify a PHP function that returns an altered
// version of the keyword.
        
        

$this->linkscripts    	= array(
			"1" => "donothing", 
			"2" => "donothing", 
			"3" => "donothing", 
			"4" => "donothing", 
			"5" => "donothing", 
			"6" => "donothing");
}


function donothing($keywordin)
{
	return $keywordin;
}

}?>
